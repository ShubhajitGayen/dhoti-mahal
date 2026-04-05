<?php
// ============================================================
// Dhoti Mahal - Order API (AJAX)
// ============================================================
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

header('Content-Type: application/json');

$action = trim($_POST['action'] ?? '');

switch ($action) {

    case 'status':
        $orderNumber = trim($_POST['order_number'] ?? '');
        if (!$orderNumber) { echo json_encode(['success' => false]); exit; }
        $order = getOrderByNumber($orderNumber);
        if (!$order) { echo json_encode(['success' => false, 'message' => 'Order not found']); exit; }
        echo json_encode([
            'success'      => true,
            'order_number' => $order['order_number'],
            'order_status' => $order['order_status'],
            'payment_status' => $order['payment_status'],
            'total'        => formatPrice((float)$order['total']),
        ]);
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Unknown action.']);
}
