<?php
// Get level scores (leaderboard)
require_once __DIR__ . '/../incl/lib.php';

header('Content-Type: text/plain');

$accountID = (int)($_POST['accountID'] ?? 0);
$gjp2 = $_POST['gjp2'] ?? '';
$levelID = (int)($_POST['levelID'] ?? 0);
$type = $_POST['type'] ?? 'top'; // top, friends, weekly

if ($levelID <= 0) die('-1');

$pdo = db();

// For simplicity, return the top scores for this level
// In a full implementation, we'd track attempts and scores
// For now return a minimal response

// Get level info
$stmt = $pdo->prepare("SELECT * FROM levels WHERE levelID = ?");
$stmt->execute([$levelID]);
$level = $stmt->fetch();

if (!$level) die('-1');

// Return empty scores for now (just the level)
// Format: score1|score2|...#total:offset:count
echo '1#0:0:0';
