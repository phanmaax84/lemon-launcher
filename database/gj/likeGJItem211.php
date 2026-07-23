<?php
// Like endpoint
require_once __DIR__ . '/../incl/lib.php';

header('Content-Type: text/plain');

$accountID = (int)($_POST['accountID'] ?? 0);
$gjp2 = $_POST['gjp2'] ?? '';
$itemID = (int)($_POST['itemID'] ?? 0);
$type = (int)($_POST['type'] ?? 0); // 1=level, 2=comment, 3=comment2
$special = (int)($_POST['special'] ?? 0);
$isLike = (int)($_POST['like'] ?? 0); // 1=like, 0=dislike
$udid = $_POST['udid'] ?? '';

if ($itemID <= 0) die('-1');

$pdo = db();

$userID = 0;
if ($accountID > 0) {
    $stmt = $pdo->prepare("SELECT userID FROM accounts WHERE accountID = ?");
    $stmt->execute([$accountID]);
    $account = $stmt->fetch();
    if ($account) $userID = $account['userID'];
}

if ($type == 1) {
    // Level like
    $change = $isLike ? 1 : -1;
    
    // Check if already liked
    $checkStmt = $pdo->prepare("SELECT * FROM likes WHERE userID = ? AND itemID = ? AND type = ?");
    $checkStmt->execute([$userID, $itemID, $type]);
    $existing = $checkStmt->fetch();
    
    if (!$existing) {
        $pdo->prepare("UPDATE levels SET likes = likes + ? WHERE levelID = ?")->execute([$change, $itemID]);
        $pdo->prepare("INSERT INTO likes (userID, itemID, type, isLike) VALUES (?, ?, ?, ?)")->execute([$userID, $itemID, $type, $isLike]);
    } else {
        // Toggle
        $oldLike = $existing['isLike'];
        if ($oldLike != $isLike) {
            $newChange = $isLike ? 2 : -2;
            $pdo->prepare("UPDATE levels SET likes = likes + ? WHERE levelID = ?")->execute([$newChange, $itemID]);
            $pdo->prepare("UPDATE likes SET isLike = ? WHERE userID = ? AND itemID = ? AND type = ?")->execute([$isLike, $userID, $itemID, $type]);
        }
    }
} elseif ($type == 2 || $type == 3) {
    // Comment like
    $change = $isLike ? 1 : -1;
    $pdo->prepare("UPDATE comments SET likes = likes + ? WHERE commentID = ?")->execute([$change, $itemID]);
}

echo 1;
