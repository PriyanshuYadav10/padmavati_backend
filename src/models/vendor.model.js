const mongoose = require('mongoose');

const vendorSchema = new mongoose.Schema(
  {
    name: {
      type: String,
      required: [true, 'Vendor name is required'],
      trim: true,
      maxlength: [120, 'Name cannot exceed 120 characters'],
    },
    phone: {
      type: String,
      required: [true, 'Phone number is required'],
      trim: true,
      maxlength: [20, 'Phone cannot exceed 20 characters'],
    },
    location: {
      type: String,
      trim: true,
      default: '',
      maxlength: [200, 'Location cannot exceed 200 characters'],
    },
    city: {
      type: String,
      trim: true,
      default: '',
      maxlength: [100, 'City cannot exceed 100 characters'],
    },
    businessType: {
      type: String,
      trim: true,
      default: '',
      maxlength: [100, 'Business type cannot exceed 100 characters'],
    },
    notes: {
      type: String,
      trim: true,
      default: '',
      maxlength: [1000, 'Notes cannot exceed 1000 characters'],
    },
  },
  {
    timestamps: true, // adds createdAt / updatedAt
    toJSON: {
      virtuals: true,
      transform(doc, ret) {
        ret.id = ret._id;
        delete ret._id;
        delete ret.__v;
        return ret;
      },
    },
  }
);

// Text index powers the free-text search endpoint.
vendorSchema.index({
  name: 'text',
  phone: 'text',
  location: 'text',
  city: 'text',
  businessType: 'text',
  notes: 'text',
});

module.exports = mongoose.model('Vendor', vendorSchema);
