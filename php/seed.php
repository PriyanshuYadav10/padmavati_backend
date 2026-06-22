<?php
/**
 * Seed script — sample contacts for quick testing (port of src/utils/seed.js).
 * Run from the server shell:  php seed.php
 * Or temporarily hit it in a browser, then DELETE this file afterwards.
 */
declare(strict_types=1);

require __DIR__ . '/src/Http.php';
require __DIR__ . '/config/db.php';
$GLOBALS['APP_CONFIG'] = require __DIR__ . '/config/env.php';

$sample = [
    ['Shree Bangle House', '+919812345670', 'Johari Bazaar', 'Jaipur', 'Wholesale', 'Best lac bangles'],
    ['Rajwadi Churi Bhandar', '+919812345671', 'Bapu Bazaar', 'Jaipur', 'Retail', 'Bulk orders welcome'],
    ['Meena Glass Works', '+919812345672', 'Firozabad Road', 'Firozabad', 'Manufacturer', 'Glass bangles supplier'],
    ['Krishna Bangles', '+919812345673', 'MG Road', 'Agra', 'Distributor', ''],
    ['Laxmi Suhaag', '+919812345674', 'Sadar Bazaar', 'Delhi', 'Retail', 'Bridal sets'],
];

try {
    db()->exec('DELETE FROM contacts');
    $stmt = db()->prepare(
        'INSERT INTO contacts (name, phone, location, city, business_type, notes)
         VALUES (?, ?, ?, ?, ?, ?)'
    );
    foreach ($sample as $row) {
        $stmt->execute($row);
    }
    $msg = '✅ Seeded ' . count($sample) . ' contacts';
    echo (PHP_SAPI === 'cli') ? $msg . PHP_EOL : json_encode(['success' => true, 'message' => $msg]);
} catch (Throwable $e) {
    echo 'Seed failed: ' . $e->getMessage() . PHP_EOL;
}
