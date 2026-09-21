-- ============================================================
-- Dhoti Mahal - Database Migrations
-- ============================================================

-- Add Customer AI Chatbot Messages Table
CREATE TABLE IF NOT EXISTS chat_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    session_id VARCHAR(64) NOT NULL,
    user_id INT NULL,
    ip_address VARCHAR(45) NULL,
    role ENUM('user', 'assistant') NOT NULL,
    message TEXT NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_session (session_id, created_at),
    INDEX idx_ip (ip_address, created_at)
);

INSERT INTO settings (setting_key, setting_value) VALUES
('chatbot_enabled', '1'),
('chatbot_business_info', 'Shop name: [SHOP NAME]\nOpening / support hours: NOT SET\nShipping charges and free-shipping threshold: NOT SET\nDelivery areas and delivery time: NOT SET\nPayment methods: NOT SET\nReturn / exchange / cancellation policy: NOT SET\nContact phone / WhatsApp / email: NOT SET\nSize guide notes: NOT SET\nTone: friendly and short')
ON DUPLICATE KEY UPDATE setting_key = VALUES(setting_key);

-- Add WhatsApp Messages Table (for tracking sent messages)
CREATE TABLE IF NOT EXISTS whatsapp_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    phone VARCHAR(15) NOT NULL,
    message TEXT NOT NULL,
    template_key VARCHAR(100),
    status ENUM('draft', 'sent', 'failed') DEFAULT 'draft',
    sent_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    INDEX (order_id),
    INDEX (status)
);

-- Add WhatsApp Settings Table (for storing WhatsApp templates and config)
CREATE TABLE IF NOT EXISTS whatsapp_templates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    key_name VARCHAR(100) UNIQUE NOT NULL,
    title VARCHAR(200) NOT NULL,
    template TEXT NOT NULL,
    description VARCHAR(255),
    variables TEXT COMMENT 'JSON array of variable names like [customer_name, order_number, order_status]',
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX (is_active)
);

-- Insert Default WhatsApp Templates
INSERT INTO whatsapp_templates (key_name, title, template, description, variables, is_active) VALUES
('order_confirmation', 'Order Confirmation', 'Hi {customer_name},\n\nThank you for your order! 🎉\n\nOrder #: {order_number}\nTotal: {order_total}\n\nWe are processing your order and will update you soon.\n\nTrack your order: {tracking_link}\n\nThank you for choosing Dhoti Mahal!\n\nFor any queries, reply to this message.', 'Sent when order is confirmed', '["customer_name", "order_number", "order_total", "tracking_link"]', 1),
('order_processing', 'Order Processing', 'Hi {customer_name},\n\nYour order #{order_number} is now being processed.\n\nWe will ship it within 2-3 business days.\n\nTrack: {tracking_link}', 'Sent when order status changes to processing', '["customer_name", "order_number", "tracking_link"]', 1),
('order_shipped', 'Order Shipped', 'Great news {customer_name}! 📦\n\nYour order #{order_number} has been shipped!\n\nCarrier: {carrier_name}\nTracking ID: {tracking_id}\n\nExpected Delivery: {delivery_date}\n\nTrack your package: {tracking_link}', 'Sent when order is shipped', '["customer_name", "order_number", "carrier_name", "tracking_id", "delivery_date", "tracking_link"]', 1),
('order_delivered', 'Order Delivered', 'Wonderful! 🎉\n\nYour order #{order_number} has been delivered!\n\nThank you for shopping with Dhoti Mahal. We hope you love your purchase!\n\nFeel free to reach out for any feedback or issues.', 'Sent when order is delivered', '["order_number"]', 1),
('order_delayed', 'Order Delayed', 'Hi {customer_name},\n\nWe apologize! Your order #{order_number} is experiencing a slight delay.\n\nNew Delivery Date: {new_delivery_date}\n\nWe appreciate your patience and will ensure fastest delivery.\n\nTrack: {tracking_link}', 'Sent when delivery is delayed', '["customer_name", "order_number", "new_delivery_date", "tracking_link"]', 1),
('payment_reminder', 'Payment Reminder', 'Hi {customer_name},\n\nThis is a reminder about your pending payment for order #{order_number}.\n\nAmount: {order_total}\n\nPlease complete the payment at your earliest convenience.\n\nLink: {payment_link}', 'Sent for pending payments', '["customer_name", "order_number", "order_total", "payment_link"]', 1),
('custom_message', 'Custom Message', '{message}', 'Send a custom message to customer', '["message"]', 1);
