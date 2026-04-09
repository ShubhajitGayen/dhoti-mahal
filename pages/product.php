<?php
// ============================================================
// Dhoti Mahal - Product Detail Page
// ============================================================
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}


$slug    = trim($_GET['slug'] ?? '');
if (!$slug) {
    redirect(BASE_URL);
}

$product = getProductBySlug($slug);
if (!$product) {
    setFlash('error', 'Product not found.');
    redirect(BASE_URL);
}



$effectivePrice  = getEffectivePrice($product);
$discountPercent = getDiscountPercent($product);
$sizes           = array_filter(array_map('trim', explode(',', $product['size'] ?? '')));
$related         = getRelatedProducts((int)$product['category_id'], (int)$product['id'], 4);
$gallery         = array_filter(array_map('trim', explode(',', $product['gallery'] ?? '')));
$pageTitle       = $product['name'];


$userId = $_SESSION['user_id'] ?? null;

if ($userId) {
    $quantity = getCartQuantity($userId, $product['id']);
}
require_once __DIR__ . '/../includes/header.php';
?>
<script>
    window.__BASE_URL = '<?= BASE_URL ?>';
</script>

<!-- Breadcrumb -->
<div class="page-hero" style="padding: 24px 0;">
    <div class="container">
        <div class="breadcrumb">
            <a href="<?= BASE_URL ?>">Home</a>
            <span>›</span>
            <a
                href="<?= BASE_URL ?>pages/category.php?slug=<?= urlencode($product['category_slug']) ?>"><?= sanitize($product['category_name']) ?></a>
            <span>›</span>
            <span><?= sanitize($product['name']) ?></span>
        </div>
    </div>
</div>

<div class="container">
    <div class="product-detail-grid">

        <!-- Gallery -->
        <div class="product-gallery">
            <div class="main-img">
                <img id="mainProductImg" src="<?= productImage($product['image'] ?? '') ?>"
                    alt="<?= sanitize($product['name']) ?>">
            </div>
            <?php if (!empty($gallery)): ?>
                <div class="thumb-strip">
                    <div class="thumb active" data-full="<?= productImage($product['image'] ?? '') ?>">
                        <img src="<?= productImage($product['image'] ?? '') ?>" alt="main">
                    </div>
                    <?php foreach ($gallery as $g): ?>
                        <div class="thumb" data-full="<?= productImage($g) ?>">
                            <img src="<?= productImage($g) ?>" alt="gallery">
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Product Info -->
        <div class="product-info">
            <div class="product-category"
                style="font-size:13px;color:var(--muted);text-transform:uppercase;letter-spacing:1px;margin-bottom:6px;">
                <?= sanitize($product['category_name']) ?>
            </div>
            <h1><?= sanitize($product['name']) ?></h1>

            <div class="product-meta">
                <?php if ($product['sku']): ?><span>SKU:
                        <strong><?= sanitize($product['sku']) ?></strong></span><?php endif; ?>
                <?php if ($product['fabric']): ?><span>Fabric:
                        <strong><?= sanitize($product['fabric']) ?></strong></span><?php endif; ?>
                <?php if ($product['color']): ?><span>Color:
                        <strong><?= sanitize($product['color']) ?></strong></span><?php endif; ?>
                <?php if ($product['occasion']): ?><span>Occasion:
                        <strong><?= sanitize($product['occasion']) ?></strong></span><?php endif; ?>
            </div>

            <!-- Price -->
            <div class="price-group">
                <span class="price-current"><?= formatPrice($effectivePrice) ?></span>
                <?php if ($discountPercent > 0): ?>
                    <span class="price-original"
                        style="margin-left: 10px;"><?= formatPrice((float)$product['price']) ?></span>
                    <span class="price-off" style="margin-left: 10px;"><?= $discountPercent ?>% off</span>
                <?php endif; ?>
                <div style="font-size: 13px; color: var(--muted); margin-top: 6px;">Inclusive of all taxes</div>
            </div>

            <?php if ($product['stock'] < 1): ?>
                <div
                    style="background: #f8d7da; color: #721c24; padding: 10px 16px; border-radius: 4px; margin: 12px 0; font-weight: 600;">
                    <i class="fas fa-times-circle"></i> Out of Stock
                </div>
            <?php else: ?>
                <div style="color: #28a745; font-weight: 600; font-size: 14px; margin-bottom: 12px;">
                    <i class="fas fa-check-circle"></i> In Stock (<?= (int)$product['stock'] ?> available)
                </div>
            <?php endif; ?>

            <?php if ($product['short_description']): ?>
                <p style="color: var(--muted); font-size: 14px; line-height: 1.8; margin-bottom: 16px;">
                    <?= sanitize($product['short_description']) ?></p>
            <?php endif; ?>

            <?php if ($product['stock'] > 0): ?>
                <form class="add-to-cart-form" action="<?= BASE_URL ?>api/cart.php" method="POST">

                    <input type="hidden" name="action" value="add">

                    <input type="hidden" name="product_id" value="<?= (int)$product['id'] ?>">
                    <input type="hidden" name="size" id="selectedSize" value="">

                    <!-- Size -->
                    <?php if (!empty($sizes)): ?>
                        <div class="size-selector">
                            <label>Select Size:</label>
                            <div class="size-options">
                                <?php foreach ($sizes as $size): ?>
                                    <button type="button" class="size-btn"
                                        data-size="<?= sanitize($size) ?>"><?= sanitize($size) ?></button>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Quantity -->
                    <div class="qty-selector">
                        <label>Quantity:</label>
                        <div class="qty-ctrl">
                            <button type="button" class="qty-minus">−</button>
                            <input type="number" name="quantity" id="quantityInput"
                                value="<?= isset($quantity) && $quantity !== '' ? max(1, (int)$quantity) : 1 ?>" min="1"
                                max="<?= (int)$product['stock'] ?>">
                            <button type="button" class="qty-plus">+</button>
                        </div>
                    </div>

                    <div class="product-actions-row">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-shopping-bag"></i> Add to Cart
                        </button>
                        <a href="<?= BASE_URL ?>pages/cart.php" class="btn btn-outline">
                            <i class="fas fa-shopping-cart"></i> View Cart
                        </a>
                    </div>
                </form>



                <!-- Direct Checkout -->
                <form method="POST" action="<?= BASE_URL ?>pages/checkout.php" style="margin-top: 10px;">
                    <input type="hidden" name="buy_now_product_id" value="<?= (int)$product['id'] ?>">
                    <input type="hidden" name="buy_now_size" id="selectedSize1" placeholder="Selected size" readonly>
                    <input type="hidden" name="buy_now_qty" id="showQty1" placeholder="Qty outside" readonly>
                    <button type="submit" class="btn btn-gold btn-full">
                        <i class="fas fa-bolt"></i> Buy Now
                    </button>
                </form>
            <?php endif; ?>

            <!-- Trust badges -->
            <div style="display:flex; gap:16px; margin-top:20px; flex-wrap:wrap;">
                <span style="font-size:13px; color:var(--muted);"><i class="fas fa-truck" style="color:var(--gold)"></i>
                    Free shipping above ₹<?= getSetting('free_shipping_above', '999') ?></span>
                <span style="font-size:13px; color:var(--muted);"><i class="fas fa-undo" style="color:var(--gold)"></i>
                    7-day returns</span>
                <span style="font-size:13px; color:var(--muted);"><i class="fab fa-whatsapp" style="color:#25D366"></i>
                    WhatsApp support</span>
            </div>
        </div>
    </div>

    <!-- Tabs -->
    <div class="product-tabs">
        <div class="tab-btns">
            <button class="tab-btn active" data-tab="tab-desc">Description</button>
            <button class="tab-btn" data-tab="tab-details">Details</button>
            <button class="tab-btn" data-tab="tab-shipping">Shipping & Returns</button>
        </div>

        <div class="tab-content active" id="tab-desc">
            <p><?= nl2br(sanitize($product['description'] ?? 'No description available.')) ?></p>
        </div>

        <div class="tab-content" id="tab-details">
            <table style="border-collapse: collapse; width: 100%; max-width: 500px;">
                <?php
                $details = [
                    'Fabric'   => $product['fabric'],
                    'Color'    => $product['color'],
                    'Size'     => $product['size'],
                    'Occasion' => $product['occasion'],
                    'SKU'      => $product['sku'],
                ];
                foreach ($details as $key => $val): if (!$val) continue; ?>
                    <tr>
                        <td
                            style="padding:10px 16px;font-weight:700;background:var(--cream);width:160px;border:1px solid var(--border);">
                            <?= $key ?></td>
                        <td style="padding:10px 16px;border:1px solid var(--border);"><?= sanitize($val) ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        </div>

        <div class="tab-content" id="tab-shipping">
            <p><strong>Shipping:</strong> Free shipping on orders above
                ₹<?= getSetting('free_shipping_above', '999') ?>. Standard delivery in 5–7 business days.</p>
            <p><strong>Returns:</strong> We accept returns within 7 days of delivery. The product must be unused and in
                its original packaging. Initiate via WhatsApp or email.</p>
            <p><strong>Payment:</strong> UPI, Google Pay, PhonePe, Paytm. 100% secure payment.</p>
        </div>
    </div>

    <!-- Related Products -->
    <?php if (!empty($related)): ?>
        <div class="section">
            <div class="section-title">
                <h2>You May Also Like</h2>
                <div class="divider"><span></span><i class="fas fa-heart"></i><span></span></div>
            </div>
            <div class="product-grid">
                <?php foreach ($related as $product): ?>
                    <?php include __DIR__ . '/../includes/product-card.php'; ?>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
    document.addEventListener("click", function(e) {
        if (e.target.classList.contains("size-btn")) {
            document.getElementById("selectedSize1").value = e.target.dataset.size;
        }
    });


    const input1 = document.getElementById("quantityInput");
    const input2 = document.getElementById("showQty1");

    function syncQty() {
        input2.value = input1.value;
    }

    // listen everything
    ["input", "change", "click"].forEach(event => {
        document.addEventListener(event, syncQty);
    });

    // initial
    syncQty();
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>