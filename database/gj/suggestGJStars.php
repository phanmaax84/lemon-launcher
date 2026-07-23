<?php
// Suggest level stars (mod rating suggestion)
require_once __DIR__ . '/../incl/lib.php';

header('Content-Type: text/plain');

$accountID = (int)($_POST['accountID'] ?? 0);
$gjp2 = $_POST['gjp2'] ?? '';
$levelID = (int)($_POST['levelID'] ?? 0);
$stars = (int)($_POST['stars'] ?? 0);
$feature = (int)($_POST['feature'] ?? 0);

if ($levelID <= 0) die('-1');

// Store the suggestion for admin review
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
        ON DUPLICATE KEY UPDATE stars = VALUES(stars)");
    $stmt2->execute([$userID, $levelID, $stars, 0, 0, 0]);
} catch (Exception $e) {}

echo 1;
