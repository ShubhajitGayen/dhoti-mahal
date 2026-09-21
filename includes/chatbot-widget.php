<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<link rel="stylesheet" href="<?= BASE_URL ?>assets/css/chatbot.css">
<div class="chatbot" id="chatbot" data-csrf-token="<?= sanitize(generateCSRF()) ?>">
    <button class="chatbot-bubble" id="chatbotBubble" type="button" aria-label="Open shopping assistant" aria-expanded="false">
        <i class="fas fa-comments"></i>
    </button>
    <section class="chatbot-window" id="chatbotWindow" hidden aria-label="Dhoti Mahal shopping assistant">
        <header class="chatbot-header">
            <div>
                <strong>Dhoti Mahal Assistant</strong>
                <span>Product and store help</span>
            </div>
            <button class="chatbot-close" id="chatbotClose" type="button" aria-label="Close chat">&times;</button>
        </header>
        <div class="chatbot-messages" id="chatbotMessages" aria-live="polite">
            <div class="chatbot-message chatbot-message-bot">Namaste! I can help you find the right dhoti or answer store questions.</div>
            <div class="chatbot-chips" id="chatbotChips">
                <button type="button">Find a dhoti</button>
                <button type="button">Help me choose a size</button>
                <button type="button">Wedding wear</button>
                <button type="button">Under ₹1000</button>
                <button type="button">Shipping &amp; returns</button>
                <button type="button">Talk to human</button>
            </div>
        </div>
        <form class="chatbot-form" id="chatbotForm">
            <input id="chatbotInput" type="text" maxlength="500" placeholder="Ask about our products..." autocomplete="off">
            <button type="submit" aria-label="Send message"><i class="fas fa-paper-plane"></i></button>
        </form>
    </section>
</div>
<script src="<?= BASE_URL ?>assets/js/chatbot.js"></script>