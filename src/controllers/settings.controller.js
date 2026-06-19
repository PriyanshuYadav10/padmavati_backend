const asyncHandler = require('../utils/asyncHandler');
const { sendResponse } = require('../utils/ApiResponse');
const settingsService = require('../services/settings.service');

const settingsController = {
  // GET /api/settings
  get: asyncHandler(async (req, res) => {
    const settings = await settingsService.get();
    sendResponse(res, { message: 'Settings fetched', data: settings });
  }),

  // POST /api/settings
  save: asyncHandler(async (req, res) => {
    const settings = await settingsService.save(req.body);
    sendResponse(res, { message: 'Settings saved', data: settings });
  }),
};

module.exports = settingsController;
