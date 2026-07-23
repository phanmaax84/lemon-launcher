<?php
// Update level description
require_once __DIR__ . '/../incl/lib.php';

header('Content-Type: text/plain');

$accountID = (int)($_POST['accountID'] ?? 0);
$gjp2 = $_POST['gjp2'] ?? '';
$levelID = (int)($_POST['levelID'] ?? 0);
$levelDesc = $_POST['levelDesc'] ?? '';

if ($levelID <= 0 || $accountID <= 0) die('-1');

$pdo = db();

// Verify ownership
$stmt = $pdo->prepare("SELECT a.accountID, u.userID FROM accounts a JOIN users u ON a.userID = u.userID WHERE a.accountID = ?");
$stmt->execute([$accountID]);
$account = $stmt->fetch();
if (!$account) die('-1');

$userID = $account['userID'];

$levelStmt = $pdo->prepare("SELECT userID FROM levels WHERE levelID = ?");
$levelStmt->execute([$levelID]);
$level = $levelStmt->fetch();

if (!$level || $level['userID'] != $userID) die('-1');

// Encode description
if (!empty($levelDesc)) {
    $storedDesc = base64_encode(gzipEncode(base64_decode($levelDesc) ?: $levelDesc));
} else {
    $storedDesc = '';
}

$pdo->prepare("UPDATE levels SET levelDescription = ? WHERE levelID = ?")->execute([$storedDesc, $levelID]);

echo 1;
