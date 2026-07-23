<?php
// Upload comment endpoint
require_once __DIR__ . '/../incl/lib.php';

header('Content-Type: text/plain');

$accountID = (int)($_POST['accountID'] ?? 0);
$gjp2 = $_POST['gjp2'] ?? '';
$comment = $_POST['comment'] ?? '';
$levelID = (int)($_POST['levelID'] ?? 0);
$percent = (int)($_POST['percent'] ?? 0);

if ($accountID <= 0 || empty($comment)) die('-1');

$pdo = db();

// Verify account
$stmt = $pdo->prepare("SELECT * FROM accounts WHERE accountID = ?");
$stmt->execute([$accountID]);
$account = $stmt->fetch();

if (!$account || $account['isBanned']) die('-1');

$userID = $account['userID'];
if (!$userID) $userID = $account['accountID'];

// Decode comment (base64 encoded)
$decodedComment = base64_decode($comment);
if ($decodedComment === false) $decodedComment = $comment;

// Clean the comment
$cleanComment = htmlspecialchars(substr($decodedComment, 0, 500), ENT_QUOTES, 'UTF-8');

try {
    $stmt2 = $pdo->prepare("INSERT INTO comments (userID, levelID, comment) VALUES (?, ?, ?)");
    $stmt2->execute([$userID, $levelID, $cleanComment]);
    
    echo 1;
} catch (Exception $e) {
    die('-1');
}
