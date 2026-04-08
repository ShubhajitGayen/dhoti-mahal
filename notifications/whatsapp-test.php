<?php
// ============================================================
// Dhoti Mahal - WhatsApp System Test & Demo
// ============================================================
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/whatsapp-helpers.php';

// Check if admin logged in (optional for demo)
$isAdmin = isset($_SESSION['admin_id']);

$systemStatus = [
    'php_version' => phpversion(),
    'tables_created' => false,
    'templates_count' => 0,
    'http_method' => $_SERVER['REQUEST_METHOD'],
    'db_connected' => false
];

try {
    $db = getDB();
    $systemStatus['db_connected'] = true;

    // Check tables
    $tables = $db->query("SHOW TABLES LIKE 'whatsapp_%'")->fetchAll();
    $systemStatus['tables_created'] = count($tables) >= 2;

    // Count templates
    $result = $db->query("SELECT COUNT(*) as cnt FROM whatsapp_templates")->fetch();
    $systemStatus['templates_count'] = $result['cnt'];

    $templates = getWhatsAppTemplates();
} catch (Exception $e) {
    $error = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WhatsApp System - Test & Demo</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 900px;
            margin: 0 auto;
        }

        .card {
            background: white;
            border-radius: 12px;
            padding: 30px;
            margin-bottom: 24px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
        }

        .header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 30px;
            border-bottom: 3px solid #25D366;
            padding-bottom: 20px;
        }

        .header h1 {
            display: flex;
            align-items: center;
            gap: 15px;
            color: #333;
        }

        .header i {
            font-size: 40px;
            color: #25D366;
        }

        .status-badge {
            display: inline-block;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
        }

        .status-badge.success {
            background: #d4edda;
            color: #155724;
        }

        .status-badge.warning {
            background: #fff3cd;
            color: #856404;
        }

        .status-badge.error {
            background: #f8d7da;
            color: #721c24;
        }

        .status-box {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
            margin-bottom: 24px;
        }

        .status-item {
            background: #f8f9fa;
            padding: 16px;
            border-radius: 8px;
            border-left: 4px solid #667eea;
        }

        .status-item.success {
            border-left-color: #28a745;
        }

        .status-item.error {
            border-left-color: #dc3545;
        }

        .status-item strong {
            display: block;
            color: #333;
            margin-bottom: 4px;
        }

        .status-item span {
            font-size: 12px;
            color: #666;
        }

        .templates-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 16px;
            margin-top: 20px;
        }

        .template-card {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 16px;
            cursor: pointer;
            transition: all 0.3s;
        }

        .template-card:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            border-color: #667eea;
        }

        .template-card h4 {
            color: #333;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .template-card p {
            font-size: 12px;
            color: #666;
            line-height: 1.4;
        }

        .template-card code {
            display: block;
            margin-top: 12px;
            background: #fff;
            padding: 8px;
            border-radius: 4px;
            font-size: 11px;
            color: #667eea;
            overflow-x: auto;
        }

        .btn {
            display: inline-block;
            padding: 10px 20px;
            border-radius: 6px;
            border: none;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
        }

        .btn-primary {
            background: #667eea;
            color: white;
        }

        .btn-primary:hover {
            background: #5568d3;
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        }

        .btn-success {
            background: #28a745;
            color: white;
        }

        .btn-success:hover {
            background: #218838;
        }

        .message-preview {
            background: #e7f3ff;
            border: 1px solid #90caf9;
            border-radius: 8px;
            padding: 16px;
            margin-top: 16px;
            font-size: 13px;
            line-height: 1.6;
            white-space: pre-wrap;
            word-wrap: break-word;
        }

        .code-block {
            background: #2d2d2d;
            color: #f8f8f2;
            padding: 16px;
            border-radius: 6px;
            overflow-x: auto;
            font-family: 'Courier New', monospace;
            font-size: 12px;
            line-height: 1.4;
            margin: 12px 0;
        }

        .section-title {
            font-size: 20px;
            color: #333;
            margin: 24px 0 16px 0;
            padding-bottom: 12px;
            border-bottom: 2px solid #667eea;
        }

        .alert {
            padding: 12px 16px;
            border-radius: 6px;
            margin-bottom: 16px;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .alert-info {
            background: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }

        .test-result {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px;
            background: #f8f9fa;
            border-radius: 6px;
            margin-bottom: 12px;
        }

        .test-result i {
            font-size: 18px;
            min-width: 25px;
        }

        .test-result.pass i {
            color: #28a745;
        }

        .test-result.fail i {
            color: #dc3545;
        }
    </style>
</head>

<body>
    <div class="container">
        <!-- Header -->
        <div class="card">
            <div class="header">
                <h1>
                    <i class="fab fa-whatsapp"></i>
                    WhatsApp Automation System
                </h1>
                <span class="status-badge <?= ($systemStatus['db_connected'] && $systemStatus['templates_count'] > 0) ? 'success' : 'error' ?>">
                    <?= ($systemStatus['db_connected'] && $systemStatus['templates_count'] > 0) ? '✅ READY' : '❌ CHECK SETUP' ?>
                </span>
            </div>

            <h2 class="section-title">System Status</h2>

            <div class="status-box">
                <div class="status-item <?= $systemStatus['db_connected'] ? 'success' : 'error' ?>">
                    <strong>Database Connection</strong>
                    <span><?= $systemStatus['db_connected'] ? '✅ Connected' : '❌ Error' ?></span>
                </div>
                <div class="status-item <?= $systemStatus['tables_created'] ? 'success' : 'error' ?>">
                    <strong>Database Tables</strong>
                    <span><?= $systemStatus['tables_created'] ? '✅ Created' : '❌ Not Found' ?></span>
                </div>
                <div class="status-item <?= $systemStatus['templates_count'] > 0 ? 'success' : 'error' ?>">
                    <strong>Templates Installed</strong>
                    <span><?= $systemStatus['templates_count'] ?> template<?= $systemStatus['templates_count'] != 1 ? 's' : '' ?> ✅</span>
                </div>
                <div class="status-item">
                    <strong>PHP Version</strong>
                    <span><?= $systemStatus['php_version'] ?></span>
                </div>
            </div>

            <?php if ($systemStatus['db_connected']): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <strong>✅ Ready to Use!</strong> All systems operational. You can start sending WhatsApp messages.
                </div>
            <?php else: ?>
                <div class="alert alert-error">
                    <i class="fas fa-times-circle"></i>
                    <strong>❌ Database Error</strong> Please check your database connection. <?= isset($error) ? $error : '' ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Templates -->
        <?php if ($systemStatus['db_connected'] && !empty($templates)): ?>
            <div class="card">
                <h2 class="section-title">
                    <i class="fas fa-envelope"></i> Installed Message Templates
                </h2>

                <p style="color: #666; margin-bottom: 20px;">
                    These <?= count($templates) ?> templates are ready to use. Click any template to see preview:
                </p>

                <div class="templates-grid">
                    <?php foreach ($templates as $template): ?>
                        <div class="template-card">
                            <h4>
                                <?php
                                $icons = [
                                    'order_confirmation' => '✅',
                                    'order_processing' => '⚙️',
                                    'order_shipped' => '📦',
                                    'order_delivered' => '✓',
                                    'order_delayed' => '⏱️',
                                    'payment_reminder' => '💵',
                                    'custom_message' => '✍️'
                                ];
                                echo ($icons[$template['key_name']] ?? '💬');
                                ?>
                                <?= htmlspecialchars($template['title']) ?>
                            </h4>
                            <p><?= htmlspecialchars($template['description']) ?></p>
                            <code><?= $template['key_name'] ?></code>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Quick Start -->
        <div class="card">
            <h2 class="section-title">🚀 How to Get Started</h2>

            <div class="test-result pass">
                <i class="fas fa-arrow-right"></i>
                <div>
                    <strong>Step 1:</strong> Go to Admin → Orders
                </div>
            </div>

            <div class="test-result pass">
                <i class="fas fa-arrow-right"></i>
                <div>
                    <strong>Step 2:</strong> Click on any order to view details
                </div>
            </div>

            <div class="test-result pass">
                <i class="fas fa-arrow-right"></i>
                <div>
                    <strong>Step 3:</strong> Look for "WhatsApp Messages" panel on the right side
                </div>
            </div>

            <div class="test-result pass">
                <i class="fas fa-arrow-right"></i>
                <div>
                    <strong>Step 4:</strong> Click a template button (e.g., "Order Confirmed")
                </div>
            </div>

            <div class="test-result pass">
                <i class="fas fa-arrow-right"></i>
                <div>
                    <strong>Step 5:</strong> Review the message preview and click OK
                </div>
            </div>

            <div class="test-result pass">
                <i class="fas fa-arrow-right"></i>
                <div>
                    <strong>Step 6:</strong> WhatsApp will open and your message will be ready to send
                </div>
            </div>

            <div style="margin-top: 20px; padding: 16px; background: #e7f3ff; border-radius: 6px;">
                <p style="color: #0c5460;">
                    <i class="fas fa-lightbulb"></i>
                    <strong>Tip:</strong> Make sure to have at least one order before testing.
                    The customer phone number must be filled in the order details.
                </p>
            </div>
        </div>

        <!-- Documentation Links -->
        <div class="card">
            <h2 class="section-title">📚 Documentation & Files</h2>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 12px;">
                <a href="/WHATSAPP_SETUP.md" target="_blank" class="btn btn-primary">
                    <i class="fas fa-book"></i> Full Documentation
                </a>
                <a href="/INSTALLATION_COMPLETE.md" target="_blank" class="btn btn-primary">
                    <i class="fas fa-check"></i> Installation Summary
                </a>
                <a href="/admin/whatsapp-quick-ref.html" target="_blank" class="btn btn-primary">
                    <i class="fas fa-cheat-sheet"></i> Quick Reference
                </a>
                <a href="/admin/orders.php" class="btn btn-success">
                    <i class="fas fa-arrow-right"></i> Go to Orders
                </a>
            </div>
        </div>

        <!-- Files Info -->
        <div class="card">
            <h2 class="section-title">📁 System Files</h2>

            <h3 style="margin: 16px 0 12px 0; color: #333;">Created Files:</h3>
            <div class="code-block">config/migrations.sql
                includes/whatsapp-helpers.php
                api/whatsapp.php
                assets/js/whatsapp.js
                WHATSAPP_SETUP.md
                INSTALLATION_COMPLETE.md
                admin/whatsapp-quick-ref.html
                verify-whatsapp.php (this file)
            </div>

            <h3 style="margin: 16px 0 12px 0; color: #333;">Modified Files:</h3>
            <div class="code-block">admin/order-detail.php</div>

            <h3 style="margin: 16px 0 12px 0; color: #333;">Database Tables:</h3>
            <div class="code-block">whatsapp_messages (message log)
                whatsapp_templates (template library)</div>
        </div>

        <!-- Footer -->
        <div style="text-align: center; padding: 30px 0; color: white;">
            <p style="margin-bottom: 10px;">✨ WhatsApp Automation System v1.0</p>
            <p style="font-size: 12px; color: rgba(255,255,255,0.7);">
                Automatically send customer notifications. No API costs. All messages tracked in database.
            </p>
        </div>
    </div>

    <script src="<?= BASE_URL ?>assets/js/whatsapp.js"></script>
</body>

</html>