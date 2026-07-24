<?php
header('Content-Type: text/plain');
echo "=== GD DEBUG ===\n";
echo "METHOD: " . $_SERVER['REQUEST_METHOD'] . "\n\n";
echo "POST DATA:\n";
foreach ($_POST as $k => $v) {
    echo "  $k = $v\n";
}
echo "\nGET DATA:\n";
foreach ($_GET as $k => $v) {
    echo "  $k = $v\n";
}
echo "\n=== END ===";
