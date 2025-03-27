const port = 3000;

// const dotenv=require('dotenv');
const app=require('./route')
// dotenv.config
console.log(app.get('env'))
app.listen(port, () => {
    console.log(`App running on http://localhost:${port}`);
});