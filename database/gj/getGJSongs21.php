<?php
// Get songs (search/reupload custom songs)
require_once __DIR__ . '/../incl/lib.php';

header('Content-Type: text/plain');

$songID = (int)($_POST['songID'] ?? 0);
$accountID = (int)($_POST['accountID'] ?? 0);

if ($songID <= 0) die('-1');

// This is the newgrounds song reupload - check if we have it
$pdo = db();

$stmt = $pdo->prepare("SELECT * FROM songs WHERE songID = ?");
$stmt->execute([$songID]);
$song = $stmt->fetch();

if ($song) {
    echo '~|~' . $song['songID'] . '~|~' . $song['name'] . '~|~1~|~' . $song['authorName'] . '~|~' . $song['downloadURL'] . '~|~' . $song['size'] . '~|~' . $song['duration'] . '~|~';
} else {
    // Try to reupload from newgrounds API
    $url = 'https://ngfiles.boomlings.com/data/redirect/' . $songID;
    $headers = @get_headers($url);
    
    if ($headers && strpos($headers[0], '200') !== false) {
        // Song exists on newgrounds - store basic info
        // Note: actual song streaming would need to proxy through the server
        $songName = 'Song ' . $songID;
        $author = 'Unknown';
        $songURL = $url;
        
        try {
            $stmt2 = $pdo->prepare("INSERT INTO songs (songID, name, authorName, downloadURL, size) VALUES (?, ?, ?, ?, ?)");
            $stmt2->execute([$songID, $songName, $author, $songURL, '0']);
            
            echo '~|~' . $songID . '~|~' . $songName . '~|~1~|~' . $author . '~|~' . $songURL . '~|~0~|~0~|~';
        } catch (Exception $e) {
            echo '~|~' . $songID . '~|~' . $songName . '~|~1~|~' . $author . '~|~' . $songURL . '~|~0~|~0~|~';
        }
    } else {
        die('-1');
    }
}
