const { body, param, query } = require('express-validator');

const createVendorRules = [
  body('name').trim().notEmpty().withMessage('Name is required').isLength({ max: 120 }),
  body('phone')
    .trim()
    .notEmpty()
    .withMessage('Phone is required')
    .matches(/^[+\d][\d\s\-()]{5,19}$/)
    .withMessage('Phone must be a valid number'),
  body('location').optional().trim().isLength({ max: 200 }),
  body('city').optional().trim().isLength({ max: 100 }),
  body('businessType').optional().trim().isLength({ max: 100 }),
  body('notes').optional().trim().isLength({ max: 1000 }),
];

const updateVendorRules = [
  param('id').isMongoId().withMessage('Invalid vendor id'),
  body('name').optional().trim().notEmpty().withMessage('Name cannot be empty').isLength({ max: 120 }),
  body('phone')
    .optional()
    .trim()
    .matches(/^[+\d][\d\s\-()]{5,19}$/)
    .withMessage('Phone must be a valid number'),
  body('location').optional().trim().isLength({ max: 200 }),
  body('city').optional().trim().isLength({ max: 100 }),
  body('businessType').optional().trim().isLength({ max: 100 }),
  body('notes').optional().trim().isLength({ max: 1000 }),
];

const idParamRule = [param('id').isMongoId().withMessage('Invalid vendor id')];

const searchRules = [query('q').optional().trim().isLength({ max: 200 })];

module.exports = { createVendorRules, updateVendorRules, idParamRule, searchRules };
