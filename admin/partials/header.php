<?php
// ============================================================
// Dhoti Mahal - Admin Header Partial
// ============================================================
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';
requireAdmin();

$adminPageTitle = isset($adminPageTitle) ? $adminPageTitle . ' | Admin' : 'Admin | ' . SITE_NAME;
$currentAdminPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= sanitize($adminPageTitle) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link
        href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Lato:wght@400;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/main.css">
</head>

<body>
    <div class="admin-wrap">
        <!-- Sidebar -->
        <aside class="admin-sidebar">
            <div class="admin-sidebar-logo" style="display: flex; align-items: center; gap: 10px;">
                <img style="width: 60px; height: 40px; background: #f0f0f0; border-radius: 50%;"
                    src="<?= BASE_URL ?>\uploads\icon\icon.png" alt="">
                <span>DM Admin</span>
            </div>

            <nav class="admin-nav">
                <a href="<?= BASE_URL ?>admin/dashboard.php"
                    class="<?= $currentAdminPage === 'dashboard.php' ? 'active' : '' ?>"><i
                        class="fas fa-tachometer-alt"></i>
                    Dashboard</a>
                <a href="<?= BASE_URL ?>admin/orders.php"
                    class="<?= $currentAdminPage === 'orders.php' ? 'active' : '' ?>"><i class="fas fa-receipt"></i>
                    Orders</a>
                <a href="<?= BASE_URL ?>admin/products.php"
                    class="<?= $currentAdminPage === 'products.php' ? 'active' : '' ?>"><i class="fas fa-boxes"></i>
                    Products</a>
                <a href="<?= BASE_URL ?>admin/product-add.php"
                    class="<?= $currentAdminPage === 'product-add.php' ? 'active' : '' ?>"><i
                        class="fas fa-plus-circle"></i>
                    Add Product</a>
                <a href="<?= BASE_URL ?>admin/categories.php"
                    class="<?= $currentAdminPage === 'categories.php' ? 'active' : '' ?>"><i class="fas fa-tags"></i>
                    Categories</a>
                <a href="<?= BASE_URL ?>admin/banners.php"
                    class="<?= $currentAdminPage === 'banners.php' ? 'active' : '' ?>"><i class="fas fa-images"></i>
                    Banners</a>
                <a href="<?= BASE_URL ?>admin/settings.php"
                    class="<?= $currentAdminPage === 'settings.php' ? 'active' : '' ?>"><i class="fas fa-cog"></i>
                    Settings</a>
                <hr style="border-color:rgba(255,255,255,0.1);margin:12px 0;">
                <a href="<?= BASE_URL ?>" target="_blank"><i class="fas fa-external-link-alt"></i> View Site</a>
                <a href="<?= BASE_URL ?>admin/logout.php" style="color:#ff6b6b;"><i class="fas fa-sign-out-alt"></i>
                    Logout</a>
            </nav>
        </aside>
        <!-- Main -->
        <main class="admin-main">
            <?php $flash = getFlash();
            if ($flash): ?>
                <div class="flash-message flash-<?= $flash['type'] ?>"
                    style="border-radius:6px;margin-bottom:20px;padding:12px 20px;"><?= sanitize($flash['message']) ?></div>
            <?php endif; ?>