<?php
// ============================================================
// Dhoti Mahal - Admin Order Detail
// ============================================================
$adminPageTitle = 'Order Detail';
require_once __DIR__ . '/partials/header.php';
require_once __DIR__ . '/../includes/whatsapp-helpers.php';

$db = getDB();
$orderId = (int)($_GET['id'] ?? 0);
if (!$orderId) {
    setFlash('error', 'Invalid order.');
    redirect(BASE_URL . 'admin/orders.php');
}

$order = $db->prepare("SELECT * FROM orders WHERE id = ?");
$order->execute([$orderId]);
$order = $order->fetch();
if (!$order) {
    setFlash('error', 'Order not found.');
    redirect(BASE_URL . 'admin/orders.php');
}

$items    = getOrderItems($orderId);
$tracking = getOrderTracking($orderId);

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $orderStatus   = $_POST['order_status'] ?? '';
    $paymentStatus = $_POST['payment_status'] ?? '';
    $trackingMsg   = trim($_POST['tracking_message'] ?? '');

    $db->prepare("UPDATE orders SET order_status=?, payment_status=?, updated_at=NOW() WHERE id=?")
        ->execute([$orderStatus, $paymentStatus, $orderId]);

    if ($trackingMsg) {
        $db->prepare("INSERT INTO order_tracking (order_id, status, message) VALUES (?,?,?)")
            ->execute([$orderId, ucfirst($orderStatus), $trackingMsg]);
    }

    setFlash('success', 'Order updated successfully.');
    redirect(BASE_URL . 'admin/order-detail.php?id=' . $orderId);
}

// Handle WhatsApp sends (no JS required)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_whatsapp_template'])) {
    $templateKey = trim($_POST['send_whatsapp_template']);
    $message = generateWhatsAppMessage($orderId, $templateKey);

    if (!$message) {
        setFlash('error', 'Failed to generate WhatsApp message template.');
        redirect(BASE_URL . 'admin/order-detail.php?id=' . $orderId);
    }

    $messageId = createWhatsAppMessage($orderId, $message, $templateKey);
    if (!$messageId) {
        setFlash('error', 'Failed to log WhatsApp message. Please check phone number.');
        redirect(BASE_URL . 'admin/order-detail.php?id=' . $orderId);
    }

    $sendResult = sendWhatsAppMessage($messageId);

    if (!$sendResult['success']) {
        setFlash('error', 'WhatsApp error: ' . ($sendResult['error'] ?? 'Unknown error'));
        redirect(BASE_URL . 'admin/order-detail.php?id=' . $orderId);
    }

    // Redirect to WhatsApp chat
    redirect($sendResult['link']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_whatsapp_custom'])) {
    $customMessage = trim($_POST['whatsapp_custom_message'] ?? '');
    if (!$customMessage) {
        setFlash('error', 'Please enter a custom message.');
        redirect(BASE_URL . 'admin/order-detail.php?id=' . $orderId);
    }

    $messageId = createWhatsAppMessage($orderId, $customMessage, 'custom_message');
    if (!$messageId) {
        setFlash('error', 'Failed to log WhatsApp message. Please check phone number.');
        redirect(BASE_URL . 'admin/order-detail.php?id=' . $orderId);
    }

    $sendResult = sendWhatsAppMessage($messageId);

    if (!$sendResult['success']) {
        setFlash('error', 'WhatsApp error: ' . ($sendResult['error'] ?? 'Unknown error'));
        redirect(BASE_URL . 'admin/order-detail.php?id=' . $orderId);
    }

    redirect($sendResult['link']);
}

?>

<div style="display:flex;align-items:center;gap:16px;margin-bottom:24px;">
    <a href="<?= BASE_URL ?>admin/orders.php" style="color:var(--muted);font-size:14px;"><i
            class="fas fa-arrow-left"></i> Back to Orders</a>
    <h1 style="margin:0;">Order #<?= sanitize($order['order_number']) ?></h1>
    <span class="status-badge status-<?= $order['order_status'] ?>"><?= ucfirst($order['order_status']) ?></span>
</div>

<div style="display:grid;grid-template-columns:1fr 340px;gap:24px;" data-order-id="<?= $orderId ?>">
    <div>
        <!-- Customer Info -->
        <div class="admin-card">
            <h3>Customer Information</h3>
            <table style="font-size:14px;width:100%;">
                <tr>
                    <td style="padding:6px 0;color:var(--muted);width:160px;">Name</td>
                    <td><strong><?= sanitize($order['guest_name']) ?></strong></td>
                </tr>
                <tr>
                    <td style="padding:6px 0;color:var(--muted);">Email</td>
                    <td><?= sanitize($order['guest_email']) ?></td>
                </tr>
                <tr>
                    <td style="padding:6px 0;color:var(--muted);">Phone</td>
                    <td><?= sanitize($order['guest_phone']) ?></td>
                </tr>
                <tr>
                    <td style="padding:6px 0;color:var(--muted);">Address</td>
                    <td><?= sanitize($order['shipping_address']) ?></td>
                </tr>
                <tr>
                    <td style="padding:6px 0;color:var(--muted);">City/State</td>
                    <td><?= sanitize($order['shipping_city']) ?>, <?= sanitize($order['shipping_state']) ?> –
                        <?= sanitize($order['shipping_pincode']) ?></td>
                </tr>
                <tr>
                    <td style="padding:6px 0;color:var(--muted);">Order Date</td>
                    <td><?= date('d M Y, h:i A', strtotime($order['created_at'])) ?></td>
                </tr>
                <?php if ($order['payment_ref']): ?>
                    <tr>
                        <td style="padding:6px 0;color:var(--muted);">Payment Ref</td>
                        <td><code><?= sanitize($order['payment_ref']) ?></code></td>
                    </tr>
                <?php endif; ?>
                <?php if ($order['notes']): ?>
                    <tr>
                        <td style="padding:6px 0;color:var(--muted);">Notes</td>
                        <td><?= sanitize($order['notes']) ?></td>
                    </tr>
                <?php endif; ?>
            </table>
        </div>

        <!-- Order Items -->
        <div class="admin-card">
            <h3>Order Items</h3>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Size</th>
                        <th>Price</th>
                        <th>Qty</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item): ?>
                        <tr>
                            <td>
                                <div style="display:flex;align-items:center;gap:10px;">
                                    <img src="<?= productImage($item['product_image'] ?? '') ?>"
                                        style="width:40px;height:50px;object-fit:cover;border-radius:3px;">
                                    <span><?= sanitize($item['product_name']) ?></span>
                                </div>
                            </td>
                            <td><?= sanitize($item['size'] ?: '—') ?></td>
                            <td><?= formatPrice((float)$item['price']) ?></td>
                            <td><?= (int)$item['quantity'] ?></td>
                            <td><?= formatPrice((float)$item['total']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="4" style="text-align:right;font-weight:700;padding:10px 14px;">Subtotal</td>
                        <td><?= formatPrice((float)$order['subtotal']) ?></td>
                    </tr>
                    <tr>
                        <td colspan="4" style="text-align:right;font-weight:700;padding:10px 14px;">Shipping</td>
                        <td><?= $order['shipping_cost'] == 0 ? 'FREE' : formatPrice((float)$order['shipping_cost']) ?>
                        </td>
                    </tr>
                    <tr style="background:var(--cream);">
                        <td colspan="4"
                            style="text-align:right;font-weight:700;padding:10px 14px;font-size:1.1rem;color:var(--crimson);">
                            Total</td>
                        <td style="font-weight:700;font-size:1.1rem;color:var(--crimson);">
                            <?= formatPrice((float)$order['total']) ?></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Tracking Timeline -->
        <div class="admin-card">
            <h3>Order Timeline</h3>
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
        </div>
    </div>

    <!-- Update Panel -->
    <div>
        <div class="admin-card" style="position:sticky;top:20px;">
            <h3>Update Order</h3>
            <form method="POST">
                <input type="hidden" name="update_status" value="1">
                <div class="form-group">
                    <label>Order Status</label>
                    <select name="order_status">
                        <?php foreach (['placed', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled'] as $s): ?>
                            <option value="<?= $s ?>" <?= $order['order_status'] === $s ? 'selected' : '' ?>>
                                <?= ucfirst($s) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Payment Status</label>
                    <select name="payment_status">
                        <?php foreach (['pending', 'paid', 'failed', 'refunded'] as $s): ?>
                            <option value="<?= $s ?>" <?= $order['payment_status'] === $s ? 'selected' : '' ?>>
                                <?= ucfirst($s) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Tracking Message (optional)</label>
                    <textarea name="tracking_message" rows="3"
                        placeholder="e.g. Dispatched via DTDC, tracking ID: XYZ123"></textarea>
                </div>
                <button type="submit" class="btn btn-primary btn-full">
                    <i class="fas fa-save"></i> Update Order
                </button>
            </form>

            <hr style="border:none;border-top:1px solid var(--border);margin:20px 0;">

            <!-- WhatsApp Messaging Panel -->
            <div>
                <h4 style="margin-top:0;margin-bottom:12px;font-size:14px;color:var(--heading);">
                    <i class="fab fa-whatsapp" style="color:#25D366;"></i> WhatsApp Messages
                </h4>

                <!-- Template Buttons (PHP form submit) -->
                <form method="POST" style="display:flex;flex-direction:column;gap:6px;margin-bottom:14px;">
                    <button type="submit" name="send_whatsapp_template" value="order_confirmation" class="btn btn-sm btn-outline" style="justify-content:center;font-size:12px;">
                        <i class="fas fa-check"></i> Order Confirmed
                    </button>
                    <button type="submit" name="send_whatsapp_template" value="order_processing" class="btn btn-sm btn-outline" style="justify-content:center;font-size:12px;">
                        <i class="fas fa-cogs"></i> Processing
                    </button>
                    <button type="submit" name="send_whatsapp_template" value="order_shipped" class="btn btn-sm btn-outline" style="justify-content:center;font-size:12px;">
                        <i class="fas fa-truck"></i> Shipped
                    </button>
                    <button type="submit" name="send_whatsapp_template" value="order_delivered" class="btn btn-sm btn-outline" style="justify-content:center;font-size:12px;">
                        <i class="fas fa-box"></i> Delivered
                    </button>
                    <button type="submit" name="send_whatsapp_template" value="payment_reminder" class="btn btn-sm btn-outline" style="justify-content:center;font-size:12px;">
                        <i class="fas fa-money-bill"></i> Payment Reminder
                    </button>
                </form>

                <!-- Custom Message Form -->
                <form method="POST" style="display:flex;flex-direction:column;gap:8px;">
                    <textarea name="whatsapp_custom_message" rows="3" placeholder="Write custom message" style="padding:8px;border:1px solid var(--border);border-radius:3px;resize:vertical;"></textarea>
                    <button type="submit" name="send_whatsapp_custom" value="1" class="btn btn-sm btn-outline btn-full" style="justify-content:center;font-size:12px;">
                        <i class="fas fa-pen"></i> Send Custom Message
                    </button>
                </form>

                <!-- Direct WhatsApp -->
                <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $order['guest_phone']) ?>?text=Hi%20<?= urlencode($order['guest_name']) ?>" target="_blank" class="btn btn-sm btn-outline btn-full" style="justify-content:center;font-size:12px;margin-top:6px;">
                    <i class="fab fa-whatsapp"></i> Open Chat
                </a>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/partials/footer.php'; ?>

<!-- WhatsApp Messaging via PHP form actions; no external JS needed -->