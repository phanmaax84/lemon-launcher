<?php
// Rate level endpoint
require_once __DIR__ . '/../incl/lib.php';

header('Content-Type: text/plain');

$accountID = (int)($_POST['accountID'] ?? 0);
$gjp2 = $_POST['gjp2'] ?? '';
$levelID = (int)($_POST['levelID'] ?? 0);
$stars = (int)($_POST['stars'] ?? 0);
$demon = (int)($_POST['demon'] ?? 0);
$difficulty = (int)($_POST['difficulty'] ?? 0);
$auto = (int)($_POST['auto'] ?? 0);
$udid = $_POST['udid'] ?? '';

if ($levelID <= 0 || $stars <= 0) die('-1');

$pdo = db();

$userID = 0;
if ($accountID > 0) {
    $stmt = $pdo->prepare("SELECT userID FROM accounts WHERE accountID = ?");
    $stmt->execute([$accountID]);
    $account = $stmt->fetch();
    if ($account) $userID = $account['userID'];
}

// Store rating attempt (for the server to review and rate officially)
try {
    $stmt2 = $pdo->prepare("INSERT INTO ratings (userID, levelID, stars, demon, difficulty, auto) 
        VALUES (?, ?, ?, ?, ?, ?) 
        ON DUPLICATE KEY UPDATE stars = VALUES(stars), demon = VALUES(demon), difficulty = VALUES(difficulty), auto = VALUES(auto)");
    $stmt2->execute([$userID, $levelID, $stars, $demon, $difficulty, $auto]);
} catch (Exception $e) {
    // Rating already exists, that's ok
}

// In a real GDPS, players can't rate levels themselves (admins/mods do it)
// But for a public server, we can auto-rate based on requested stars
if ($stars > 0) {
    $levelStmt = $pdo->prepare("SELECT requestedStars FROM levels WHERE levelID = ?");
    $levelStmt->execute([$levelID]);
    $level = $levelStmt->fetch();
    
    $rateStars = $level['requestedStars'] ?? $stars;
    if ($rateStars > 0 && $rateStars <= 10) {
        $pdo->prepare("UPDATE levels SET starStars = ?, starDemon = ?, starDifficulty = ?, starAuto = ? WHERE levelID = ? AND starStars = 0")
            ->execute([$rateStars, $demon, $difficulty, $auto, $levelID]);
    }
}

echo 1;
