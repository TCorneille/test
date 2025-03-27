const express = require('express');
const fs = require('fs')
const app = express();
const morgan=require('morgan');
const port = 3000;

app.use(morgan('dev'))
app.use(express.json());
app.use((req, res, next) => {
    req.requestTime = new Date().toISOString();
    console.log('Hello from the middleware 👋');
    next();
  });
  
const tours = JSON.parse(fs.readFileSync(`${__dirname}/dev-data/data/tours-simple.json`))


const getAll=(req, res) => {
    console.log(req.requestTime)
    res.status(200).json({
        status: 'Yes',
        requestedAt:req.requestTime,
        results: tours.length,
        data: {
            tours: tours
        }
    })

}


const getOne=(req, res) => {
    console.log(req.params)
    
    const id = req.params.id * 1;
const tour = tours.find(el => el.id === id);
if (id > tours.length) {
    return res.status(404).json({
      status: 'fail',
      message: 'Invalid ID'
    });
  }
  

    res.status(200).json({
        status: 'Yes',
        // results: tours.length,
        data: {
            tour
        }
    })

};


const createOne=(req, res) => {
    // console.log(req.body);
  
    const newId = tours[tours.length - 1].id + 1;
    const newTour = Object.assign({ id: newId }, req.body);
  
    tours.push(newTour);
  
  fs.writeFile(
    `${__dirname}/dev-data/data/tours-simple.json`,
    JSON.stringify(tours),
    err => {
      res.status(201).json({
        status: 'success',
        data: {
          tour: newTour
        }
      });
    }
  );
  }
  


// app.post('/api/v1/tours', (req, res) => {
//     console.log(req.body)
//     res.send('done')

// });
const UpdateOne=(req, res) => {
    if (req.params.id *1> tours.length) {
        return res.status(404).json({
          status: 'fail',
          message: 'Invalid ID'
        });
      }
      
    res.status(200).json({
        status: 'Yes',
        results: tours.length,
        data: {
            tours: '<update tours>'
        }
    })

};

const deleteOne=(req, res) => {
    if (req.params.id *1> tours.length) {
        return res.status(404).json({
          status: 'fail',
          message: 'Invalid ID'
        });
      }
      
    res.status(200).json({
        status: 'Yes',
        results: tours.length,
        data:null
    })

};




app.get('/api/v1/tours',getAll )
app.get('/api/v1/tours/:id',getOne) 
app.post('/api/v1/tours',createOne)
app.patch('/api/v1/tours/:id',UpdateOne ) 
app.delete('/api/v1/tours/:id',deleteOne )


app
  .route('/api/v1/tours')
  .get(getAll)
  .post(createOne);

app
  .route('/api/v1/tours/:id')
  .get(getOne)
  .patch(UpdateOne)
  .delete(deleteOne);


app.listen(port, () => {
    console.log(`App running on http://localhost:${port}`);
});