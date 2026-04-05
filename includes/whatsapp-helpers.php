<?php
// ============================================================
// Dhoti Mahal - WhatsApp Helper Functions
// ============================================================
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/config.php';

/**
 * Get all WhatsApp message templates
 */
function getWhatsAppTemplates(bool $activeOnly = true): array
{
    $db = getDB();
    $sql = "SELECT * FROM whatsapp_templates";
    if ($activeOnly) $sql .= " WHERE is_active = 1";
    $sql .= " ORDER BY title ASC";
    return $db->query($sql)->fetchAll();
}

/**
 * Get a specific template by key
 */
function getWhatsAppTemplate(string $key): ?array
{
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM whatsapp_templates WHERE key_name = ?");
    $stmt->execute([$key]);
    return $stmt->fetch() ?: null;
}

/**
 * Replace template variables with actual values
 * @param string $template - Template text with {variables}
 * @param array $variables - Array of variable_name => value
 * @return string - Processed message
 */
function processWhatsAppTemplate(string $template, array $variables): string
{
    $message = $template;
    foreach ($variables as $key => $value) {
        $message = str_replace('{' . $key . '}', $value, $message);
    }
    return $message;
}

/**
 * Get order details for WhatsApp messaging
 */
function getOrderDetailsForMessage(int $orderId): array
{
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM orders WHERE id = ?");
    $stmt->execute([$orderId]);
    $order = $stmt->fetch();

    if (!$order) {
        return [];
    }

    return [
        'customer_name' => $order['guest_name'] ?? 'Customer',
        'order_number' => $order['order_number'] ?? '',
        'order_total' => formatPrice((float)($order['total'] ?? 0)),
        'order_status' => ucfirst($order['order_status'] ?? ''),
        'phone' => $order['guest_phone'] ?? '',
        'tracking_link' => BASE_URL . 'track-order.php?order=' . urlencode($order['order_number']),
    ];
}

/**
 * Create a WhatsApp message record (draft)
 */
function createWhatsAppMessage(int $orderId, string $message, string $templateKey = 'custom_message'): ?int
{
    $db = getDB();

    // Get phone number
    $orderData = getOrderDetailsForMessage($orderId);
    $phone = $orderData['phone'] ?? null;

    if (!$phone) {
        error_log("WhatsApp: No phone number for order ID $orderId");
        return null;
    }

    try {
        $stmt = $db->prepare(
            "INSERT INTO whatsapp_messages (order_id, phone, message, template_key, status) 
             VALUES (?, ?, ?, ?, 'draft')"
        );
        $stmt->execute([$orderId, $phone, $message, $templateKey]);
        return $db->lastInsertId();
    } catch (Exception $e) {
        error_log("WhatsApp Error: " . $e->getMessage());
        return null;
    }
}

/**
 * Update message status
 */
function updateWhatsAppMessageStatus(int $messageId, string $status): bool
{
    $db = getDB();
    $stmt = $db->prepare(
        "UPDATE whatsapp_messages 
         SET status = ?, sent_at = NOW(), updated_at = NOW() 
         WHERE id = ?"
    );
    return $stmt->execute([$status, $messageId]);
}

/**
 * Get WhatsApp message history for an order
 */
function getWhatsAppMessageHistory(int $orderId): array
{
    $db = getDB();
    $stmt = $db->prepare(
        "SELECT * FROM whatsapp_messages 
         WHERE order_id = ? 
         ORDER BY created_at DESC"
    );
    $stmt->execute([$orderId]);
    return $stmt->fetchAll();
}

/**
 * Send WhatsApp message via API
 * For now, this generates the WhatsApp link. Later can integrate with Twilio/WhatsApp Business API
 */
function sendWhatsAppMessage(int $messageId): array
{
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM whatsapp_messages WHERE id = ?");
    $stmt->execute([$messageId]);
    $msg = $stmt->fetch();

    if (!$msg) {
        return ['success' => false, 'error' => 'Message not found'];
    }

    // Remove any special formatting that WhatsApp web doesn't like well
    $cleanedMessage = preg_replace('/\s+/', ' ', $msg['message']);

    // Generate WhatsApp link
    $phone = formatPhoneForWhatsApp($msg['phone']);

    if (!$phone || !preg_match('/^[0-9]{10,15}$/', $phone)) {
        return ['success' => false, 'error' => 'Invalid customer phone number'];
    }

    $whatsappLink = "https://wa.me/$phone?text=" . urlencode($cleanedMessage);

    // Update status to sent
    updateWhatsAppMessageStatus($messageId, 'sent');

    return [
        'success' => true,
        'link' => $whatsappLink,
        'message' => $msg['message'],
        'phone' => $msg['phone']
    ];
}

/**
 * Generate a formatted message from template with order data
 */
function generateWhatsAppMessage(int $orderId, string $templateKey, array $customVars = []): ?string
{
    $template = getWhatsAppTemplate($templateKey);
    if (!$template) {
        return null;
    }

    $orderData = getOrderDetailsForMessage($orderId);
    $variables = array_merge($orderData, $customVars);

    return processWhatsAppTemplate($template['template'], $variables);
}

/**
 * Quick send - Create and send message directly
 */
function quickSendWhatsApp(int $orderId, string $templateKey, array $customVars = []): array
{
    $message = generateWhatsAppMessage($orderId, $templateKey, $customVars);

    if (!$message) {
        return ['success' => false, 'error' => 'Template not found'];
    }

    $messageId = createWhatsAppMessage($orderId, $message, $templateKey);
    if (!$messageId) {
        return ['success' => false, 'error' => 'Failed to create message'];
    }

    return sendWhatsAppMessage($messageId);
}

/**
 * Get WhatsApp business number (from settings)
 */
function getWhatsAppBusinessNumber(): string
{
    return getSetting('whatsapp_number', '919876543210');
}

/**
 * Format phone for WhatsApp (international format)
 */
function formatPhoneForWhatsApp(string $phone): string
{
    $phone = preg_replace('/[^0-9]/', '', $phone);

    // If 10 digits (India), add country code
    if (strlen($phone) == 10) {
        $phone = '91' . $phone;
    }

    return $phone;
}

// Format price function (if not in functions.php)
if (!function_exists('formatPrice')) {
    function formatPrice(float $price): string
    {
        return getSetting('currency_symbol', '₹') . number_format($price, 2);
    }
}
