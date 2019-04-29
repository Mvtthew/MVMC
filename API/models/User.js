const mongoose = require('mongoose');

const userSchema = new mongoose.Schema({
    name: {
        type: String,
        required: true
    },
    email: {
        type: String,
        required: true
    },
    password: {
        type: String,
        required: true
    },
    exp: {
        type: Number,
        default: 0
    },
    exp_next: {
        type: Number,
        default: 150
    },
    level: {
        type: Number,
        default: 1
    }
});

module.exports = User = mongoose.model('user', userSchema);
