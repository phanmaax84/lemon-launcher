<?php
// Rate difficulty endpoint
require_once __DIR__ . '/../incl/lib.php';

header('Content-Type: text/plain');

$accountID = (int)($_POST['accountID'] ?? 0);
$gjp2 = $_POST['gjp2'] ?? '';
$levelID = (int)($_POST['levelID'] ?? 0);
$difficulty = (int)($_POST['difficulty'] ?? 0);
$stars = (int)($_POST['stars'] ?? 0);
$demon = (int)($_POST['demon'] ?? 0);
$auto = (int)($_POST['auto'] ?? 0);
$udid = $_POST['udid'] ?? '';

if ($levelID <= 0) die('-1');

// Store the difficulty vote
$pdo = db();

$userID = 0;
if ($accountID > 0) {
    $stmt = $pdo->prepare("SELECT userID FROM accounts WHERE accountID = ?");
    $stmt->execute([$accountID]);
    $account = $stmt->fetch();
    if ($account) $userID = $account['userID'];
}

try {
    $stmt2 = $pdo->prepare("INSERT INTO ratings (userID, levelID, stars, demon, difficulty, auto) 
        VALUES (?, ?, ?, ?, ?, ?) 
        ON DUPLICATE KEY UPDATE difficulty = VALUES(difficulty)");
    $stmt2->execute([$userID, $levelID, $stars, $demon, $difficulty, $auto]);
} catch (Exception $e) {}

echo 1;
