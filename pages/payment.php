<?php
// ============================================================
// Dhoti Mahal - Payment Page (UPI/QR)
// ============================================================
$pageTitle = 'Complete Payment';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

$orderNumber = $_SESSION['pending_order'] ?? null;
if (!$orderNumber) { redirect(BASE_URL . 'pages/cart.php'); }

$order = getOrderByNumber($orderNumber);
if (!$order) { redirect(BASE_URL . 'pages/cart.php'); }

$upiId   = getSetting('upi_id', 'dhotimahal@upi');
$upiName = getSetting('upi_name', 'Dhoti Mahal');
$amount  = number_format((float)$order['total'], 2, '.', '');

// Handle payment confirmation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_payment'])) {
    if (!validateCSRF($_POST[CSRF_TOKEN_NAME] ?? '')) {
        setFlash('error', 'Invalid request. Please try again.');
    } else {
        $ref = trim($_POST['payment_ref'] ?? '');
        $db  = getDB();
        $db->prepare("UPDATE orders SET payment_status='paid', payment_ref=?, order_status='confirmed', updated_at=NOW() WHERE order_number=?")
           ->execute([$ref, $orderNumber]);
        $db->prepare("INSERT INTO order_tracking (order_id, status, message) VALUES (?,?,?)")
           ->execute([$order['id'], 'Payment Confirmed', 'Payment verified. Your order is confirmed.']);

        unset($_SESSION['pending_order']);
        setFlash('success', 'Payment received! Your order is confirmed.');
        redirect(BASE_URL . 'pages/confirmation.php?order=' . urlencode($orderNumber));
    }
}

require_once __DIR__ . '/../includes/header.php';

// Generate UPI deep link
$upiLink = "upi://pay?pa=" . urlencode($upiId) . "&pn=" . urlencode($upiName) . "&am=$amount&cu=INR&tn=" . urlencode("Order " . $orderNumber);
// QR using Google Charts API
$qrUrl = "https://chart.googleapis.com/chart?cht=qr&chs=200x200&chl=" . urlencode($upiLink);
?>
<script>window.__BASE_URL = '<?= BASE_URL ?>';</script>

<div class="page-hero" style="padding:24px 0;">
    <div class="container">
        <h1>Complete Your Payment</h1>
        <div class="breadcrumb"><a href="<?= BASE_URL ?>">Home</a><span>›</span><span>Payment</span></div>
    </div>
</div>

<div class="container" style="padding: 40px 20px;">
<div class="payment-box">
    <h2>🔒 Secure UPI Payment</h2>
    <p style="color:var(--muted);margin-bottom:0;">Order #<?= sanitize($orderNumber) ?></p>

    <div class="qr-code-box">
        <p style="font-size: 13px; color: var(--muted); margin-bottom: 12px;">Scan QR with any UPI app</p>
        <img src="<?= $qrUrl ?>" alt="UPI QR Code" style="width: 200px; height: 200px; margin: 0 auto;">

        <div class="amount-box"><?= formatPrice((float)$order['total']) ?></div>
        <div>UPI ID: <span class="upi-id"><?= sanitize($upiId) ?></span></div>
        <button class="btn btn-outline btn-sm" id="copyUpiBtn" data-upi="<?= sanitize($upiId) ?>" style="margin-top:10px;">
            <i class="fas fa-copy"></i> Copy UPI ID
        </button>
    </div>

    <ol class="payment-steps">
        <li>Open GPay, PhonePe, Paytm or any UPI app</li>
        <li>Scan the QR code above or enter UPI ID <strong><?= sanitize($upiId) ?></strong></li>
        <li>Enter amount: <strong><?= formatPrice((float)$order['total']) ?></strong></li>
        <li>Complete payment and note the UTR / Transaction ID</li>
        <li>Enter the Transaction ID below to confirm your order</li>
    </ol>

    <!-- Also available as app link -->
    <div style="text-align:center;margin:16px 0;">
        <a href="<?= sanitize($upiLink) ?>" class="btn btn-gold" style="width:100%;justify-content:center;">
            <i class="fas fa-mobile-alt"></i> Pay via UPI App
        </a>
    </div>

    <hr style="border:none;border-top:1px solid var(--border);margin:24px 0;">

    <form method="POST">
        <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= generateCSRF() ?>">
        <input type="hidden" name="confirm_payment" value="1">
        <div class="form-group">
            <label>Transaction ID / UTR Number *</label>
            <input type="text" name="payment_ref" required placeholder="e.g. 421234567890" style="font-size: 16px; letter-spacing: 1px;">
            <div style="font-size: 12px; color: var(--muted); margin-top: 6px;">Enter the 12-digit UTR or transaction reference after successful payment</div>
        </div>
        <button type="submit" class="btn btn-primary btn-full" style="font-size: 1rem; padding: 14px;">
            <i class="fas fa-check-circle"></i> Confirm Payment & Place Order
        </button>
    </form>

    <p style="text-align:center;font-size:13px;color:var(--muted);margin-top:16px;">
        Having trouble? Contact us on <a href="https://wa.me/<?= sanitize(getSetting('whatsapp_number')) ?>" style="color:#25D366;font-weight:600;"><i class="fab fa-whatsapp"></i> WhatsApp</a>
    </p>
</div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
