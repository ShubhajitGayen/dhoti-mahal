<?php

require_once __DIR__ . '/../includes/functions.php'; // make sure this exists


if (
    (isset($_POST['user_id']) || isset($_POST['session_id'])) &&
    isset($_POST['product_id'], $_POST['remove'])
) {
    $user_id = (int) $_POST['user_id'] ?? null;
    $product_id = (int) $_POST['product_id'];
    $session_id = $_POST['session_id'] ?? null;

    if (removeFromCart($user_id, $session_id, $product_id)) {
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    } else {
        die("Delete failed");
    }
}


// UPDATE QUANTITY
if ((isset($_POST['user_id']) || isset($_POST['session_id'])) && isset($_POST['quantity'], $_POST['update'])) {
    $user_id = (int) $_POST['user_id'] ?? null;
    $session_id = $_POST['session_id'] ?? null;
    $product_id = (int) $_POST['product_id'];
    $qty     = max(1, (int) $_POST['quantity']); // minimum 1
    if (updateCartQty($user_id, $session_id, $product_id, $qty)) {
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    } else {
        die("Quantity update failed");
    }
}
// ============================================================
// Dhoti Mahal - Cart Page
// ============================================================
$pageTitle = 'Shopping Cart';
require_once __DIR__ . '/../includes/header.php';

$cart     = getCart();
$subtotal = getCartTotal();
$shipping = calculateShipping($subtotal);
$total    = $subtotal + $shipping;
$freeAbove = (float)getSetting('free_shipping_above', '999');



?>
<script>
window.__BASE_URL = '<?= BASE_URL ?>';
</script>

<div class="page-hero" style="padding:24px 0;">
    <div class="container">
        <h1>Shopping Cart</h1>
        <div class="breadcrumb"><a href="<?= BASE_URL ?>">Home</a><span>›</span><span>Cart</span></div>
    </div>
</div>

<div class="container">
    <?php if (empty($cart)): ?>
    <div class="empty-state" style="padding: 80px 0;">
        <i class="fas fa-shopping-bag"></i>
        <h3>Your cart is empty</h3>
        <p>Looks like you haven't added any items to your cart yet.</p>
        <a href="<?= BASE_URL ?>pages/category.php" class="btn btn-primary">Start Shopping</a>
    </div>


    <?php else: ?>
    <div class="cart-layout">
        <!-- Cart Items -->
        <div>
            <div class="cart-table">
                <div
                    style="padding: 16px 20px; border-bottom: 2px solid var(--border); display: flex; align-items: center; justify-content: space-between;">
                    <h3 style="font-family: var(--font-head); font-size: 1.2rem;"><?= count($cart) ?>
                        Item<?= count($cart) > 1 ? 's' : '' ?> in Cart</h3>
                    <a href="<?= BASE_URL ?>pages/category.php" style="font-size: 13px; color: var(--crimson);">
                        <i class="fas fa-arrow-left"></i> Continue Shopping
                    </a>


                </div>

                <?php foreach ($cart as $item): ?>
                <div class="cart-item" id="cart-item-<?= (int)$item['id'] ?>">
                    <div class="cart-item-img">
                        <img src="<?= productImage($item['image'] ?? '') ?>" alt="<?= sanitize($item['name']) ?>">
                    </div>
                    <div>
                        <div class="cart-item-name">
                            <a
                                href="<?= BASE_URL ?>pages/product.php?slug=<?= urlencode($item['slug']) ?>"><?= sanitize($item['name']) ?></a>
                        </div>
                        <?php if ($item['size']): ?>
                        <div class="cart-item-meta">Size: <?= sanitize($item['size']) ?></div>
                        <?php endif; ?>
                        <div class="cart-item-meta"><?= formatPrice($item['price']) ?> each</div>
                    </div>
                    <div>
                        <form method="POST" style="display:flex; align-items:center;">
                            <input type="hidden" name="product_id" value="<?= (int)$item['product_id'] ?>">
                            <?php
                                    $userId = $_SESSION['user_id'] ?? null;
                                    $session_id = session_id();
                                    if ($userId) { ?>
                            <input type="hidden" name="user_id" value="<?= (int)$item['user_id'] ?>">
                            <?php } elseif ($session_id) { ?>
                            <input type="hidden" name="session_id" value="<?= $session_id ?>">
                            <?php } ?>
                            <div class="qty-ctrl" style="width: 110px;">
                                <button type="button" class="qty-minus"
                                    onclick="updateQty('<?= (int)$item['id'] ?>', -1)">−</button>

                                <input type="number" name="quantity" value="<?= (int)$item['quantity'] ?>" min="1"
                                    max="99" style="width:52px;text-align:center;border:none;outline:none;">

                                <button type="button" class="qty-plus"
                                    onclick="updateQty('<?= (int)$item['id'] ?>', +1)">+</button>
                            </div>

                            <button type="submit" class="btn btn-sm" style="margin-left:8px;" name="update"
                                title="Update">
                                Update
                            </button>
                        </form>

                    </div>
                    <div style="font-weight: 700; color: var(--crimson);">
                        <?= formatPrice($item['price'] * $item['quantity']) ?>
                    </div>
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="product_id" value="<?= (int)$item['product_id'] ?>">
                        <?php
                                $userId = $_SESSION['user_id'] ?? null;
                                $session_id = session_id();
                                if ($userId) { ?>
                        <input type="hidden" name="user_id" value="<?= (int)$item['user_id'] ?>">
                        <?php } elseif ($session_id) { ?>
                        <input type="hidden" name="session_id" value="<?= $session_id ?>">
                        <?php } ?>
                        <button class="cart-remove-btn" name="remove" title="Remove">
                            <i class="fas fa-times"></i>
                        </button>
                    </form>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Order Summary -->
        <div>
            <div class="cart-summary">
                <h3>Order Summary</h3>
                <div class="summary-row">
                    <span>Subtotal</span>
                    <span class="cart-subtotal"><?= formatPrice($subtotal) ?></span>
                </div>
                <div class="summary-row">
                    <span>Shipping</span>
                    <span><?= $shipping == 0 ? '<strong style="color:#28a745">FREE</strong>' : formatPrice($shipping) ?></span>
                </div>
                <?php if ($subtotal < $freeAbove): ?>
                <div class="free-shipping-note">
                    <i class="fas fa-truck"></i>
                    Add <?= formatPrice($freeAbove - $subtotal) ?> more for FREE shipping!
                </div>
                <?php endif; ?>
                <div class="summary-row total">
                    <span>Total</span>
                    <span class="cart-total"><?= formatPrice($total) ?></span>
                </div>
                <a href="<?= BASE_URL ?>pages/checkout.php" class="btn btn-primary btn-full" style="margin-top: 16px;">
                    Proceed to Checkout <i class="fas fa-arrow-right"></i>
                </a>
                <div style="text-align: center; margin-top: 14px;">
                    <img src="<?= BASE_URL ?>assets/images/upi-logos.svg" alt="UPI Payment"
                        style="max-height: 28px; margin: 0 auto;" onerror="this.style.display='none'">
                    <div style="display: flex; justify-content: center; gap: 8px; margin-top: 8px;">
                        <span style="font-size: 12px; color: var(--muted);">Pay via UPI · GPay · PhonePe · Paytm</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>


<?php require_once __DIR__ . '/../includes/footer.php'; ?>