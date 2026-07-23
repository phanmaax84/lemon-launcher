<?php
// Get user info endpoint
require_once __DIR__ . '/../incl/lib.php';

header('Content-Type: text/plain');

$targetAccountID = (int)($_POST['targetAccountID'] ?? 0);
$accountID = (int)($_POST['accountID'] ?? 0);
$gjp2 = $_POST['gjp2'] ?? '';
$gameVersion = $_POST['gameVersion'] ?? '';

if ($targetAccountID <= 0) die('-1');

$pdo = db();

// Find account and user
$stmt = $pdo->prepare("SELECT * FROM accounts WHERE accountID = ?");
$stmt->execute([$targetAccountID]);
$account = $stmt->fetch();

if (!$account) die('-1');

$userID = $account['userID'];
if (!$userID) $userID = $account['accountID'];

$user = getUser($userID);
if (!$user) die('-1');

echo formatUserInfo($user, $account);
