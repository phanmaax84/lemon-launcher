<?php
// Get gauntlets endpoint
require_once __DIR__ . '/../incl/lib.php';

header('Content-Type: text/plain');

$accountID = (int)($_POST['accountID'] ?? 0);
$gjp2 = $_POST['gjp2'] ?? '';

$pdo = db();

$stmt = $pdo->query("SELECT * FROM gauntlets ORDER BY gauntletID");
$gauntlets = $stmt->fetchAll();

if (empty($gauntlets)) {
    echo 'game:1';
    exit;
}

$gauntletStrings = [];
foreach ($gauntlets as $g) {
    $levels = [];
    for ($i = 1; $i <= 5; $i++) {
        $levels[] = $g['level' . $i];
    }
    $gauntletStrings[] = '1:' . $g['gauntletID'] . ':3:' . implode(',', $levels);
}

echo implode('|', $gauntletStrings);
