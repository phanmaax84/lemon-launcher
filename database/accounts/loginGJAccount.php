<?php
// Login endpoint for GD
require_once __DIR__ . '/../incl/lib.php';

header('Content-Type: text/plain');

$username = $_POST['userName'] ?? '';
$password = $_POST['password'] ?? '';
$udid = $_POST['udid'] ?? '';
$sid = $_POST['sID'] ?? '';
$gjp2 = $_POST['gjp2'] ?? '';

if (empty($username) || (empty($password) && empty($gjp2))) {
    die('-1');
}

$pdo = db();

// Try GJP2 auth first
if (!empty($gjp2)) {
    $decodedPass = xorCipher(base64_decode($gjp2), XOR_KEY_PASS);
    if ($decodedPass) {
        $password = $decodedPass;
    }
}

// Check accounts table
$stmt = $pdo->prepare("SELECT * FROM accounts WHERE userName = ?");
$stmt->execute([$username]);
$account = $stmt->fetch();

if (!$account) {
    die('-1');
}

if (!verifyGamePassword($password, $account['password'])) {
    die('-1');
}

if ($account['isBanned']) {
    die('-1');
}

// Make sure user exists
$userID = $account['userID'];
if (!$userID) {
    $userID = $account['accountID'];
    $stmt2 = $pdo->prepare("UPDATE accounts SET userID = ? WHERE accountID = ?");
    $stmt2->execute([$userID, $account['accountID']]);
}

$userStmt = $pdo->prepare("SELECT * FROM users WHERE userID = ?");
$userStmt->execute([$userID]);
$user = $userStmt->fetch();

if (!$user) {
    // Create user profile
    $stmt3 = $pdo->prepare("INSERT INTO users (userID, userName) VALUES (?, ?)");
    $stmt3->execute([$userID, $account['userName']]);
}

// Update last played
$pdo->prepare("UPDATE users SET lastPlayed = NOW() WHERE userID = ?")->execute([$userID]);

// Response: accountID,userID
echo $account['accountID'] . ',' . $userID;
