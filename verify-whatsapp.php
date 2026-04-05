<?php
// Quick verification script
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/config.php';

try {
    $db = getDB();

    echo "=== WhatsApp System Verification ===\n\n";

    // Check whatsapp_messages table
    $result = $db->query("SHOW TABLES LIKE 'whatsapp_messages'")->fetchAll();
    echo "✓ whatsapp_messages table: " . (count($result) > 0 ? "EXISTS" : "NOT FOUND") . "\n";

    // Check whatsapp_templates table
    $result = $db->query("SHOW TABLES LIKE 'whatsapp_templates'")->fetchAll();
    echo "✓ whatsapp_templates table: " . (count($result) > 0 ? "EXISTS" : "NOT FOUND") . "\n";

    // Count templates
    $count = $db->query("SELECT COUNT(*) as cnt FROM whatsapp_templates")->fetch()['cnt'];
    echo "✓ Templates installed: $count templates\n";

    // List templates
    echo "\n=== Installed Templates ===\n";
    $templates = $db->query("SELECT key_name, title FROM whatsapp_templates")->fetchAll();
    foreach ($templates as $t) {
        echo "  • {$t['key_name']}: {$t['title']}\n";
    }

    echo "\n✅ WhatsApp System is ready to use!\n";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
