<?php

$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
    ? "https://"
    : "http://";

$host = $_SERVER['HTTP_HOST'];

if (strpos($host, 'localhost') !== false) {
    // XAMPP
    define('BASE_URL', $protocol . $host . '/dhoti-mahal/');
} else {
    // Render
    define('BASE_URL', $protocol . $host . '/');
}

define('SITE_NAME', 'Dhoti Mahal');
define('SITE_TAGLINE', 'The House of Traditional Indian Attire');

// Paths
define('ROOT_PATH', dirname(__DIR__) . '/');
define('UPLOAD_PATH', ROOT_PATH . 'uploads/');
define('UPLOAD_URL', BASE_URL . 'uploads/');

// Currency
define('CURRENCY', '₹');

// Pagination
define('PRODUCTS_PER_PAGE', 12);

// Session name
define('SESSION_NAME', 'dhoti_mahal_session');

// Security
define('CSRF_TOKEN_NAME', '_csrf_token');

// Timezone
date_default_timezone_set('Asia/Kolkata');

// Environment
define('APP_ENV', getenv('APP_ENV') ?: 'development');
define('IS_PRODUCTION', APP_ENV === 'production');
define('DISPLAY_ERRORS', filter_var(getenv('DISPLAY_ERRORS') ?: (IS_PRODUCTION ? '0' : '1'), FILTER_VALIDATE_BOOLEAN));

// Owner-level payment control
// ⚠️ CHANGE THESE TO YOUR OWN CREDENTIALS ⚠️
define('OWNER_USERNAME', 'owner');  // Change 'admin' to your username
define('OWNER_PASSWORD_HASH', password_hash('owner123', PASSWORD_BCRYPT));  // Change 'admin123' to your password
define('OWNER_AUTH_TIMEOUT', 900); // 15 minutes

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', DISPLAY_ERRORS ? '1' : '0');
