<?php
// Register endpoint for GD
require_once __DIR__ . '/../incl/lib.php';

header('Content-Type: text/plain');

$username = validateInput($_POST['userName'] ?? '', 50);
$password = $_POST['password'] ?? '';
$email = validateInput($_POST['email'] ?? '', 255);

if (empty($username) || empty($password)) {
    die('-1');
}

// Validate username
if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
    die('-8'); // invalid username format
}

if (strlen($username) < 3) {
    die('-9'); // username too short
}

if (strlen($password) < 6) {
    die('-8'); // password too short
}

$pdo = db();

// Check if username already exists
$stmt = $pdo->prepare("SELECT accountID FROM accounts WHERE userName = ?");
$stmt->execute([$username]);
if ($stmt->fetch()) {
    die('-2'); // username taken
}

// Check if email is taken
if (!empty($email)) {
    $stmt2 = $pdo->prepare("SELECT accountID FROM accounts WHERE email = ?");
    $stmt2->execute([$email]);
    if ($stmt2->fetch()) {
        die('-3'); // email taken
    }
}

// Create account
$hashedPass = hashGamePassword($password);

try {
    $stmt3 = $pdo->prepare("INSERT INTO accounts (userName, password, email) VALUES (?, ?, ?)");
    $stmt3->execute([$username, $hashedPass, $email]);
    $accountID = $pdo->lastInsertId();
    
    // Create user profile
    $stmt4 = $pdo->prepare("INSERT INTO users (userID, userName) VALUES (?, ?)");
    $stmt4->execute([$accountID, $username]);
    
    // Link account to user
    $pdo->prepare("UPDATE accounts SET userID = ? WHERE accountID = ?")->execute([$accountID, $accountID]);
    
    echo $accountID;
} catch (Exception $e) {
    die('-1');
}
