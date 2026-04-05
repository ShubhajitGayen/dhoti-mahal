<?php
// ============================================================
// Dhoti Mahal - Admin Categories
// ============================================================
$adminPageTitle = 'Categories';
require_once __DIR__ . '/partials/header.php';

$db     = getDB();
$errors = [];

// Toggle Active Status
if (isset($_GET['toggle'])) {
    $cid = (int)$_GET['toggle'];

    $stmt = $db->prepare("SELECT is_active FROM categories WHERE id=?");
    $stmt->execute([$cid]);
    $cat = $stmt->fetch();

    if ($cat) {
        $newStatus = $cat['is_active'] ? 0 : 1;

        $db->prepare("UPDATE categories SET is_active=? WHERE id=?")
            ->execute([$newStatus, $cid]);

        setFlash('success', $newStatus ? 'Activated' : 'Deactivated');
    }

    redirect(BASE_URL . 'admin/categories.php');
}
// Add
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_category'])) {
    $name  = trim($_POST['name'] ?? '');
    $desc  = trim($_POST['description'] ?? '');
    $order = (int)($_POST['sort_order'] ?? 0);
    if (!$name) $errors[] = 'Category name is required.';
    if (empty($errors)) {
        $slug = generateSlug($name);
        $check = $db->prepare("SELECT id FROM categories WHERE slug=?");
        $check->execute([$slug]);
        if ($check->fetch()) $slug .= '-' . time();
        $db->prepare("INSERT INTO categories (name, slug, description, sort_order) VALUES (?,?,?,?)")
            ->execute([$name, $slug, $desc, $order]);
        setFlash('success', "Category \"$name\" added!");
        redirect(BASE_URL . 'admin/categories.php');
    }
}

$cats = getCategories(false);
?>

<h1>Categories</h1>

<div style="display:grid;grid-template-columns:1fr 340px;gap:24px;">
    <div class="admin-card">
        <h3>All Categories</h3>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Slug</th>
                    <th>Products</th>
                    <th>Order</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($cats as $cat):
                    $count = (int)$db->prepare("SELECT COUNT(*) FROM products WHERE category_id=?")->execute([$cat['id']]) ? $db->query("SELECT COUNT(*) FROM products WHERE category_id=" . (int)$cat['id'])->fetchColumn() : 0;
                ?>
                <tr>
                    <td><strong><?= sanitize($cat['name']) ?></strong></td>
                    <td><code style="font-size:12px;"><?= sanitize($cat['slug']) ?></code></td>
                    <td><?= (int)$count ?></td>
                    <td><?= (int)$cat['sort_order'] ?></td>
                    <td><span class="status-badge"
                            style="background:<?= $cat['is_active'] ? '#d4edda' : '#f8d7da' ?>;color:<?= $cat['is_active'] ? '#155724' : '#721c24' ?>;"><?= $cat['is_active'] ? 'Active' : 'Inactive' ?></span>
                    </td>
                    <td class="action-links">
                        <a href="<?= BASE_URL ?>pages/category.php?slug=<?= urlencode($cat['slug']) ?>" target="_blank"
                            class="edit">View</a>
                        <a href="?toggle=<?= (int)$cat['id'] ?>" onclick="return confirm('Change status?')">
                            <?= $cat['is_active'] ? 'Deactivate' : 'Activate' ?>
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="admin-card">
        <h3>Add New Category</h3>
        <?php if (!empty($errors)): ?>
        <div class="flash-message flash-error" style="border-radius:4px;padding:10px 14px;margin-bottom:14px;">
            <?= sanitize($errors[0]) ?></div>
        <?php endif; ?>
        <form method="POST">
            <input type="hidden" name="add_category" value="1">
            <div class="form-group">
                <label>Category Name *</label>
                <input type="text" name="name" required placeholder="e.g. Silk Dhotis">
            </div>
            <div class="form-group">
                <label>Description</label>
                <textarea name="description" rows="3" placeholder="Brief description..."></textarea>
            </div>
            <div class="form-group">
                <label>Sort Order</label>
                <input type="number" name="sort_order" value="0" min="0">
            </div>
            <button type="submit" class="btn btn-primary btn-full"><i class="fas fa-plus"></i> Add Category</button>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/partials/footer.php'; ?>