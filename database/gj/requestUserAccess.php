<?php
// Request user access (account sync check)
require_once __DIR__ . '/../incl/lib.php';

header('Content-Type: text/plain');

$accountID = (int)($_POST['accountID'] ?? 0);
$gjp2 = $_POST['gjp2'] ?? '';
$udid = $_POST['udid'] ?? '';

if ($accountID <= 0) die('-1');

$pdo = db();

$stmt = $pdo->prepare("SELECT * FROM accounts WHERE accountID = ?");
$stmt->execute([$accountID]);
$account = $stmt->fetch();

if (!$account) die('-1');

// Just confirm access
echo 1;
