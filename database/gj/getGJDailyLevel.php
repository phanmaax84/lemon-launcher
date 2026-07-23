<?php
// Get daily/weekly level endpoint
require_once __DIR__ . '/../incl/lib.php';

header('Content-Type: text/plain');

$week = (int)($_POST['weekly'] ?? 0);
$accountID = (int)($_POST['accountID'] ?? 0);
$gjp2 = $_POST['gjp2'] ?? '';

$pdo = db();

if ($week) {
    // Weekly feature
    $stmt = $pdo->prepare("SELECT * FROM dailyFeatures WHERE isWeekly = 1 ORDER BY timestamp DESC LIMIT 1");
} else {
    // Daily feature
    $stmt = $pdo->prepare("SELECT * FROM dailyFeatures WHERE isWeekly = 0 ORDER BY timestamp DESC LIMIT 1");
}

$stmt->execute();
$feature = $stmt->fetch();

if (!$feature) {
    // Return a random featured level as fallback
    $randStmt = $pdo->prepare("SELECT levelID FROM levels WHERE starStars > 0 ORDER BY RAND() LIMIT 1");
    $randStmt->execute();
    $randLevel = $randStmt->fetch();
    
    if (!$randLevel) {
        // Return any level
        $anyStmt = $pdo->prepare("SELECT levelID FROM levels ORDER BY RAND() LIMIT 1");
        $anyStmt->execute();
        $anyLevel = $anyStmt->fetch();
        
        if (!$anyLevel) {
            echo '0';
            exit;
        }
        $featureID = 1;
        $levelID = $anyLevel['levelID'];
    } else {
        $featureID = 1;
        $levelID = $randLevel['levelID'];
    }
} else {
    $featureID = $feature['featureID'];
    $levelID = $feature['levelID'];
}

// Calculate time remaining until next daily/weekly
if ($feature) {
    $timestamp = strtotime($feature['timestamp']);
    if ($week) {
        $nextTime = $timestamp + 604800; // +1 week
    } else {
        $nextTime = $timestamp + 86400; // +1 day
    }
    $timeLeft = max(0, $nextTime - time());
} else {
    // Default: time until next day
    $timeLeft = strtotime('tomorrow') - time();
}

echo $levelID . '|' . $featureID . '|' . $timeLeft;
