<?php
/**
 * Lightweight .env loader + config (mirrors src/config/env.js).
 * No Composer / vlucas dotenv needed — we parse the file ourselves.
 */

function load_env(string $path): void
{
    if (!is_readable($path)) {
        return; // fall back to getenv()/defaults
    }
    foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || $line[0] === '#') {
            continue;
        }
        $pos = strpos($line, '=');
        if ($pos === false) {
            continue;
        }
        $key = trim(substr($line, 0, $pos));
        $val = trim(substr($line, $pos + 1));
        // strip surrounding quotes
        if (strlen($val) >= 2 && ($val[0] === '"' || $val[0] === "'") && substr($val, -1) === $val[0]) {
            $val = substr($val, 1, -1);
        }
        if (getenv($key) === false) {
            putenv("$key=$val");
            $_ENV[$key] = $val;
        }
    }
}

load_env(__DIR__ . '/../.env');

function env_get(string $key, $default = null)
{
    $v = getenv($key);
    return $v === false ? $default : $v;
}

return [
    'appEnv'      => env_get('APP_ENV', 'production'),
    'db' => [
        'host' => env_get('DB_HOST', 'localhost'),
        'port' => (int) env_get('DB_PORT', 3306),
        'name' => env_get('DB_NAME', 'padmavati_bangles'),
        'user' => env_get('DB_USER', 'root'),
        'pass' => env_get('DB_PASS', ''),
    ],
    'corsOrigin'  => env_get('CORS_ORIGIN', '*'),
    'jwtSecret'   => env_get('JWT_SECRET', 'change_this_super_secret_key'),
    'jwtExpiresIn'=> env_get('JWT_EXPIRES_IN', '7d'),
    // Authentication is intentionally disabled for now (see src/middleware/auth.middleware.js).
    'authEnabled' => strtolower((string) env_get('AUTH_ENABLED', 'false')) === 'true',
];
