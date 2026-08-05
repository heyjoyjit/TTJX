<?php
// includes/config.php

// 1. Prevent "Constant already defined" warnings
if (!defined('BASE_URL')) {
    define('BASE_URL', 'http://localhost/TTJX/');   // change for production
}
if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname($_SERVER['DOCUMENT_ROOT']) . '/TTJX/');
}

// Database credentials
$config = [
    'db_host' => 'localhost',
    'db_name' => 'traveltara',
    'db_user' => 'root',
    'db_pass' => '5102003',
    'db_charset' => 'utf8mb4'
];

// Razorpay Keys (test mode)
if (!defined('RAZORPAY_KEY_ID')) {
    define('RAZORPAY_KEY_ID', 'rzp_test_xxxxx');
}
if (!defined('RAZORPAY_KEY_SECRET')) {
    define('RAZORPAY_KEY_SECRET', 'your_secret_here');
}

// 2. Prevent "Session ini settings cannot be changed" warnings
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
        ini_set('session.cookie_secure', 1);
    }
    session_start();
}

// Error reporting (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

$email_config = [
    'host'       => 'smtp.gmail.com', // FIXED: Replaced smtp.example.com with Gmail SMTP host
    'username'   => 'smarak.haldar.official@gmail.com',
    'password'   => 'cthv vfwy jblr fsba', // Your Gmail App Password
    'port'       => 587,
    'encryption' => 'tls',
    'from_email' => 'smarak.haldar.official@gmail.com',
    'from_name'  => 'TravelTara'
];

if (!defined('EMAIL_CONFIG')) {
    define('EMAIL_CONFIG', serialize($email_config)); // Store serialized config for later use
}

if (!defined('SITE_NAME')) {
    define('SITE_NAME', 'TravelTara');
}

if (!defined('SITE_URL')) {
    define('SITE_URL', BASE_URL);
}

return $config;
