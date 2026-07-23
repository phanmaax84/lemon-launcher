<?php
// Get songs endpoint (custom songs)
require_once __DIR__ . '/../incl/lib.php';

header('Content-Type: text/plain');

$songID = (int)($_POST['songID'] ?? 0);

if ($songID <= 0) die('-1');

$pdo = db();

$stmt = $pdo->prepare("SELECT * FROM songs WHERE songID = ?");
$stmt->execute([$songID]);
$song = $stmt->fetch();

if (!$song) die('-1');

// Response format for custom song info
echo '~|~' . $song['songID'] . '~|~' . $song['name'] . '~|~1~|~' . $song['authorName'] . '~|~' . $song['downloadURL'] . '~|~' . $song['size'] . '~|~' . $song['duration'] . '~|~';
