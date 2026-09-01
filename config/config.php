<?php
// ============================================================
// Dhoti Mahal - Configuration & Constants
// ============================================================

// Detect protocol (HTTPS in production, allow HTTP in local/dev)
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
    ? "https://"
    : "http://";

$host = $_SERVER['HTTP_HOST'];

// Determine BASE_URL based on environment
if (strpos($host, 'localhost') !== false || strpos($host, '127.0.0.1') !== false) {
    // Local development (XAMPP)
    define('BASE_URL', $protocol . $host . '/dhoti-mahal/');
} else {
    // Production (Render, etc.) - serve from root
    define('BASE_URL', $protocol . $host . '/');
}

define('SITE_NAME', 'Dhoti Mahal');
define('SITE_TAGLINE', 'The House of Traditional Indian Attire');

// Paths
define('ROOT_PATH', dirname(__DIR__) . '/');
define('UPLOAD_PATH', ROOT_PATH . 'uploads/');
// IMPORTANT: Ensure UPLOAD_URL doesn't have double slashes
define('UPLOAD_URL', rtrim(BASE_URL, '/') . '/uploads/');

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
define('OWNER_USERNAME', getenv('OWNER_USERNAME') ?: 'owner');
define('OWNER_PASSWORD_HASH', getenv('OWNER_PASSWORD_HASH') ?: password_hash('owner123', PASSWORD_BCRYPT));
define('OWNER_AUTH_TIMEOUT', 900); // 15 minutes

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', DISPLAY_ERRORS ? '1' : '0');
