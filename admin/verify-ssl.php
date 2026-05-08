<?php
// ============================================================
// SSL Certificate Verification Helper
// ============================================================
// This file helps diagnose and fix SSL certificate issues
// Access: http://localhost/dhoti-mahal/admin/verify-ssl.php

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

// Only allow admin access
if (!isset($_SESSION['admin_logged_in'])) {
    die('Access denied. Please login as admin.');
}

?>
<!DOCTYPE html>
<html>

<head>
    <title>SSL Certificate Verification</title>
    <style>
        body {
            font-family: Arial;
            margin: 20px;
        }

        .section {
            margin: 20px 0;
            padding: 15px;
            background: #f5f5f5;
            border-radius: 5px;
        }

        .success {
            color: green;
            font-weight: bold;
        }

        .error {
            color: red;
            font-weight: bold;
        }

        .warning {
            color: orange;
            font-weight: bold;
        }

        code {
            background: #eee;
            padding: 2px 5px;
            border-radius: 3px;
        }

        button {
            padding: 10px 20px;
            background: #0066cc;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        button:hover {
            background: #0052a3;
        }
    </style>
</head>

<body>
    <h1>🔒 SSL Certificate & cURL Configuration Check</h1>

    <div class="section">
        <h2>1. PHP & cURL Information</h2>
        <p><strong>PHP Version:</strong> <?= phpversion() ?></p>
        <p><strong>cURL Extension:</strong>
            <?= extension_loaded('curl') ? '<span class="success">✓ Loaded</span>' : '<span class="error">✗ Not loaded</span>' ?>
        </p>
        <p><strong>OpenSSL Version:</strong> <?= phpversion('openssl') ?></p>
        <p><strong>curl.cainfo Setting:</strong> <code><?= ini_get('curl.cainfo') ?></code></p>
    </div>

    <div class="section">
        <h2>2. CA Bundle Detection</h2>
        <?php
        $caBundlePath = ini_get('curl.cainfo');
        $foundBundles = [];

        $commonPaths = [
            __DIR__ . '/../cacert.pem',
            'C:/xampp/php/extras/ssl/cacert.pem',
            'C:/Program Files/xampp/php/extras/ssl/cacert.pem',
            '/etc/ssl/certs/ca-certificates.crt',
            '/etc/pki/tls/certs/ca-bundle.crt',
        ];

        foreach ($commonPaths as $path) {
            if ($path && file_exists($path)) {
                $foundBundles[] = $path;
                echo '<p><span class="success">✓</span> Found: <code>' . htmlspecialchars($path) . '</code></p>';
            }
        }

        if (empty($foundBundles)) {
            echo '<p><span class="error">✗ No CA bundle found!</span></p>';
        }
        ?>
    </div>

    <div class="section">
        <h2>3. Razorpay API Connectivity Test</h2>
        <?php
        $rzpKeyId = getSetting('razorpay_key_id');
        $rzpKeySecret = getSetting('razorpay_key_secret');

        if (!$rzpKeyId || !$rzpKeySecret) {
            echo '<p><span class="error">✗ Razorpay credentials not configured!</span></p>';
        } else {
            echo '<p><span class="success">✓</span> Razorpay credentials found (masked)</p>';

            // Test API connection
            $ch = curl_init('https://api.razorpay.com/v1/orders');
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => json_encode(['amount' => 100, 'currency' => 'INR', 'receipt' => 'test']),
                CURLOPT_USERPWD => "$rzpKeyId:$rzpKeySecret",
                CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
                CURLOPT_TIMEOUT => 10,
                CURLOPT_SSL_VERIFYPEER => true,
                CURLOPT_SSL_VERIFYHOST => 2,
            ]);

            if (!empty($foundBundles[0])) {
                curl_setopt($ch, CURLOPT_CAINFO, $foundBundles[0]);
            }

            $response = curl_exec($ch);
            $error = curl_error($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($error) {
                echo '<p><span class="error">✗ Connection Error:</span> ' . htmlspecialchars($error) . '</p>';
            } elseif ($httpCode >= 200 && $httpCode < 300) {
                echo '<p><span class="success">✓ Connected successfully!</span></p>';
            } else {
                echo '<p><span class="warning">⚠ HTTP ' . $httpCode . '</span></p>';
            }
        }
        ?>
    </div>

    <div class="section">
        <h2>4. Quick Fix - Download CA Certificate</h2>
        <?php
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['download_cacert'])) {
            // Download from Mozilla's CA bundle
            echo '<p>Downloading CA certificate bundle from Mozilla...</p>';
            $url = 'https://curl.haxx.se/ca/cacert.pem';
            $content = @file_get_contents($url);

            if ($content) {
                $savePath = __DIR__ . '/../cacert.pem';
                if (file_put_contents($savePath, $content)) {
                    echo '<p><span class="success">✓ Successfully downloaded and saved to project root!</span></p>';
                    echo '<p>Path: <code>' . htmlspecialchars($savePath) . '</code></p>';
                } else {
                    echo '<p><span class="error">✗ Failed to save certificate file. Check write permissions.</span></p>';
                }
            } else {
                echo '<p><span class="error">✗ Failed to download. Check internet connection.</span></p>';
            }
        }
        ?>
        <form method="POST">
            <button type="submit" name="download_cacert">Download CA Certificate Bundle</button>
        </form>
        <p><small>This downloads Mozilla's trusted CA certificates and stores them in the project root.</small></p>
    </div>

    <div class="section">
        <h2>5. Manual Configuration</h2>
        <p>If automatic download fails, manually:</p>
        <ol>
            <li>Download from: <a href="https://curl.haxx.se/ca/cacert.pem"
                    target="_blank">https://curl.haxx.se/ca/cacert.pem</a></li>
            <li>Save as <code>cacert.pem</code> in your project root</li>
            <li>Or configure in php.ini: <code>curl.cainfo = "C:/xampp/php/extras/ssl/cacert.pem"</code></li>
        </ol>
    </div>

</body>

</html>