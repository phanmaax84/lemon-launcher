<?php
// Sync endpoint - saves/loads game data
require_once __DIR__ . '/../incl/lib.php';

header('Content-Type: text/plain');

$accountID = $_POST['accountID'] ?? '';
$gjp2 = $_POST['gjp2'] ?? '';
$gameVersion = $_POST['gameVersion'] ?? '';
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
$saveData = $_POST['saveData'] ?? '';

if (empty($accountID)) die('-1');

// Decode GJP2 if present
if (!empty($gjp2)) {
    $decodedPass = xorCipher(base64_decode($gjp2), XOR_KEY_PASS);
}

$pdo = db();

// Verify account
$stmt = $pdo->prepare("SELECT * FROM accounts WHERE accountID = ?");
$stmt->execute([$accountID]);
$account = $stmt->fetch();

if (!$account) die('-1');

$userID = $account['userID'];
if (!$userID) $userID = $account['accountID'];

// Update user data
$pdo->prepare("UPDATE users SET 
    stars = GREATEST(stars, ?),
    diamonds = GREATEST(diamonds, ?),
    demons = GREATEST(demons, ?),
    secretCoins = GREATEST(secretCoins, ?),
    userCoins = GREATEST(userCoins, ?),
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
    $icon, $color1, $color2, $iconType, $special,
    $accIcon, $accShip, $accBall, $accBird, $accDart,
    $accRobot, $accGlow, $accSpider, $accExplosion, $accSwing, $accJetpack,
    $userID
]);

// If there's save data and a load request
$load = (int)($_POST['load'] ?? 0);

if ($load) {
    // Return save data - for simplicity return a basic response
    $userData = getUser($userID);
    // Return accountID,userID
    echo $accountID . ',' . $userID;
} else {
    echo $accountID . ',' . $userID;
}
