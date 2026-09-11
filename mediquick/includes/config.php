<?php
/**
 * Site-wide configuration. Edit DB_* to match your XAMPP/MySQL setup.
 */

// --- Database ---
define('DB_HOST', 'sql211.infinityfree.com');
define('DB_NAME', 'if0_42894052_mediquick');
define('DB_USER', 'if0_42894052');
define('DB_PASS', 'Uq47qffnVY4Am');

// --- Business rules ---
define('DELIVERY_FEE', 350.00);
define('FREE_DELIVERY_OVER', 5000.00);
define('MAX_RX_FILE_BYTES', 5 * 1024 * 1024); // 5 MB
define('RX_ALLOWED_MIME', ['image/jpeg', 'image/png', 'application/pdf']);

// --- PayHere sandbox ---
// Get these from https://sandbox.payhere.lk after creating a sandbox merchant account.
// COD checkout works fully without these; card checkout needs them filled in.
define('PAYHERE_MERCHANT_ID', 'YOUR_SANDBOX_MERCHANT_ID');
define('PAYHERE_MERCHANT_SECRET', 'YOUR_SANDBOX_MERCHANT_SECRET');
define('PAYHERE_SANDBOX', true);
define('PAYHERE_CHECKOUT_URL', PAYHERE_SANDBOX
    ? 'https://sandbox.payhere.lk/pay/checkout'
    : 'https://www.payhere.lk/pay/checkout');

// --- Base URL (works regardless of the folder name this app is installed under) ---
$appDir = str_replace('\\', '/', dirname(__DIR__));
$docRoot = str_replace('\\', '/', rtrim($_SERVER['DOCUMENT_ROOT'] ?? '', '/'));
$base = $docRoot !== '' && strpos($appDir, $docRoot) === 0
    ? substr($appDir, strlen($docRoot))
    : '';
define('BASE_URL', rtrim($base, '/'));

define('APP_ROOT', dirname(__DIR__));

// --- Error visibility: show to developer, never to a customer ---
error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');
ini_set('error_log', APP_ROOT . '/error.log');
