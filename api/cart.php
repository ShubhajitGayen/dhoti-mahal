<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

header('Content-Type: application/json');

function response($success, $message = '', $data = [])
{
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data'    => $data
    ]);
    exit;
}

// CSRF check (optional but recommended)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
        response(false, 'Invalid request');
    }
}

$action = trim($_POST['action'] ?? $_GET['action'] ?? '');

switch ($action) {

    case 'add':
        $productId = (int)($_POST['product_id'] ?? 0);
        $quantity  = max(1, (int)($_POST['quantity'] ?? 1));
        $size      = trim($_POST['size'] ?? '');

        if (!$productId) {
            response(false, 'Invalid product.');
        }

        $ok = addToCart($productId, $quantity, $size);

        response(
            $ok,
            $ok ? 'Added to cart!' : 'Out of stock',
            ['cart_count' => getCartCount()]
        );
        break;

    case 'remove':
        $key = $_POST['key'] ?? '';

        if (!$key) response(false, 'Invalid key');

        removeFromCart($user_id, $product_id);

        $subtotal = getCartTotal();
        $shipping = calculateShipping($subtotal);

        response(true, 'Item removed', [
            'cart_count' => getCartCount(),
            'subtotal'   => formatPrice($subtotal),
            'total'      => formatPrice($subtotal + $shipping),
        ]);
        break;

    case 'update':
        $key = $_POST['key'] ?? '';
        $qty = max(1, (int)($_POST['qty'] ?? 1));

        if (!$key) response(false, 'Invalid key');

        $ok = updateCartQty($user_id, $product_id, $qty);

        if (!$ok) response(false, 'Update failed');

        $subtotal = getCartTotal();
        $shipping = calculateShipping($subtotal);

        response(true, 'Cart updated', [
            'cart_count' => getCartCount(),
            'subtotal'   => formatPrice($subtotal),
            'shipping'   => formatPrice($shipping),
            'total'      => formatPrice($subtotal + $shipping),
        ]);
        break;

    case 'count':
        response(true, '', ['cart_count' => getCartCount()]);
        break;

    case 'clear':
        clearCart();
        response(true, 'Cart cleared', ['cart_count' => 0]);
        break;

    default:
        response(false, 'Unknown action');
}
