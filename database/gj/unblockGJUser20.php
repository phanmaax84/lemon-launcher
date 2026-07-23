<?php
// Unblock user endpoint
require_once __DIR__ . '/../incl/lib.php';

header('Content-Type: text/plain');

$accountID = (int)($_POST['accountID'] ?? 0);
$gjp2 = $_POST['gjp2'] ?? '';
$targetAccountID = (int)($_POST['targetAccountID'] ?? 0);

if ($accountID <= 0 || $targetAccountID <= 0) die('-1');

// Delegate to block endpoint with isBlocked=0
$_POST['isBlocked'] = 0;
require __DIR__ . '/blockGJUser20.php';
