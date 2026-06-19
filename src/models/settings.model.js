const mongoose = require('mongoose');

/**
 * App-level settings. Although the passcode is primarily managed LOCALLY on the
 * device, these endpoints allow optional server-side backup/sync of settings.
 * The passcode hash is never returned to clients.
 */
const settingsSchema = new mongoose.Schema(
  {
    // A single global settings doc is used; key keeps it findable/unique.
    key: {
      type: String,
      default: 'app',
      unique: true,
    },
    passcodeEnabled: {
      type: Boolean,
      default: false,
    },
    passcodeHash: {
      type: String,
      default: null,
      select: false, // never leaks in normal queries
    },
    preferences: {
      type: mongoose.Schema.Types.Mixed,
      default: {},
    },
  },
  {
    timestamps: true,
    toJSON: {
      transform(doc, ret) {
        ret.id = ret._id;
        delete ret._id;
        delete ret.__v;
        delete ret.passcodeHash;
        return ret;
      },
    },
  }
);

module.exports = mongoose.model('Settings', settingsSchema);
