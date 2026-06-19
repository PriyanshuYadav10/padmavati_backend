require('dotenv').config();

const env = {
  port: process.env.PORT || 4000,
  nodeEnv: process.env.NODE_ENV || 'development',
  mongoUri: process.env.MONGODB_URI || 'mongodb://127.0.0.1:27017/padmavati_bangles',
  corsOrigin: process.env.CORS_ORIGIN || '*',
  jwtSecret: process.env.JWT_SECRET || 'change_this_super_secret_key',
  jwtExpiresIn: process.env.JWT_EXPIRES_IN || '7d',
  // Authentication is intentionally disabled for now. The JWT middleware is
  // wired up and ready; flip this flag (or the env var) to enable it later.
  authEnabled: String(process.env.AUTH_ENABLED).toLowerCase() === 'true',
};

module.exports = env;
