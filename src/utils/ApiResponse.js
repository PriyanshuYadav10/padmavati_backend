/**
 * Standard success envelope so every endpoint returns a predictable shape:
 * { success, message, data, meta }
 */
function sendResponse(res, { statusCode = 200, message = 'Success', data = null, meta = undefined }) {
  const body = { success: true, message, data };
  if (meta !== undefined) body.meta = meta;
  return res.status(statusCode).json(body);
}

module.exports = { sendResponse };
