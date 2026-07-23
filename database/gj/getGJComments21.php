<?php
// Get comments endpoint
require_once __DIR__ . '/../incl/lib.php';

header('Content-Type: text/plain');

$levelID = (int)($_POST['levelID'] ?? 0);
$page = (int)($_POST['page'] ?? 0);
$accountID = (int)($_POST['accountID'] ?? 0);
$mode = $_POST['mode'] ?? '0'; // 0 = level comments, 1 = user comments
$count = (int)($_POST['count'] ?? 20);
$total = $_POST['total'] ?? '0';

$pdo = db();
$perPage = 20;
$offset = $page * $perPage;

if ($mode == '1') {
    // User comments - get by userID from accountID
    $userID = 0;
    if ($levelID > 0) {
        $stmt = $pdo->prepare("SELECT userID FROM users WHERE userID = ?");
        $stmt->execute([$levelID]);
        $user = $stmt->fetch();
        if ($user) $userID = $user['userID'];
    }
    
    $stmt = $pdo->prepare("SELECT c.*, u.userName FROM comments c 
        LEFT JOIN users u ON c.userID = u.userID 
        WHERE c.userID = ? AND c.levelID = 0 
        ORDER BY c.timestamp DESC LIMIT $perPage OFFSET $offset");
    $stmt->execute([$levelID]); // using levelID as userID for user comments
    $comments = $stmt->fetchAll();
    
    $countStmt = $pdo->prepare("SELECT COUNT(*) as cnt FROM comments WHERE userID = ? AND levelID = 0");
    $countStmt->execute([$levelID]);
    $totalCount = $countStmt->fetch()['cnt'];
} else {
    // Level comments
    $stmt = $pdo->prepare("SELECT c.*, u.userName FROM comments c 
        LEFT JOIN users u ON c.userID = u.userID 
        WHERE c.levelID = ? 
        ORDER BY c.timestamp DESC LIMIT $perPage OFFSET $offset");
    $stmt->execute([$levelID]);
    $comments = $stmt->fetchAll();
    
    $countStmt = $pdo->prepare("SELECT COUNT(*) as cnt FROM comments WHERE levelID = ?");
    $countStmt->execute([$levelID]);
    $totalCount = $countStmt->fetch()['cnt'];
}

if (empty($comments)) {
    echo '#0:0:0';
    exit;
}

$commentStrings = [];
foreach ($comments as $c) {
    $encodedComment = base64_encode(gzipEncode($c['comment']));
    $age = time() - strtotime($c['timestamp']);
    $ageStr = formatAge($age);
    
    // Comment format: base64~userID~likes~0~commentID~spam~age~percent~userName
    $commentStrings[] = $encodedComment . '~' . $c['userID'] . '~' . $c['likes'] . '~0~' . $c['commentID'] . '~0~' . $ageStr . '~' . ($mode == 1 ? 0 : 0) . '~' . ($c['userName'] ?? 'Unknown');
}

$pages = max(1, ceil($totalCount / $perPage));
echo implode('|', $commentStrings) . '#' . $totalCount . ':' . $offset . ':' . $pages;

function formatAge($seconds) {
    if ($seconds < 60) return $seconds . 's';
    if ($seconds < 3600) return round($seconds / 60) . 'm';
    if ($seconds < 86400) return round($seconds / 3600) . 'h';
    if ($seconds < 2592000) return round($seconds / 86400) . 'd';
    if ($seconds < 31536000) return round($seconds / 2592000) . 'w';
    return round($seconds / 31536000) . 'y';
}
