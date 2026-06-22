const express = require('express');
const contactRoutes = require('./contact.routes');
const settingsRoutes = require('./settings.routes');

const router = express.Router();

router.get('/health', (req, res) => {
  res.json({ success: true, message: 'Padmavati Bangles API is healthy', timestamp: new Date().toISOString() });
});

router.use('/contacts', contactRoutes);
router.use('/settings', settingsRoutes);

module.exports = router;
