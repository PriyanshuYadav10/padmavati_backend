/**
 * Seed script: populates the DB with sample contacts for quick testing.
 * Usage: npm run seed
 */
const mongoose = require('mongoose');
const connectDB = require('../config/db');
const Contact = require('../models/contact.model');

const sampleContacts = [
  { name: 'Shree Bangle House', phone: '+919812345670', location: 'Johari Bazaar', city: 'Jaipur', businessType: 'Wholesale', notes: 'Best lac bangles' },
  { name: 'Rajwadi Churi Bhandar', phone: '+919812345671', location: 'Bapu Bazaar', city: 'Jaipur', businessType: 'Retail', notes: 'Bulk orders welcome' },
  { name: 'Meena Glass Works', phone: '+919812345672', location: 'Firozabad Road', city: 'Firozabad', businessType: 'Manufacturer', notes: 'Glass bangles supplier' },
  { name: 'Krishna Bangles', phone: '+919812345673', location: 'MG Road', city: 'Agra', businessType: 'Distributor', notes: '' },
  { name: 'Laxmi Suhaag', phone: '+919812345674', location: 'Sadar Bazaar', city: 'Delhi', businessType: 'Retail', notes: 'Bridal sets' },
];

(async () => {
  try {
    await connectDB();
    await Contact.deleteMany({});
    await Contact.insertMany(sampleContacts);
    console.log(`✅ Seeded ${sampleContacts.length} contacts`);
  } catch (err) {
    console.error('Seed failed:', err.message);
  } finally {
    await mongoose.connection.close();
    process.exit(0);
  }
})();
