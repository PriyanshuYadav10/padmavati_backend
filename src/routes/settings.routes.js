const express = require('express');
const settingsController = require('../controllers/settings.controller');
const validate = require('../middleware/validate.middleware');
const { saveSettingsRules } = require('../validators/settings.validator');

const router = express.Router();

router.get('/', settingsController.get);
router.post('/', validate(saveSettingsRules), settingsController.save);

module.exports = router;
