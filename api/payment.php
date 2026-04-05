<?php
// ============================================================
// Dhoti Mahal - Payment Verify API
// ============================================================
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

header('Content-Type: application/json');

$action = trim($_POST['action'] ?? '');

switch ($action) {
    case 'verify':
        $orderNumber = trim($_POST['order_number'] ?? '');
        $paymentRef  = trim($_POST['payment_ref'] ?? '');

        if (!$orderNumber || !$paymentRef) {
            echo json_encode(['success' => false, 'message' => 'Order number and payment reference required.']);
            exit;
        }

        $order = getOrderByNumber($orderNumber);
        if (!$order) {
            echo json_encode(['success' => false, 'message' => 'Order not found.']);
            exit;
        }

        if ($order['payment_status'] === 'paid') {
            echo json_encode(['success' => false, 'message' => 'This order is already marked as paid.']);
            exit;
        }

        $db = getDB();
        $db->prepare("UPDATE orders SET payment_status='paid', payment_ref=?, order_status='confirmed', updated_at=NOW() WHERE order_number=?")
           ->execute([$paymentRef, $orderNumber]);
        $db->prepare("INSERT INTO order_tracking (order_id, status, message) VALUES (?,?,?)")
           ->execute([$order['id'], 'Payment Confirmed', 'UPI payment verified. Ref: ' . $paymentRef]);

        echo json_encode(['success' => true, 'message' => 'Payment confirmed successfully.', 'order_number' => $orderNumber]);
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Unknown action.']);
}
