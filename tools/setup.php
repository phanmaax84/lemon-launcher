<?php
require_once __DIR__ . '/../incl/lib.php';
header('Content-Type: text/html; charset=utf-8');
echo "<h2>🍋 GDPS Setup</h2>";
try {
    $pdo = db();
    $tables = ['ratings','dailyFeatures','gauntlets','songs','friendRequests','messages','likes','comments','levels','accounts','users'];
    if (getDbType() === 'mysql') $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
    foreach ($tables as $t) { $pdo->exec("DROP TABLE IF EXISTS $t"); echo "DROP $t ✓<br>"; }
    if (getDbType() === 'mysql') $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
    runSetup();
    echo "<p style='color:green'>✅ Готово! Все таблицы созданы.</p>";
    echo "<a href='/index.php'>→ Панель управления</a>";
} catch (Exception $e) {
    echo "<p style='color:red'>❌ " . $e->getMessage() . "</p>";
}
