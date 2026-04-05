<?php
// Shared product card template
// Variable: $product (array from DB)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if (!isset($product)) return;

$effectivePrice  = getEffectivePrice($product);
$discountPercent = getDiscountPercent($product);
$isOnSale        = $discountPercent > 0;
$isNew           = (time() - strtotime($product['created_at'])) < 86400 * 14;
$isOutOfStock    = $product['stock'] < 1;
?>
<div class="product-card">
    <div class="product-card-img">
        <a href="<?= BASE_URL ?>pages/product.php?slug=<?= urlencode($product['slug']) ?>">
            <img src="<?= productImage($product['image'] ?? '') ?>" alt="<?= sanitize($product['name']) ?>"
                loading="lazy">
        </a>

        <?php if ($isOutOfStock): ?>
        <span class="badge badge-out">Out of Stock</span>
        <?php elseif ($isOnSale): ?>
        <span class="badge badge-sale"><?= $discountPercent ?>% OFF</span>
        <?php elseif ($isNew): ?>
        <span class="badge badge-new">New</span>
        <?php endif; ?>

        <?php if (!$isOutOfStock): ?>
        <div class="product-actions">

            <a href="<?= BASE_URL ?>pages/product.php?slug=<?= urlencode($product['slug']) ?>"
                class="btn btn-gold btn-sm">
                <i class="fas fa-eye"></i>
            </a>
        </div>
        <?php endif; ?>
    </div>

    <div class="product-card-body">
        <div class="product-category"><?= sanitize($product['category_name'] ?? '') ?></div>
        <a href="<?= BASE_URL ?>pages/product.php?slug=<?= urlencode($product['slug']) ?>" class="product-name">
            <?= sanitize($product['name']) ?>
        </a>
        <div class="product-price">
            <span class="price-current"><?= formatPrice($effectivePrice) ?></span>
            <?php if ($isOnSale): ?>
            <span class="price-original"><?= formatPrice((float)$product['price']) ?></span>
            <span class="price-off"><?= $discountPercent ?>% off</span>
            <?php endif; ?>
        </div>
    </div>
</div>