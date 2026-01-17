<?php
// Script to fix database schema and create user

$host = getenv('MYSQLHOST') ?: 'localhost';
$port = getenv('MYSQLPORT') ?: '3306';
$db = getenv('MYSQLDATABASE') ?: 'railway';
$user = getenv('MYSQLUSER') ?: 'root';
$pass = getenv('MYSQLPASSWORD') ?: '';

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $action = isset($_GET['action']) ? $_GET['action'] : 'info';

    if ($action === 'fix_schema') {
        echo "=== FIXING DATABASE SCHEMA ===\n\n";

        // Check if id column is primary key
        $stmt = $pdo->query("SHOW KEYS FROM user WHERE Key_name = 'PRIMARY'");
        $hasPrimaryKey = $stmt->fetch();
        if (!$hasPrimaryKey) {
            echo "Checking for duplicate IDs...\n";
            $stmt = $pdo->query("SELECT id, COUNT(*) as cnt FROM user GROUP BY id HAVING cnt > 1");
            $dupes = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if ($dupes) {
                echo "Found duplicate IDs: " . json_encode($dupes) . "\n";
                echo "Removing duplicates (keeping first)...\n";
                foreach ($dupes as $dupe) {
                    $pdo->exec("DELETE FROM user WHERE id = " . (int)$dupe['id'] . " LIMIT " . ($dupe['cnt'] - 1));
                }
                echo "Done removing duplicates.\n";
            }

            // Get max ID to set auto_increment
            $stmt = $pdo->query("SELECT MAX(id) as max_id FROM user");
            $maxId = (int)$stmt->fetch(PDO::FETCH_ASSOC)['max_id'];

            echo "Setting 'id' as primary key...\n";
            try {
                $pdo->exec("ALTER TABLE user ADD PRIMARY KEY (id)");
                $pdo->exec("ALTER TABLE user MODIFY id int NOT NULL AUTO_INCREMENT");
                $pdo->exec("ALTER TABLE user AUTO_INCREMENT = " . ($maxId + 1));
                echo "Done.\n\n";
            } catch (PDOException $e) {
                echo "Error: " . $e->getMessage() . "\n\n";
            }
        }

        // Check if 'user' column exists but 'name' doesn't
        $stmt = $pdo->query("SHOW COLUMNS FROM user LIKE 'user'");
        $hasUserColumn = $stmt->fetch();

        $stmt = $pdo->query("SHOW COLUMNS FROM user LIKE 'name'");
        $hasNameColumn = $stmt->fetch();

        if ($hasUserColumn && !$hasNameColumn) {
            echo "Renaming 'user' column to 'name'...\n";
            $pdo->exec("ALTER TABLE user CHANGE COLUMN `user` `name` text");
            echo "Done.\n\n";
        }

        // Check if 'cafe' column exists but 'franchisee_id' doesn't
        $stmt = $pdo->query("SHOW COLUMNS FROM user LIKE 'cafe'");
        $hasCafeColumn = $stmt->fetch();

        $stmt = $pdo->query("SHOW COLUMNS FROM user LIKE 'franchisee_id'");
        $hasFranchiseeColumn = $stmt->fetch();

        if ($hasCafeColumn && !$hasFranchiseeColumn) {
            echo "Adding 'franchisee_id' column...\n";
            $pdo->exec("ALTER TABLE user ADD COLUMN `franchisee_id` int DEFAULT 1");
            echo "Done.\n\n";
        }

        // Add missing columns
        $stmt = $pdo->query("SHOW COLUMNS FROM user LIKE 'phone'");
        if (!$stmt->fetch()) {
            echo "Adding 'phone' column...\n";
            $pdo->exec("ALTER TABLE user ADD COLUMN `phone` varchar(20) DEFAULT ''");
            echo "Done.\n\n";
        }

        $stmt = $pdo->query("SHOW COLUMNS FROM user LIKE 'lg'");
        if (!$stmt->fetch()) {
            echo "Adding 'lg' column...\n";
            $pdo->exec("ALTER TABLE user ADD COLUMN `lg` varchar(10) DEFAULT 'en-EN'");
            echo "Done.\n\n";
        }

        // Fix cafe table schema
        echo "Checking cafe table...\n";
        $stmt = $pdo->query("SHOW COLUMNS FROM cafe LIKE 'franchisee_id'");
        if (!$stmt->fetch()) {
            echo "Adding 'franchisee_id' to cafe table...\n";
            $pdo->exec("ALTER TABLE cafe ADD COLUMN `franchisee_id` int DEFAULT 1");
            echo "Done.\n";
        }

        // Check cafe primary key
        $stmt = $pdo->query("SHOW KEYS FROM cafe WHERE Key_name = 'PRIMARY'");
        if (!$stmt->fetch()) {
            echo "Setting cafe primary key...\n";
            try {
                $pdo->exec("ALTER TABLE cafe ADD PRIMARY KEY (id)");
                echo "Done.\n";
            } catch (PDOException $e) {
                echo "Error: " . $e->getMessage() . "\n";
            }
        }

        echo "\nSchema fix complete!\n\n";
    }

    // Show current table structure
    echo "=== CURRENT TABLE STRUCTURE ===\n";
    $stmt = $pdo->query("DESCRIBE user");
    while ($col = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo sprintf("%-15s %-15s %-5s %s\n", $col['Field'], $col['Type'], $col['Key'], $col['Extra']);
    }
    echo "\n";

    // Show indexes
    echo "=== TABLE INDEXES ===\n";
    $stmt = $pdo->query("SHOW KEYS FROM user");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo sprintf("%-15s %-15s\n", $row['Key_name'], $row['Column_name']);
    }
    echo "\n";

    if ($action === 'create_user' || $action === 'fix_schema') {
        // Determine which column to use
        $stmt = $pdo->query("SHOW COLUMNS FROM user LIKE 'name'");
        $usernameCol = $stmt->fetch() ? 'name' : 'user';

        // Check if user exists
        $stmt = $pdo->prepare("SELECT id FROM user WHERE $usernameCol = ?");
        $stmt->execute(['filipp1']);

        if ($stmt->fetch()) {
            echo "User 'filipp1' exists. Updating password...\n";
            $hash = password_hash('Airbus380+', PASSWORD_BCRYPT, ['cost' => 13]);
            $stmt = $pdo->prepare("UPDATE user SET pass = ?, state = 0 WHERE $usernameCol = ?");
            $stmt->execute([$hash, 'filipp1']);
            echo "Password updated.\n\n";
        } else {
            $stmt = $pdo->query("SELECT COALESCE(MAX(id), 0) + 1 as next_id FROM user");
            $nextId = $stmt->fetch(PDO::FETCH_ASSOC)['next_id'];

            $hash = password_hash('Airbus380+', PASSWORD_BCRYPT, ['cost' => 13]);
            $stmt = $pdo->prepare("INSERT INTO user (id, $usernameCol, pass, email, state, franchisee_id) VALUES (?, ?, ?, ?, 0, 1)");
            $stmt->execute([$nextId, 'filipp1', $hash, 'filipp1@test.com']);
            echo "User created with ID: $nextId\n\n";
        }

        // Show user
        $stmt = $pdo->prepare("SELECT * FROM user WHERE $usernameCol = ?");
        $stmt->execute(['filipp1']);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "User: " . json_encode($row, JSON_PRETTY_PRINT) . "\n";
    }

    echo "\n=== ACTIONS ===\n";
    echo "?action=fix_schema  - Fix table schema and create user\n";
    echo "?action=create_user - Just create/update user\n";

} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage() . "\n";
    exit(1);
}
