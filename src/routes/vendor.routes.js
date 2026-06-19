const express = require('express');
const vendorController = require('../controllers/vendor.controller');
const validate = require('../middleware/validate.middleware');
const { protect } = require('../middleware/auth.middleware');
const {
  createVendorRules,
  updateVendorRules,
  idParamRule,
  searchRules,
} = require('../validators/vendor.validator');

const router = express.Router();

// `protect` is a no-op while AUTH_ENABLED=false, ready to enforce later.
router.use(protect);

router.get('/search', validate(searchRules), vendorController.search);
router.get('/', vendorController.getAll);
router.post('/', validate(createVendorRules), vendorController.create);
router.get('/:id', validate(idParamRule), vendorController.getById);
router.put('/:id', validate(updateVendorRules), vendorController.update);
router.delete('/:id', validate(idParamRule), vendorController.remove);

module.exports = router;
