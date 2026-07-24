<?php
require_once __DIR__ . '/../incl/lib.php';
echo "<h2>GDPS Setup</h2>";
try {
    $pdo = db();
    $tables = ['ratings','dailyFeatures','gauntlets','songs','friendRequests','messages','likes','comments','levels','accounts','users'];
    try { $pdo->exec("SET FOREIGN_KEY_CHECKS = 0"); } catch(Exception $e) {}
    foreach ($tables as $t) { $pdo->exec("DROP TABLE IF EXISTS $t"); echo "DROP $t<br>"; }
    try { $pdo->exec("SET FOREIGN_KEY_CHECKS = 1"); } catch(Exception $e) {}
    runSetup();
    echo "<h3 style='color:green'>OK! Tables created.</h3>";
    echo "<a href='/index.php'>Go to admin panel</a>";
} catch (Exception $e) {
    echo "<h3 style='color:red'>ERROR: " . $e->getMessage() . "</h3>";
}
