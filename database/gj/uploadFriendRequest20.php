<?php
// Send friend request endpoint
require_once __DIR__ . '/../incl/lib.php';

header('Content-Type: text/plain');

$accountID = (int)($_POST['accountID'] ?? 0);
$gjp2 = $_POST['gjp2'] ?? '';
$toAccountID = (int)($_POST['toAccountID'] ?? 0);
$comment = base64_decode($_POST['comment'] ?? '');

if ($accountID <= 0 || $toAccountID <= 0) die('-1');
if ($accountID == $toAccountID) die('-1');

$pdo = db();

// Verify both accounts
$stmt = $pdo->prepare("SELECT * FROM accounts WHERE accountID = ?");
$stmt->execute([$accountID]);
$sender = $stmt->fetch();
if (!$sender) die('-1');

$stmt2 = $pdo->prepare("SELECT * FROM accounts WHERE accountID = ?");
$stmt2->execute([$toAccountID]);
$receiver = $stmt2->fetch();
if (!$receiver) die('-1');

try {
    $stmt3 = $pdo->prepare("INSERT INTO friendRequests (accountID, toAccountID, comment) VALUES (?, ?, ?)");
    $stmt3->execute([$accountID, $toAccountID, $comment]);
    echo 1;
} catch (Exception $e) {
    // Already sent request
    die('-1');
}
