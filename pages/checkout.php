<?php
// ============================================================
// Dhoti Mahal - Checkout Page
// ============================================================
$pageTitle = 'Checkout';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

// ============================================================
// CLEAN CHECKOUT LOGIC - Two flows: Buy Now vs Cart Checkout
// ============================================================

$cart = [];
$isBuyNow = false;

// ===== PHASE 1: Check for incoming Buy Now POST =====
if (!empty($_POST['buy_now_product_id'])) {
    $pid = (int)$_POST['buy_now_product_id'];
    $qty = max(1, (int)($_POST['buy_now_qty'] ?? 1));  // Ensure qty >= 1
    $size = !empty($_POST['buy_now_size']) ? $_POST['buy_now_size'] : '';

    // Validate product exists
    $product = getProductById($pid);
    if (!$product) {
        setFlash('error', 'Product not found.');
        redirect(BASE_URL);
    }

    // Store in SESSION for persistence across page reloads
    $_SESSION['buy_now'] = [
        'product_id' => (int)$product['id'],
        'name'       => $product['name'],
        'image'      => $product['image'],
        'price'      => (float)$product['price'],
        'quantity'   => $qty,
        'size'       => $size
    ];

    $isBuyNow = true;
}

// ===== PHASE 2: Load cart (Buy Now from SESSION or Database) =====
if (!empty($_SESSION['buy_now'])) {
    // Buy Now flow: use stored item from SESSION
    $cart = [$_SESSION['buy_now']];
    $isBuyNow = true;
} else {
    // Normal cart flow: load from database
    $cart = getCart();

    if (empty($cart)) {
        setFlash('info', 'Your cart is empty.');
        redirect(BASE_URL . 'pages/cart.php');
    }
}

// ===== PHASE 3: Calculate subtotal =====
$subtotal = 0;
foreach ($cart as $item) {
    $subtotal += (float)$item['price'] * (int)$item['quantity'];
}

refreshUserSession();
$currentUser = getLoggedUser();

$shipping = calculateShipping($subtotal);
$total    = $subtotal + $shipping;
$errors   = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order'])) {
    // Validate CSRF
    if (!validateCSRF($_POST[CSRF_TOKEN_NAME] ?? '')) {
        $errors[] = 'Invalid form submission. Please try again.';
    } else {
        $name    = trim($_POST['name'] ?? '');
        $email   = trim($_POST['email'] ?? '');
        $phone   = trim($_POST['phone'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $city    = trim($_POST['city'] ?? '');
        $state   = trim($_POST['state'] ?? '');
        $pincode = trim($_POST['pincode'] ?? '');
        $notes   = trim($_POST['notes'] ?? '');
        $paymentMethod = strtolower(trim($_POST['payment_method'] ?? 'upi'));
        $allowedMethods = ['upi'];
        if (!in_array($paymentMethod, $allowedMethods, true)) {
            $paymentMethod = 'upi';
        }

        if (!$name)    $errors[] = 'Full name is required.';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email is required.';
        if (!$phone || !preg_match('/^[6-9]\d{9}$/', $phone)) $errors[] = 'Valid 10-digit mobile number is required.';
        if (!$address) $errors[] = 'Delivery address is required.';
        if (!$city)    $errors[] = 'City is required.';
        if (!$state)   $errors[] = 'State is required.';
        if (!$pincode || !preg_match('/^\d{6}$/', $pincode)) $errors[] = 'Valid 6-digit pincode is required.';

        if (empty($errors)) {
            $orderNumber = generateOrderNumber();
            $orderId = placeOrder([
                'order_number' => $orderNumber,
                'user_id'      => $currentUser ? $currentUser['id'] : null,
                'name'         => $name,
                'email'        => $email,
                'phone'        => $phone,
                'address'      => $address,
                'city'         => $city,
                'state'        => $state,
                'pincode'      => $pincode,
                'subtotal'     => $subtotal,
                'shipping'     => $shipping,
                'payment_method' => $paymentMethod,
                'notes'        => $notes,
            ], $cart);

            if ($orderId) {
                $_SESSION['pending_order'] = $orderNumber;

                // Clean up based on flow type
                if ($isBuyNow) {
                    // Buy Now → unset the buy_now session
                    unset($_SESSION['buy_now']);
                } else {
                    // Cart checkout → clear full cart from database
                    clearCart();
                }
                redirect(BASE_URL . 'pages/payment.php');
            } else {
                $errors[] = 'Order could not be placed. Please try again.';
            }
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
?>
<script>
    window.__BASE_URL = '<?= BASE_URL ?>';
</script>

<div class="page-hero" style="padding:24px 0;">
    <div class="container">
        <h1>Checkout</h1>
        <div class="breadcrumb">
            <a href="<?= BASE_URL ?>">Home</a><span>›</span>
            <a href="<?= BASE_URL ?>pages/cart.php">Cart</a><span>›</span>
            <span>Checkout</span>
        </div>
    </div>
</div>

<div class="container">
    <?php if (!empty($errors)): ?>
        <div class="flash-message flash-error" style="border-radius:6px;margin-top:20px;">
            <div style="padding: 14px 20px;">
                <strong>Please fix the following:</strong>
                <ul style="margin:8px 0 0 20px;">
                    <?php foreach ($errors as $e): ?><li><?= sanitize($e) ?></li><?php endforeach; ?>
                </ul>
            </div>
        </div>
    <?php endif; ?>

    <form method="POST">
        <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= generateCSRF() ?>">
        <input type="hidden" name="place_order" value="1">

        <div class="checkout-grid">
            <!-- Left: Form -->
            <div>
                <!-- Personal Info -->
                <div class="checkout-section">
                    <h3><i class="fas fa-user"></i> Personal Information</h3>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Full Name *</label>
                            <input type="text" name="name"
                                value="<?= sanitize($currentUser['name'] ?? $_POST['name'] ?? '') ?>" required
                                placeholder="Your full name">
                        </div>
                        <div class="form-group">
                            <label>Mobile Number *</label>
                            <input type="tel" name="phone"
                                value="<?= sanitize($currentUser['phone'] ?? $_POST['phone'] ?? '') ?>" required
                                placeholder="10-digit mobile">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Email Address *</label>
                        <input type="email" name="email"
                            value="<?= sanitize($currentUser['email'] ?? $_POST['email'] ?? '') ?>" required
                            placeholder="your@email.com">
                    </div>
                </div>

                <!-- Shipping Address -->
                <div class="checkout-section">
                    <h3><i class="fas fa-map-marker-alt"></i> Delivery Address</h3>
                    <div class="form-group">
                        <label>Street Address *</label>
                        <textarea name="address" rows="3" required
                            placeholder="House/Flat No., Street, Area..."><?= sanitize($currentUser['address'] ?? $_POST['address'] ?? '') ?></textarea>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>City *</label>
                            <input type="text" name="city"
                                value="<?= sanitize($currentUser['city'] ?? $_POST['city'] ?? '') ?>" required
                                placeholder="City">
                        </div>
                        <div class="form-group">
                            <label>State *</label>
                            <input type="text" name="state" id="state"
                                value="<?= sanitize($currentUser['state'] ?? $_POST['state'] ?? '') ?>" required
                                placeholder="State">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Pincode *</label>
                            <input type="text" name="pincode"
                                value="<?= sanitize($currentUser['pincode'] ?? $_POST['pincode'] ?? '') ?>" required
                                placeholder="6-digit pincode" maxlength="6">
                        </div>
                        <div class="form-group">
                            <label>Order Notes (Optional)</label>
                            <input type="text" name="notes" value="<?= sanitize($_POST['notes'] ?? '') ?>"
                                placeholder="Any special instructions">
                        </div>
                    </div>
                </div>

                <!-- Payment Method -->
                <div class="checkout-section">
                    <h3><i class="fas fa-credit-card"></i> Payment Method</h3>
                    <div
                        style="background: var(--cream); border: 2px solid var(--gold); border-radius: 6px; padding: 16px 20px; display: flex; align-items: center; gap: 12px;">
                        <input type="radio" name="payment_method" value="upi" checked id="pay_upi">
                        <label for="pay_upi" style="font-weight: 700; cursor: pointer;">
                            📱 UPI Payment (GPay / PhonePe / Paytm / Any UPI)
                        </label>
                    </div>
                    <p style="font-size: 13px; color: var(--muted); margin-top: 10px;">
                        <i class="fas fa-shield-alt" style="color: var(--gold)"></i>
                        After placing the order, the secure Razorpay checkout will open. Select UPI and complete payment
                        from there.
                    </p>
                </div>
            </div>

            <!-- Right: Order Summary -->
            <div>
                <div class="cart-summary">
                    <h3>Order Summary</h3>
                    <?php foreach ($cart as $item): ?>
                        <div
                            style="display:flex;gap:10px;margin-bottom:12px;align-items:center;padding-bottom:12px;border-bottom:1px solid var(--border);">
                            <img src="<?= productImage($item['image'] ?? '') ?>"
                                style="width:50px;height:60px;object-fit:cover;border-radius:4px;" alt="">
                            <div style="flex:1;">
                                <div style="font-size:13px;font-weight:600;"><?= sanitize($item['name']) ?></div>
                                <?php if ($item['size']): ?><div style="font-size:12px;color:var(--muted);">Size:
                                        <?= sanitize($item['size']) ?></div><?php endif; ?>
                                <div style="font-size:13px;color:var(--muted);">Qty: <?= (int)$item['quantity'] ?></div>
                            </div>
                            <div style="font-weight:700;font-size:14px;">
                                <?= formatPrice($item['price'] * $item['quantity']) ?></div>
                        </div>
                    <?php endforeach; ?>

                    <div class="summary-row"><span>Subtotal</span><span><?= formatPrice($subtotal) ?></span></div>
                    <div class="summary-row">
                        <span>Shipping</span><span><?= $shipping == 0 ? '<strong style="color:#28a745">FREE</strong>' : formatPrice($shipping) ?></span>
                    </div>
                    <div class="summary-row total"><span>Total</span><span><?= formatPrice($total) ?></span></div>

                    <button type="submit" class="btn btn-primary btn-full"
                        style="margin-top: 20px; font-size: 1rem; padding: 14px;">
                        <i class="fas fa-lock"></i> Place Order Securely
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>