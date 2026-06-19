const jwt = require('jsonwebtoken');
const env = require('../config/env');
const ApiError = require('../utils/ApiError');

/**
 * JWT auth middleware — PREPARED FOR FUTURE USE.
 *
 * Authentication is currently disabled via the AUTH_ENABLED flag, so this is a
 * no-op pass-through. When you are ready to lock the API down, set
 * AUTH_ENABLED=true and issue tokens; the verification logic below is ready.
 */
function protect(req, res, next) {
  if (!env.authEnabled) {
    return next(); // auth disabled — allow through
  }

  const header = req.headers.authorization || '';
  const token = header.startsWith('Bearer ') ? header.slice(7) : null;

  if (!token) {
    return next(ApiError.unauthorized('Authentication token missing'));
  }

  try {
    req.user = jwt.verify(token, env.jwtSecret);
    return next();
  } catch (err) {
    return next(ApiError.unauthorized('Invalid or expired token'));
  }
}

module.exports = { protect };
