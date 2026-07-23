<?php
// Search users endpoint
require_once __DIR__ . '/../incl/lib.php';

header('Content-Type: text/plain');

$str = $_POST['str'] ?? '';
$page = (int)($_POST['page'] ?? 0);
$accountID = (int)($_POST['accountID'] ?? 0);
$gjp2 = $_POST['gjp2'] ?? '';

if (empty($str)) die('-1');

$pdo = db();
$perPage = 10;
$offset = $page * $perPage;

$stmt = $pdo->prepare("SELECT * FROM users WHERE userName LIKE ? LIMIT $perPage OFFSET $offset");
$stmt->execute(['%' . $str . '%']);
$users = $stmt->fetchAll();

$countStmt = $pdo->prepare("SELECT COUNT(*) as cnt FROM users WHERE userName LIKE ?");
$countStmt->execute(['%' . $str . '%']);
$totalCount = $countStmt->fetch()['cnt'];

if (empty($users)) {
    echo '#0:0:0';
    exit;
}

$userStrings = [];
foreach ($users as $u) {
    // Format: userID:userName:stars:diamonds:accountID:demons:creatorPoints:icon:color1:color2:coins:userCoins:secret
    $userStrings[] = $u['userID'] . ':' . $u['userName'] . ':' . $u['stars'] . ':0:' . $u['userID'] . ':' . $u['demons'] . ':' . $u['creatorPoints'] . ':' . $u['icon'] . ':' . $u['color1'] . ':' . $u['color2'] . ':' . $u['secretCoins'] . ':' . $u['userCoins'] . ':' . 0;
}

$pages = max(1, ceil($totalCount / $perPage));
echo implode('|', $userStrings) . '#' . $totalCount . ':' . $offset . ':' . $pages;
