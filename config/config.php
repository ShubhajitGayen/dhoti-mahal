<?php
// ============================================================
// Dhoti Mahal - Site Configuration
// ============================================================

// Base URL (trailing slash included)
define('BASE_URL', 'http://localhost/dhoti-mahal/');
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

// Error reporting (set to 0 in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);
