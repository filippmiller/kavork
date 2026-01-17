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

    // Schema: id, user, pass, last_sess, role, state, cafe, email, color
    // Check if user already exists
    $stmt = $pdo->prepare("SELECT id FROM user WHERE user = ?");
    $stmt->execute(['filipp1']);
    if ($stmt->fetch()) {
        echo "User 'filipp1' already exists. Updating password...\n";
        $hash = password_hash('Airbus380+', PASSWORD_BCRYPT, ['cost' => 13]);
        $stmt = $pdo->prepare("UPDATE user SET pass = ?, state = 0 WHERE user = ?");
        $stmt->execute([$hash, 'filipp1']);
        echo "Password updated successfully.\n";
    } else {
        // Create new user - get next ID first
        $stmt = $pdo->query("SELECT COALESCE(MAX(id), 0) + 1 as next_id FROM user");
        $nextId = $stmt->fetch(PDO::FETCH_ASSOC)['next_id'];

        $hash = password_hash('Airbus380+', PASSWORD_BCRYPT, ['cost' => 13]);
        $stmt = $pdo->prepare("INSERT INTO user (id, user, pass, email, state, cafe) VALUES (?, ?, ?, ?, 0, 1)");
        $stmt->execute([$nextId, 'filipp1', $hash, 'filipp1@test.com']);
        echo "User 'filipp1' created successfully with ID: $nextId\n";
    }

    // Verify
    $stmt = $pdo->prepare("SELECT id, user, email, state, cafe FROM user WHERE user = ?");
    $stmt->execute(['filipp1']);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "User details: " . json_encode($row) . "\n";

} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage() . "\n";
    exit(1);
}
