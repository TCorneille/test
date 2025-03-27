const express = require('express');
const app = express();
const port = 3000;

app.get('/', (req, res) => {
    res.status(200).json({message:'Hello, I am the server', app:'express'});
});
app.post('/', (req, res) => {
    res.send('you can post something here');
});
app.listen(port, () => {
    console.log(`App running on http://localhost:${port}`);
});
