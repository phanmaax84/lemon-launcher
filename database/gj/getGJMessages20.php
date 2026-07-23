<?php
// Get messages endpoint
require_once __DIR__ . '/../incl/lib.php';

header('Content-Type: text/plain');

$accountID = (int)($_POST['accountID'] ?? 0);
$gjp2 = $_POST['gjp2'] ?? '';
$page = (int)($_POST['page'] ?? 0);
$getSent = (int)($_POST['getSent'] ?? 0); // 0 = inbox, 1 = outbox

if ($accountID <= 0) die('-1');

$pdo = db();

$stmt = $pdo->prepare("SELECT * FROM accounts WHERE accountID = ?");
$stmt->execute([$accountID]);
$account = $stmt->fetch();
if (!$account) die('-1');

$userID = $account['userID'] ?: $account['accountID'];
$perPage = 20;
$offset = $page * $perPage;

if ($getSent) {
    $query = "SELECT m.*, s.userName as senderName, r.userName as receiverName 
              FROM messages m 
              LEFT JOIN users s ON m.senderID = s.userID
              LEFT JOIN users r ON m.receiverID = r.userID
              WHERE m.senderID = ? AND m.senderDelete = 0
              ORDER BY m.timestamp DESC LIMIT $perPage OFFSET $offset";
    $countQuery = "SELECT COUNT(*) as cnt FROM messages WHERE senderID = ? AND senderDelete = 0";
} else {
    $query = "SELECT m.*, s.userName as senderName, r.userName as receiverName 
              FROM messages m 
              LEFT JOIN users s ON m.senderID = s.userID
              LEFT JOIN users r ON m.receiverID = r.userID
              WHERE m.receiverID = ? AND m.receiverDelete = 0
              ORDER BY m.timestamp DESC LIMIT $perPage OFFSET $offset";
    $countQuery = "SELECT COUNT(*) as cnt FROM messages WHERE receiverID = ? AND receiverDelete = 0";
}

$stmt2 = $pdo->prepare($query);
$stmt2->execute([$userID]);
$messages = $stmt2->fetchAll();

$countStmt = $pdo->prepare($countQuery);
$countStmt->execute([$userID]);
$totalCount = $countStmt->fetch()['cnt'];

if (empty($messages)) {
    echo '#0:0:0';
    exit;
}

$msgStrings = [];
foreach ($messages as $m) {
    $age = time() - strtotime($m['timestamp']);
    $ageStr = formatAge($age);
    
    $encodedSubject = base64_encode(gzipEncode($m['subject']));
    $otherUserID = $getSent ? $m['receiverID'] : $m['senderID'];
    $otherName = $getSent ? ($m['receiverName'] ?? 'Unknown') : ($m['senderName'] ?? 'Unknown');
    
    // Format: sender_id:sender_name:message_id:subject:is_read:age:body:other_id:other_name
    $msgStrings[] = $m['senderID'] . '~' . ($m['senderName'] ?? 'Unknown') . '~' . $m['messageID'] . '~' . $encodedSubject . '~' . $m['isRead'] . '~' . $ageStr . '~' . $otherUserID . '~' . $otherName;
}

$pages = max(1, ceil($totalCount / $perPage));
echo implode('|', $msgStrings) . '#' . $totalCount . ':' . $offset . ':' . $pages;

function formatAge($seconds) {
    if ($seconds < 60) return $seconds . 's';
    if ($seconds < 3600) return round($seconds / 60) . 'm';
    if ($seconds < 86400) return round($seconds / 3600) . 'h';
    if ($seconds < 2592000) return round($seconds / 86400) . 'd';
    return round($seconds / 2592000) . 'w';
}
