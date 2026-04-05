<?php
// ============================================================
// Dhoti Mahal - Category / Product Listing
// ============================================================
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

$slug     = trim($_GET['slug'] ?? '');
$search   = trim($_GET['search'] ?? '');
$page     = max(1, (int)($_GET['page'] ?? 1));
$sortBy   = $_GET['sort'] ?? 'latest';

$category = null;
$products = [];
$total    = 0;
$pageTitle = 'All Products';

if ($search) {
    $pageTitle = 'Search: ' . $search;
    $products  = searchProducts($search, $page);
    $total     = count($products); // simplified
} elseif ($slug) {
    $category  = getCategoryBySlug($slug);
    if (!$category) {
        setFlash('error', 'Category not found.');
        redirect(BASE_URL);
    }
    $pageTitle = $category['name'];
    $products  = getProductsByCategory((int)$category['id'], $page);
    $total     = countProductsByCategory((int)$category['id']);
} else {
    // Show all products
    $db = getDB();
    $offset = ($page - 1) * PRODUCTS_PER_PAGE;
    $orderMap = ['latest' => 'p.created_at DESC', 'price_asc' => 'ep ASC', 'price_desc' => 'ep DESC', 'name' => 'p.name ASC'];
    $order = $orderMap[$sortBy] ?? 'p.created_at DESC';
    $stmt = $db->prepare("
        SELECT p.*, c.name AS category_name, c.slug AS category_slug,
               COALESCE(p.sale_price, p.price) AS ep
        FROM products p
        JOIN categories c ON p.category_id = c.id
        WHERE p.is_active = 1
        ORDER BY $order
        LIMIT ? OFFSET ?
    ");
    $stmt->execute([PRODUCTS_PER_PAGE, $offset]);
    $products = $stmt->fetchAll();
    $total    = (int)$db->query("SELECT COUNT(*) FROM products WHERE is_active=1")->fetchColumn();
}

require_once __DIR__ . '/../includes/header.php';
?>
<script>window.__BASE_URL = '<?= BASE_URL ?>';</script>

<!-- Page Hero -->
<div class="page-hero">
    <div class="container">
        <h1><?= sanitize($pageTitle) ?></h1>
        <?php if ($category && $category['description']): ?>
            <p><?= sanitize($category['description']) ?></p>
        <?php elseif ($search): ?>
            <p><?= $total ?> results for "<?= sanitize($search) ?>"</p>
        <?php endif; ?>
        <div class="breadcrumb">
            <a href="<?= BASE_URL ?>">Home</a>
            <span>›</span>
            <span><?= sanitize($pageTitle) ?></span>
        </div>
    </div>
</div>

<div class="container section">
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px; flex-wrap: wrap; gap: 12px;">
        <p style="color: var(--muted); font-size: 14px;">
            Showing <?= count($products) ?> of <?= $total ?> products
        </p>
        <?php if (!$search): ?>
        <form method="GET" style="display: flex; align-items: center; gap: 10px;">
            <?php if ($slug): ?><input type="hidden" name="slug" value="<?= sanitize($slug) ?>"><?php endif; ?>
            <label style="font-size: 14px; font-weight: 600;">Sort:</label>
            <select name="sort" onchange="this.form.submit()" style="padding: 8px 12px; border: 2px solid var(--border); border-radius: 4px; font-family: var(--font-body); font-size: 14px;">
                <option value="latest"     <?= $sortBy === 'latest' ? 'selected' : '' ?>>Latest</option>
                <option value="price_asc"  <?= $sortBy === 'price_asc' ? 'selected' : '' ?>>Price: Low to High</option>
                <option value="price_desc" <?= $sortBy === 'price_desc' ? 'selected' : '' ?>>Price: High to Low</option>
                <option value="name"       <?= $sortBy === 'name' ? 'selected' : '' ?>>Name A–Z</option>
            </select>
        </form>
        <?php endif; ?>
    </div>

    <?php if (!empty($products)): ?>
        <div class="product-grid">
            <?php foreach ($products as $product): ?>
                <?php include __DIR__ . '/../includes/product-card.php'; ?>
            <?php endforeach; ?>
        </div>

        <?php
        // Pagination
        $urlPattern = BASE_URL . 'pages/category.php?' . ($slug ? "slug=$slug&" : '') . ($search ? "search=" . urlencode($search) . "&" : '') . "sort=$sortBy&page=%d";
        echo paginate($total, $page, PRODUCTS_PER_PAGE, $urlPattern);
        ?>

    <?php else: ?>
        <div class="empty-state">
            <i class="fas fa-search"></i>
            <h3>No products found</h3>
            <p><?= $search ? "Try a different search term or browse our categories." : "This category has no products yet." ?></p>
            <a href="<?= BASE_URL ?>" class="btn btn-primary">Back to Home</a>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
