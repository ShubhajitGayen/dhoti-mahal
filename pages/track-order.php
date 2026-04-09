<?php
// ============================================================
// Dhoti Mahal - Track Order
// ============================================================
$pageTitle = 'Track Your Order';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

$order    = null;
$items    = [];
$tracking = [];
$searched = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' || !empty($_GET['order'])) {
    $searched     = true;
    $orderNumber  = trim($_POST['order_number'] ?? $_GET['order'] ?? '');
    $email        = trim($_POST['email'] ?? '');

    if ($orderNumber) {
        $order = getOrderByNumber($orderNumber);
        if ($order) {
            // Verify via email if not logged in and not admin
            $currentUser = getLoggedUser();
            if (!$currentUser || ($order['user_id'] && $order['user_id'] != $currentUser['id'])) {
                if ($email && strtolower($order['guest_email']) !== strtolower($email)) {
                    $order = null; // hide
                }
            }
            if ($order) {
                $items    = getOrderItems((int)$order['id']);
                $tracking = getOrderTracking((int)$order['id']);
            }
        }
    }
}

require_once __DIR__ . '/../includes/header.php';

$statusOrder = ['placed', 'confirmed', 'processing', 'shipped', 'delivered'];
$statusIcon  = [
    'placed'      => 'fa-receipt',
    'confirmed'   => 'fa-check-circle',
    'processing'  => 'fa-cog',
    'shipped'     => 'fa-truck',
    'delivered'   => 'fa-box-open',
    'cancelled'   => 'fa-times-circle',
];
?>
<script>
    window.__BASE_URL = '<?= BASE_URL ?>';
</script>

<div class="page-hero" style="padding:24px 0;">
    <div class="container">
        <h1>Track Your Order</h1>
        <div class="breadcrumb"><a href="<?= BASE_URL ?>">Home</a><span>›</span><span>Track Order</span></div>
    </div>
</div>

<div class="container">
    <div class="track-box">
        <!-- Search Form -->
        <div class="checkout-section" style="margin-bottom: 30px;">
            <h3><i class="fas fa-search"></i> Enter Order Details</h3>
            <form method="POST">
                <div class="form-row">
                    <div class="form-group">
                        <label>Order Number *</label>
                        <input type="text" name="order_number" required
                            value="<?= sanitize($_POST['order_number'] ?? $_GET['order'] ?? '') ?>"
                            placeholder="e.g. DMAB1234">
                    </div>
                    <div class="form-group">
                        <label>Email (for guest orders)</label>
                        <input type="email" name="email" placeholder="your@email.com"
                            value="<?= sanitize($_POST['email'] ?? '') ?>">
                    </div>
                </div>
                <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Track Order</button>
            </form>
        </div>

        <?php if ($searched && !$order): ?>
            <div class="flash-message flash-error" style="border-radius:6px;padding:14px 20px;">
                Order not found. Please check your order number and email address.
            </div>
        <?php endif; ?>

        <?php if ($order): ?>
            <!-- Order Details -->
            <div class="checkout-section">
                <h3>Order #<?= sanitize($order['order_number']) ?></h3>
                <div style="display:flex;gap:20px;flex-wrap:wrap;margin-bottom:20px;">
                    <div><span
                            style="color:var(--muted);font-size:13px;">Date</span><br><strong><?= date('d M Y', strtotime($order['created_at'])) ?></strong>
                    </div>
                    <div><span
                            style="color:var(--muted);font-size:13px;">Total</span><br><strong><?= formatPrice((float)$order['total']) ?></strong>
                    </div>
                    <div><span style="color:var(--muted);font-size:13px;">Payment</span><br><span
                            class="status-badge status-<?= $order['payment_status'] ?>"><?= ucfirst($order['payment_status']) ?></span>
                    </div>
                    <div><span style="color:var(--muted);font-size:13px;">Status</span><br><span
                            class="status-badge status-<?= $order['order_status'] ?>"><?= ucfirst($order['order_status']) ?></span>
                    </div>
                </div>

                <!-- Progress Bar -->
                <?php if ($order['order_status'] !== 'cancelled'): ?>
                    <div style="margin: 24px 0; overflow-x: auto;">
                        <div style="display:flex;align-items:center;min-width:400px;">
                            <?php foreach ($statusOrder as $i => $st):
                                $done   = array_search($order['order_status'], $statusOrder) >= $i;
                                $color  = $done ? 'var(--crimson)' : 'var(--border)';
                                $icon   = $statusIcon[$st] ?? 'fa-circle';
                            ?>
                                <div style="display:flex;flex-direction:column;align-items:center;flex:1;position:relative;">
                                    <div
                                        style="width:36px;height:36px;border-radius:50%;background:<?= $color ?>;color:white;display:flex;align-items:center;justify-content:center;font-size:14px;z-index:1;">
                                        <i class="fas <?= $icon ?>"></i>
                                    </div>
                                    <div
                                        style="font-size:11px;margin-top:6px;color:<?= $done ? 'var(--crimson)' : 'var(--muted)' ?>;font-weight:<?= $done ? '700' : '400' ?>;text-transform:uppercase;letter-spacing:0.5px;">
                                        <?= ucfirst($st) ?></div>
                                    <?php if ($i < count($statusOrder) - 1): ?>
                                        <div
                                            style="position:absolute;top:18px;left:50%;width:100%;height:2px;background:<?= $done ? 'var(--crimson)' : 'var(--border)' ?>;z-index:0;">
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Timeline -->
                <div class="tracking-timeline">
                    <?php foreach (array_reverse($tracking) as $i => $event): ?>
                        <div class="track-step <?= $i === 0 ? 'active' : 'done' ?>">
                            <div class="track-icon"><i class="fas fa-check"></i></div>
                            <div class="track-info">
                                <h4><?= sanitize($event['status']) ?></h4>
                                <p><?= sanitize($event['message']) ?></p>
                                <div class="track-date"><?= date('d M Y, h:i A', strtotime($event['created_at'])) ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Items -->
                <h4
                    style="font-family:var(--font-head);margin-bottom:12px;border-top:1px solid var(--border);padding-top:16px;">
                    Items in This Order</h4>
                <?php foreach ($items as $item): ?>
                    <div style="display:flex;gap:12px;padding:10px 0;border-bottom:1px solid var(--border);align-items:center;">
                        <img src="<?= productImage($item['product_image'] ?? '') ?>"
                            style="width:50px;height:60px;object-fit:cover;border-radius:4px;" alt="">
                        <div style="flex:1;">
                            <div style="font-weight:600;"><?= sanitize($item['product_name']) ?></div>
                            <?php if ($item['size']): ?><div style="font-size:12px;color:var(--muted);">Size:
                                    <?= sanitize($item['size']) ?></div><?php endif; ?>
                            <div style="font-size:13px;color:var(--muted);">Qty: <?= (int)$item['quantity'] ?> ×
                                <?= formatPrice((float)$item['price']) ?></div>
                        </div>
                        <div style="font-weight:700;"><?= formatPrice((float)$item['total']) ?></div>
                    </div>
                <?php endforeach; ?>

                <div style="text-align:center;margin-top:20px;">
                    <p style="font-size:13px;color:var(--muted);">Need help?
                        <a href="https://wa.me/<?= sanitize(getSetting('whatsapp_number')) ?>?text=Help with order <?= urlencode($order['order_number']) ?>"
                            style="color:#25D366;font-weight:600;">
                            <i class="fab fa-whatsapp"></i> WhatsApp Us
                        </a>
                    </p>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>