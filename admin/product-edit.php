<?php
// ============================================================
// Dhoti Mahal - Admin Edit Product
// ============================================================
$adminPageTitle = 'Edit Product';
require_once __DIR__ . '/partials/header.php';

$db  = getDB();
$pid = (int)($_GET['id'] ?? 0);
if (!$pid) { redirect(BASE_URL . 'admin/products.php'); }

$product = $db->prepare("SELECT * FROM products WHERE id=?");
$product->execute([$pid]);
$product = $product->fetch();
if (!$product) { setFlash('error', 'Product not found.'); redirect(BASE_URL . 'admin/products.php'); }

$categories = getCategories(false);
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name      = trim($_POST['name'] ?? '');
    $catId     = (int)($_POST['category_id'] ?? 0);
    $desc      = trim($_POST['description'] ?? '');
    $shortDesc = trim($_POST['short_description'] ?? '');
    $price     = (float)($_POST['price'] ?? 0);
    $salePrice = ($_POST['sale_price'] !== '') ? (float)$_POST['sale_price'] : null;
    $sku       = trim($_POST['sku'] ?? '');
    $stock     = (int)($_POST['stock'] ?? 0);
    $fabric    = trim($_POST['fabric'] ?? '');
    $color     = trim($_POST['color'] ?? '');
    $size      = trim($_POST['size'] ?? '');
    $occasion  = trim($_POST['occasion'] ?? '');
    $featured  = isset($_POST['is_featured']) ? 1 : 0;
    $active    = isset($_POST['is_active']) ? 1 : 0;

    if (!$name)   $errors[] = 'Product name is required.';
    if ($price <= 0) $errors[] = 'Valid price is required.';

    $imageName = $product['image'];
    if (!empty($_FILES['image']['name'])) {
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['jpg','jpeg','png','webp'])) {
            $errors[] = 'Invalid image format.';
        } else {
            $newName = uniqid('prod_') . '.' . $ext;
            $dest = UPLOAD_PATH . 'products/' . $newName;
            if (move_uploaded_file($_FILES['image']['tmp_name'], $dest)) {
                $imageName = $newName;
            }
        }
    }

    if (empty($errors)) {
        $db->prepare("
            UPDATE products SET
            category_id=?, name=?, description=?, short_description=?, price=?, sale_price=?,
            sku=?, stock=?, fabric=?, color=?, size=?, occasion=?, image=?, is_featured=?, is_active=?,
            updated_at=NOW()
            WHERE id=?
        ")->execute([
            $catId, $name, $desc, $shortDesc, $price, $salePrice,
            $sku, $stock, $fabric, $color, $size, $occasion, $imageName, $featured, $active, $pid
        ]);
        setFlash('success', 'Product updated successfully!');
        redirect(BASE_URL . 'admin/product-edit.php?id=' . $pid);
    }
    // Repopulate with POST data on error
    $product = array_merge($product, $_POST);
}
?>

<div style="display:flex;align-items:center;gap:16px;margin-bottom:24px;">
    <a href="<?= BASE_URL ?>admin/products.php" style="color:var(--muted);font-size:14px;"><i class="fas fa-arrow-left"></i> Back</a>
    <h1 style="margin:0;">Edit: <?= sanitize($product['name']) ?></h1>
</div>

<?php if (!empty($errors)): ?>
    <div class="flash-message flash-error" style="border-radius:6px;padding:14px 20px;margin-bottom:20px;">
        <ul style="margin:0;padding-left:18px;"><?php foreach ($errors as $e): ?><li><?= sanitize($e) ?></li><?php endforeach; ?></ul>
    </div>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data">
<div style="display:grid;grid-template-columns:1fr 320px;gap:24px;">
    <div>
        <div class="admin-card">
            <h3>Product Information</h3>
            <div class="form-group">
                <label>Product Name *</label>
                <input type="text" name="name" value="<?= sanitize($product['name']) ?>" required>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Category</label>
                    <select name="category_id">
                        <?php foreach ($categories as $c): ?>
                            <option value="<?= $c['id'] ?>" <?= $product['category_id'] == $c['id'] ? 'selected' : '' ?>><?= sanitize($c['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>SKU</label>
                    <input type="text" name="sku" value="<?= sanitize($product['sku'] ?? '') ?>">
                </div>
            </div>
            <div class="form-group">
                <label>Short Description</label>
                <input type="text" name="short_description" value="<?= sanitize($product['short_description'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label>Full Description</label>
                <textarea name="description" rows="5"><?= sanitize($product['description'] ?? '') ?></textarea>
            </div>
        </div>

        <div class="admin-card">
            <h3>Pricing & Stock</h3>
            <div class="form-row">
                <div class="form-group">
                    <label>MRP / Price (₹) *</label>
                    <input type="number" name="price" value="<?= $product['price'] ?>" step="0.01" required>
                </div>
                <div class="form-group">
                    <label>Sale Price (₹)</label>
                    <input type="number" name="sale_price" value="<?= $product['sale_price'] ?? '' ?>" step="0.01" placeholder="Leave blank for no sale">
                </div>
            </div>
            <div class="form-group" style="max-width:200px;">
                <label>Stock Quantity</label>
                <input type="number" name="stock" value="<?= (int)$product['stock'] ?>" min="0">
            </div>
        </div>

        <div class="admin-card">
            <h3>Product Attributes</h3>
            <div class="form-row">
                <div class="form-group">
                    <label>Fabric</label>
                    <input type="text" name="fabric" value="<?= sanitize($product['fabric'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>Color</label>
                    <input type="text" name="color" value="<?= sanitize($product['color'] ?? '') ?>">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Sizes (comma-separated)</label>
                    <input type="text" name="size" value="<?= sanitize($product['size'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>Occasion</label>
                    <input type="text" name="occasion" value="<?= sanitize($product['occasion'] ?? '') ?>">
                </div>
            </div>
        </div>
    </div>

    <div>
        <div class="admin-card">
            <h3>Product Image</h3>
            <?php if ($product['image']): ?>
                <img src="<?= productImage($product['image']) ?>" style="max-width:100%;border-radius:4px;margin-bottom:12px;" alt="Current image">
            <?php endif; ?>
            <div class="form-group">
                <label>Replace Image (optional)</label>
                <input type="file" name="image" id="productImage" accept="image/*">
                <img id="imagePreview" src="" alt="Preview" style="display:none;max-width:100%;margin-top:10px;border-radius:4px;">
            </div>
        </div>

        <div class="admin-card">
            <h3>Visibility</h3>
            <div style="display:flex;flex-direction:column;gap:12px;">
                <label style="display:flex;align-items:center;gap:10px;cursor:pointer;font-size:14px;">
                    <input type="checkbox" name="is_active" <?= $product['is_active'] ? 'checked' : '' ?> style="width:16px;height:16px;">
                    Active (visible in store)
                </label>
                <label style="display:flex;align-items:center;gap:10px;cursor:pointer;font-size:14px;">
                    <input type="checkbox" name="is_featured" <?= $product['is_featured'] ? 'checked' : '' ?> style="width:16px;height:16px;">
                    Featured on Homepage
                </label>
            </div>
        </div>

        <button type="submit" class="btn btn-primary btn-full" style="font-size:1rem;padding:14px;">
            <i class="fas fa-save"></i> Save Changes
        </button>
        <a href="<?= BASE_URL ?>pages/product.php?slug=<?= urlencode($product['slug']) ?>" target="_blank" class="btn btn-outline btn-full" style="margin-top:8px;">
            <i class="fas fa-external-link-alt"></i> View on Site
        </a>
    </div>
</div>
</form>

<?php require_once __DIR__ . '/partials/footer.php'; ?>
