const express = require('express');
const fs = require('fs')
const app = express();
const morgan=require('morgan');


const tourRouter=require('./tourRoutes');

app.use(morgan('dev'))
app.use(express.json());

app.use(express.static(`${__dirname}/public`));

app.use((req, res, next) => {
    req.requestTime = new Date().toISOString();
    console.log('Hello from the middleware 👋');
    next();
  });
  




// app.get('/api/v1/tours',getAll )
// app.get('/api/v1/tours/:id',getOne) 
// app.post('/api/v1/tours',createOne)
// app.patch('/api/v1/tours/:id',UpdateOne ) 
// app.delete('/api/v1/tours/:id',deleteOne )


// app
//   .route('/api/v1/tours')
//   .get(getAll)
//   .post(createOne);

// app
//   .route('/api/v1/tours/:id')
//   .get(getOne)
//   .patch(UpdateOne)
//   .delete(deleteOne);



  app.use('/api/v1/tours', tourRouter);




module.exports=app