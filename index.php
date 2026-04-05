<?php
// ============================================================
// Dhoti Mahal - Main Router / Entry Point
// ============================================================
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/auth.php';

// Route the request
$request = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');

// Strip base path if project is in a subdirectory
$base = trim(parse_url(BASE_URL, PHP_URL_PATH), '/');

if ($base && strpos($request, $base) === 0) {
    $request = ltrim(substr($request, strlen($base)), '/');
}
// Remove query string
$request = explode('?', $request)[0];

// Simple routing map
$routes = [
    ''                    => 'pages/home.php',
    'home'                => 'pages/home.php',
    'category'            => 'pages/category.php',
    'product'             => 'pages/product.php',
    'cart'                => 'pages/cart.php',
    'checkout'            => 'pages/checkout.php',
    'payment'             => 'pages/payment.php',
    'confirmation'        => 'pages/confirmation.php',
    'track-order'         => 'pages/track-order.php',
    'login'               => 'pages/login.php',
    'register'            => 'pages/register.php',
    'logout'              => 'pages/logout.php',
    'profile'             => 'pages/user-profile.php',
    'user-profile'        => 'pages/user-profile.php',
    'admin'               => 'admin/dashboard.php',
    'admin/login'         => 'admin/login.php',
    'admin/logout'        => 'admin/logout.php',
    'admin/dashboard'     => 'admin/dashboard.php',
    'admin/orders'        => 'admin/orders.php',
    'admin/order-detail'  => 'admin/order-detail.php',
    'admin/products'      => 'admin/products.php',
    'admin/product-add'   => 'admin/product-add.php',
    'admin/product-edit'  => 'admin/product-edit.php',
    'admin/categories'    => 'admin/categories.php',
    'admin/banners'       => 'admin/banners.php',
    'admin/settings'      => 'admin/settings.php',
];

// Normalize request (handle .php extensions in URL)
$request = preg_replace('/\.php$/', '', $request);

if (isset($routes[$request])) {
    $file = __DIR__ . '/' . $routes[$request];
    if (file_exists($file)) {
        require $file;
    } else {
        http_response_code(404);
        echo '<h1>404 - Page Not Found</h1>';
    }
} else {
    // Try direct file
    $directFile = __DIR__ . '/' . $request . '.php';
    if (file_exists($directFile)) {
        require $directFile;
    } else {
        // Default: homepage
        require __DIR__ . '/pages/home.php';
    }
}