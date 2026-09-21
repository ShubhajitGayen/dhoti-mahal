<?php
// ============================================================
// Dhoti Mahal - Common Header
// ============================================================
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

cleanupGuestCart();

$siteName    = getSetting('site_name', SITE_NAME);
$currentUser = getLoggedUser();
$cartCount   = getCartCount();
$categories  = getCategories();
$flash       = getFlash();

$pageTitle   = isset($pageTitle) ? $pageTitle . ' | ' . $siteName : $siteName;
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= sanitize($pageTitle) ?></title>
    <meta name="description" content="<?= sanitize(getSetting('meta_description')) ?>">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link
        href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,600;0,700;1,400&family=Lato:wght@300;400;700&display=swap"
        rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <!-- Main CSS -->
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/main.css">
    <script>
        // Expose BASE_URL to JavaScript globally (both names for compatibility)
        window.__BASE_URL = window.BASE_URL = "<?= rtrim(BASE_URL, '/') . '/' ?>";
    </script>
    <base href="<?= rtrim(BASE_URL, '/') . '/' ?>">
</head>

<body>

    <!-- Top Bar -->
    <div class="topbar">
        <div class="container">
            <span><i class="fas fa-phone-alt"></i> <?= sanitize(getSetting('site_phone')) ?></span>
            <span><i class="fas fa-envelope"></i> <?= sanitize(getSetting('site_email')) ?></span>
            <span class="topbar-right">
                <a href="<?= BASE_URL ?>pages/track-order.php"><i class="fas fa-truck"></i> Track Order</a>
            </span>
        </div>
    </div>

    <!-- Header -->
    <header class="site-header">
        <div class="container header-inner">
            <!-- Logo -->
            <a href="<?= BASE_URL ?>" class="logo">
                <span class="logo-icon"><img style="width: 100px; height: auto;"
                        src="<?= BASE_URL ?>uploads/icon/icon.png" alt=""></span>
                <div>
                    <div class="logo-name"><?= sanitize($siteName) ?></div>
                    <div class="logo-tagline"><?= sanitize(getSetting('site_tagline', SITE_TAGLINE)) ?></div>
                </div>
            </a>

            <!-- Search -->
            <form class="header-search" action="<?= BASE_URL ?>pages/category.php" method="GET">
                <input type="text" name="search" placeholder="Search dhotis, silk, cotton..."
                    value="<?= sanitize($_GET['search'] ?? '') ?>">
                <button type="submit"><i class="fas fa-search"></i></button>
            </form>

            <!-- Header Actions -->
            <div class="header-actions">
                <?php if ($currentUser): ?>
                    <a href="<?= BASE_URL ?>pages/user-profile.php" class="action-btn" title="My Account">
                        <i class="fas fa-user"></i>
                        <span><?= sanitize(explode(' ', $currentUser['name'])[0]) ?></span>
                    </a>
                <?php else: ?>
                    <a href="<?= BASE_URL ?>pages/login.php" class="action-btn" title="Login">
                        <i class="fas fa-user"></i>
                        <span>Login</span>
                    </a>
                <?php endif; ?>
                <a href="<?= BASE_URL ?>pages/cart.php" class="action-btn cart-btn" title="Cart">
                    <i class="fas fa-shopping-bag"></i>
                    <span>Cart</span>
                    <?php if ($cartCount > 0): ?>
                        <span class="cart-badge"><?= $cartCount ?></span>
                    <?php endif; ?>
                </a>
            </div>
        </div>

        <!-- Navigation -->
        <nav class="main-nav">
            <div class="container">
                <button class="nav-toggle" id="navToggle"><i class="fas fa-bars"></i> Menu</button>
                <ul class="nav-list" id="navList">
                    <li><a href="<?= BASE_URL ?>">Home</a></li>
                    <?php foreach ($categories as $cat): ?>
                        <li>
                            <a href="<?= BASE_URL ?>pages/category.php?slug=<?= urlencode($cat['slug']) ?>">
                                <?= sanitize($cat['name']) ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                    <li><a href="<?= BASE_URL ?>pages/track-order.php">Track Order</a></li>
                </ul>
            </div>
        </nav>
    </header>

    <!-- Flash Message -->
    <?php if ($flash): ?>
        <div class="flash-message flash-<?= sanitize($flash['type']) ?>" id="flashMsg">
            <div class="container">
                <span><?= sanitize($flash['message']) ?></span>
                <button onclick="this.parentElement.parentElement.remove()">✕</button>
            </div>
        </div>
    <?php endif; ?>

    <!-- Main Content -->
    <main>
