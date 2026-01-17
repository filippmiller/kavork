<?php
// Temporary script to create user - delete after use

$host = getenv('MYSQLHOST') ?: 'localhost';
$port = getenv('MYSQLPORT') ?: '3306';
$db = getenv('MYSQLDATABASE') ?: 'railway';
$user = getenv('MYSQLUSER') ?: 'root';
$pass = getenv('MYSQLPASSWORD') ?: '';

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // First, show table structure for debugging
    $stmt = $pdo->query("DESCRIBE user");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "Table columns: " . implode(', ', $columns) . "\n";

    // Determine the username column (could be 'name' or 'user')
    $usernameCol = in_array('name', $columns) ? 'name' : 'user';

    // Check if user already exists
    $stmt = $pdo->prepare("SELECT id FROM user WHERE $usernameCol = ?");
    $stmt->execute(['filipp1']);
    if ($stmt->fetch()) {
        echo "User 'filipp1' already exists. Updating password...\n";
        $hash = password_hash('Airbus380+', PASSWORD_BCRYPT, ['cost' => 13]);
        $stmt = $pdo->prepare("UPDATE user SET pass = ?, state = 0 WHERE $usernameCol = ?");
        $stmt->execute([$hash, 'filipp1']);
        echo "Password updated successfully.\n";
    } else {
        // Create new user
        $hash = password_hash('Airbus380+', PASSWORD_BCRYPT, ['cost' => 13]);
        $stmt = $pdo->prepare("INSERT INTO user ($usernameCol, pass, email, state, franchisee_id, lg) VALUES (?, ?, ?, 0, 1, 'en-EN')");
        $stmt->execute(['filipp1', $hash, 'filipp1@test.com']);
        echo "User 'filipp1' created successfully with ID: " . $pdo->lastInsertId() . "\n";
    }

    // Verify
    $stmt = $pdo->prepare("SELECT id, $usernameCol as username, email, state, franchisee_id FROM user WHERE $usernameCol = ?");
    $stmt->execute(['filipp1']);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "User details: " . json_encode($row) . "\n";

} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage() . "\n";
    exit(1);
}
