const express = require('express');
const vendorRoutes = require('./vendor.routes');
const settingsRoutes = require('./settings.routes');

const router = express.Router();

router.get('/health', (req, res) => {
  res.json({ success: true, message: 'Padmavati Bangles API is healthy', timestamp: new Date().toISOString() });
});

router.use('/vendors', vendorRoutes);
router.use('/settings', settingsRoutes);

module.exports = router;
