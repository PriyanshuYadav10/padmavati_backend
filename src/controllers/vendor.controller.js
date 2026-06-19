const asyncHandler = require('../utils/asyncHandler');
const { sendResponse } = require('../utils/ApiResponse');
const vendorService = require('../services/vendor.service');

const vendorController = {
  // POST /api/vendors
  create: asyncHandler(async (req, res) => {
    const vendor = await vendorService.create(req.body);
    sendResponse(res, { statusCode: 201, message: 'Vendor created', data: vendor });
  }),

  // GET /api/vendors
  getAll: asyncHandler(async (req, res) => {
    const { items, meta } = await vendorService.findAll(req.query);
    sendResponse(res, { message: 'Vendors fetched', data: items, meta });
  }),

  // GET /api/vendors/search?q=
  search: asyncHandler(async (req, res) => {
    const items = await vendorService.search(req.query.q);
    sendResponse(res, { message: 'Search results', data: items, meta: { total: items.length, q: req.query.q || '' } });
  }),

  // GET /api/vendors/:id
  getById: asyncHandler(async (req, res) => {
    const vendor = await vendorService.findById(req.params.id);
    sendResponse(res, { message: 'Vendor fetched', data: vendor });
  }),

  // PUT /api/vendors/:id
  update: asyncHandler(async (req, res) => {
    const vendor = await vendorService.update(req.params.id, req.body);
    sendResponse(res, { message: 'Vendor updated', data: vendor });
  }),

  // DELETE /api/vendors/:id
  remove: asyncHandler(async (req, res) => {
    await vendorService.remove(req.params.id);
    sendResponse(res, { message: 'Vendor deleted', data: { id: req.params.id } });
  }),
};

module.exports = vendorController;
