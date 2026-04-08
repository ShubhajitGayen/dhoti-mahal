<?php
require_once __DIR__ . '/../config/database.php';

try {
    $db = getDB();

    // Check for WhatsApp tables
    $result = $db->query("SHOW TABLES LIKE 'whatsapp%'")->fetchAll();

    echo "=== WhatsApp Database Tables Check ===\n\n";

    if (count($result) > 0) {
        echo "✅ SUCCESS! WhatsApp tables found:\n";
        foreach ($result as $row) {
            echo "   ✓ " . $row[0] . "\n";
        }

        // Count templates
        $count = $db->query("SELECT COUNT(*) as cnt FROM whatsapp_templates")->fetch()['cnt'];
        echo "\n✓ Templates in database: $count\n";

        // List templates
        echo "\nInstalled Templates:\n";
        $templates = $db->query("SELECT key_name, title FROM whatsapp_templates ORDER BY title")->fetchAll();
        foreach ($templates as $t) {
            echo "  • {$t['key_name']}: {$t['title']}\n";
        }
    } else {
        echo "❌ TABLES NOT FOUND!\n";
        echo "WhatsApp tables were not created in the database.\n";
        echo "The migration may not have run successfully.\n";
    }
} catch (Exception $e) {
    echo "❌ Database Error: " . $e->getMessage();
}