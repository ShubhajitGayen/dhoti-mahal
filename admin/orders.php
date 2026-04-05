<?php
// ============================================================
// Dhoti Mahal - Admin Orders
// ============================================================
$adminPageTitle = 'Orders';
require_once __DIR__ . '/partials/header.php';

$db     = getDB();
$status = trim($_GET['status'] ?? '');
$page   = max(1, (int)($_GET['page'] ?? 1));
$perPage = 20;
$offset = ($page - 1) * $perPage;

$where = $status ? "WHERE order_status = " . $db->quote($status) : '';
$orders = $db->query("SELECT * FROM orders $where ORDER BY created_at DESC LIMIT $perPage OFFSET $offset")->fetchAll();
$total  = (int)$db->query("SELECT COUNT(*) FROM orders $where")->fetchColumn();
$totalPages = (int)ceil($total / $perPage);
?>

<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;flex-wrap:wrap;gap:12px;">
    <h1 style="margin-bottom:0;">Orders</h1>
    <!-- Status Filter -->
    <div style="display:flex;gap:8px;flex-wrap:wrap;">
        <?php
        $statuses = ['','placed','confirmed','processing','shipped','delivered','cancelled'];
        $labels   = ['All','Placed','Confirmed','Processing','Shipped','Delivered','Cancelled'];
        foreach ($statuses as $i => $s):
            $active = $status === $s ? 'background:var(--crimson);color:white;' : 'background:white;';
        ?>
            <a href="?status=<?= urlencode($s) ?>" style="padding:6px 14px;border-radius:20px;font-size:13px;font-weight:600;border:1px solid var(--border);<?= $active ?>color:<?= $status===$s?'white':'var(--charcoal)' ?>;">
                <?= $labels[$i] ?>
            </a>
        <?php endforeach; ?>
    </div>
</div>

<div class="admin-card">
    <p style="color:var(--muted);font-size:13px;margin-bottom:16px;">Showing <?= count($orders) ?> of <?= $total ?> orders</p>
    <table class="admin-table">
        <thead>
            <tr>
                <th>Order #</th>
                <th>Customer</th>
                <th>Phone</th>
                <th>Total</th>
                <th>Payment</th>
                <th>Status</th>
                <th>Date</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
        <?php if (empty($orders)): ?>
            <tr><td colspan="8" style="text-align:center;padding:40px;color:var(--muted);">No orders found.</td></tr>
        <?php endif; ?>
        <?php foreach ($orders as $order): ?>
            <tr>
                <td><strong><?= sanitize($order['order_number']) ?></strong></td>
                <td><?= sanitize($order['guest_name'] ?: '—') ?></td>
                <td><?= sanitize($order['guest_phone'] ?: '—') ?></td>
                <td><?= formatPrice((float)$order['total']) ?></td>
                <td><span class="status-badge status-<?= $order['payment_status'] ?>"><?= ucfirst($order['payment_status']) ?></span></td>
                <td><span class="status-badge status-<?= $order['order_status'] ?>"><?= ucfirst($order['order_status']) ?></span></td>
                <td><?= date('d M Y', strtotime($order['created_at'])) ?></td>
                <td class="action-links">
                    <a href="<?= BASE_URL ?>admin/order-detail.php?id=<?= $order['id'] ?>" class="edit">
                        <i class="fas fa-eye"></i> View
                    </a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
    <div class="pagination" style="margin:20px 0 0;">
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <a href="?status=<?= urlencode($status) ?>&page=<?= $i ?>" class="page-link <?= $i === $page ? 'active' : '' ?>"><?= $i ?></a>
        <?php endfor; ?>
    </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/partials/footer.php'; ?>
