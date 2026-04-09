<?php
// ============================================================
// Dhoti Mahal - Helper Functions
// ============================================================

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/config.php';

// ---- Initialization ----
function cleanupGuestCart()
{
    $db = getDB();

    $db->query("
        DELETE FROM cart 
        WHERE session_id IS NOT NULL 
        AND updated_at < NOW() - INTERVAL 1 HOUR
    ");
}



// ---get cart quantity for a product (used in product page to limit max qty)---

function getCartQuantity(?int $userId, ?string $session_id, int $productId): int
{
    $db = getDB();
    $stmt = $db->prepare("
        SELECT SUM(quantity) AS quantity 
        FROM cart  
        WHERE (user_id = ? OR session_id = ?) AND product_id = ?
    ");

    $stmt->execute([$userId, $session_id,  $productId]);
    $result = $stmt->fetch();

    return (int)($result['quantity'] ?? 1); // default = 1
}




// ---- Security ----


function sanitize(string $input): string
{
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

function generateCSRF(): string
{
    if (empty($_SESSION[CSRF_TOKEN_NAME])) {
        $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
    }
    return $_SESSION[CSRF_TOKEN_NAME];
}

function validateCSRF(string $token): bool
{
    return isset($_SESSION[CSRF_TOKEN_NAME]) && hash_equals($_SESSION[CSRF_TOKEN_NAME], $token);
}

function generateSlug(string $text): string
{
    $text = strtolower($text);
    $text = preg_replace('/[^\w\s-]/', '', $text);
    $text = preg_replace('/[\s_-]+/', '-', $text);
    return trim($text, '-');
}

// ---- Site Settings ----

function getSetting(string $key, string $default = ''): string
{
    static $settings = [];
    if (empty($settings)) {
        $db = getDB();
        $stmt = $db->query("SELECT setting_key, setting_value FROM settings");
        $settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    }
    return $settings[$key] ?? $default;
}

// ---- Categories ----

function getCategories(bool $activeOnly = true): array
{
    $db = getDB();
    $sql = "SELECT * FROM categories";
    if ($activeOnly) $sql .= " WHERE is_active = 1";
    $sql .= " ORDER BY sort_order ASC, name ASC";
    return $db->query($sql)->fetchAll();
}

function getCategoryBySlug(string $slug): ?array
{
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM categories WHERE slug = ? AND is_active = 1");
    $stmt->execute([$slug]);
    return $stmt->fetch() ?: null;
}

// ---- Products ----

function getFeaturedProducts(int $limit = 8): array
{
    $db = getDB();
    $stmt = $db->prepare("
        SELECT p.*, c.name AS category_name, c.slug AS category_slug
        FROM products p
        JOIN categories c ON p.category_id = c.id
        WHERE p.is_featured = 1 AND p.is_active = 1
        ORDER BY p.created_at DESC
        LIMIT ?
    ");
    $stmt->execute([$limit]);
    return $stmt->fetchAll();
}

function getProductsByCategory(int $categoryId, int $page = 1, int $perPage = PRODUCTS_PER_PAGE): array
{
    $db = getDB();
    $offset = ($page - 1) * $perPage;
    $stmt = $db->prepare("
        SELECT p.*, c.name AS category_name
        FROM products p
        JOIN categories c ON p.category_id = c.id
        WHERE p.category_id = ? AND p.is_active = 1
        ORDER BY p.created_at DESC
        LIMIT ? OFFSET ?
    ");
    $stmt->execute([$categoryId, $perPage, $offset]);
    return $stmt->fetchAll();
}

function countProductsByCategory(int $categoryId): int
{
    $db = getDB();
    $stmt = $db->prepare("SELECT COUNT(*) FROM products WHERE category_id = ? AND is_active = 1");
    $stmt->execute([$categoryId]);
    return (int)$stmt->fetchColumn();
}

function getProductBySlug(string $slug): ?array
{
    $db = getDB();
    $stmt = $db->prepare("
        SELECT p.*, c.name AS category_name, c.slug AS category_slug
        FROM products p
        JOIN categories c ON p.category_id = c.id
        WHERE p.slug = ? AND p.is_active = 1
    ");
    $stmt->execute([$slug]);
    return $stmt->fetch() ?: null;
}

function getProductById(int $id): ?array
{
    $db = getDB();
    $stmt = $db->prepare("SELECT p.*, c.name AS category_name FROM products p JOIN categories c ON p.category_id = c.id WHERE p.id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch() ?: null;
}

function getRelatedProducts(int $categoryId, int $excludeId, int $limit = 4): array
{
    $db = getDB();
    $stmt = $db->prepare("
        SELECT * FROM products
        WHERE category_id = ? AND id != ? AND is_active = 1
        ORDER BY RAND() LIMIT ?
    ");
    $stmt->execute([$categoryId, $excludeId, $limit]);
    return $stmt->fetchAll();
}

function searchProducts(string $query, int $page = 1): array
{
    $db = getDB();
    $offset = ($page - 1) * PRODUCTS_PER_PAGE;
    $like = '%' . $query . '%';
    $stmt = $db->prepare("
        SELECT p.*, c.name AS category_name, c.slug AS category_slug
        FROM products p
        JOIN categories c ON p.category_id = c.id
        WHERE p.is_active = 1 AND (p.name LIKE ? OR p.description LIKE ? OR p.fabric LIKE ? OR p.color LIKE ?)
        ORDER BY p.is_featured DESC, p.created_at DESC
        LIMIT ? OFFSET ?
    ");
    $stmt->execute([$like, $like, $like, $like, PRODUCTS_PER_PAGE, $offset]);
    return $stmt->fetchAll();
}

// ---- Pricing ----

function formatPrice(float $price): string
{
    return CURRENCY . number_format($price, 2);
}

function getEffectivePrice(array $product): float
{
    return ($product['sale_price'] && $product['sale_price'] < $product['price'])
        ? (float)$product['sale_price']
        : (float)$product['price'];
}

function getDiscountPercent(array $product): int
{
    if (!$product['sale_price'] || $product['sale_price'] >= $product['price']) return 0;
    return (int)round((($product['price'] - $product['sale_price']) / $product['price']) * 100);
}

// ---- Cart ----
function getCart(): array
{
    $db = getDB();

    $userId    = $_SESSION['user_id'] ?? null;
    $sessionId = session_id();

    // Ensure only ONE is used
    if ($userId) {
        $sessionId = null;
    } else {
        $userId = null;
    }


    if ($userId) {
        $stmt = $db->prepare("
             SELECT c.user_id,
                c.id,
                c.product_id,
                SUM(c.quantity) as quantity,
                c.size,
                p.name,
                p.price,
                p.image,
                p.slug
            FROM cart c
            JOIN products p ON c.product_id = p.id
            WHERE c.user_id = :user_id GROUP BY product_id
        ");
        $stmt->execute([':user_id' => $userId]);
    } else {
        $stmt = $db->prepare("
            SELECT 
                c.id,
                c.product_id,
                c.quantity,
                c.size,
                p.name,
                p.price,
                p.image,
                p.slug
            FROM cart c
            JOIN products p ON c.product_id = p.id
            WHERE c.session_id = :session_id
        ");
        $stmt->execute([':session_id' => $sessionId]);
    }

    echo $stmt->errorInfo()[2] ?? '';

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}


function getCartCount(): int
{
    $db = getDB();

    $userId    = $_SESSION['user_id'] ?? null;
    $sessionId = session_id();

    $stmt = $db->prepare("
        SELECT SUM(quantity) FROM cart
        WHERE user_id = ? OR session_id = ?
    ");
    $stmt->execute([$userId, $sessionId]);

    return (int)$stmt->fetchColumn();
}

function getCartTotal(): float
{
    $cart = getCart();
    $total = 0;
    foreach ($cart as $item) {
        $total += $item['price'] * $item['quantity'];
    }
    return $total;
}

function addToCart(int $productId, int $quantity, string $size = ''): bool
{
    $db = getDB();

    $product = getProductById($productId);
    if (!$product) return false;

    $userId    = $_SESSION['user_id'] ?? null;
    $sessionId = session_id();


    if ($userId) {
        $sessionId = null;
    } else {
        $userId = null;
    }

    // Check existing quantity
    $stmt = $db->prepare("
        SELECT quantity FROM cart 
        WHERE product_id = :product_id 
        AND (user_id = :user_id OR session_id = :session_id)
        AND size = :size
    ");
    $stmt->execute([
        ':product_id' => $productId,
        ':user_id' => $userId,
        ':session_id' => $sessionId,
        ':size' => $size
    ]);

    $existing = $stmt->fetchColumn() ?: 0;

    if (($existing + $quantity) > $product['stock']) return false;

    try {
        $stmt = $db->prepare("
            INSERT INTO cart (user_id, session_id, product_id, quantity, size)
            VALUES (:user_id, :session_id, :product_id, :quantity, :size)
            ON DUPLICATE KEY UPDATE quantity = quantity + VALUES(quantity)
        ");

        $stmt->execute([
            ':user_id'    => $userId,
            ':session_id' => $userId ? null : $sessionId,
            ':product_id' => $productId,
            ':quantity'   => $quantity,
            ':size'       => $size
        ]);

        return true;
    } catch (PDOException $e) {
        error_log($e->getMessage());
        return false;
    }
}

function isProductInCart($userId, $sessionId, $productId): bool
{
    $db = getDB();
    $stmt = $db->prepare("
        SELECT EXISTS(
            SELECT 1 
            FROM cart 
            WHERE (user_id = :user_id OR session_id = :session_id) 
            AND product_id = :product_id
        ) AS is_in_cart
    ");

    $stmt->execute([
        ':user_id' => $userId,
        ':session_id' => $sessionId,
        ':product_id' => $productId
    ]);
    $result = $stmt->fetch();
    return (bool) $result['is_in_cart']; // true or false
}

function removeFromCart($user_id, $session_id, $product_id): bool
{
    $db = getDB();

    $stmt = $db->prepare("DELETE FROM cart WHERE (user_id = :user_id OR session_id = :session_id) AND product_id = :product_id");
    return $stmt->execute([
        ':user_id' => $user_id,
        ':session_id' => $session_id,
        ':product_id' => $product_id
    ]);
}

function updateCartQty($user_id,  $session_id,  $product_id, $qty): bool
{
    $db = getDB();

    $stmt = $db->prepare("UPDATE cart SET quantity = :qty WHERE (user_id = :user_id OR session_id = :session_id) AND product_id = :product_id");
    return $stmt->execute([
        ':qty' => $qty,
        ':user_id' => $user_id,
        ':session_id' => $session_id,
        ':product_id' => $product_id
    ]);
}
function clearCart(): bool
{
    $db = getDB();

    $userId    = $_SESSION['user_id'] ?? null;
    $sessionId = session_id();

    if ($userId) {
        $stmt = $db->prepare("DELETE FROM cart WHERE user_id = :user_id");
        return $stmt->execute([':user_id' => $userId]);
    } else {
        $stmt = $db->prepare("DELETE FROM cart WHERE session_id = :session_id");
        return $stmt->execute([':session_id' => $sessionId]);
    }
}

// ---- Shipping ----

function calculateShipping(float $subtotal): float
{
    $freeAbove = (float)getSetting('free_shipping_above', '999');
    $cost      = (float)getSetting('shipping_cost', '99');
    return $subtotal >= $freeAbove ? 0.0 : $cost;
}

// ---- Orders ----

function generateOrderNumber(): string
{
    return 'DM' . strtoupper(substr(uniqid(), -6)) . rand(10, 99);
}

function placeOrder(array $data, array $cart): int|false
{
    $db = getDB();
    try {
        $db->beginTransaction();

        $subtotal = $data['subtotal'];
        $shipping = $data['shipping'];
        $total    = $subtotal + $shipping;

        $db->prepare("
            INSERT INTO orders
            (order_number, user_id, guest_name, guest_email, guest_phone,
             shipping_address, shipping_city, shipping_state, shipping_pincode,
             subtotal, shipping_cost, total, payment_method, notes)
            VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)
        ")->execute([
            $data['order_number'],
            $data['user_id'] ?? null,
            $data['name'],
            $data['email'],
            $data['phone'],
            $data['address'],
            $data['city'],
            $data['state'],
            $data['pincode'],
            $subtotal,
            $shipping,
            $total,
            $data['payment_method'] ?? 'upi',
            $data['notes'] ?? null,
        ]);

        $orderId = (int)$db->lastInsertId();

        foreach ($cart as $item) {
            $db->prepare("
                INSERT INTO order_items (order_id, product_id, product_name, product_image, size, price, quantity, total)
                VALUES (?,?,?,?,?,?,?,?)
            ")->execute([
                $orderId,
                $item['product_id'],
                $item['name'],
                $item['image'],
                $item['size'],
                $item['price'],
                $item['quantity'],
                $item['price'] * $item['quantity'],
            ]);
        }

        // Initial tracking entry
        $db->prepare("INSERT INTO order_tracking (order_id, status, message) VALUES (?,?,?)")
            ->execute([$orderId, 'Order Placed', 'Your order has been placed successfully.']);

        $db->commit();
        return $orderId;
    } catch (Exception $e) {
        $db->rollBack();
        error_log("Order placement failed: " . $e->getMessage());
        return false;
    }
}

function getOrderByNumber(string $orderNumber): ?array
{
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM orders WHERE order_number = ?");
    $stmt->execute([$orderNumber]);
    return $stmt->fetch() ?: null;
}

function getOrderItems(int $orderId): array
{
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM order_items WHERE order_id = ?");
    $stmt->execute([$orderId]);
    return $stmt->fetchAll();
}

function getOrderTracking(int $orderId): array
{
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM order_tracking WHERE order_id = ? ORDER BY created_at ASC");
    $stmt->execute([$orderId]);
    return $stmt->fetchAll();
}

function getUserOrders(int $userId): array
{
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
    $stmt->execute([$userId]);
    return $stmt->fetchAll();
}

// ---- Banners ----

function getBanners(): array
{
    $db = getDB();
    return $db->query("SELECT * FROM banners WHERE is_active = 1 ORDER BY sort_order ASC")->fetchAll();
}

// ---- Pagination ----

function paginate(int $total, int $page, int $perPage, string $urlPattern): string
{
    $totalPages = (int)ceil($total / $perPage);
    if ($totalPages <= 1) return '';

    $html = '<div class="pagination">';
    for ($i = 1; $i <= $totalPages; $i++) {
        $url    = sprintf($urlPattern, $i);
        $active = ($i === $page) ? ' active' : '';
        $html  .= "<a href=\"$url\" class=\"page-link$active\">$i</a>";
    }
    $html .= '</div>';
    return $html;
}

// ---- Flash Messages ----

function setFlash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function getFlash(): ?array
{
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

// ---- Product Image ----

function productImage(string $image = '', string $size = 'medium'): string
{
    if ($image && file_exists(UPLOAD_PATH . 'products/' . $image)) {
        return UPLOAD_URL . 'products/' . $image;
    }
    return BASE_URL . 'assets/images/no-image.svg';
}

function bannerImage(string $image = ''): string
{
    if ($image && file_exists(UPLOAD_PATH . 'banners/' . $image)) {
        return UPLOAD_URL . 'banners/' . $image;
    }
    return BASE_URL . 'assets/images/banner-placeholder.jpg';
}

// ---- Redirect ----

function redirect(string $url): void
{
    header("Location: $url");
    exit;
}

// ---- Logging ----

function logError(string $message): void
{
    $line = '[' . date('Y-m-d H:i:s') . '] ' . $message . PHP_EOL;
    file_put_contents(ROOT_PATH . 'storage/logs.txt', $line, FILE_APPEND);
}
