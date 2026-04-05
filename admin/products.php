<?php
// ============================================================
// Dhoti Mahal - Admin Products
// ============================================================
$adminPageTitle = 'Products';
require_once __DIR__ . '/partials/header.php';

$db = getDB();

// Handle delete
if (!empty($_GET['delete'])) {
    $pid = (int)$_GET['delete'];
    $db->prepare("UPDATE products SET is_active=0 WHERE id=?")->execute([$pid]);
    setFlash('success', 'Product deactivated successfully.');
    redirect(BASE_URL . 'admin/products.php');
}

$page    = max(1, (int)($_GET['page'] ?? 1));
$perPage = 20;
$offset  = ($page - 1) * $perPage;
$search  = trim($_GET['search'] ?? '');

$like = '%' . $search . '%';
$stmt = $db->prepare("
    SELECT p.*, c.name AS category_name
    FROM products p
    JOIN categories c ON p.category_id = c.id
    WHERE p.name LIKE ? OR c.name LIKE ?
    ORDER BY p.created_at DESC
    LIMIT ? OFFSET ?
");
$stmt->execute([$like, $like, $perPage, $offset]);
$products = $stmt->fetchAll();
$total    = (int)$db->query("SELECT COUNT(*) FROM products")->fetchColumn();
$totalPages = (int)ceil($total / $perPage);
?>

<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;flex-wrap:wrap;gap:12px;">
    <h1 style="margin-bottom:0;">Products</h1>
    <a href="<?= BASE_URL ?>admin/product-add.php" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Add Product</a>
</div>

<!-- Search -->
<form method="GET" style="margin-bottom:20px;display:flex;gap:10px;max-width:400px;">
    <input type="text" name="search" value="<?= sanitize($search) ?>" placeholder="Search products..." style="flex:1;padding:9px 14px;border:2px solid var(--border);border-radius:4px;font-size:14px;outline:none;font-family:var(--font-body);">
    <button type="submit" class="btn btn-outline btn-sm"><i class="fas fa-search"></i></button>
</form>

<div class="admin-card">
    <table class="admin-table">
        <thead>
            <tr><th>Image</th><th>Name</th><th>Category</th><th>Price</th><th>Stock</th><th>Status</th><th>Actions</th></tr>
        </thead>
        <tbody>
        <?php if (empty($products)): ?>
            <tr><td colspan="7" style="text-align:center;padding:40px;color:var(--muted);">No products found.</td></tr>
        <?php endif; ?>
        <?php foreach ($products as $p): ?>
            <tr>
                <td>
                    <img src="<?= productImage($p['image'] ?? '') ?>" style="width:45px;height:55px;object-fit:cover;border-radius:4px;" alt="">
                </td>
                <td>
                    <strong style="font-size:14px;"><?= sanitize($p['name']) ?></strong>
                    <?php if ($p['is_featured']): ?><br><span style="font-size:11px;background:#fff3cd;color:#856404;padding:2px 6px;border-radius:2px;">Featured</span><?php endif; ?>
                </td>
                <td><?= sanitize($p['category_name']) ?></td>
                <td>
                    <?php if ($p['sale_price']): ?>
                        <span style="text-decoration:line-through;color:var(--muted);font-size:12px;"><?= formatPrice((float)$p['price']) ?></span><br>
                        <strong style="color:var(--crimson);"><?= formatPrice((float)$p['sale_price']) ?></strong>
                    <?php else: ?>
                        <?= formatPrice((float)$p['price']) ?>
                    <?php endif; ?>
                </td>
                <td>
                    <span style="font-weight:700;color:<?= $p['stock'] == 0 ? '#dc3545' : ($p['stock'] <= 5 ? '#856404' : '#28a745') ?>;">
                        <?= (int)$p['stock'] ?>
                    </span>
                </td>
                <td>
                    <span class="status-badge" style="background:<?= $p['is_active'] ? '#d4edda' : '#f8d7da' ?>;color:<?= $p['is_active'] ? '#155724' : '#721c24' ?>;">
                        <?= $p['is_active'] ? 'Active' : 'Inactive' ?>
                    </span>
                </td>
                <td class="action-links">
                    <a href="<?= BASE_URL ?>admin/product-edit.php?id=<?= $p['id'] ?>" class="edit"><i class="fas fa-edit"></i> Edit</a>
                    <a href="?delete=<?= $p['id'] ?>" class="delete confirm-delete"><i class="fas fa-trash"></i> Remove</a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
    <div class="pagination" style="margin:20px 0 0;">
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <a href="?page=<?= $i ?>&search=<?= urlencode($search) ?>" class="page-link <?= $i === $page ? 'active' : '' ?>"><?= $i ?></a>
        <?php endfor; ?>
    </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/partials/footer.php'; ?>
