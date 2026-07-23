<?php
// Delete comment endpoint
require_once __DIR__ . '/../incl/lib.php';

header('Content-Type: text/plain');

$accountID = (int)($_POST['accountID'] ?? 0);
$gjp2 = $_POST['gjp2'] ?? '';
$commentID = (int)($_POST['commentID'] ?? 0);
$levelID = (int)($_POST['levelID'] ?? 0);

if ($commentID <= 0 || $accountID <= 0) die('-1');

$pdo = db();

// Get user ID
$stmt = $pdo->prepare("SELECT userID FROM accounts WHERE accountID = ?");
$stmt->execute([$accountID]);
$account = $stmt->fetch();
if (!$account) die('-1');

$userID = $account['userID'] ?: $account['accountID'];

// Verify ownership
$commentStmt = $pdo->prepare("SELECT userID FROM comments WHERE commentID = ?");
$commentStmt->execute([$commentID]);
$comment = $commentStmt->fetch();

if (!$comment) die('-1');

// Can delete own comments or if admin
if ($comment['userID'] != $userID) {
    $adminStmt = $pdo->prepare("SELECT isAdmin FROM accounts WHERE accountID = ?");
    $adminStmt->execute([$accountID]);
    $adminData = $adminStmt->fetch();
    if (!$adminData['isAdmin']) die('-1');
}

$pdo->prepare("DELETE FROM comments WHERE commentID = ?")->execute([$commentID]);
echo 1;
