<?php
// ============================================================
// Dhoti Mahal - Admin Dashboard
// ============================================================
$adminPageTitle = 'Dashboard';
require_once __DIR__ . '/partials/header.php';

$db = getDB();

// Stats
$totalOrders    = (int)$db->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$totalRevenue   = (float)$db->query("SELECT COALESCE(SUM(total),0) FROM orders WHERE payment_status='paid'")->fetchColumn();
$totalProducts  = (int)$db->query("SELECT COUNT(*) FROM products WHERE is_active=1")->fetchColumn();
$totalUsers     = (int)$db->query("SELECT COUNT(*) FROM users")->fetchColumn();
$pendingOrders  = (int)$db->query("SELECT COUNT(*) FROM orders WHERE order_status='placed'")->fetchColumn();
$recentOrders   = $db->query("SELECT * FROM orders ORDER BY created_at DESC LIMIT 10")->fetchAll();
$lowStock       = $db->query("SELECT * FROM products WHERE stock <= 5 AND is_active=1 ORDER BY stock ASC LIMIT 5")->fetchAll();
?>

<h1>Dashboard</h1>

<!-- Stat Cards -->
<div class="stat-cards">
    <div class="stat-card">
        <div class="stat-icon red"><i class="fas fa-receipt"></i></div>
        <div>
            <div class="stat-value"><?= $totalOrders ?></div>
            <div class="stat-label">Total Orders</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon gold"><i class="fas fa-rupee-sign"></i></div>
        <div>
            <div class="stat-value"><?= formatPrice($totalRevenue) ?></div>
            <div class="stat-label">Total Revenue</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon green"><i class="fas fa-boxes"></i></div>
        <div>
            <div class="stat-value"><?= $totalProducts ?></div>
            <div class="stat-label">Active Products</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon blue"><i class="fas fa-users"></i></div>
        <div>
            <div class="stat-value"><?= $totalUsers ?></div>
            <div class="stat-label">Registered Users</div>
        </div>
    </div>
</div>

<?php if ($pendingOrders > 0): ?>
<div style="background:#fff3cd;border:1px solid #ffc107;border-radius:6px;padding:12px 20px;margin-bottom:24px;display:flex;align-items:center;gap:12px;">
    <i class="fas fa-exclamation-triangle" style="color:#856404;font-size:1.3rem;"></i>
    <span><strong><?= $pendingOrders ?> pending order<?= $pendingOrders > 1 ? 's' : '' ?></strong> awaiting confirmation. <a href="<?= BASE_URL ?>admin/orders.php?status=placed" style="color:var(--crimson);font-weight:700;">View Now →</a></span>
</div>
<?php endif; ?>

<div style="display:grid;grid-template-columns:1fr 320px;gap:24px;flex-wrap:wrap;">

    <!-- Recent Orders -->
    <div class="admin-card">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
            <h3>Recent Orders</h3>
            <a href="<?= BASE_URL ?>admin/orders.php" style="font-size:13px;color:var(--crimson);font-weight:700;">View All →</a>
        </div>
        <table class="admin-table">
            <thead>
                <tr><th>Order #</th><th>Customer</th><th>Total</th><th>Status</th><th>Date</th><th>Action</th></tr>
            </thead>
            <tbody>
            <?php foreach ($recentOrders as $order): ?>
                <tr>
                    <td><strong><?= sanitize($order['order_number']) ?></strong></td>
                    <td><?= sanitize($order['guest_name'] ?: 'User #' . $order['user_id']) ?></td>
                    <td><?= formatPrice((float)$order['total']) ?></td>
                    <td><span class="status-badge status-<?= $order['order_status'] ?>"><?= ucfirst($order['order_status']) ?></span></td>
                    <td><?= date('d M', strtotime($order['created_at'])) ?></td>
                    <td class="action-links">
                        <a href="<?= BASE_URL ?>admin/order-detail.php?id=<?= $order['id'] ?>" class="edit">View</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Low Stock -->
    <div class="admin-card">
        <h3>⚠️ Low Stock Alert</h3>
        <?php if (empty($lowStock)): ?>
            <p style="color:var(--muted);font-size:14px;">All products are well stocked.</p>
        <?php else: ?>
            <?php foreach ($lowStock as $p): ?>
            <div style="display:flex;align-items:center;justify-content:space-between;padding:10px 0;border-bottom:1px solid var(--border);">
                <div style="font-size:14px;font-weight:600;"><?= sanitize($p['name']) ?></div>
                <span style="background:<?= $p['stock'] == 0 ? '#f8d7da' : '#fff3cd' ?>;color:<?= $p['stock'] == 0 ? '#721c24' : '#856404' ?>;padding:3px 10px;border-radius:20px;font-size:12px;font-weight:700;">
                    <?= $p['stock'] == 0 ? 'Out of Stock' : $p['stock'] . ' left' ?>
                </span>
            </div>
            <?php endforeach; ?>
            <div style="margin-top:12px;">
                <a href="<?= BASE_URL ?>admin/products.php" style="font-size:13px;color:var(--crimson);font-weight:700;">Manage Products →</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/partials/footer.php'; ?>
