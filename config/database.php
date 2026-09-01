
<?php
// ============================================================
// Dhoti Mahal - Database Configuration
// ============================================================

// Get database configuration from environment variables
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_NAME', getenv('DB_NAME') ?: 'dhoti_mahal');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASSWORD') ?: '');
define('DB_PORT', getenv('DB_PORT') ?: '3306');
define('DB_CHARSET', 'utf8mb4');

// Establish PDO connection
function getDB(): PDO
{
    static $pdo = null;

    if ($pdo === null) {

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

            error_log("DB Connection Failed: " . $e->getMessage());

            die(json_encode([
                'error' => 'Database connection failed. Please try again later.'
            ]));
        }
    }

    return $pdo;
}

