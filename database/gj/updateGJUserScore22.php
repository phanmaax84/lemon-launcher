<?php
// Update user score endpoint
require_once __DIR__ . '/../incl/lib.php';

header('Content-Type: text/plain');

$accountID = (int)($_POST['accountID'] ?? 0);
$gjp2 = $_POST['gjp2'] ?? '';
$gameVersion = $_POST['gameVersion'] ?? '21';
$stars = (int)($_POST['stars'] ?? 0);
$diamonds = (int)($_POST['diamonds'] ?? 0);
$demons = (int)($_POST['demons'] ?? 0);
$secretCoins = (int)($_POST['secretCoins'] ?? 0);
$userCoins = (int)($_POST['userCoins'] ?? 0);
$icon = (int)($_POST['icon'] ?? 0);
$color1 = (int)($_POST['color1'] ?? 0);
$color2 = (int)($_POST['color2'] ?? 3);
$iconType = (int)($_POST['iconType'] ?? 0);
$special = (int)($_POST['special'] ?? 0);
$accIcon = (int)($_POST['accIcon'] ?? 0);
$accShip = (int)($_POST['accShip'] ?? 0);
$accBall = (int)($_POST['accBall'] ?? 0);
$accBird = (int)($_POST['accBird'] ?? 0);
$accDart = (int)($_POST['accDart'] ?? 0);
$accRobot = (int)($_POST['accRobot'] ?? 0);
$accGlow = (int)($_POST['accGlow'] ?? 0);
$accSpider = (int)($_POST['accSpider'] ?? 0);
$accExplosion = (int)($_POST['accExplosion'] ?? 0);
$accSwing = (int)($_POST['accSwing'] ?? 0);
$accJetpack = (int)($_POST['accJetpack'] ?? 0);
$udid = $_POST['udid'] ?? '';

if ($accountID <= 0) die('-1');

$pdo = db();

// Verify account
$stmt = $pdo->prepare("SELECT * FROM accounts WHERE accountID = ?");
$stmt->execute([$accountID]);
$account = $stmt->fetch();

if (!$account) die('-1');

$userID = $account['userID'];
if (!$userID) $userID = $account['accountID'];

// Calculate creator points
$cpStmt = $pdo->prepare("SELECT COUNT(*) as cp FROM levels WHERE userID = ? AND isFeatured = 1");
$cpStmt->execute([$userID]);
$creatorPoints = $cpStmt->fetch()['cp'];

// Update user stats - only increase values, never decrease
$pdo->prepare("UPDATE users SET 
    stars = GREATEST(stars, ?),
    diamonds = GREATEST(diamonds, ?),
    demons = GREATEST(demons, ?),
    secretCoins = GREATEST(secretCoins, ?),
    userCoins = GREATEST(userCoins, ?),
    creatorPoints = ?,
    icon = ?,
    color1 = ?,
    color2 = ?,
    iconType = ?,
    special = ?,
    accIcon = ?,
    accShip = ?,
    accBall = ?,
    accBird = ?,
    accDart = ?,
    accRobot = ?,
    accGlow = ?,
    accSpider = ?,
    accExplosion = ?,
    accSwing = ?,
    accJetpack = ?,
    lastPlayed = NOW()
    WHERE userID = ?")->execute([
    $stars, $diamonds, $demons, $secretCoins, $userCoins,
    $creatorPoints, $icon, $color1, $color2, $iconType, $special,
    $accIcon, $accShip, $accBall, $accBird, $accDart,
    $accRobot, $accGlow, $accSpider, $accExplosion, $accSwing, $accJetpack,
    $userID
]);

echo $userID;
