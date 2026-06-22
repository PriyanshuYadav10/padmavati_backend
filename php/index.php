<?php
/**
 * Front controller — the PHP equivalent of src/app.js + src/server.js.
 * Bootstraps config, applies CORS + security headers, wires routes, dispatches,
 * and normalises every error into the standard envelope.
 */
declare(strict_types=1);

require __DIR__ . '/src/Http.php';      // ApiException + send_response/send_error
require __DIR__ . '/src/Request.php';
require __DIR__ . '/src/Router.php';
require __DIR__ . '/src/Validator.php';
require __DIR__ . '/src/Auth.php';
require __DIR__ . '/config/db.php';
require __DIR__ . '/services/ContactService.php';
require __DIR__ . '/services/SettingsService.php';
require __DIR__ . '/controllers/ContactController.php';
require __DIR__ . '/controllers/SettingsController.php';

$GLOBALS['APP_CONFIG'] = require __DIR__ . '/config/env.php';
$config = $GLOBALS['APP_CONFIG'];

/* ----- Security headers (helmet-ish) ----- */
header_remove('X-Powered-By');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Referrer-Policy: no-referrer');

/* ----- CORS ----- */
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
$allowed = $config['corsOrigin'];
if ($allowed === '*') {
    header('Access-Control-Allow-Origin: *');
} elseif ($origin !== '') {
    $list = array_map('trim', explode(',', $allowed));
    if (in_array($origin, $list, true)) {
        header('Access-Control-Allow-Origin: ' . $origin);
        header('Vary: Origin');
    }
}
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS') {
    http_response_code(204);
    exit;
}

/* ----- Routes (mirrors src/routes/*) ----- */
$router = new Router();

// Root
$router->get('/', function () {
    send_response(['message' => 'Padmavati Bangles Contact Management API', 'data' => ['docs' => '/api/health']]);
});

// Health
$router->get('/api/health', function () {
    send_response([
        'message' => 'Padmavati Bangles API is healthy',
        'data'    => ['timestamp' => gmdate('Y-m-d\TH:i:s') . '.000Z'],
    ]);
});

// Contacts — `protect` is a no-op while AUTH_ENABLED=false, ready to enforce later.
$contact = new ContactController();
$router->get('/api/contacts/search', function (Request $r) use ($contact) { Auth::protect($r); $contact->search($r); });
$router->get('/api/contacts', function (Request $r) use ($contact) { Auth::protect($r); $contact->getAll($r); });
$router->post('/api/contacts', function (Request $r) use ($contact) { Auth::protect($r); $contact->create($r); });
$router->get('/api/contacts/:id', function (Request $r) use ($contact) { Auth::protect($r); $contact->getById($r); });
$router->put('/api/contacts/:id', function (Request $r) use ($contact) { Auth::protect($r); $contact->update($r); });
$router->delete('/api/contacts/:id', function (Request $r) use ($contact) { Auth::protect($r); $contact->remove($r); });

// Settings
$settings = new SettingsController();
$router->get('/api/settings', fn (Request $r) => $settings->get($r));
$router->post('/api/settings', fn (Request $r) => $settings->save($r));

/* ----- Dispatch + central error handling ----- */
try {
    $request = new Request();
    $router->dispatch($request);
} catch (ApiException $e) {
    if ($e->getCode() >= 500 && $e->internal) {
        error_log('💥 Error: ' . $e->internal);
    }
    send_error((int) $e->getCode(), $e->getMessage(), $e->details);
} catch (Throwable $e) {
    error_log('💥 Unhandled: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
    $msg = $config['appEnv'] === 'production' ? 'Internal server error' : $e->getMessage();
    send_error(500, $msg, null);
}
