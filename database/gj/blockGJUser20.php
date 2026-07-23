<?php
// Block user endpoint
require_once __DIR__ . '/../incl/lib.php';

header('Content-Type: text/plain');

$accountID = (int)($_POST['accountID'] ?? 0);
$gjp2 = $_POST['gjp2'] ?? '';
$targetAccountID = (int)($_POST['targetAccountID'] ?? 0);
$isBlocked = (int)($_POST['isBlocked'] ?? 0); // 1 = block, 0 = unblock

if ($accountID <= 0 || $targetAccountID <= 0) die('-1');

$pdo = db();

// Get user IDs
$stmt = $pdo->prepare("SELECT userID FROM accounts WHERE accountID = ?");
$stmt->execute([$accountID]);
$account = $stmt->fetch();
if (!$account) die('-1');

$userID = $account['userID'] ?: $account['accountID'];

$stmt2 = $pdo->prepare("SELECT userID FROM accounts WHERE accountID = ?");
$stmt2->execute([$targetAccountID]);
$targetAccount = $stmt2->fetch();
if (!$targetAccount) die('-1');

$targetUserID = $targetAccount['userID'] ?: $targetAccount['accountID'];

// Get current blocked list
$stmt3 = $pdo->prepare("SELECT blockedUsers FROM users WHERE userID = ?");
$stmt3->execute([$userID]);
$user = $stmt3->fetch();

$blocked = array_filter(explode(',', $user['blockedUsers'] ?? ''), function($v) { return !empty($v); });

if ($isBlocked) {
    // Add to blocked
    if (!in_array($targetUserID, $blocked)) {
        $blocked[] = $targetUserID;
    }
} else {
    // Remove from blocked
    $blocked = array_filter($blocked, function($v) use ($targetUserID) { return $v != $targetUserID; });
}

$blockedStr = implode(',', $blocked);
$pdo->prepare("UPDATE users SET blockedUsers = ? WHERE userID = ?")->execute([$blockedStr, $userID]);

echo 1;
