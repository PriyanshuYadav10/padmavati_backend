const express = require('express');
const contactController = require('../controllers/contact.controller');
const validate = require('../middleware/validate.middleware');
const { protect } = require('../middleware/auth.middleware');
const {
  createContactRules,
  updateContactRules,
  idParamRule,
  searchRules,
} = require('../validators/contact.validator');

const router = express.Router();

// `protect` is a no-op while AUTH_ENABLED=false, ready to enforce later.
router.use(protect);

router.get('/search', validate(searchRules), contactController.search);
router.get('/', contactController.getAll);
router.post('/', validate(createContactRules), contactController.create);
router.get('/:id', validate(idParamRule), contactController.getById);
router.put('/:id', validate(updateContactRules), contactController.update);
router.delete('/:id', validate(idParamRule), contactController.remove);

module.exports = router;
