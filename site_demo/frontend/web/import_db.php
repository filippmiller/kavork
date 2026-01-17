<?php
// Database import script - REMOVE AFTER USE
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('max_execution_time', 600);
ini_set('memory_limit', '512M');

$host = getenv('MYSQLHOST') ?: 'localhost';
$port = getenv('MYSQLPORT') ?: '3306';
$db = getenv('MYSQLDATABASE') ?: 'railway';
$user = getenv('MYSQLUSER') ?: 'root';
$pass = getenv('MYSQLPASSWORD') ?: '';

// Security check
$secret = isset($_GET['key']) ? $_GET['key'] : '';
if ($secret !== 'kavork2026import') {
    die('Unauthorized');
}

echo "<pre>\n";
echo "=== DATABASE IMPORT ===\n\n";

// Handle file upload first
$sqlFile = '/tmp/import.sql';
$gzFile = '/tmp/import.sql.gz';

if (isset($_FILES['sql']) && $_FILES['sql']['error'] === UPLOAD_ERR_OK) {
    $tmpFile = $_FILES['sql']['tmp_name'];
    $name = $_FILES['sql']['name'];

    echo "Received file: $name (" . $_FILES['sql']['size'] . " bytes)\n";

    if (substr($name, -3) === '.gz') {
        move_uploaded_file($tmpFile, $gzFile);
        echo "Extracting gzip...\n";
        $gz = gzopen($gzFile, 'rb');
        $out = fopen($sqlFile, 'wb');
        while (!gzeof($gz)) {
            fwrite($out, gzread($gz, 4096));
        }
        gzclose($gz);
        fclose($out);
        unlink($gzFile);
        echo "Extracted to $sqlFile (" . filesize($sqlFile) . " bytes)\n\n";
    } else {
        move_uploaded_file($tmpFile, $sqlFile);
        echo "Saved to $sqlFile\n\n";
    }
}

// If no SQL file, show upload form
if (!file_exists($sqlFile)) {
    echo "No SQL file found. Upload one:\n\n";
    echo '</pre>';
    echo '<form method="POST" enctype="multipart/form-data">';
    echo '<input type="hidden" name="MAX_FILE_SIZE" value="100000000">';
    echo '<input type="file" name="sql" accept=".sql,.gz">';
    echo '<button type="submit">Upload & Import</button>';
    echo '</form>';
    echo '<pre>';
    echo "\nOr use curl:\n";
    echo 'curl -F "sql=@file.sql.gz" "' . "https://{$_SERVER['HTTP_HOST']}/import_db.php?key=kavork2026import\"\n";
    exit;
}

try {
    $pdo = new PDO("mysql:host=$host;port=$port", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Drop and recreate database
    echo "1. Dropping existing database...\n";
    $pdo->exec("DROP DATABASE IF EXISTS `$db`");
    echo "   Done.\n\n";

    echo "2. Creating fresh database...\n";
    $pdo->exec("CREATE DATABASE `$db` CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci");
    $pdo->exec("USE `$db`");
    echo "   Done.\n\n";

    if (file_exists($sqlFile)) {
        echo "3. Importing SQL file (" . filesize($sqlFile) . " bytes)...\n";

        $sql = file_get_contents($sqlFile);

        // Remove definer statements that cause permission issues
        $sql = preg_replace('/DEFINER=[^\s]+/', '', $sql);

        // Split by statements
        $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");

        // Execute multi-statement
        $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, 0);

        $statements = preg_split('/;\s*\n/', $sql);
        $count = 0;
        $errors = 0;

        foreach ($statements as $stmt) {
            $stmt = trim($stmt);
            if (empty($stmt) || strpos($stmt, '--') === 0 || strpos($stmt, '/*') === 0) {
                continue;
            }
            try {
                $pdo->exec($stmt);
                $count++;
                if ($count % 100 === 0) {
                    echo "   Executed $count statements...\n";
                    flush();
                }
            } catch (PDOException $e) {
                $errors++;
                if ($errors < 10) {
                    echo "   Error: " . substr($e->getMessage(), 0, 100) . "\n";
                }
            }
        }

        $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");

        echo "   Completed: $count statements, $errors errors\n\n";

        // Cleanup
        unlink($sqlFile);
    }

    // Show tables
    echo "4. Tables in database:\n";
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    foreach ($tables as $table) {
        $countStmt = $pdo->query("SELECT COUNT(*) FROM `$table`");
        $rowCount = $countStmt->fetchColumn();
        echo "   - $table ($rowCount rows)\n";
    }
    echo "\n   Total: " . count($tables) . " tables\n";

} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage() . "\n";
}

echo "\n=== DONE ===\n";
echo "</pre>";
