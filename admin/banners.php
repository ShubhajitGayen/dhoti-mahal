<?php
// ============================================================
// Dhoti Mahal - Admin Banners
// ============================================================
$adminPageTitle = 'Banners';
require_once __DIR__ . '/partials/header.php';

$db     = getDB();
$errors = [];

// Delete
if (!empty($_GET['delete'])) {
    $db->prepare("DELETE FROM banners WHERE id=?")->execute([(int)$_GET['delete']]);
    setFlash('success', 'Banner deleted.');
    redirect(BASE_URL . 'admin/banners.php');
}

// Add
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title   = trim($_POST['title'] ?? '');
    $sub     = trim($_POST['subtitle'] ?? '');
    $link    = trim($_POST['link'] ?? '');
    $btnText = trim($_POST['button_text'] ?? '');
    $order   = (int)($_POST['sort_order'] ?? 0);

    $imgName = '';
    if (!empty($_FILES['image']['name'])) {
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg','jpeg','png','webp'])) {
            $imgName = uniqid('banner_') . '.' . $ext;
            move_uploaded_file($_FILES['image']['tmp_name'], UPLOAD_PATH . 'banners/' . $imgName);
        } else {
            $errors[] = 'Invalid image format.';
        }
    } else {
        $errors[] = 'Banner image is required.';
    }

    if (empty($errors)) {
        $db->prepare("INSERT INTO banners (title, subtitle, image, link, button_text, sort_order) VALUES (?,?,?,?,?,?)")
           ->execute([$title, $sub, $imgName, $link, $btnText, $order]);
        setFlash('success', 'Banner added successfully!');
        redirect(BASE_URL . 'admin/banners.php');
    }
}

$banners = getBanners();
?>

<h1>Banners</h1>
<div style="display:grid;grid-template-columns:1fr 360px;gap:24px;">
    <div class="admin-card">
        <h3>Active Banners</h3>
        <?php if (empty($banners)): ?>
            <p style="color:var(--muted);">No banners yet. Add one to show on the homepage slider.</p>
        <?php endif; ?>
        <?php foreach ($banners as $b): ?>
        <div style="display:flex;gap:16px;align-items:center;padding:14px 0;border-bottom:1px solid var(--border);">
            <img src="<?= bannerImage($b['image']) ?>" style="width:100px;height:60px;object-fit:cover;border-radius:4px;" alt="">
            <div style="flex:1;">
                <strong><?= sanitize($b['title']) ?></strong>
                <div style="font-size:13px;color:var(--muted);"><?= sanitize($b['subtitle']) ?></div>
                <div style="font-size:12px;color:var(--muted);">Sort Order: <?= (int)$b['sort_order'] ?></div>
            </div>
            <a href="?delete=<?= $b['id'] ?>" class="btn btn-outline btn-sm confirm-delete" style="color:var(--crimson);border-color:var(--crimson);">
                <i class="fas fa-trash"></i>
            </a>
        </div>
        <?php endforeach; ?>
    </div>

    <div class="admin-card">
        <h3>Add New Banner</h3>
        <?php if (!empty($errors)): ?>
            <div class="flash-message flash-error" style="border-radius:4px;padding:10px 14px;margin-bottom:14px;"><?= sanitize($errors[0]) ?></div>
        <?php endif; ?>
        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label>Banner Image * (JPG/PNG/WebP)</label>
                <input type="file" name="image" required accept="image/*">
                <div style="font-size:12px;color:var(--muted);margin-top:4px;">Recommended: 1400×500px</div>
            </div>
            <div class="form-group">
                <label>Title</label>
                <input type="text" name="title" placeholder="e.g. New Silk Collection">
            </div>
            <div class="form-group">
                <label>Subtitle</label>
                <input type="text" name="subtitle" placeholder="Brief description or tagline">
            </div>
            <div class="form-group">
                <label>Link URL</label>
                <input type="text" name="link" placeholder="e.g. pages/category.php?slug=silk-dhotis">
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Button Text</label>
                    <input type="text" name="button_text" placeholder="e.g. Shop Now">
                </div>
                <div class="form-group">
                    <label>Sort Order</label>
                    <input type="number" name="sort_order" value="0" min="0">
                </div>
            </div>
            <button type="submit" class="btn btn-primary btn-full"><i class="fas fa-plus"></i> Add Banner</button>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/partials/footer.php'; ?>
