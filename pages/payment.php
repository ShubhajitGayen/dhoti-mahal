<?php
// ============================================================
// Dhoti Mahal - Payment Page (Razorpay) [PRO VERSION]
// ============================================================

$pageTitle = 'Complete Payment';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

// 🔒 FORCE HTTPS
if (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] === 'off') {
    die('Payment requires HTTPS connection.');
}

// ── GET ORDER ────────────────────────────────────────────────
$orderNumber = $_SESSION['pending_order'] ?? null;
if (!$orderNumber) redirect(BASE_URL . 'pages/cart.php');

$order = getOrderByNumber($orderNumber);
if (!$order) redirect(BASE_URL . 'pages/cart.php');

// 🛑 Idempotency check
if ($order['payment_status'] === 'paid') {
    redirect(BASE_URL . 'pages/confirmation.php?order=' . urlencode($orderNumber));
}

// ── RAZORPAY CONFIG ─────────────────────────────────────────
$rzpKeyId     = getSetting('razorpay_key_id');
$rzpKeySecret = getSetting('razorpay_key_secret');
$rzpName      = getSetting('razorpay_name', 'Dhoti Mahal');
$rzpLogo      = getSetting('razorpay_logo', BASE_URL . 'assets/images/logo.png');

if (!$rzpKeyId || !$rzpKeySecret) {
    setFlash('error', 'Payment gateway not configured.');
    redirect(BASE_URL . 'pages/cart.php');
}

$amountPaise = (int) round($order['total'] * 100);

// ── CREATE / FETCH RAZORPAY ORDER ───────────────────────────
$razorpayOrderId = $order['razorpay_order_id'];

if (!$razorpayOrderId) {

    $payload = json_encode([
        'amount' => $amountPaise,
        'currency' => 'INR',
        'receipt' => $orderNumber,
        'payment_capture' => 1
    ]);

    $ch = curl_init('https://api.razorpay.com/v1/orders');

    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $payload,
        CURLOPT_USERPWD => "$rzpKeyId:$rzpKeySecret",
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        CURLOPT_TIMEOUT => 30,
        CURLOPT_CONNECTTIMEOUT => 10
    ]);

    $response = curl_exec($ch);
    $error    = curl_error($ch);
    curl_close($ch);

    $data = json_decode($response, true);

    if (!empty($data['id'])) {
        $razorpayOrderId = $data['id'];

        getDB()->prepare("
            UPDATE orders SET razorpay_order_id=? WHERE order_number=?
        ")->execute([$razorpayOrderId, $orderNumber]);
    } else {
        error_log("Razorpay Error: " . $response);
        setFlash('error', 'Payment initialization failed.');
        redirect(BASE_URL . 'pages/cart.php');
    }
}

// ── PAYMENT SUCCESS HANDLER ─────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['razorpay_payment_id'])) {

    if (!validateCSRF($_POST[CSRF_TOKEN_NAME] ?? '')) {
        setFlash('error', 'Invalid request.');
        redirect(BASE_URL . 'pages/payment.php');
    }

    $rpPaymentId = $_POST['razorpay_payment_id'];
    $rpOrderId   = $_POST['razorpay_order_id'];
    $rpSignature = $_POST['razorpay_signature'];

    // 🔐 Signature verify
    $expected = hash_hmac('sha256', $rpOrderId . '|' . $rpPaymentId, $rzpKeySecret);

    if (!hash_equals($expected, $rpSignature)) {
        setFlash('error', 'Payment verification failed.');
        redirect(BASE_URL . 'pages/payment.php');
    }

    // 🔍 EXTRA VERIFICATION FROM RAZORPAY API
    $ch = curl_init("https://api.razorpay.com/v1/payments/$rpPaymentId");

    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_USERPWD => "$rzpKeyId:$rzpKeySecret"
    ]);

    $verifyRes = curl_exec($ch);
    curl_close($ch);

    $payment = json_decode($verifyRes, true);

    if (($payment['status'] ?? '') !== 'captured') {
        setFlash('error', 'Payment not completed.');
        redirect(BASE_URL . 'pages/payment.php');
    }

    // ✅ UPDATE ORDER
    $db = getDB();

    $db->prepare("
        UPDATE orders
        SET payment_status='paid',
            payment_ref=?,
            razorpay_payment_id=?,
            razorpay_signature=?,
            order_status='confirmed',
            updated_at=NOW()
        WHERE order_number=?
    ")->execute([$rpPaymentId, $rpPaymentId, $rpSignature, $orderNumber]);

    $db->prepare("
        INSERT INTO order_tracking (order_id, status, message)
        VALUES (?,?,?)
    ")->execute([$order['id'], 'Payment Confirmed', 'Payment verified via Razorpay API']);

    unset($_SESSION['pending_order']);

    setFlash('success', 'Payment successful!');
    redirect(BASE_URL . 'pages/confirmation.php?order=' . urlencode($orderNumber));
}

// ── PAYMENT FAILURE ─────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['razorpay_error'])) {

    setFlash('error', 'Payment failed. Try again.');
    redirect(BASE_URL . 'pages/payment.php');
}

// ── UI ─────────────────────────────────────────────────────
require_once __DIR__ . '/../includes/header.php';

$custName  = sanitize($order['customer_name']);
$custEmail = sanitize($order['customer_email']);
$custPhone = sanitize($order['customer_phone']);
?>

<script src="https://checkout.razorpay.com/v1/checkout.js"></script>

<div class="container" style="padding:40px;">
    <h2>Secure Payment</h2>
    <p>Order #<?= sanitize($orderNumber) ?></p>

    <h3><?= formatPrice($order['total']) ?></h3>

    <button id="pay-btn">Pay Now</button>

    <form id="success-form" method="POST" style="display:none;">
        <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= generateCSRF() ?>">
        <input type="hidden" name="razorpay_payment_id" id="pid">
        <input type="hidden" name="razorpay_order_id" id="oid">
        <input type="hidden" name="razorpay_signature" id="sig">
    </form>
</div>

<script>
    var options = {
        key: "<?= $rzpKeyId ?>",
        amount: <?= $amountPaise ?>,
        currency: "INR",
        name: "<?= $rzpName ?>",
        order_id: "<?= $razorpayOrderId ?>",

        prefill: {
            name: "<?= $custName ?>",
            email: "<?= $custEmail ?>",
            contact: "<?= $custPhone ?>"
        },

        handler: function(res) {
            document.getElementById("pid").value = res.razorpay_payment_id;
            document.getElementById("oid").value = res.razorpay_order_id;
            document.getElementById("sig").value = res.razorpay_signature;
            document.getElementById("success-form").submit();
        }
    };

    var rzp = new Razorpay(options);

    document.getElementById("pay-btn").onclick = function(e) {
        e.preventDefault();
        if (this.disabled) return;
        this.disabled = true;
        rzp.open();
    };
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>