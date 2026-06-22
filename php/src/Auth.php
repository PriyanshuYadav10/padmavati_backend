<?php
/**
 * JWT auth guard — PREPARED FOR FUTURE USE (port of src/middleware/auth.middleware.js).
 *
 * Auth is disabled via AUTH_ENABLED, so protect() is a no-op pass-through. When
 * you flip the flag, this verifies an HS256 "Authorization: Bearer <token>".
 * A pure-PHP HS256 verifier is included so no Composer package is required.
 */
class Auth
{
    public static function protect(Request $req): void
    {
        if (empty($GLOBALS['APP_CONFIG']['authEnabled'])) {
            return; // auth disabled — allow through
        }

        $header = self::authorizationHeader();
        $token  = (stripos($header, 'Bearer ') === 0) ? substr($header, 7) : null;
        if (!$token) {
            throw ApiException::unauthorized('Authentication token missing');
        }

        $payload = self::verifyJwt($token, $GLOBALS['APP_CONFIG']['jwtSecret']);
        if ($payload === null) {
            throw ApiException::unauthorized('Invalid or expired token');
        }
        $req->params['_user'] = $payload;
    }

    private static function authorizationHeader(): string
    {
        if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
            return $_SERVER['HTTP_AUTHORIZATION'];
        }
        if (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
            return $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
        }
        if (function_exists('apache_request_headers')) {
            $h = apache_request_headers();
            return $h['Authorization'] ?? $h['authorization'] ?? '';
        }
        return '';
    }

    /** @return array|null decoded payload, or null if invalid/expired */
    private static function verifyJwt(string $jwt, string $secret): ?array
    {
        $parts = explode('.', $jwt);
        if (count($parts) !== 3) {
            return null;
        }
        [$h64, $p64, $s64] = $parts;
        $signing = $h64 . '.' . $p64;
        $expected = self::b64url(hash_hmac('sha256', $signing, $secret, true));
        if (!hash_equals($expected, $s64)) {
            return null;
        }
        $payload = json_decode(self::b64urlDecode($p64), true);
        if (!is_array($payload)) {
            return null;
        }
        if (isset($payload['exp']) && time() >= (int) $payload['exp']) {
            return null;
        }
        return $payload;
    }

    private static function b64url(string $bin): string
    {
        return rtrim(strtr(base64_encode($bin), '+/', '-_'), '=');
    }
    private static function b64urlDecode(string $s): string
    {
        return base64_decode(strtr($s, '-_', '+/'));
    }
}
