const mongoose = require('mongoose');
const dbConfig = require('../config/db_config');

mongoose
    .connect(dbConfig.MONGO_URI, {
        useNewUrlParser: true,
        useFindAndModify: false
    })
    .then(() => {
        console.log('MongoDB database connected!');
    })
    .catch(err => {
        console.error('MongoDB error!', err);
    });
