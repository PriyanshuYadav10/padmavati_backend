const Vendor = require('../models/vendor.model');
const ApiError = require('../utils/ApiError');

/**
 * Vendor service — all DB access for vendors lives here (separation from
 * controllers keeps the HTTP layer thin and the logic testable).
 */
const vendorService = {
  async create(payload) {
    return Vendor.create(payload);
  },

  async findAll({ page = 1, limit = 50, sort = '-createdAt' } = {}) {
    const skip = (Number(page) - 1) * Number(limit);
    const [items, total] = await Promise.all([
      Vendor.find().sort(sort).skip(skip).limit(Number(limit)),
      Vendor.countDocuments(),
    ]);
    return {
      items,
      meta: { total, page: Number(page), limit: Number(limit), pages: Math.ceil(total / Number(limit)) },
    };
  },

  async findById(id) {
    const vendor = await Vendor.findById(id);
    if (!vendor) throw ApiError.notFound('Vendor not found');
    return vendor;
  },

  async update(id, payload) {
    const vendor = await Vendor.findByIdAndUpdate(id, payload, {
      new: true,
      runValidators: true,
    });
    if (!vendor) throw ApiError.notFound('Vendor not found');
    return vendor;
  },

  async remove(id) {
    const vendor = await Vendor.findByIdAndDelete(id);
    if (!vendor) throw ApiError.notFound('Vendor not found');
    return vendor;
  },

  /**
   * Search across all fields. Uses a case-insensitive regex OR so partial
   * matches work for name/phone/location/city/businessType/notes.
   */
  async search(q) {
    if (!q || !q.trim()) {
      return Vendor.find().sort('-createdAt');
    }
    const safe = q.trim().replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    const rx = new RegExp(safe, 'i');
    return Vendor.find({
      $or: [
        { name: rx },
        { phone: rx },
        { location: rx },
        { city: rx },
        { businessType: rx },
        { notes: rx },
      ],
    }).sort('-createdAt');
  },
};

module.exports = vendorService;
