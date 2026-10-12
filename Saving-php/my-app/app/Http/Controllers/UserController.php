<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManagerStatic as Image;

class UserController extends Controller
{
    // ------------------- MULTER / SHARP EQUIVALENT -------------------

    /**
     * Handles uploading and resizing the user profile photo.
     */
    private function handlePhotoUpload(Request $request): ?string
    {
        if (!$request->hasFile('photo')) {
            return null;
        }

        $file = $request->file('photo');

        // Validate image type
        if (!str_starts_with($file->getMimeType(), 'image/')) {
            abort(400, 'Not an image! Please upload only images.');
        }

        // Generate custom filename
        $filename = sprintf('user-%s-%d.jpeg', auth()->id(), time() * 1000);
        $destinationPath = public_path('img/users/' . $filename);

        // Ensure directory exists
        if (!file_exists(public_path('img/users'))) {
            mkdir(public_path('img/users'), 0755, true);
        }

        // Resize and format image using Intervention Image
        Image::make($file)
            ->fit(500, 500)
            ->encode('jpeg', 90)
            ->save($destinationPath);

        return $filename;
    }

    // ------------------- USER SELF-ACTIONS -------------------

    public function getMe(Request $request)
    {
        return response()->json([
            'status' => 'success',
            'data' => [
                'user' => $request->user(),
            ],
        ], 200);
    }

    public function updateMe(Request $request)
    {
        // 1) Block password updates
        if ($request->has('password') || $request->has('passwordConfirm')) {
            return response()->json([
                'status' => 'error',
                'message' => 'This route is not for password updates. Please use /updateMyPassword.',
            ], 400);
        }

        // 2) Filter allowed fields
        $filteredData = $request->only(['name', 'email']);

        // Handle photo if uploaded
        $filename = $this->handlePhotoUpload($request);
        if ($filename) {
            $filteredData['photo'] = $filename;
        }

        // 3) Update user document
        $user = $request->user();
        $user->update($filteredData);

        return response()->json([
            'status' => 'success',
            'data' => [
                'user' => $user->fresh(),
            ],
        ], 200);
    }

    public function deleteMe(Request $request)
    {
        $user = $request->user();
        $user->update(['active' => false]);

        return response()->json(null, 204);
    }

    // ------------------- ADMIN / FACTORY HANDLERS -------------------

    public function getAllUsers()
    {
        $users = User::all();

        return response()->json([
            'status' => 'success',
            'results' => $users->count(),
            'data' => [
                'users' => $users,
            ],
        ], 200);
    }

    public function getUser($id)
    {
        $user = User::findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data' => [
                'user' => $user,
            ],
        ], 200);
    }

    public function createUser(Request $request)
    {
        return response()->json([
            'status' => 'error',
            'message' => 'This route is not yet defined! Please use /signup instead.',
        ], 500);
    }

    public function updateUser(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $user->update($request->all());

        return response()->json([
            'status' => 'success',
            'data' => [
                'user' => $user,
            ],
        ], 200);
    }

    public function deleteUser($id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return response()->json(null, 204);
    }
}