const bcrypt = require('bcryptjs');
const Settings = require('../models/settings.model');

const SETTINGS_KEY = 'app';

const settingsService = {
  async get() {
    let settings = await Settings.findOne({ key: SETTINGS_KEY });
    if (!settings) {
      settings = await Settings.create({ key: SETTINGS_KEY });
    }
    return settings;
  },

  async save({ passcodeEnabled, passcode, preferences }) {
    const settings = await this.get();

    if (typeof passcodeEnabled === 'boolean') {
      settings.passcodeEnabled = passcodeEnabled;
      if (!passcodeEnabled) {
        settings.passcodeHash = null; // disabling clears the stored hash
      }
    }

    if (passcode) {
      settings.passcodeHash = await bcrypt.hash(passcode, 10);
      settings.passcodeEnabled = true;
    }

    if (preferences && typeof preferences === 'object') {
      settings.preferences = { ...settings.preferences, ...preferences };
    }

    await settings.save();
    return settings;
  },

  /** Optional helper for future server-side passcode verification. */
  async verifyPasscode(passcode) {
    const settings = await Settings.findOne({ key: SETTINGS_KEY }).select('+passcodeHash');
    if (!settings || !settings.passcodeHash) return false;
    return bcrypt.compare(passcode, settings.passcodeHash);
  },
};

module.exports = settingsService;
