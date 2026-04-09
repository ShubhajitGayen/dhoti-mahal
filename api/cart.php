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
        $session_id = session_id() ?? null;
        $userId = $_SESSION['user_id'] ?? null;
        if (!$productId) {
            response(false, 'Invalid product.');
        }
        $present = isProductInCart($userId, $session_id, $productId);

        if ($present) {
            $ok = updateCartQty($userId, $session_id, $productId, $quantity);
        } else {
            $ok = addToCart($productId, $quantity, $size);
        }


        response(
            $ok,
            $ok ? 'Added to cart!' : 'Out of stock',
            ['cart_count' => getCartCount()]
        );
        break;


    default:
        response(false, 'Unknown action');
}
