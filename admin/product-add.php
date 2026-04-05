<?php
// ============================================================
// Dhoti Mahal - Admin Add Product
// ============================================================
$adminPageTitle = 'Add Product';
require_once __DIR__ . '/partials/header.php';

$db         = getDB();
$categories = getCategories(false);
$errors     = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name      = trim($_POST['name'] ?? '');
    $catId     = (int)($_POST['category_id'] ?? 0);
    $desc      = trim($_POST['description'] ?? '');
    $shortDesc = trim($_POST['short_description'] ?? '');
    $price     = (float)($_POST['price'] ?? 0);
    $salePrice = $_POST['sale_price'] !== '' ? (float)$_POST['sale_price'] : null;
    $sku       = trim($_POST['sku'] ?? '');
    $stock     = (int)($_POST['stock'] ?? 0);
    $fabric    = trim($_POST['fabric'] ?? '');
    $color     = trim($_POST['color'] ?? '');
    $size      = trim($_POST['size'] ?? '');
    $occasion  = trim($_POST['occasion'] ?? '');
    $featured  = isset($_POST['is_featured']) ? 1 : 0;
    $active    = isset($_POST['is_active']) ? 1 : 0;

    if (!$name)   $errors[] = 'Product name is required.';
    if (!$catId)  $errors[] = 'Category is required.';
    if ($price <= 0) $errors[] = 'Valid price is required.';

    // Handle image upload
    // Handle image upload
$imageName = '';

if (!empty($_FILES['image']['name'])) {

    // Check upload error
    if ($_FILES['image']['error'] !== UPLOAD_ERR_OK) {
        $errors[] = 'Upload error occurred.';
    } else {

        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));

        // Validate extension
        if (!in_array($ext, ['jpg','jpeg','png','webp'])) {
            $errors[] = 'Invalid image format. Use JPG, PNG or WebP.';
        } else {

            // Upload directory
            $uploadDir = UPLOAD_PATH . 'products/';

            // 🔥 Create folder if not exists (IMPORTANT FIX)
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            // Generate unique file name
            $imageName = uniqid('prod_') . '.' . $ext;

            // Full path
            $dest = $uploadDir . $imageName;

            // Move file
            if (!move_uploaded_file($_FILES['image']['tmp_name'], $dest)) {
                $errors[] = 'Image upload failed.';
                $imageName = '';
            }
        }
    }
}

    if (empty($errors)) {
        $slug = generateSlug($name);
        // Ensure unique slug
        $check = $db->prepare("SELECT id FROM products WHERE slug = ?");
        $check->execute([$slug]);
        if ($check->fetch()) $slug .= '-' . time();

        $db->prepare("
            INSERT INTO products
            (category_id, name, slug, description, short_description, price, sale_price, sku, stock,
             fabric, color, size, occasion, image, is_featured, is_active)
            VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)
        ")->execute([
            $catId, $name, $slug, $desc, $shortDesc, $price, $salePrice, $sku, $stock,
            $fabric, $color, $size, $occasion, $imageName, $featured, $active
        ]);
        setFlash('success', "Product \"$name\" added successfully!");
        redirect(BASE_URL . 'admin/products.php');
    }
}
?>

<div style="display:flex;align-items:center;gap:16px;margin-bottom:24px;">
    <a href="<?= BASE_URL ?>admin/products.php" style="color:var(--muted);font-size:14px;"><i
            class="fas fa-arrow-left"></i> Back</a>
    <h1 style="margin:0;">Add New Product</h1>
</div>

<?php if (!empty($errors)): ?>
<div class="flash-message flash-error" style="border-radius:6px;padding:14px 20px;margin-bottom:20px;">
    <ul style="margin:0;padding-left:18px;"><?php foreach ($errors as $e): ?><li><?= sanitize($e) ?></li>
        <?php endforeach; ?></ul>
</div>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data">
    <div style="display:grid;grid-template-columns:1fr 320px;gap:24px;">
        <div>
            <div class="admin-card">
                <h3>Product Information</h3>
                <div class="form-group">
                    <label>Product Name *</label>
                    <input type="text" name="name" value="<?= sanitize($_POST['name'] ?? '') ?>" required>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Category *</label>
                        <select name="category_id" required>
                            <option value="">Select Category</option>
                            <?php foreach ($categories as $c): ?>
                            <option value="<?= $c['id'] ?>"
                                <?= ($_POST['category_id'] ?? '') == $c['id'] ? 'selected' : '' ?>>
                                <?= sanitize($c['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>SKU</label>
                        <input type="text" name="sku" value="<?= sanitize($_POST['sku'] ?? '') ?>"
                            placeholder="e.g. DM-SILK-001">
                    </div>
                </div>
                <div class="form-group">
                    <label>Short Description</label>
                    <input type="text" name="short_description"
                        value="<?= sanitize($_POST['short_description'] ?? '') ?>"
                        placeholder="One-line product summary">
                </div>
                <div class="form-group">
                    <label>Full Description</label>
                    <textarea name="description" rows="5"><?= sanitize($_POST['description'] ?? '') ?></textarea>
                </div>
            </div>

            <div class="admin-card">
                <h3>Pricing & Stock</h3>
                <div class="form-row">
                    <div class="form-group">
                        <label>MRP / Original Price (₹) *</label>
                        <input type="number" name="price" value="<?= sanitize($_POST['price'] ?? '') ?>" step="0.01"
                            required placeholder="0.00">
                    </div>
                    <div class="form-group">
                        <label>Sale Price (₹) — leave blank for no discount</label>
                        <input type="number" name="sale_price" value="<?= sanitize($_POST['sale_price'] ?? '') ?>"
                            step="0.01" placeholder="0.00">
                    </div>
                </div>
                <div class="form-group" style="max-width:200px;">
                    <label>Stock Quantity *</label>
                    <input type="number" name="stock" value="<?= sanitize($_POST['stock'] ?? '0') ?>" min="0" required>
                </div>
            </div>

            <div class="admin-card">
                <h3>Product Attributes</h3>
                <div class="form-row">
                    <div class="form-group">
                        <label>Fabric</label>
                        <input type="text" name="fabric" value="<?= sanitize($_POST['fabric'] ?? '') ?>"
                            placeholder="e.g. Pure Silk, Cotton">
                    </div>
                    <div class="form-group">
                        <label>Color</label>
                        <input type="text" name="color" value="<?= sanitize($_POST['color'] ?? '') ?>"
                            placeholder="e.g. Cream with Gold Border">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Available Sizes (comma-separated)</label>
                        <input type="text" name="size" value="<?= sanitize($_POST['size'] ?? '') ?>"
                            placeholder="e.g. 4 Meters, 4.5 Meters, 5 Meters">
                    </div>
                    <div class="form-group">
                        <label>Occasion</label>
                        <input type="text" name="occasion" value="<?= sanitize($_POST['occasion'] ?? '') ?>"
                            placeholder="e.g. Wedding, Festival">
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column -->
        <div>
            <div class="admin-card">
                <h3>Product Image</h3>
                <div class="form-group">
                    <label>Upload Image (JPG/PNG/WebP)</label>
                    <input type="file" name="image" id="productImage" accept="image/*">
                    <img id="imagePreview" src="" alt="Preview"
                        style="display:none;max-width:100%;margin-top:10px;border-radius:4px;">
                </div>
            </div>

            <div class="admin-card">
                <h3>Visibility</h3>
                <div style="display:flex;flex-direction:column;gap:12px;">
                    <label style="display:flex;align-items:center;gap:10px;cursor:pointer;font-size:14px;">
                        <input type="checkbox" name="is_active" <?= ($_POST['is_active'] ?? '1') ? 'checked' : '' ?>
                            style="width:16px;height:16px;">
                        Active (visible in store)
                    </label>
                    <label style="display:flex;align-items:center;gap:10px;cursor:pointer;font-size:14px;">
                        <input type="checkbox" name="is_featured" <?= ($_POST['is_featured'] ?? '') ? 'checked' : '' ?>
                            style="width:16px;height:16px;">
                        Featured on Homepage
                    </label>
                </div>
            </div>

            <button type="submit" class="btn btn-primary btn-full" style="font-size:1rem;padding:14px;">
                <i class="fas fa-plus-circle"></i> Add Product
            </button>
        </div>
    </div>
</form>

<?php require_once __DIR__ . '/partials/footer.php'; ?>