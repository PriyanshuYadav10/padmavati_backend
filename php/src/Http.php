<?php
/**
 * Response envelope + operational error type.
 * Mirrors utils/ApiResponse.js and utils/ApiError.js.
 */

class ApiException extends Exception
{
    /** @var array|null field-level validation details */
    public $details;
    /** @var string|null internal-only detail (logged, never sent in prod) */
    public $internal;

    public function __construct(int $statusCode, string $message, ?array $details = null, ?string $internal = null)
    {
        parent::__construct($message, $statusCode);
        $this->details  = $details;
        $this->internal = $internal;
    }

    public static function badRequest(string $msg = 'Bad request', ?array $details = null): self
    {
        return new self(400, $msg, $details);
    }
    public static function unauthorized(string $msg = 'Unauthorized'): self
    {
        return new self(401, $msg);
    }
    public static function forbidden(string $msg = 'Forbidden'): self
    {
        return new self(403, $msg);
    }
    public static function notFound(string $msg = 'Resource not found'): self
    {
        return new self(404, $msg);
    }
    public static function internal(string $msg = 'Internal server error'): self
    {
        return new self(500, $msg);
    }
}

/**
 * Standard success envelope: { success, message, data, meta? }
 */
function send_response(array $opts): void
{
    $statusCode = $opts['statusCode'] ?? 200;
    $body = [
        'success' => true,
        'message' => $opts['message'] ?? 'Success',
        'data'    => $opts['data'] ?? null,
    ];
    if (array_key_exists('meta', $opts)) {
        $body['meta'] = $opts['meta'];
    }
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($body, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}

/**
 * Standard error envelope: { success:false, message, details }
 */
function send_error(int $statusCode, string $message, ?array $details = null): void
{
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'success' => false,
        'message' => $message,
        'details' => $details,
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}
