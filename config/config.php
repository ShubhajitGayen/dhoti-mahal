<?php
// ============================================================
// Dhoti Mahal - Site Configuration
// ============================================================

// Base URL (trailing slash included)
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
define('BASE_URL', $protocol . $_SERVER['HTTP_HOST'] . '/dhoti-mahal/');

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
define('OWNER_USERNAME', getenv('OWNER_USERNAME') ?: 'owner');
$ownerPasswordSecret = getenv('OWNER_PASSWORD_HASH') ?: getenv('OWNER_PASSWORD');
if ($ownerPasswordSecret === false || $ownerPasswordSecret === '') {
    $ownerPasswordSecret = password_hash('ownerpassword', PASSWORD_BCRYPT);
} elseif (!str_starts_with($ownerPasswordSecret, '$2y$')) {
    $ownerPasswordSecret = password_hash($ownerPasswordSecret, PASSWORD_BCRYPT);
}
define('OWNER_PASSWORD_HASH', $ownerPasswordSecret);
define('OWNER_AUTH_TIMEOUT', 900); // 15 minutes

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', DISPLAY_ERRORS ? '1' : '0');
