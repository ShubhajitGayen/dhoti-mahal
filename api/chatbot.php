<?php
require_once __DIR__ . '/../includes/chatbot-helpers.php';

header('Content-Type: application/json; charset=utf-8');

function chatbotResponse(bool $success, string $message, array $data = []): void
{
    echo json_encode(['success' => $success, 'message' => $message, 'data' => $data], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    chatbotResponse(false, 'Method not allowed.');
}

$contentType = strtolower($_SERVER['CONTENT_TYPE'] ?? '');
if (!str_contains($contentType, 'application/json')) {
    http_response_code(415);
    chatbotResponse(false, 'Invalid request format.');
}

if ((int)($_SERVER['CONTENT_LENGTH'] ?? 0) > 65536) {
    http_response_code(413);
    chatbotResponse(false, 'Your message is too large.');
}

$rawBody = file_get_contents('php://input');
$body = json_decode($rawBody ?: '', true);
if (!is_array($body)) {
    http_response_code(400);
    chatbotResponse(false, 'Invalid request.');
}

if (!validateCSRF((string)($body['csrf_token'] ?? ''))) {
    http_response_code(403);
    chatbotResponse(false, 'Invalid request. Please refresh the page.');
}

$action = (string)($body['action'] ?? '');
$sessionId = session_id();
$ipAddress = substr((string)($_SERVER['REMOTE_ADDR'] ?? ''), 0, 45);
$userId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;

if ($action === 'init') {
    chatbotResponse(true, 'Ready', [
        'enabled' => chatbotIsEnabled(),
        'csrf_token' => generateCSRF(),
        'whatsapp_url' => chatbotWhatsAppUrl(),
    ]);
}

if ($action !== 'chat') {
    http_response_code(400);
    chatbotResponse(false, 'Unknown action.');
}

if (!chatbotIsEnabled()) {
    chatbotResponse(false, 'Chat support is currently unavailable. Please contact us on WhatsApp.', [
        'fallback' => true,
        'whatsapp_url' => chatbotWhatsAppUrl(),
    ]);
}

$userMessage = trim((string)($body['message'] ?? ''));
if ($userMessage === '' || mb_strlen($userMessage) > 500) {
    http_response_code(400);
    chatbotResponse(false, 'Please send a short message, up to 500 characters.');
}

if (chatbotRateLimited($sessionId, $ipAddress)) {
    chatbotResponse(false, 'You have reached the chat limit for now. Please contact us on WhatsApp.', [
        'fallback' => true,
        'whatsapp_url' => chatbotWhatsAppUrl($userMessage),
    ]);
}

chatbotSaveMessage($sessionId, $userId, $ipAddress, 'user', $userMessage);
$history = chatbotHistory($sessionId);
$reply = chatbotCallProvider(chatbotPrompt($userMessage), $history);

if ($reply === null) {
    chatbotResponse(false, 'I\'m unable to reply right now. Please contact us on WhatsApp.', [
        'fallback' => true,
        'whatsapp_url' => chatbotWhatsAppUrl($userMessage),
    ]);
}

chatbotSaveMessage($sessionId, $userId, $ipAddress, 'assistant', $reply);
$cards = chatbotProductCards($reply);
chatbotResponse(true, chatbotCleanTokens($reply), [
    'cards' => $cards,
    'whatsapp_url' => chatbotWhatsAppUrl($userMessage),
]);
