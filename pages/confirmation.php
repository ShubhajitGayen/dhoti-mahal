<?php
// ============================================================
// Dhoti Mahal - Order Confirmation
// ============================================================
$pageTitle = 'Order Confirmed!';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

$orderNumber = trim($_GET['order'] ?? '');
if (!$orderNumber) { redirect(BASE_URL); }

$order = getOrderByNumber($orderNumber);
if (!$order) { redirect(BASE_URL); }

$items    = getOrderItems((int)$order['id']);
$tracking = getOrderTracking((int)$order['id']);

require_once __DIR__ . '/../includes/header.php';
?>
<script>window.__BASE_URL = '<?= BASE_URL ?>';</script>

<div class="container" style="padding: 40px 20px;">
<div class="confirm-box">
    <div class="confirm-icon"><i class="fas fa-check-circle"></i></div>
    <h1>Order Confirmed!</h1>
    <p style="color:var(--muted);font-size:1rem;">Thank you for shopping at <?= sanitize(getSetting('site_name', SITE_NAME)) ?>. Your order has been placed successfully.</p>

    <div class="confirm-order">
        <h3>📦 Order Details</h3>
        <table style="width:100%;font-size:14px;">
            <tr><td style="padding:6px 0;color:var(--muted);">Order Number</td><td style="font-weight:700;"><?= sanitize($order['order_number']) ?></td></tr>
            <tr><td style="padding:6px 0;color:var(--muted);">Order Date</td><td><?= date('d M Y, h:i A', strtotime($order['created_at'])) ?></td></tr>
            <tr><td style="padding:6px 0;color:var(--muted);">Payment Status</td><td><span class="status-badge status-<?= $order['payment_status'] ?>"><?= ucfirst($order['payment_status']) ?></span></td></tr>
            <tr><td style="padding:6px 0;color:var(--muted);">Order Status</td><td><span class="status-badge status-<?= $order['order_status'] ?>"><?= ucfirst($order['order_status']) ?></span></td></tr>
            <tr><td style="padding:6px 0;color:var(--muted);">Deliver To</td><td><?= sanitize($order['shipping_address']) ?>, <?= sanitize($order['shipping_city']) ?>, <?= sanitize($order['shipping_state']) ?> – <?= sanitize($order['shipping_pincode']) ?></td></tr>
        </table>
    </div>

    <!-- Items -->
    <div class="confirm-order">
        <h3>🛍️ Items Ordered</h3>
        <?php foreach ($items as $item): ?>
        <div style="display:flex;gap:12px;padding:10px 0;border-bottom:1px solid var(--border);align-items:center;">
            <img src="<?= productImage($item['product_image'] ?? '') ?>" style="width:50px;height:60px;object-fit:cover;border-radius:4px;" alt="">
            <div style="flex:1;text-align:left;">
                <div style="font-weight:600;font-size:14px;"><?= sanitize($item['product_name']) ?></div>
                <?php if ($item['size']): ?><div style="font-size:12px;color:var(--muted);">Size: <?= sanitize($item['size']) ?></div><?php endif; ?>
                <div style="font-size:13px;color:var(--muted);">Qty: <?= (int)$item['quantity'] ?> × <?= formatPrice((float)$item['price']) ?></div>
            </div>
            <div style="font-weight:700;"><?= formatPrice((float)$item['total']) ?></div>
        </div>
        <?php endforeach; ?>

        <div style="margin-top:12px;display:flex;flex-direction:column;gap:6px;align-items:flex-end;">
            <span style="font-size:14px;color:var(--muted);">Subtotal: <?= formatPrice((float)$order['subtotal']) ?></span>
            <span style="font-size:14px;color:var(--muted);">Shipping: <?= $order['shipping_cost'] == 0 ? '<strong style="color:#28a745">FREE</strong>' : formatPrice((float)$order['shipping_cost']) ?></span>
            <span style="font-size:1.1rem;font-weight:700;color:var(--crimson);">Total: <?= formatPrice((float)$order['total']) ?></span>
        </div>
    </div>

    <div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap;margin-top:24px;">
        <a href="<?= BASE_URL ?>pages/track-order.php?order=<?= urlencode($order['order_number']) ?>" class="btn btn-outline">
            <i class="fas fa-truck"></i> Track Order
        </a>
        <a href="<?= BASE_URL ?>" class="btn btn-primary">
            <i class="fas fa-shopping-bag"></i> Continue Shopping
        </a>
    </div>

    <p style="text-align:center;font-size:13px;color:var(--muted);margin-top:20px;">
        A confirmation has been noted for order <strong><?= sanitize($order['order_number']) ?></strong>.<br>
        Need help? WhatsApp us at <a href="https://wa.me/<?= sanitize(getSetting('whatsapp_number')) ?>">+<?= sanitize(getSetting('whatsapp_number')) ?></a>
    </p>
</div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
