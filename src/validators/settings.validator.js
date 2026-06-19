const { body } = require('express-validator');

const saveSettingsRules = [
  body('passcodeEnabled').optional().isBoolean().withMessage('passcodeEnabled must be boolean'),
  body('passcode')
    .optional({ nullable: true })
    .isString()
    .isLength({ min: 4, max: 12 })
    .withMessage('Passcode must be 4-12 characters'),
  body('preferences').optional().isObject().withMessage('preferences must be an object'),
];

module.exports = { saveSettingsRules };
