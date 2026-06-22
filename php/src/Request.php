<?php
/**
 * Tiny request helper — parses the JSON (or form) body once and exposes the
 * method, path (without query string or base dir), and parsed body.
 */
class Request
{
    public string $method;
    public string $path;
    public array $query;
    public array $body;
    public array $params = []; // populated by the router from :placeholders

    public function __construct()
    {
        $this->method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $this->query  = $_GET;
        $this->path   = self::resolvePath();
        $this->body   = self::parseBody();
    }

    private static function resolvePath(): string
    {
        $uri  = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
        // If the app lives in a subfolder, strip the script's directory prefix
        // so routes are matched relative to where index.php sits.
        $base = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');
        if ($base !== '' && $base !== '/' && strpos($uri, $base) === 0) {
            $uri = substr($uri, strlen($base));
        }
        $uri = '/' . ltrim($uri, '/');
        // collapse trailing slash (except root) so "/api/contacts/" == "/api/contacts"
        if (strlen($uri) > 1) {
            $uri = rtrim($uri, '/');
        }
        return $uri === '' ? '/' : $uri;
    }

    private static function parseBody(): array
    {
        $ctype = $_SERVER['CONTENT_TYPE'] ?? '';
        if (stripos($ctype, 'application/json') !== false) {
            $raw = file_get_contents('php://input');
            if ($raw === '' || $raw === false) {
                return [];
            }
            $decoded = json_decode($raw, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw ApiException::badRequest('Invalid JSON body');
            }
            return is_array($decoded) ? $decoded : [];
        }
        // urlencoded / multipart form
        return $_POST;
    }
}
