const mongoose = require('mongoose');

const productSchema = new mongoose.Schema({
    user_id: {
        type: String,
        required: true
    },
    name: {
        type: String,
        required: true
    },
    brand: {
        type: String,
        required: true
    },
    category: {
        type: String,
        required: true
    },
    description: {
        type: String,
        default: ''
    },
    first_impressions: {
        type: String,
        default: ''
    },
    rating: {
        type: Number,
        default: 0
    },
    photos: {
        type: [String],
        default: []
    },
    remaining_amount: {
        type: Number,
        default: 100
    },
    uses_count: {
        type: Number,
        default: 0
    },
    pan: {
        type: Boolean,
        default: false
    },
    bought_at: {
        type: Date,
        default: 0
    },
    expire_months: {
        type: Number,
        default: 0
    }
});

module.exports = Product = mongoose.model('product', productSchema);