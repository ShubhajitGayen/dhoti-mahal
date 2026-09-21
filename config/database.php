<?php
// ============================================================
// Dhoti Mahal - Database Configuration
// ============================================================

// Use local XAMPP defaults unless environment variables are explicitly provided.
// This avoids connecting to an unreachable remote database in local development.
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_NAME', getenv('DB_NAME') ?: 'dhoti_mahal');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASSWORD') ?: getenv('DB_PASS') ?: '');
define('DB_PORT', getenv('DB_PORT') ?: '3306');
define('DB_CHARSET', 'utf8mb4');

// Establish PDO connection
function getDB(): PDO
{
    static $pdo = null;

    if ($pdo === null) {
        // Validate that credentials are available
        if (!DB_HOST || !DB_NAME || !DB_USER) {
            error_log("Database configuration incomplete: Missing DB_HOST, DB_NAME, or DB_USER");
            // Return error without exposing details
            http_response_code(500);
            die('Database connection failed. Please try again later.');
        }

        $dsn = "mysql:host=" . DB_HOST .
            ";port=" . DB_PORT .
            ";dbname=" . DB_NAME .
            ";charset=" . DB_CHARSET;

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $pdo = new PDO(
                $dsn,
                DB_USER,
                DB_PASS,
                $options
            );
        } catch (PDOException $e) {
            // Log detailed error for debugging (not visible to users)
            error_log("DB Connection Failed: " . $e->getMessage());

            // Return generic error to frontend
            http_response_code(500);
            die('Database connection failed. Please try again later.');
        }
    }

    return $pdo;
}
