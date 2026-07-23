<?php
// Get friend requests endpoint
require_once __DIR__ . '/../incl/lib.php';

header('Content-Type: text/plain');

$accountID = (int)($_POST['accountID'] ?? 0);
$gjp2 = $_POST['gjp2'] ?? '';
$page = (int)($_POST['page'] ?? 0);
$getSent = (int)($_POST['getSent'] ?? 0);

if ($accountID <= 0) die('-1');

$pdo = db();
$perPage = 20;
$offset = $page * $perPage;

if ($getSent) {
    $query = "SELECT fr.*, a.userName as receiverName FROM friendRequests fr 
              LEFT JOIN accounts a ON fr.toAccountID = a.accountID
              WHERE fr.accountID = ?
              ORDER BY fr.timestamp DESC LIMIT $perPage OFFSET $offset";
    $countQuery = "SELECT COUNT(*) as cnt FROM friendRequests WHERE accountID = ?";
} else {
    $query = "SELECT fr.*, a.userName as senderName FROM friendRequests fr 
              LEFT JOIN accounts a ON fr.accountID = a.accountID
              WHERE fr.toAccountID = ?
              ORDER BY fr.timestamp DESC LIMIT $perPage OFFSET $offset";
    $countQuery = "SELECT COUNT(*) as cnt FROM friendRequests WHERE toAccountID = ?";
}

$stmt = $pdo->prepare($query);
$stmt->execute([$accountID]);
$requests = $stmt->fetchAll();

$countStmt = $pdo->prepare($countQuery);
$countStmt->execute([$accountID]);
$totalCount = $countStmt->fetch()['cnt'];

if (empty($requests)) {
    echo '#0:0:0';
    exit;
}

$reqStrings = [];
foreach ($requests as $r) {
    $age = time() - strtotime($r['timestamp']);
    $ageStr = formatAge($age);
    
    $otherID = $getSent ? $r['toAccountID'] : $r['accountID'];
    $otherName = $getSent ? ($r['receiverName'] ?? 'Unknown') : ($r['senderName'] ?? 'Unknown');
    $encodedComment = base64_encode(gzipEncode($r['comment'] ?? ''));
    
    // Format: sender_name:sender_id:request_id:comment:is_new:age:other_id:other_name
    $reqStrings[] = $otherName . '~' . $otherID . '~' . $r['requestID'] . '~' . $encodedComment . '~' . $r['isNew'] . '~' . $ageStr;
}

$pages = max(1, ceil($totalCount / $perPage));
echo implode('|', $reqStrings) . '#' . $totalCount . ':' . $offset . ':' . $pages;

function formatAge($seconds) {
    if ($seconds < 60) return $seconds . 's';
    if ($seconds < 3600) return round($seconds / 60) . 'm';
    if ($seconds < 86400) return round($seconds / 3600) . 'h';
    if ($seconds < 2592000) return round($seconds / 86400) . 'd';
    return round($seconds / 2592000) . 'w';
}
