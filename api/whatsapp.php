<?php
// ============================================================
// Dhoti Mahal - WhatsApp Message API
// ============================================================
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/whatsapp-helpers.php';

// Only allow Admin
if (!isset($_SESSION['admin_id'])) {
    http_response_code(401);
    die(json_encode(['success' => false, 'error' => 'Unauthorized']));
}

// Prevent caching
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Content-Type: application/json');

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$response = ['success' => false, 'error' => 'Invalid action'];

switch ($action) {

    case 'send_template':
        // Send a predefined template to a customer
        $orderId = (int)($_POST['order_id'] ?? 0);
        $templateKey = $_POST['template_key'] ?? '';

        if (!$orderId || !$templateKey) {
            $response = ['success' => false, 'error' => 'Missing order ID or template'];
            break;
        }

        // Verify order exists
        $db = getDB();
        $stmt = $db->prepare("SELECT id FROM orders WHERE id = ?");
        $stmt->execute([$orderId]);
        if (!$stmt->fetch()) {
            $response = ['success' => false, 'error' => 'Order not found'];
            break;
        }

        // Get the template
        $template = getWhatsAppTemplate($templateKey);
        if (!$template) {
            $response = ['success' => false, 'error' => 'Template not found'];
            break;
        }

        // Generate message
        $message = generateWhatsAppMessage($orderId, $templateKey);
        if (!$message) {
            $response = ['success' => false, 'error' => 'Failed to generate message'];
            break;
        }

        // Create message record
        $messageId = createWhatsAppMessage($orderId, $message, $templateKey);
        if (!$messageId) {
            $response = ['success' => false, 'error' => 'Failed to create message'];
            break;
        }

        // Send message
        $sendResult = sendWhatsAppMessage($messageId);
        $response = $sendResult;
        $response['message_id'] = $messageId;
        break;

    case 'send_custom':
        // Send a custom message
        $orderId = (int)($_POST['order_id'] ?? 0);
        $customMessage = $_POST['message'] ?? '';

        if (!$orderId || !$customMessage) {
            $response = ['success' => false, 'error' => 'Missing order ID or message'];
            break;
        }

        // Create message record
        $messageId = createWhatsAppMessage($orderId, $customMessage, 'custom_message');
        if (!$messageId) {
            $response = ['success' => false, 'error' => 'Failed to create message'];
            break;
        }

        // Send message
        $sendResult = sendWhatsAppMessage($messageId);
        $response = $sendResult;
        $response['message_id'] = $messageId;
        break;

    case 'get_template_preview':
        // Get a preview of what the message will look like
        $orderId = (int)($_POST['order_id'] ?? 0);
        $templateKey = $_POST['template_key'] ?? '';

        if (!$orderId || !$templateKey) {
            $response = ['success' => false, 'error' => 'Missing order ID or template'];
            break;
        }

        $message = generateWhatsAppMessage($orderId, $templateKey);
        if (!$message) {
            $response = ['success' => false, 'error' => 'Failed to generate preview'];
            break;
        }

        $response = [
            'success' => true,
            'preview' => $message,
            'template_key' => $templateKey
        ];
        break;

    case 'get_message_history':
        // Get message history for an order
        $orderId = (int)($_GET['order_id'] ?? 0);

        if (!$orderId) {
            $response = ['success' => false, 'error' => 'Missing order ID'];
            break;
        }

        $messages = getWhatsAppMessageHistory($orderId);
        $response = [
            'success' => true,
            'messages' => $messages
        ];
        break;

    case 'get_templates':
        // Get list of all templates for dropdown
        $templates = getWhatsAppTemplates();
        $response = [
            'success' => true,
            'templates' => $templates
        ];
        break;

    default:
        $response = ['success' => false, 'error' => 'Unknown action: ' . $action];
}

http_response_code($response['success'] ? 200 : 400);
echo json_encode($response);
exit;
