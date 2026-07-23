<?php
// Send message endpoint
require_once __DIR__ . '/../incl/lib.php';

header('Content-Type: text/plain');

$accountID = (int)($_POST['accountID'] ?? 0);
$gjp2 = $_POST['gjp2'] ?? '';
$toAccountID = (int)($_POST['toAccountID'] ?? 0);
$subject = base64_decode($_POST['subject'] ?? '');
$body = base64_decode($_POST['body'] ?? '');

if ($accountID <= 0 || $toAccountID <= 0 || empty($subject)) die('-1');

$pdo = db();

// Verify sender
$stmt = $pdo->prepare("SELECT * FROM accounts WHERE accountID = ?");
$stmt->execute([$accountID]);
$sender = $stmt->fetch();
if (!$sender) die('-1');

// Verify receiver
$stmt2 = $pdo->prepare("SELECT * FROM accounts WHERE accountID = ?");
$stmt2->execute([$toAccountID]);
$receiver = $stmt2->fetch();
if (!$receiver) die('-1');

$senderUserID = $sender['userID'] ?: $sender['accountID'];
$receiverUserID = $receiver['userID'] ?: $receiver['accountID'];

// Check if blocked
$userStmt = $pdo->prepare("SELECT blockedUsers FROM users WHERE userID = ?");
$userStmt->execute([$receiverUserID]);
$receiverUser = $userStmt->fetch();
if ($receiverUser && !empty($receiverUser['blockedUsers'])) {
    $blocked = explode(',', $receiverUser['blockedUsers']);
    if (in_array($senderUserID, $blocked)) die('-1');
}

$cleanSubject = htmlspecialchars(substr($subject, 0, 200), ENT_QUOTES, 'UTF-8');
$cleanBody = htmlspecialchars(substr($body, 0, 2000), ENT_QUOTES, 'UTF-8');

try {
    $stmt3 = $pdo->prepare("INSERT INTO messages (senderID, receiverID, subject, body) VALUES (?, ?, ?, ?)");
    $stmt3->execute([$senderUserID, $receiverUserID, $cleanSubject, $cleanBody]);
    echo 1;
} catch (Exception $e) {
    die('-1');
}
