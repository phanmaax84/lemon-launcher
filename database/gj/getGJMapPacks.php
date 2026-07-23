<?php
// Get map packs endpoint
require_once __DIR__ . '/../incl/lib.php';

header('Content-Type: text/plain');

$page = (int)($_POST['page'] ?? 0);
$secret = $_POST['secret'] ?? '';

$pdo = db();
$perPage = 20;
$offset = $page * $perPage;

// Map packs = groups of levels with a star requirement
// For simplicity, group by star count
$stmt = $pdo->prepare("SELECT starStars, COUNT(*) as count FROM levels WHERE starStars > 0 GROUP BY starStars ORDER BY starStars");
$stmt->execute();
$packs = $stmt->fetchAll();

if (empty($packs)) {
    echo '#0:0:0';
    exit;
}

$packStrings = [];
foreach ($packs as $p) {
    $name = $p['starStars'] . ' Star Pack';
    
    // Get level IDs for this pack
    $levelStmt = $pdo->prepare("SELECT GROUP_CONCAT(levelID) as ids FROM levels WHERE starStars = ?");
    $levelStmt->execute([$p['starStars']]);
    $levelData = $levelStmt->fetch();
    
    $colors = '0,0,0';
    $encodedName = base64_encode(gzipEncode($name));
    
    $packStrings[] = '1:1:2:' . $encodedName . ':3:' . ($levelData['ids'] ?? '') . ':4:' . $p['starStars'] . ':5:' . $colors . ':6:0:7:0';
}

$pages = max(1, ceil(count($packs) / $perPage));
echo implode('|', $packStrings) . '#' . count($packs) . ':0:' . $pages;
