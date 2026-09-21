<?php
// ============================================================
// Dhoti Mahal - Admin Settings
// ============================================================
$adminPageTitle = 'Settings';
require_once __DIR__ . '/partials/header.php';

$db = getDB();
$ownerVerified = isOwnerAuthorized();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRF($_POST[CSRF_TOKEN_NAME] ?? '')) {
        setFlash('error', 'Invalid request. Please refresh and try again.');
        redirect(BASE_URL . 'admin/settings.php');
    }

    if (isset($_POST['owner_auth'])) {
        $ownerUsername = trim($_POST['owner_username'] ?? '');
        $ownerPassword = $_POST['owner_password'] ?? '';

        if (authorizeOwner($ownerUsername, $ownerPassword)) {
            $_SESSION['owner_verified'] = true;
            $_SESSION['owner_verified_at'] = time();
            setFlash('success', 'Owner authorized. Payment settings are now unlocked.');
        } else {
            setFlash('error', 'Owner credentials invalid.');
        }
        redirect(BASE_URL . 'admin/settings.php');
    }

    if (isset($_POST['owner_logout'])) {
        deauthorizeOwner();
        setFlash('success', 'Payment settings locked. Owner access revoked.');
        redirect(BASE_URL . 'admin/settings.php');
    }

    $paymentFields = [
        'razorpay_key_id',
        'razorpay_key_secret',
        'razorpay_name',
        'razorpay_logo'
    ];
    $requiresOwner = false;
    foreach ($paymentFields as $key) {
        if (array_key_exists($key, $_POST)) {
            $requiresOwner = true;
            break;
        }
    }

    if ($requiresOwner && !$ownerVerified) {
        setFlash('error', 'Owner authorization is required to change payment settings.');
        redirect(BASE_URL . 'admin/settings.php');
    }
    $allowed = [
        'site_name',
        'site_tagline',
        'site_email',
        'site_phone',
        'site_address',
        'upi_id',
        'upi_name',
        'free_shipping_above',
        'shipping_cost',
        'facebook_url',
        'instagram_url',
        'whatsapp_number',
        'gst_number',
        'meta_description',
        'chatbot_enabled',
        'chatbot_business_info',
        'razorpay_key_id',
        'razorpay_key_secret',
        'razorpay_name',
        'razorpay_logo'
    ];
    foreach ($allowed as $key) {
        $val = trim($_POST[$key] ?? '');
        $db->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?,?) ON DUPLICATE KEY UPDATE setting_value=?")
            ->execute([$key, $val, $val]);
    }
    setFlash('success', 'Settings saved successfully!');
    redirect(BASE_URL . 'admin/settings.php');
}
?>

<h1>Site Settings</h1>

<form method="POST">
    <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= generateCSRF() ?>">
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;">

        <div class="admin-card">
            <h3>General Settings</h3>
            <?php foreach (
                [
                    ['site_name',    'Site Name',     'text', 'Dhoti Mahal'],
                    ['site_tagline', 'Site Tagline',  'text', 'The House of Traditional Indian Attire'],
                    ['site_email',   'Contact Email', 'email', 'info@dhotimahal.com'],
                    ['site_phone',   'Phone Number',  'text', '+91 98765 43210'],
                ] as [$key, $label, $type, $placeholder]
            ): ?>
                <div class="form-group">
                    <label><?= $label ?></label>
                    <input type="<?= $type ?>" name="<?= $key ?>" value="<?= sanitize(getSetting($key)) ?>"
                        placeholder="<?= $placeholder ?>">
                </div>
            <?php endforeach; ?>
            <div class="form-group">
                <label>Address</label>
                <textarea name="site_address" rows="2"><?= sanitize(getSetting('site_address')) ?></textarea>
            </div>
            <div class="form-group">
                <label>Meta Description (SEO)</label>
                <textarea name="meta_description" rows="3"><?= sanitize(getSetting('meta_description')) ?></textarea>
            </div>
        </div>

        <div>
            <?php if ($ownerVerified): ?>
                <div class="admin-card">
                    <h3>Payment Settings</h3>
                    <div class="form-group">
                        <label>Razorpay Key ID</label>
                        <input type="text" name="razorpay_key_id" value="<?= sanitize(getSetting('razorpay_key_id')) ?>"
                            placeholder="rzp_test_xxxxxx">
                    </div>
                    <div class="form-group">
                        <label>Razorpay Key Secret</label>
                        <input type="text" name="razorpay_key_secret"
                            value="<?= sanitize(getSetting('razorpay_key_secret')) ?>"
                            placeholder="xxxxxxxxxxxxxxxxxxxxxxxx">
                    </div>
                    <div class="form-group">
                        <label>Razorpay Merchant Name</label>
                        <input type="text" name="razorpay_name"
                            value="<?= sanitize(getSetting('razorpay_name', 'Dhoti Mahal')) ?>" placeholder="Dhoti Mahal">
                    </div>
                    <div class="form-group">
                        <label>Razorpay Logo URL</label>
                        <input type="text" name="razorpay_logo"
                            value="<?= sanitize(getSetting('razorpay_logo', BASE_URL . 'assets/images/logo.png')) ?>"
                            placeholder="https://example.com/path/to/logo.png">
                    </div>
                    <div class="form-group">
                        <label>UPI ID</label>
                        <input type="text" name="upi_id" value="<?= sanitize(getSetting('upi_id')) ?>"
                            placeholder="yourname@upi">
                    </div>
                    <div class="form-group">
                        <label>UPI Display Name</label>
                        <input type="text" name="upi_name" value="<?= sanitize(getSetting('upi_name')) ?>"
                            placeholder="Business name shown in UPI apps">
                    </div>
                </div>

                <div class="admin-card" style="background: #d4edda; border: 1px solid #c3e6cb;">
                    <div style="text-align: center;">
                        <p style="color: #155724; margin: 0;">
                            <i class="fas fa-check-circle"></i> <strong>Owner Verified</strong>
                        </p>
                        <p style="color: #155724; font-size: 0.9rem; margin: 8px 0 16px 0;">
                            Payment settings unlocked for <?= (OWNER_AUTH_TIMEOUT / 60) ?> minutes
                        </p>
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= generateCSRF() ?>">
                            <input type="hidden" name="owner_logout" value="1">
                            <button type="submit" class="btn btn-secondary"
                                style="background: #c3e6cb; color: #155724; border: 1px solid #b0dfb5;">
                                <i class="fas fa-lock"></i> Lock Payment Settings
                            </button>
                        </form>
                    </div>
                </div>
            <?php else: ?>
                <div class="admin-card">
                    <h3>Payment Settings</h3>
                    <div class="flash-message flash-warning" style="margin-bottom:16px;">
                        <strong>Payment settings are locked.</strong> Owner authorization is required to view and edit
                        Razorpay configuration.
                    </div>
                </div>

                <div class="admin-card">
                    <h3>🔐 Owner Verification Required</h3>
                    <p style="color:var(--muted);margin-bottom:16px;">Enter owner credentials to unlock payment settings.
                    </p>
                    <form method="POST" style="display:grid;gap:12px;">
                        <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= generateCSRF() ?>">
                        <input type="hidden" name="owner_auth" value="1">

                        <div class="form-group">
                            <label>Owner Username</label>
                            <input type="text" name="owner_username" placeholder="Enter owner username" required autofocus>
                        </div>

                        <div class="form-group">
                            <label>Owner Password</label>
                            <input type="password" name="owner_password" placeholder="Enter owner password" required>
                        </div>

                        <button type="submit" class="btn btn-primary" style="width:100%;">
                            <i class="fas fa-unlock"></i> Verify Owner
                        </button>
                    </form>
                </div>
            <?php endif; ?>

            <div class="admin-card">
                <h3>Shipping Settings</h3>
                <div class="form-row">
                    <div class="form-group">
                        <label>Shipping Cost (₹)</label>
                        <input type="number" name="shipping_cost"
                            value="<?= sanitize(getSetting('shipping_cost', '99')) ?>" min="0">
                    </div>
                    <div class="form-group">
                        <label>Free Shipping Above (₹)</label>
                        <input type="number" name="free_shipping_above"
                            value="<?= sanitize(getSetting('free_shipping_above', '999')) ?>" min="0">
                    </div>
                </div>
            </div>

            <div class="admin-card">
                <h3>Social & Business</h3>
                <div class="form-group">
                    <label>Facebook URL</label>
                    <input type="url" name="facebook_url" value="<?= sanitize(getSetting('facebook_url')) ?>">
                </div>
                <div class="form-group">
                    <label>Instagram URL</label>
                    <input type="url" name="instagram_url" value="<?= sanitize(getSetting('instagram_url')) ?>">
                </div>
                <div class="form-group">
                    <label>WhatsApp Number (with country code, no +)</label>
                    <input type="text" name="whatsapp_number" value="<?= sanitize(getSetting('whatsapp_number')) ?>"
                        placeholder="919876543210">
                </div>
                <div class="form-group">
                    <label>GST Number</label>
                    <input type="text" name="gst_number" value="<?= sanitize(getSetting('gst_number')) ?>">
                </div>
            </div>

            <div class="admin-card">
                <h3>Chatbot</h3>
                <div class="form-group">
                    <label>
                        <input type="checkbox" name="chatbot_enabled" value="1"
                            <?= filter_var(getSetting('chatbot_enabled', '1'), FILTER_VALIDATE_BOOLEAN) ? 'checked' : '' ?>>
                        Enable customer shopping assistant
                    </label>
                </div>
                <div class="form-group">
                    <label>Business information used by the assistant</label>
                    <textarea name="chatbot_business_info" rows="10"><?= sanitize(getSetting('chatbot_business_info', "Shop name: [SHOP NAME]\nOpening / support hours: NOT SET\nShipping charges and free-shipping threshold: NOT SET\nDelivery areas and delivery time: NOT SET\nPayment methods: NOT SET\nReturn / exchange / cancellation policy: NOT SET\nContact phone / WhatsApp / email: NOT SET\nSize guide notes: NOT SET\nTone: friendly and short")) ?></textarea>
                </div>
            </div>
        </div>
    </div>

    <div style="text-align:center;margin-top:8px;">
        <button type="submit" class="btn btn-primary" style="padding:13px 40px;font-size:1rem;">
            <i class="fas fa-save"></i> Save All Settings
        </button>
    </div>
</form>

<?php require_once __DIR__ . '/partials/footer.php'; ?>