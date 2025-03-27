const express = require('express');
const fs = require('fs')
const app = express();
const morgan=require('morgan');
const port = 5000;

app.use(morgan('dev'))
app.use(express.json());
app.use((req, res, next) => {
    req.requestTime = new Date().toISOString();
    console.log('Hello from the middleware 👋');
    next();
  });
  
const weather = JSON.parse(fs.readFileSync(`${__dirname}/dev-data/weather.json`))


const getAll=(req, res) => {
    console.log(req.requestTime)
    res.status(200).json({
        status: 'Yes',
        requestedAt:req.requestTime,
        results: weather.length,
        data: {
            weather: weather
        }
    })

}


const getOne=(req, res) => {
    console.log(req.params)
    
    const id = req.params.id * 1;
const weather2 = weather.find(el => el.id === id);
if (id > weather.length) {
    return res.status(404).json({
      status: 'fail',
      message: 'Invalid ID'
    });
  }

  

    res.status(200).json({
        status: 'Yes',
        // results: tours.length,
        data: {
            weather
        }
    })

};


const createOne=(req, res) => {
    // console.log(req.body);
  
    const newId = weather[weather.length - 1].id + 1;
    const newW = Object.assign({ id: newId }, req.body);
  
    weather.push(newW);
  
  fs.writeFile(
    `${__dirname}/dev-data/weather.json`,
    JSON.stringify(weather),
    err => {
      res.status(201).json({
        status: 'success',
        data: {
          weather: newW
        }
      });
    }
  );
  }
  


app.post('/api/v1/weather', (req, res) => {
    console.log(req.body)
    res.send('done')

});
const UpdateOne=(req, res) => {
    if (req.params.id *1> weather.length) {
        return res.status(404).json({
          status: 'fail',
          message: 'Invalid ID'
        });
      }
      
    res.status(200).json({
        status: 'Yes',
        results: weather.length,
        data: {
            weather: '<update weather>'
        }
    })

};

const deleteOne=(req, res) => {
    if (req.params.id *1> weather.length) {
        return res.status(404).json({
          status: 'fail',
          message: 'Invalid ID'
        });
      }
      
    res.status(200).json({
        status: 'Yes',
        results: weather.length,
        data:null
    })

};




app.get('/api/v1/weather',getAll )
app.get('/api/v1/weather/:id',getOne) 
app.post('/api/v1/weather',createOne)
app.patch('/api/v1/weather/:id',UpdateOne ) 
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


app.listen(port, () => {
    console.log(`App running on http://localhost:${port}`);
});