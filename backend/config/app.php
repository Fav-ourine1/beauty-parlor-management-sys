<?php
/**
 * Application Configuration
 * Edit the values below for your environment.
 * Never commit real credentials to version control — use .env in production.
 */

// ── Environment ──────────────────────────────────────────────
define('APP_ENV',   'development');   // 'development' | 'production'
define('APP_NAME',  'Mbagathi Beauty Parlour');
define('APP_URL',   'http://localhost:8000');
define('APP_DEBUG',  APP_ENV === 'development');

// ── Database ─────────────────────────────────────────────────
define('DB_HOST',   'localhost');
define('DB_PORT',   '3306');
define('DB_NAME',   'mbagathi_parlour');
define('DB_USER',   'mbagathi');
define('DB_PASS',   'Mbagathi2024!');
define('DB_CHARSET','utf8mb4');

// ── Session ───────────────────────────────────────────────────
define('SESSION_LIFETIME', 7200);    // seconds (2 hours)
define('SESSION_NAME',     'mbagathi_sess');

// ── Security ─────────────────────────────────────────────────
define('JWT_SECRET',  'CHANGE_THIS_TO_A_STRONG_RANDOM_STRING');
define('BCRYPT_COST',  12);

// ── Safaricom Daraja (M-Pesa) ────────────────────────────────
define('MPESA_ENV',              'sandbox');   // 'sandbox' | 'production'
define('MPESA_CONSUMER_KEY',     'YOUR_DARAJA_CONSUMER_KEY');
define('MPESA_CONSUMER_SECRET',  'YOUR_DARAJA_CONSUMER_SECRET');
define('MPESA_SHORTCODE',        '174379');    // Sandbox test shortcode
define('MPESA_PASSKEY',          'YOUR_DARAJA_PASSKEY');
define('MPESA_CALLBACK_URL',     APP_URL . '/api/payments/mpesa/callback');
define('MPESA_BASE_URL',
    MPESA_ENV === 'production'
        ? 'https://api.safaricom.co.ke'
        : 'https://sandbox.safaricom.co.ke'
);

// ── Africa's Talking (SMS) ───────────────────────────────────
define('AT_USERNAME',   'sandbox');            // Your AT username
define('AT_API_KEY',    'YOUR_AT_API_KEY');
define('AT_SENDER_ID',  'Mbagathi');           // Must be approved by AT in production
define('AT_ENV',        'sandbox');            // 'sandbox' | 'production'

// ── Email (PHPMailer / SMTP) ─────────────────────────────────
define('MAIL_HOST',       'smtp.gmail.com');
define('MAIL_PORT',        587);
define('MAIL_USERNAME',   'your@gmail.com');
define('MAIL_PASSWORD',   'YOUR_APP_PASSWORD');
define('MAIL_FROM_NAME',   APP_NAME);
define('MAIL_ENCRYPTION', 'tls');

// ── Paths ─────────────────────────────────────────────────────
define('STORAGE_PATH', BASE_PATH . '/storage');
define('LOG_PATH',     STORAGE_PATH . '/logs');

// ── Error handling ────────────────────────────────────────────
if (APP_DEBUG) {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    error_reporting(0);
}
ini_set('log_errors', 1);
ini_set('error_log', LOG_PATH . '/php_errors.log');