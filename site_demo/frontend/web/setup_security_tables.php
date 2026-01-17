<?php
/**
 * Security Tables Setup Script
 * Creates the required tables for rate limiting and security logging
 *
 * Run once after deployment, then delete this file
 * URL: /setup_security_tables.php?key=YOUR_SECRET_KEY
 */

// Security: Require a secret key to run
$secretKey = 'kavork-security-setup-2026'; // Change this!
if (!isset($_GET['key']) || $_GET['key'] !== $secretKey) {
    http_response_code(403);
    die('Access denied. Provide correct key parameter.');
}

error_reporting(E_ALL);
ini_set('display_errors', 1);

$host = getenv('MYSQLHOST') ?: 'localhost';
$port = getenv('MYSQLPORT') ?: '3306';
$db = getenv('MYSQLDATABASE') ?: 'railway';
$user = getenv('MYSQLUSER') ?: 'root';
$pass = getenv('MYSQLPASSWORD') ?: '';

echo "<pre>\n";
echo "=== SECURITY TABLES SETUP ===\n\n";

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 1. Create login_attempts table
    echo "1. Creating login_attempts table...\n";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS login_attempts (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(255) NOT NULL,
            ip_address VARCHAR(45) NOT NULL,
            attempted_at INT NOT NULL,
            success TINYINT(1) DEFAULT 0,
            INDEX idx_login_attempts_username (username),
            INDEX idx_login_attempts_ip (ip_address),
            INDEX idx_login_attempts_time (attempted_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
    echo "   Done.\n\n";

    // 2. Create security_log table
    echo "2. Creating security_log table...\n";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS security_log (
            id INT AUTO_INCREMENT PRIMARY KEY,
            event_type VARCHAR(50) NOT NULL,
            user_id INT NULL,
            username VARCHAR(255) NULL,
            ip_address VARCHAR(45) NOT NULL,
            user_agent VARCHAR(500) NULL,
            details TEXT NULL,
            created_at INT NOT NULL,
            INDEX idx_security_log_event (event_type),
            INDEX idx_security_log_user (user_id),
            INDEX idx_security_log_ip (ip_address),
            INDEX idx_security_log_time (created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
    echo "   Done.\n\n";

    // 3. Create rate_limit table
    echo "3. Creating rate_limit table...\n";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS rate_limit (
            id INT AUTO_INCREMENT PRIMARY KEY,
            identifier VARCHAR(255) NOT NULL,
            endpoint VARCHAR(100) NOT NULL,
            requests INT DEFAULT 0,
            window_start INT NOT NULL,
            INDEX idx_rate_limit_identifier (identifier, endpoint),
            INDEX idx_rate_limit_window (window_start)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
    echo "   Done.\n\n";

    // 4. Add columns to user table (if not exists)
    echo "4. Adding security columns to user table...\n";

    // Check if columns exist
    $stmt = $pdo->query("DESCRIBE user");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if (!in_array('failed_login_attempts', $columns)) {
        $pdo->exec("ALTER TABLE user ADD COLUMN failed_login_attempts INT DEFAULT 0");
        echo "   Added: failed_login_attempts\n";
    } else {
        echo "   Exists: failed_login_attempts\n";
    }

    if (!in_array('locked_until', $columns)) {
        $pdo->exec("ALTER TABLE user ADD COLUMN locked_until INT NULL");
        echo "   Added: locked_until\n";
    } else {
        echo "   Exists: locked_until\n";
    }

    if (!in_array('last_failed_login', $columns)) {
        $pdo->exec("ALTER TABLE user ADD COLUMN last_failed_login INT NULL");
        echo "   Added: last_failed_login\n";
    } else {
        echo "   Exists: last_failed_login\n";
    }

    echo "\n";

    // 5. Verify tables
    echo "5. Verifying tables...\n";
    $tables = ['login_attempts', 'security_log', 'rate_limit'];
    foreach ($tables as $table) {
        $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
        $exists = $stmt->fetch() ? 'OK' : 'MISSING';
        echo "   $table: $exists\n";
    }

    echo "\n=== SETUP COMPLETE ===\n";
    echo "\nIMPORTANT: Delete this file after running!\n";
    echo "rm site_demo/frontend/web/setup_security_tables.php\n";

} catch (PDOException $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}

echo "</pre>";
