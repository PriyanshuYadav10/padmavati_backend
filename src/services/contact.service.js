const Contact = require('../models/contact.model');
const ApiError = require('../utils/ApiError');

/**
 * Contact service — all DB access for contacts lives here (separation from
 * controllers keeps the HTTP layer thin and the logic testable).
 */
const contactService = {
  async create(payload) {
    return Contact.create(payload);
  },

  async findAll({ page = 1, limit = 50, sort = '-createdAt' } = {}) {
    const skip = (Number(page) - 1) * Number(limit);
    const [items, total] = await Promise.all([
      Contact.find().sort(sort).skip(skip).limit(Number(limit)),
      Contact.countDocuments(),
    ]);
    return {
      items,
      meta: { total, page: Number(page), limit: Number(limit), pages: Math.ceil(total / Number(limit)) },
    };
  },

  async findById(id) {
    const contact = await Contact.findById(id);
    if (!contact) throw ApiError.notFound('Contact not found');
    return contact;
  },

  async update(id, payload) {
    const contact = await Contact.findByIdAndUpdate(id, payload, {
      new: true,
      runValidators: true,
    });
    if (!contact) throw ApiError.notFound('Contact not found');
    return contact;
  },

  async remove(id) {
    const contact = await Contact.findByIdAndDelete(id);
    if (!contact) throw ApiError.notFound('Contact not found');
    return contact;
  },

  /**
   * Search across all fields. Uses a case-insensitive regex OR so partial
   * matches work for name/phone/location/city/businessType/notes.
   */
  async search(q) {
    if (!q || !q.trim()) {
      return Contact.find().sort('-createdAt');
    }
    const safe = q.trim().replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    const rx = new RegExp(safe, 'i');
    return Contact.find({
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

module.exports = contactService;
