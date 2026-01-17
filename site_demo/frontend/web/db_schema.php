<?php
// Simple database schema check - no Yii

error_reporting(E_ALL);
ini_set('display_errors', 1);

$host = getenv('MYSQLHOST') ?: 'localhost';
$port = getenv('MYSQLPORT') ?: '3306';
$db = getenv('MYSQLDATABASE') ?: 'railway';
$user = getenv('MYSQLUSER') ?: 'root';
$pass = getenv('MYSQLPASSWORD') ?: '';

echo "<pre>\n";
echo "=== DATABASE SCHEMA CHECK ===\n\n";

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 1. Check required tables
    echo "1. REQUIRED TABLES\n";
    $requiredTables = ['user', 'cafe', 'franchisee', 'user_cafe', 'cafe_params', 'tariffs'];
    $stmt = $pdo->query("SHOW TABLES");
    $existingTables = $stmt->fetchAll(PDO::FETCH_COLUMN);

    foreach ($requiredTables as $table) {
        $exists = in_array($table, $existingTables);
        echo "   $table: " . ($exists ? "OK" : "MISSING!") . "\n";
    }
    echo "\n";

    // 2. Check user_cafe table (critical for login)
    echo "2. USER_CAFE TABLE (user-cafe assignments)\n";
    if (in_array('user_cafe', $existingTables)) {
        $stmt = $pdo->query("DESCRIBE user_cafe");
        while ($col = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "   {$col['Field']}: {$col['Type']}\n";
        }

        $stmt = $pdo->query("SELECT COUNT(*) FROM user_cafe");
        $count = $stmt->fetchColumn();
        echo "\n   Total records: $count\n";

        if ($count > 0) {
            $stmt = $pdo->query("SELECT uc.*, u.name as user_name, c.name as cafe_name
                                 FROM user_cafe uc
                                 LEFT JOIN user u ON u.id = uc.user_id
                                 LEFT JOIN cafe c ON c.id = uc.cafe_id
                                 LIMIT 5");
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo "\n   Sample records:\n";
            foreach ($rows as $row) {
                echo "   - User {$row['user_id']} ({$row['user_name']}) -> Cafe {$row['cafe_id']} ({$row['cafe_name']})\n";
            }
        }
    } else {
        echo "   TABLE MISSING!\n";
    }
    echo "\n";

    // 3. Check test user filipp1
    echo "3. TEST USER 'filipp1'\n";
    $stmt = $pdo->prepare("SELECT id, name, franchisee_id, state FROM user WHERE name = ?");
    $stmt->execute(['filipp1']);
    $testUser = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($testUser) {
        echo "   ID: {$testUser['id']}\n";
        echo "   Name: {$testUser['name']}\n";
        echo "   Franchisee ID: {$testUser['franchisee_id']}\n";
        echo "   State: {$testUser['state']}\n";

        // Check user's cafe assignments
        if (in_array('user_cafe', $existingTables)) {
            $stmt = $pdo->prepare("SELECT cafe_id FROM user_cafe WHERE user_id = ?");
            $stmt->execute([$testUser['id']]);
            $cafes = $stmt->fetchAll(PDO::FETCH_COLUMN);
            if ($cafes) {
                echo "   Cafe assignments: " . implode(', ', $cafes) . "\n";
            } else {
                echo "\n   !!! WARNING: NO CAFE ASSIGNMENTS !!!\n";
                echo "   This causes logout after login because getCafesList() returns empty.\n";
            }
        }
    } else {
        echo "   USER NOT FOUND!\n";
    }
    echo "\n";

    // 4. Check cafes for franchisee 1
    echo "4. CAFES FOR FRANCHISEE ID 1\n";
    $stmt = $pdo->query("SELECT id, name, franchisee_id FROM cafe LIMIT 10");
    $cafes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if ($cafes) {
        foreach ($cafes as $cafe) {
            echo "   ID {$cafe['id']}: {$cafe['name']} (franchisee: {$cafe['franchisee_id']})\n";
        }
    } else {
        echo "   No cafes found!\n";
    }
    echo "\n";

    // 5. FIX: Create user_cafe assignment if requested
    if (isset($_GET['fix'])) {
        echo "5. FIXING USER_CAFE ASSIGNMENT\n";

        if ($testUser) {
            // Get first cafe
            $stmt = $pdo->query("SELECT id FROM cafe LIMIT 1");
            $cafeId = $stmt->fetchColumn();

            if ($cafeId) {
                // Check if assignment exists
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM user_cafe WHERE user_id = ? AND cafe_id = ?");
                $stmt->execute([$testUser['id'], $cafeId]);
                if ($stmt->fetchColumn() == 0) {
                    // Get next ID
                    $stmt = $pdo->query("SELECT COALESCE(MAX(id), 0) + 1 FROM user_cafe");
                    $nextId = $stmt->fetchColumn();

                    // Check table structure
                    $stmt = $pdo->query("DESCRIBE user_cafe");
                    $cols = $stmt->fetchAll(PDO::FETCH_COLUMN);

                    if (in_array('state', $cols)) {
                        $stmt = $pdo->prepare("INSERT INTO user_cafe (id, user_id, cafe_id, state) VALUES (?, ?, ?, 0)");
                        $stmt->execute([$nextId, $testUser['id'], $cafeId]);
                    } else {
                        $stmt = $pdo->prepare("INSERT INTO user_cafe (id, user_id, cafe_id) VALUES (?, ?, ?)");
                        $stmt->execute([$nextId, $testUser['id'], $cafeId]);
                    }
                    echo "   CREATED: User {$testUser['id']} -> Cafe $cafeId (ID: $nextId)\n";
                } else {
                    echo "   Assignment already exists.\n";
                }
            } else {
                echo "   ERROR: No cafes found to assign.\n";
            }
        } else {
            echo "   ERROR: User not found.\n";
        }
        echo "\n";
    }

    echo "=== ACTIONS ===\n";
    echo "Add ?fix to URL to create user_cafe assignment for filipp1\n";

} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage() . "\n";
}

echo "</pre>";
