<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    // ------------------ TOKEN HELPER ------------------

    /**
     * Issues a Sanctum token and sets an HTTP-only cookie (optional/recommended).
     */
    private function createSendToken(User $user, int $statusCode, string $message = 'Success')
    {
        // 1) Generate Sanctum Plain Text Token
        $token = $user->createToken('auth_token')->plainTextToken;

        // 2) Prepare HTTP-only cookie (matches Express res.cookie) - 30 days default
        $cookieMinutes = 60 * 24 * 30; 
        
        $cookie = cookie(
            'jwt',
            $token,
            $cookieMinutes,
            '/',
            config('session.domain'),
            config('app.env') === 'production', // Secure in production
            true,  // HttpOnly
            false, // Raw
            'Lax'  // SameSite
        );

        // Hide sensitive fields before returning
        $user->makeHidden(['password', 'remember_token']);

        return response()->json([
            'status'  => 'success',
            'message' => $message,
            'token'   => $token,
            'data'    => [
                'user' => $user,
            ],
        ], $statusCode)->withCookie($cookie);
    }

    // ------------------ AUTH CONTROLLERS ------------------

    public function signup(Request $request)
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'role'     => 'nullable|string|in:user,guide,lead-guide,admin',
        ]);

        $user = User::create($validated);

        return $this->createSendToken($user, 201, 'User registered successfully');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Incorrect email or password.'],
            ]);
        }

        if (!$user->active) {
            return response()->json([
                'status'  => 'fail',
                'message' => 'Your account has been deactivated.',
            ], 403);
        }

        return $this->createSendToken($user, 200, 'Logged in successfully');
    }

    public function forgotPassword(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'status'  => 'fail',
                'message' => 'No user found with that email address.',
            ], 404);
        }

        // Sends Laravel's native password reset email notification
        $status = Password::sendResetLink($request->only('email'));

        if ($status === Password::RESET_LINK_SENT) {
            return response()->json([
                'status'  => 'success',
                'message' => 'Token sent to email!',
            ], 200);
        }

        return response()->json([
            'status'  => 'error',
            'message' => 'There was an error sending the email. Try again later!',
        ], 500);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token'    => 'required|string',
            'email'    => 'required|email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => $password,
                    'remember_token' => Str::random(60),
                ])->save();
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            $user = User::where('email', $request->email)->first();
            return $this->createSendToken($user, 200, 'Password reset successful');
        }

        return response()->json([
            'status'  => 'fail',
            'message' => 'Token is invalid or has expired',
        ], 400);
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'currentPassword' => 'required|string',
            'newPassword'     => 'required|string|min:8',
        ]);

        $user = $request->user();

        if (!Hash::check($request->currentPassword, $user->password)) {
            return response()->json([
                'status'  => 'fail',
                'message' => 'Your current password is wrong.',
            ], 401);
        }

        $user->password = $request->newPassword;
        $user->save();

        return $this->createSendToken($user, 200, 'Password updated successfully');
    }

    public function logout(Request $request)
    {
        // Revoke current token
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'Logged out successfully',
        ], 200)->withoutCookie('jwt');
    }
}