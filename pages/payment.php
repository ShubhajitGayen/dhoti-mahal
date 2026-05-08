<?php
// ============================================================
// Dhoti Mahal - Payment Page (Razorpay) [IMPROVED]
// ============================================================

$pageTitle = 'Complete Payment';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

// ── SECURITY: Force HTTPS (skipped on localhost for local dev) ──
$isLocalhost = in_array($_SERVER['HTTP_HOST'], ['localhost', '127.0.0.1', '::1'], true)
    || str_ends_with($_SERVER['HTTP_HOST'], '.local');

if (!$isLocalhost && (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] === 'off')) {
    header('Location: https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'], true, 301);
    exit;
}

// ── SECURITY: Prevent clickjacking ──────────────────────────
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');

// ── GET ORDER ────────────────────────────────────────────────
$orderNumber = $_SESSION['pending_order'] ?? null;
if (!$orderNumber) {
    redirect(BASE_URL . 'pages/cart.php');
}

$order = getOrderByNumber($orderNumber);
if (!$order) {
    redirect(BASE_URL . 'pages/cart.php');
}

// ── Idempotency: already paid ────────────────────────────────
if ($order['payment_status'] === 'paid') {
    redirect(BASE_URL . 'pages/confirmation.php?order=' . urlencode($orderNumber));
}

// ── RAZORPAY CONFIG ──────────────────────────────────────────
$rzpKeyId     = getSetting('razorpay_key_id');
$rzpKeySecret = getSetting('razorpay_key_secret');
$rzpName      = getSetting('razorpay_name', 'Dhoti Mahal');
$rzpLogo      = getSetting('razorpay_logo', BASE_URL . 'assets/images/logo.png');

// Detect whether Razorpay is running in test or live mode
$isTestMode = str_starts_with($rzpKeyId, 'rzp_test_');
$isLiveMode = str_starts_with($rzpKeyId, 'rzp_live_');

if (!$rzpKeyId || !$rzpKeySecret) {
    setFlash('error', 'Payment gateway not configured. Please contact support.');
    redirect(BASE_URL . 'pages/cart.php');
}

// ── VALIDATE KEY FORMAT (must be publishable key) ────────────
if (!str_starts_with($rzpKeyId, 'rzp_')) {
    error_log('Razorpay: Invalid key_id format configured.');
    setFlash('error', 'Payment gateway misconfigured.');
    redirect(BASE_URL . 'pages/cart.php');
}

$amountPaise = (int) round($order['total'] * 100);

// ── VALIDATE AMOUNT ──────────────────────────────────────────
// Razorpay minimum is 100 paise (₹1). Also sanity-cap at ₹5,00,000.
if ($amountPaise < 100 || $amountPaise > 50000000) {
    setFlash('error', 'Invalid order amount.');
    redirect(BASE_URL . 'pages/cart.php');
}

// ── CREATE / FETCH RAZORPAY ORDER ────────────────────────────
$razorpayOrderId = $order['razorpay_order_id'] ?? null;

if (!$razorpayOrderId) {
    $payload = json_encode([
        'amount'          => $amountPaise,
        'currency'        => 'INR',
        'receipt'         => $orderNumber,
        'payment_capture' => 1,
        'notes'           => [
            'order_number' => $orderNumber,
            'source'       => 'dhoti_mahal_web',
        ],
    ]);

    $ch = curl_init('https://api.razorpay.com/v1/orders');

    $curlOptions = [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $payload,
        CURLOPT_USERPWD        => "$rzpKeyId:$rzpKeySecret",
        CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
        CURLOPT_TIMEOUT        => 30,
        CURLOPT_CONNECTTIMEOUT => 10,
        // SECURITY: enforce TLS certificate verification
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_SSL_VERIFYHOST => 2,
    ];

    // For Windows/XAMPP environments, add CA bundle path
    $caBundlePath = ini_get('curl.cainfo');
    if (!$caBundlePath || !file_exists($caBundlePath)) {
        // Try common locations for CA bundle
        $commonPaths = [
            __DIR__ . '/../cacert.pem',  // Project root
            'C:/xampp/php/extras/ssl/cacert.pem',       // Common XAMPP path
            'C:/Program Files/xampp/php/extras/ssl/cacert.pem',
            '/usr/local/share/ca-certificates/cacert.pem',
        ];

        foreach ($commonPaths as $path) {
            if (file_exists($path)) {
                $caBundlePath = $path;
                break;
            }
        }
    }

    if ($caBundlePath && file_exists($caBundlePath)) {
        $curlOptions[CURLOPT_CAINFO] = $caBundlePath;
    } elseif (!IS_PRODUCTION) {
        // In development only, allow unverified SSL (not recommended for production)
        error_log("Warning: CA bundle not found. SSL verification disabled for development.");
        $curlOptions[CURLOPT_SSL_VERIFYPEER] = false;
    }

    curl_setopt_array($ch, $curlOptions);

    $response = curl_exec($ch);
    $curlErr  = curl_error($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($curlErr) {
        error_log("Razorpay order creation failed - cURL error: $curlErr (code: " . curl_errno($ch) . ")");
        setFlash('error', 'Payment gateway unreachable. Please check your internet connection and try again.');
        redirect(BASE_URL . 'pages/cart.php');
    }

    $data = json_decode($response, true);

    if ($httpCode === 200 && !empty($data['id'])) {
        $razorpayOrderId = $data['id'];

        // SECURITY: validate the returned order ID looks like a real RZP order
        if (!preg_match('/^order_[A-Za-z0-9]{14,}$/', $razorpayOrderId)) {
            error_log("Razorpay: suspicious order_id returned: $razorpayOrderId");
            setFlash('error', 'Payment initialization error.');
            redirect(BASE_URL . 'pages/cart.php');
        }

        getDB()->prepare("
            UPDATE orders SET razorpay_order_id = ?, updated_at = NOW()
            WHERE order_number = ?
        ")->execute([$razorpayOrderId, $orderNumber]);
    } else {
        $jsonError = json_last_error_msg();
        error_log("Razorpay order create failed (HTTP $httpCode): $response json_error=$jsonError");
        setFlash('error', 'Payment initialization failed. Please try again.');
        redirect(BASE_URL . 'pages/cart.php');
    }
}

// ── POST: PAYMENT SUCCESS HANDLER ────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // SECURITY: Reject all non-POST methods at protocol level
    if (!in_array($_SERVER['REQUEST_METHOD'], ['POST'], true)) {
        http_response_code(405);
        exit('Method Not Allowed');
    }

    // ── Payment success callback ──────────────────────────────
    if (isset($_POST['razorpay_payment_id'])) {

        // CSRF
        if (!validateCSRF($_POST[CSRF_TOKEN_NAME] ?? '')) {
            setFlash('error', 'Invalid request. Please try again.');
            redirect(BASE_URL . 'pages/payment.php');
        }

        $rpPaymentId = trim($_POST['razorpay_payment_id'] ?? '');
        $rpOrderId   = trim($_POST['razorpay_order_id']   ?? '');
        $rpSignature = trim($_POST['razorpay_signature']   ?? '');

        // SECURITY: Validate field formats before using them
        if (
            !preg_match('/^pay_[A-Za-z0-9]{14,}$/', $rpPaymentId) ||
            !preg_match('/^order_[A-Za-z0-9]{14,}$/', $rpOrderId) ||
            !preg_match('/^[a-f0-9]{64}$/', $rpSignature)
        ) {
            error_log("Razorpay: invalid POST field formats. pay=$rpPaymentId ord=$rpOrderId");
            setFlash('error', 'Invalid payment data.');
            redirect(BASE_URL . 'pages/payment.php');
        }

        // SECURITY: Ensure order ID matches what WE created (prevents order-swap attacks)
        if (!hash_equals($razorpayOrderId, $rpOrderId)) {
            error_log("Razorpay: order_id mismatch. Expected $razorpayOrderId, got $rpOrderId");
            setFlash('error', 'Payment verification failed.');
            redirect(BASE_URL . 'pages/payment.php');
        }

        // 🔐 HMAC-SHA256 signature verification
        $expected = hash_hmac('sha256', $rpOrderId . '|' . $rpPaymentId, $rzpKeySecret);

        if (!hash_equals($expected, $rpSignature)) {
            error_log("Razorpay: signature mismatch for order $rpOrderId");
            setFlash('error', 'Payment verification failed.');
            redirect(BASE_URL . 'pages/payment.php');
        }

        // 🔍 Server-side payment verification via Razorpay API
        $ch = curl_init("https://api.razorpay.com/v1/payments/" . urlencode($rpPaymentId));

        $verifyOptions = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_USERPWD        => "$rzpKeyId:$rzpKeySecret",
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
        ];

        // Apply CA bundle if available (same logic as order creation)
        $caBundlePath = ini_get('curl.cainfo');
        if (!$caBundlePath || !file_exists($caBundlePath)) {
            $commonPaths = [
                __DIR__ . '/../cacert.pem',
                'C:/xampp/php/extras/ssl/cacert.pem',
                'C:/Program Files/xampp/php/extras/ssl/cacert.pem',
                '/usr/local/share/ca-certificates/cacert.pem',
            ];
            foreach ($commonPaths as $path) {
                if (file_exists($path)) {
                    $caBundlePath = $path;
                    break;
                }
            }
        }

        if ($caBundlePath && file_exists($caBundlePath)) {
            $verifyOptions[CURLOPT_CAINFO] = $caBundlePath;
        } elseif (!IS_PRODUCTION) {
            error_log("Warning: CA bundle not found. SSL verification disabled for development.");
            $verifyOptions[CURLOPT_SSL_VERIFYPEER] = false;
        }

        curl_setopt_array($ch, $verifyOptions);

        $verifyRes  = curl_exec($ch);
        $verifyCurl = curl_error($ch);
        $verifyHttp = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($verifyCurl || $verifyHttp !== 200) {
            error_log("Razorpay verify API failed (HTTP $verifyHttp / cURL: $verifyCurl)");
            setFlash('error', 'Could not verify payment. Contact support with order #' . htmlspecialchars($orderNumber));
            redirect(BASE_URL . 'pages/payment.php');
        }

        $payment = json_decode($verifyRes, true);

        // SECURITY: Cross-check amount and currency from Razorpay API response
        if (
            ($payment['status']   ?? '')    !== 'captured'   ||
            ($payment['amount']   ?? 0)     !== $amountPaise ||
            ($payment['currency'] ?? '')    !== 'INR'        ||
            ($payment['order_id'] ?? '')    !== $razorpayOrderId
        ) {
            error_log("Razorpay: payment verification mismatch. Response: $verifyRes");
            setFlash('error', 'Payment verification failed. Contact support.');
            redirect(BASE_URL . 'pages/payment.php');
        }

        // ✅ All checks passed — update DB in a transaction
        $db = getDB();

        try {
            $db->beginTransaction();

            $db->prepare("
                UPDATE orders
                SET payment_status      = 'paid',
                    payment_ref         = ?,
                    razorpay_payment_id = ?,
                    razorpay_signature  = ?,
                    order_status        = 'confirmed',
                    updated_at          = NOW()
                WHERE order_number = ?
                  AND payment_status != 'paid'
            ")->execute([$rpPaymentId, $rpPaymentId, $rpSignature, $orderNumber]);

            $db->prepare("
                INSERT INTO order_tracking (order_id, status, message, created_at)
                VALUES (?, 'Payment Confirmed', 'Payment verified via Razorpay API', NOW())
            ")->execute([$order['id']]);

            $db->commit();
        } catch (\Throwable $e) {
            $db->rollBack();
            error_log("Razorpay DB update failed: " . $e->getMessage());
            setFlash('error', 'Order update failed. Contact support with order #' . htmlspecialchars($orderNumber));
            redirect(BASE_URL . 'pages/payment.php');
        }

        unset($_SESSION['pending_order']);

        setFlash('success', 'Payment successful! Your order is confirmed.');
        redirect(BASE_URL . 'pages/confirmation.php?order=' . urlencode($orderNumber));
    }

    // ── Payment failure callback ──────────────────────────────
    if (isset($_POST['razorpay_error'])) {
        // Log the error code from Razorpay for debugging
        $errCode = sanitize($_POST['razorpay_error_code']    ?? 'unknown');
        $errDesc = sanitize($_POST['razorpay_error_description'] ?? '');
        error_log("Razorpay payment failed: code=$errCode desc=$errDesc order=$orderNumber");

        setFlash('error', 'Payment was not completed. Please try again.');
        redirect(BASE_URL . 'pages/payment.php');
    }
}

// ── UI ───────────────────────────────────────────────────────
require_once __DIR__ . '/../includes/header.php';

// Sanitize display values — these go into JS via json_encode, NOT raw echo
$custName  = $order['guest_name'] ?? '';
$custEmail = $order['guest_email'] ?? '';
$custPhone = $order['guest_phone'] ?? '';
?>

<script src="https://checkout.razorpay.com/v1/checkout.js" integrity="<?= getSetting('razorpay_checkout_sri', '') ?>"
    crossorigin="anonymous"></script>

<div class="page-hero">
    <div class="container">
        <h1>Complete Payment</h1>
        <p>Secure billing for order <strong>#<?= sanitize($orderNumber) ?></strong></p>
        <div class="breadcrumb">
            <a href="<?= BASE_URL ?>">Home</a><span>›</span>
            <a href="<?= BASE_URL ?>pages/cart.php">Cart</a><span>›</span>
            <span>Payment</span>
        </div>
    </div>
</div>

<div class="container">
    <div class="payment-box">
        <h2>Secure Checkout</h2>
        <p class="payment-note">Complete your payment using Razorpay. Your order will be confirmed instantly once the
            transaction succeeds.</p>

        <div class="amount-box"><?= formatPrice($order['total']) ?></div>

        <div class="payment-info">
            <p>Pay with UPI, cards, net banking, wallets and other supported Razorpay options. Your details are
                prefilled for a faster checkout.</p>
            <p><strong>For UPI payments:</strong> Scan the QR code with Google Pay (GPay), PhonePe, or your preferred
                UPI app.</p>
        </div>

        <ul class="payment-steps">
            <li>Review order amount and customer details.</li>
            <li>Confirm payment in the secure Razorpay window.</li>
            <li>Get instant confirmation and order tracking details.</li>
        </ul>

        <div class="payment-action">
            <button id="pay-btn" class="btn btn-primary btn-full" type="button">
                Pay <?= formatPrice($order['total']) ?>
            </button>
            <p id="pay-error" class="error-text" style="display:none;"></p>
            <p class="payment-hint">If the checkout popup does not open, please allow popups for this site and try
                again.</p>
        </div>

        <form id="success-form" method="POST" action="<?= sanitize(BASE_URL) ?>pages/payment.php" style="display:none;">
            <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= generateCSRF() ?>">
            <input type="hidden" name="razorpay_payment_id" id="pid">
            <input type="hidden" name="razorpay_order_id" id="oid">
            <input type="hidden" name="razorpay_signature" id="sig">
        </form>

        <form id="failure-form" method="POST" action="<?= sanitize(BASE_URL) ?>pages/payment.php" style="display:none;">
            <input type="hidden" name="razorpay_error" id="err-flag" value="1">
            <input type="hidden" name="razorpay_error_code" id="err-code">
            <input type="hidden" name="razorpay_error_description" id="err-desc">
        </form>
    </div>
</div>

<script>
    (function() {
        // All dynamic values injected via json_encode — never raw echo into JS
        var options = {
            key: <?= json_encode($rzpKeyId) ?>,
            amount: <?= json_encode($amountPaise) ?>,
            currency: "INR",
            name: <?= json_encode($rzpName) ?>,
            image: <?= json_encode($rzpLogo) ?>,
            order_id: <?= json_encode($razorpayOrderId) ?>,

            prefill: {
                name: <?= json_encode($custName) ?>,
                email: <?= json_encode($custEmail) ?>,
                contact: <?= json_encode($custPhone) ?>
            },

            description: <?= json_encode('Order #' . $orderNumber) ?>,
            notes: {
                order_number: <?= json_encode($orderNumber) ?>
            },
            theme: {
                color: "#2c7a5a"
            },

            // ── Success: submit the hidden success form ──────────
            handler: function(res) {
                document.getElementById("pid").value = res.razorpay_payment_id;
                document.getElementById("oid").value = res.razorpay_order_id;
                document.getElementById("sig").value = res.razorpay_signature;
                document.getElementById("success-form").submit();
            },

            // ── Modal close without paying ───────────────────────
            modal: {
                ondismiss: function() {
                    var btn = document.getElementById("pay-btn");
                    btn.disabled = false;
                    btn.textContent = "Pay <?= formatPrice($order['total']) ?>";
                    document.getElementById("pay-error").style.display = "block";
                    document.getElementById("pay-error").textContent =
                        "Payment cancelled. Click below to try again.";
                }
            }
        };

        var rzp = new Razorpay(options);

        // ── Payment failure from Razorpay SDK ───────────────────
        rzp.on("payment.failed", function(response) {
            document.getElementById("err-code").value = response.error.code || "";
            document.getElementById("err-desc").value = response.error.description || "";
            document.getElementById("failure-form").submit();
        });

        document.getElementById("pay-btn").addEventListener("click", function(e) {
            e.preventDefault();
            if (this.disabled) return;
            this.disabled = true;
            this.textContent = "Opening payment…";
            document.getElementById("pay-error").style.display = "none";
            rzp.open();
        });
    })();
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>