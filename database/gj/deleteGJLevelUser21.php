<?php
// Delete level endpoint
require_once __DIR__ . '/../incl/lib.php';

header('Content-Type: text/plain');

$accountID = (int)($_POST['accountID'] ?? 0);
$gjp2 = $_POST['gjp2'] ?? '';
$levelID = (int)($_POST['levelID'] ?? 0);

if ($levelID <= 0 || $accountID <= 0) die('-1');

$pdo = db();

// Verify account owns the level
$stmt = $pdo->prepare("SELECT a.*, u.userID FROM accounts a JOIN users u ON a.userID = u.userID WHERE a.accountID = ?");
$stmt->execute([$accountID]);
$account = $stmt->fetch();

if (!$account) die('-1');

$userID = $account['userID'];

// Check ownership
$levelStmt = $pdo->prepare("SELECT userID FROM levels WHERE levelID = ?");
$levelStmt->execute([$levelID]);
$level = $levelStmt->fetch();

if (!$level || $level['userID'] != $userID) die('-1');

// Delete level and related data
$pdo->prepare("DELETE FROM levels WHERE levelID = ?")->execute([$levelID]);
$pdo->prepare("DELETE FROM comments WHERE levelID = ?")->execute([$levelID]);
$pdo->prepare("DELETE FROM likes WHERE itemID = ? AND type = 1")->execute([$levelID]);
$pdo->prepare("DELETE FROM ratings WHERE levelID = ?")->execute([$levelID]);

echo 1;
