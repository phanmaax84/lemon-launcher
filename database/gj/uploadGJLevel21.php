<?php
// Upload level endpoint
require_once __DIR__ . '/../incl/lib.php';

header('Content-Type: text/plain');

$accountID = (int)($_POST['accountID'] ?? 0);
$gjp2 = $_POST['gjp2'] ?? '';
$levelName = validateInput($_POST['levelName'] ?? 'Unnamed', 100);
$levelDesc = $_POST['levelDesc'] ?? '';
$levelVersion = (int)($_POST['levelVersion'] ?? 1);
$levelLength = (int)($_POST['levelLength'] ?? 0);
$audioTrack = (int)($_POST['audioTrack'] ?? 0);
$auto = (int)($_POST['auto'] ?? 0);
$password = (int)($_POST['password'] ?? 0);
$original = (int)($_POST['original'] ?? 0);
$twoPlayer = (int)($_POST['twoPlayer'] ?? 0);
$songID = (int)($_POST['songID'] ?? 0);
$objects = (int)($_POST['objects'] ?? 0);
$coins = (int)($_POST['coins'] ?? 0);
$requestedStars = (int)($_POST['requestedStars'] ?? 0);
$unlisted = (int)($_POST['unlisted'] ?? 0);
$ldm = (int)($_POST['ldm'] ?? 0);
$levelString = $_POST['levelString'] ?? '';
$wt = (int)($_POST['wt'] ?? 0);
$wt2 = (int)($_POST['wt2'] ?? 0);
$wt3 = (int)($_POST['wt3'] ?? 0);
$extraString = $_POST['extraString'] ?? '';
$levelInfo = $_POST['levelInfo'] ?? '';
$seed = $_POST['seed'] ?? '';
$seed2 = $_POST['seed2'] ?? '';

if (empty($accountID)) die('-1');

$pdo = db();

// Verify account
$stmt = $pdo->prepare("SELECT * FROM accounts WHERE accountID = ?");
$stmt->execute([$accountID]);
$account = $stmt->fetch();

if (!$account || $account['isBanned']) die('-1');

$userID = $account['userID'];
if (!$userID) $userID = $account['accountID'];

$uploadTime = time();

// Handle level description encoding
if (!empty($levelDesc)) {
    // The game sends base64 encoded gzip compressed description
    $decoded = base64_decode($levelDesc);
    if ($decoded !== false) {
        $decompressed = @gzdecode($decoded);
        if ($decompressed !== false) {
            $levelDesc = $decompressed;
        }
    }
    // Store as base64 of gzip
    $storedDesc = base64_encode(gzipEncode($levelDesc));
} else {
    $storedDesc = '';
}

try {
    $stmt2 = $pdo->prepare("INSERT INTO levels 
        (levelName, levelDescription, levelVersion, levelLength, audioTrack, auto, password, 
         original, twoPlayer, songID, objects, coins, requestedStars, unlisted, ldm,
         levelString, uploadDateInt, updateDateInt, userID, extID, wt, wt2, wt3)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    $stmt2->execute([
        $levelName, $storedDesc, $levelVersion, $levelLength, $audioTrack, $auto, $password,
        $original, $twoPlayer, $songID, $objects, $coins, $requestedStars, $unlisted, $ldm,
        $levelString, $uploadTime, $uploadTime, $userID, $accountID, $wt, $wt2, $wt3
    ]);
    
    echo $pdo->lastInsertId();
} catch (Exception $e) {
    die('-1');
}
