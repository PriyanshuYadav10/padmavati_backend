<?php
/**
 * MySQL (PDO) connection — the PHP equivalent of src/config/db.js.
 * The connection time zone is forced to UTC so CURRENT_TIMESTAMP columns line
 * up with the ISO-8601 ".000Z" strings Mongoose used to emit.
 */

function db(): PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $cfg = $GLOBALS['APP_CONFIG']['db'];
    $dsn = sprintf(
        'mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4',
        $cfg['host'],
        $cfg['port'],
        $cfg['name']
    );

    try {
        $pdo = new PDO($dsn, $cfg['user'], $cfg['pass'], [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
        $pdo->exec("SET time_zone = '+00:00'");
    } catch (PDOException $e) {
        // Mirror the Node behaviour: fail loudly, but never leak credentials.
        throw new ApiException(500, 'Database connection failed', null, $e->getMessage());
    }

    return $pdo;
}
