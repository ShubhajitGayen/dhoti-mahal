<?php
// ============================================================
// Dhoti Mahal - Admin Settings
// ============================================================
$adminPageTitle = 'Settings';
require_once __DIR__ . '/partials/header.php';

$db = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $allowed = [
        'site_name','site_tagline','site_email','site_phone','site_address',
        'upi_id','upi_name','free_shipping_above','shipping_cost',
        'facebook_url','instagram_url','whatsapp_number','gst_number','meta_description'
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
<div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;">

    <div class="admin-card">
        <h3>General Settings</h3>
        <?php foreach ([
            ['site_name',    'Site Name',     'text', 'Dhoti Mahal'],
            ['site_tagline', 'Site Tagline',  'text', 'The House of Traditional Indian Attire'],
            ['site_email',   'Contact Email', 'email','info@dhotimahal.com'],
            ['site_phone',   'Phone Number',  'text', '+91 98765 43210'],
        ] as [$key, $label, $type, $placeholder]): ?>
        <div class="form-group">
            <label><?= $label ?></label>
            <input type="<?= $type ?>" name="<?= $key ?>" value="<?= sanitize(getSetting($key)) ?>" placeholder="<?= $placeholder ?>">
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
        <div class="admin-card">
            <h3>Payment Settings</h3>
            <div class="form-group">
                <label>UPI ID</label>
                <input type="text" name="upi_id" value="<?= sanitize(getSetting('upi_id')) ?>" placeholder="yourname@upi">
            </div>
            <div class="form-group">
                <label>UPI Display Name</label>
                <input type="text" name="upi_name" value="<?= sanitize(getSetting('upi_name')) ?>" placeholder="Business name shown in UPI apps">
            </div>
        </div>

        <div class="admin-card">
            <h3>Shipping Settings</h3>
            <div class="form-row">
                <div class="form-group">
                    <label>Shipping Cost (₹)</label>
                    <input type="number" name="shipping_cost" value="<?= sanitize(getSetting('shipping_cost', '99')) ?>" min="0">
                </div>
                <div class="form-group">
                    <label>Free Shipping Above (₹)</label>
                    <input type="number" name="free_shipping_above" value="<?= sanitize(getSetting('free_shipping_above', '999')) ?>" min="0">
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
                <input type="text" name="whatsapp_number" value="<?= sanitize(getSetting('whatsapp_number')) ?>" placeholder="919876543210">
            </div>
            <div class="form-group">
                <label>GST Number</label>
                <input type="text" name="gst_number" value="<?= sanitize(getSetting('gst_number')) ?>">
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
