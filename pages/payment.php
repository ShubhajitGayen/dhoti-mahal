<?php
// ============================================================
// Dhoti Mahal - Payment Page (Razorpay)
// ============================================================
$pageTitle = 'Complete Payment';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

$orderNumber = $_SESSION['pending_order'] ?? null;
if (!$orderNumber) {
    redirect(BASE_URL . 'pages/cart.php');
}

$order = getOrderByNumber($orderNumber);
if (!$order) {
    redirect(BASE_URL . 'pages/cart.php');
}

// Razorpay credentials from settings
$defaultRzpKeyId = 'rzp_test_XXXXXXXXXXXXXXXX';
$defaultRzpSecret = 'XXXXXXXXXXXXXXXXXXXXXXXX';
$rzpKeyId     = getSetting('razorpay_key_id',     $defaultRzpKeyId);
$rzpKeySecret = getSetting('razorpay_key_secret',  $defaultRzpSecret);
$rzpName      = getSetting('razorpay_name',         'Dhoti Mahal');
$rzpLogo      = getSetting('razorpay_logo',         BASE_URL . 'assets/images/logo.png');

if ($rzpKeyId === $defaultRzpKeyId || $rzpKeySecret === $defaultRzpSecret) {
    setFlash('error', 'Payment gateway is not configured. Please set your Razorpay Key ID and Secret in admin settings.');
    redirect(BASE_URL . 'pages/cart.php');
}

$amountPaise = (int) round((float)$order['total'] * 100); // Razorpay needs paise

// ── Create Razorpay Order (server-side) ───────────────────────────────────────
// Only create if not already created for this session order
if (empty($_SESSION['razorpay_order_id'])) {
    $rzpOrderPayload = json_encode([
        'amount'          => $amountPaise,
        'currency'        => 'INR',
        'receipt'         => $orderNumber,
        'payment_capture' => 1,
    ]);

    $ch = curl_init('https://api.razorpay.com/v1/orders');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $rzpOrderPayload,
        CURLOPT_USERPWD        => "$rzpKeyId:$rzpKeySecret",
        CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
    ]);
    $rzpResponse = curl_exec($ch);
    $curlError = curl_error($ch);
    $curlErrno = curl_errno($ch);
    curl_close($ch);
    $rzpOrder = json_decode($rzpResponse, true);

    if (!empty($rzpOrder['id'])) {
        $_SESSION['razorpay_order_id'] = $rzpOrder['id'];
        // Store razorpay_order_id in your DB for reconciliation
        $db = getDB();
        $db->prepare("UPDATE orders SET razorpay_order_id=? WHERE order_number=?")
            ->execute([$rzpOrder['id'], $orderNumber]);
    } else {
        $errorMessage = 'Payment gateway error. Please try again or contact support.';
        if ($curlErrno) {
            $errorMessage .= ' [Network error: ' . htmlspecialchars($curlError) . ']';
        } elseif (!empty($rzpOrder['error']['description'])) {
            $errorMessage .= ' [Gateway response: ' . htmlspecialchars($rzpOrder['error']['description']) . ']';
        } elseif ($rzpResponse !== false) {
            $errorMessage .= ' [Gateway response: ' . htmlspecialchars($rzpResponse) . ']';
        }
        setFlash('error', $errorMessage);
        redirect(BASE_URL . 'pages/cart.php');
    }
}

$razorpayOrderId = $_SESSION['razorpay_order_id'];

// ── Handle Razorpay Callback (POST after payment) ────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['razorpay_payment_id'])) {
    if (!validateCSRF($_POST[CSRF_TOKEN_NAME] ?? '')) {
        setFlash('error', 'Invalid request. Please try again.');
        redirect(BASE_URL . 'pages/payment.php');
    }

    $rpPaymentId = trim($_POST['razorpay_payment_id'] ?? '');
    $rpOrderId   = trim($_POST['razorpay_order_id']   ?? '');
    $rpSignature = trim($_POST['razorpay_signature']   ?? '');

    // Verify signature
    $expectedSig = hash_hmac('sha256', $rpOrderId . '|' . $rpPaymentId, $rzpKeySecret);

    if (hash_equals($expectedSig, $rpSignature)) {
        // Payment verified ✓
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

        $db->prepare("INSERT INTO order_tracking (order_id, status, message) VALUES (?,?,?)")
            ->execute([$order['id'], 'Payment Confirmed', 'Razorpay payment verified. Order confirmed.']);

        unset($_SESSION['pending_order'], $_SESSION['razorpay_order_id']);
        setFlash('success', 'Payment successful! Your order is confirmed.');
        redirect(BASE_URL . 'pages/confirmation.php?order=' . urlencode($orderNumber));
    } else {
        // Signature mismatch — possible tampering
        $db = getDB();
        $db->prepare("UPDATE orders SET payment_status='failed', updated_at=NOW() WHERE order_number=?")
            ->execute([$orderNumber]);
        setFlash('error', 'Payment verification failed. Please contact support with your transaction ID: ' . htmlspecialchars($rpPaymentId));
        redirect(BASE_URL . 'pages/payment.php');
    }
}

// ── Handle Razorpay Failure Callback ─────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['razorpay_error'])) {
    $errorCode = htmlspecialchars($_POST['razorpay_error_code'] ?? 'UNKNOWN');
    $errorDesc = htmlspecialchars($_POST['razorpay_error_description'] ?? 'Payment failed.');
    setFlash('error', "Payment failed ($errorCode): $errorDesc. Please try again.");
    redirect(BASE_URL . 'pages/payment.php');
}

require_once __DIR__ . '/../includes/header.php';

// Customer info for prefill
$custName  = sanitize($order['customer_name']  ?? '');
$custEmail = sanitize($order['customer_email'] ?? '');
$custPhone = sanitize($order['customer_phone'] ?? '');
?>

<script>
    window.__BASE_URL = '<?= BASE_URL ?>';
</script>

<!-- Razorpay Checkout SDK -->
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>

<div class="page-hero" style="padding:24px 0;">
    <div class="container">
        <h1>Complete Your Payment</h1>
        <div class="breadcrumb">
            <a href="<?= BASE_URL ?>">Home</a><span>›</span><span>Payment</span>
        </div>
    </div>
</div>

<div class="container" style="padding: 40px 20px;">
    <div class="payment-box">

        <h2>🔒 Secure Payment</h2>
        <p style="color:var(--muted);margin-bottom:0;">Order #<?= sanitize($orderNumber) ?></p>

        <!-- Order Summary -->
        <div class="amount-box" style="margin: 24px 0;">
            <?= formatPrice((float)$order['total']) ?>
        </div>

        <!-- Flash messages -->
        <?php if ($flash = getFlash('error')): ?>
            <div class="alert alert-danger" style="margin-bottom:16px;"><?= $flash ?></div>
        <?php endif; ?>

        <!-- Pay Button -->
        <button id="rzp-pay-btn" class="btn btn-primary btn-full" style="font-size:1rem;padding:14px;">
            <i class="fas fa-lock"></i> Pay <?= formatPrice((float)$order['total']) ?> Securely
        </button>

        <!-- Hidden form — submitted by JS after Razorpay success -->
        <form id="rzp-success-form" method="POST" style="display:none;">
            <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= generateCSRF() ?>">
            <input type="hidden" name="razorpay_payment_id" id="rzp_payment_id">
            <input type="hidden" name="razorpay_order_id" id="rzp_order_id">
            <input type="hidden" name="razorpay_signature" id="rzp_signature">
        </form>

        <!-- Hidden form — submitted by JS after Razorpay failure -->
        <form id="rzp-failure-form" method="POST" style="display:none;">
            <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= generateCSRF() ?>">
            <input type="hidden" name="razorpay_error" value="1">
            <input type="hidden" name="razorpay_error_code" id="rzp_error_code">
            <input type="hidden" name="razorpay_error_description" id="rzp_error_description">
        </form>

        <p style="text-align:center;font-size:12px;color:var(--muted);margin-top:16px;">
            Powered by <strong>Razorpay</strong> · UPI, Cards, Net Banking, Wallets accepted
        </p>

        <hr style="border:none;border-top:1px solid var(--border);margin:24px 0;">

        <p style="text-align:center;font-size:13px;color:var(--muted);">
            Having trouble? Contact us on
            <a href="https://wa.me/<?= sanitize(getSetting('whatsapp_number')) ?>"
                style="color:#25D366;font-weight:600;">
                <i class="fab fa-whatsapp"></i> WhatsApp
            </a>
        </p>
    </div>
</div>

<script>
    (function() {
        var options = {
            key: '<?= $rzpKeyId ?>',
            amount: <?= $amountPaise ?>,
            currency: 'INR',
            name: '<?= addslashes($rzpName) ?>',
            description: 'Order #<?= addslashes($orderNumber) ?>',
            image: '<?= $rzpLogo ?>',
            order_id: '<?= $razorpayOrderId ?>',

            prefill: {
                name: '<?= addslashes($custName) ?>',
                email: '<?= addslashes($custEmail) ?>',
                contact: '<?= addslashes($custPhone) ?>'
            },

            theme: {
                color: '#c9a84c'
            }, // match your --gold variable

            handler: function(response) {
                // Payment succeeded — populate and submit success form
                document.getElementById('rzp_payment_id').value = response.razorpay_payment_id;
                document.getElementById('rzp_order_id').value = response.razorpay_order_id;
                document.getElementById('rzp_signature').value = response.razorpay_signature;
                document.getElementById('rzp-success-form').submit();
            },

            modal: {
                ondismiss: function() {
                    // User closed the modal without paying — optionally show a message
                    var btn = document.getElementById('rzp-pay-btn');
                    btn.disabled = false;
                    btn.innerHTML =
                        '<i class="fas fa-lock"></i> Pay <?= formatPrice((float)$order['total']) ?> Securely';
                }
            }
        };

        var rzp = new Razorpay(options);

        rzp.on('payment.failed', function(response) {
            document.getElementById('rzp_error_code').value = response.error.code || '';
            document.getElementById('rzp_error_description').value = response.error.description ||
                'Payment failed.';
            document.getElementById('rzp-failure-form').submit();
        });

        document.getElementById('rzp-pay-btn').addEventListener('click', function(e) {
            e.preventDefault();
            this.disabled = true;
            this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Opening payment...';
            rzp.open();
        });
    })();
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>