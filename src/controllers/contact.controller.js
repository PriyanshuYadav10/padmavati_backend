const asyncHandler = require('../utils/asyncHandler');
const { sendResponse } = require('../utils/ApiResponse');
const contactService = require('../services/contact.service');

const contactController = {
  // POST /api/contacts
  create: asyncHandler(async (req, res) => {
    const contact = await contactService.create(req.body);
    sendResponse(res, { statusCode: 201, message: 'Contact created', data: contact });
  }),

  // GET /api/contacts
  getAll: asyncHandler(async (req, res) => {
    const { items, meta } = await contactService.findAll(req.query);
    sendResponse(res, { message: 'Contacts fetched', data: items, meta });
  }),

  // GET /api/contacts/search?q=
  search: asyncHandler(async (req, res) => {
    const items = await contactService.search(req.query.q);
    sendResponse(res, { message: 'Search results', data: items, meta: { total: items.length, q: req.query.q || '' } });
  }),

  // GET /api/contacts/:id
  getById: asyncHandler(async (req, res) => {
    const contact = await contactService.findById(req.params.id);
    sendResponse(res, { message: 'Contact fetched', data: contact });
  }),

  // PUT /api/contacts/:id
  update: asyncHandler(async (req, res) => {
    const contact = await contactService.update(req.params.id, req.body);
    sendResponse(res, { message: 'Contact updated', data: contact });
  }),

  // DELETE /api/contacts/:id
  remove: asyncHandler(async (req, res) => {
    await contactService.remove(req.params.id);
    sendResponse(res, { message: 'Contact deleted', data: { id: req.params.id } });
  }),
};

module.exports = contactController;
