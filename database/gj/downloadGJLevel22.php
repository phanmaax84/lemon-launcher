<?php
// Download level endpoint
require_once __DIR__ . '/../incl/lib.php';

header('Content-Type: text/plain');

$levelID = (int)($_POST['levelID'] ?? 0);
$accountID = (int)($_POST['accountID'] ?? 0);
$gjp2 = $_POST['gjp2'] ?? '';
$gameVersion = $_POST['gameVersion'] ?? '';
$udid = $_POST['udid'] ?? '';

if ($levelID <= 0) die('-1');

$pdo = db();

// Get level
$stmt = $pdo->prepare("SELECT * FROM levels WHERE levelID = ?");
$stmt->execute([$levelID]);
$level = $stmt->fetch();

if (!$level) die('-1');

// Increment downloads
$pdo->prepare("UPDATE levels SET downloads = downloads + 1 WHERE levelID = ?")->execute([$levelID]);

// Get user info
$user = getUser($level['userID']);

// Format response
$desc = $level['levelDescription'];
if (!empty($desc)) {
    // Make sure it's properly encoded
    $testDecode = base64_decode($desc, true);
    if ($testDecode === false) {
        $desc = base64_encode(gzipEncode($desc));
    }
}

$diffNum = getDifficultyNum($level['starStars'], $level['starDemon'], $level['starAuto'], $level['starDifficulty']);

// Main level info
$response = '1:' . $level['levelID'];
$response .= ':2:' . $level['levelName'];
$response .= ':3:' . $desc;
$response .= ':4:' . $level['levelString'];
$response .= ':5:' . $level['levelVersion'];
$response .= ':6:' . $level['userID'];
$response .= ':8:' . $level['likes'];
$response .= ':9:' . $diffNum;
$response .= ':10:' . ($level['downloads'] + 1);
$response .= ':11:' . ($level['starStars'] ?? 0);
$response .= ':12:' . $level['audioTrack'];
$response .= ':13:' . $level['audioTrack'];
$response .= ':14:' . $level['likes'];
$response .= ':15:' . $level['levelLength'];
$response .= ':17:' . ($level['starDemon'] ?? 0);
$response .= ':18:' . (($level['starStars'] ?? 0) > 0 ? 1 : 0);
$response .= ':19:' . ($level['starFeatured'] ?? 0);
$response .= ':25:' . ($level['starAuto'] ?? 0);
$response .= ':28:' . $level['uploadDateInt'];
$response .= ':29:' . $level['updateDateInt'];
$response .= ':30:' . $level['levelVersion'];
$response .= ':31:' . ($level['isFeatured'] ?? 0);
$response .= ':35:' . $level['songID'];
$response .= ':36:' . $level['songID'];
$response .= ':37:' . ($level['coins'] ?? 0);
$response .= ':38:' . ($level['starCoins'] ?? 0);
$response .= ':39:' . ($level['requestedStars'] ?? 0);
$response .= ':40:' . ($level['twoPlayer'] ?? 0);
$response .= ':41:' . ($level['ldm'] ?? 0);
$response .= ':42:' . ($level['isEpic'] ?? 0);
$response .= ':43:' . ($level['starDemonDiff'] ?? 0);
$response .= ':45:' . ($level['unlisted'] ?? 0);
$response .= ':46:0';
$response .= ':47:' . ($level['objects'] ?? 0);
$response .= ':48:0';
$response .= ':49:0';
$response .= ':50:' . ($level['wt'] ?? 0);
$response .= ':51';
$response .= ':52';
$response .= ':53';
$response .= ':54';
$response .= ':55';
$response .= ':56';
$response .= ':57';

// User section
$hash = sha1($level['levelID'] . $level['likes'] . ($level['downloads'] + 1) . ($level['starStars'] ?? 0) . ($level['starFeatured'] ?? 0) . 'xI25fpAapCQg');

// User info
$userStr = '';
if ($user) {
    $userStr = $level['userID'] . ':' . $user['userName'] . ':' . $level['userID'];
}

// Song info
$songStr = '';
if ($level['songID'] > 0) {
    $songStmt = $pdo->prepare("SELECT * FROM songs WHERE songID = ?");
    $songStmt->execute([$level['songID']]);
    $song = $songStmt->fetch();
    if ($song) {
        $songStr = '~|~' . $song['songID'] . '~|~' . $song['name'] . '~|~' . $song['authorName'] . '~|~' . $song['downloadURL'] . '~|~' . $song['size'] . '~|~0~|~0';
    }
}

$response .= '#'; // End of level data hash
$response .= $hash . '#';
$response .= $userStr . '#';
$response .= $songStr . '#';
// Copy section
$response .= 'S0:0:0|S0:0:0';

echo $response;
