<?php
// Setup script - run this to create database tables
require_once __DIR__ . '/../incl/lib.php';

echo "Setting up GDPS database...\n";

try {
    runSetup();
    echo "✅ Database tables created successfully!\n";
    echo "Tables created:\n";
    echo "  - users (player profiles)\n";
    echo "  - accounts (login accounts)\n";
    echo "  - levels (uploaded levels)\n";
    echo "  - comments (level/user comments)\n";
    echo "  - likes (like tracking)\n";
    echo "  - messages (private messages)\n";
    echo "  - friendRequests (friend requests)\n";
    echo "  - songs (custom songs)\n";
    echo "  - gauntlets (gauntlet levels)\n";
    echo "  - dailyFeatures (daily/weekly levels)\n";
    echo "  - ratings (level ratings)\n\n";
    echo "✅ GDPS is ready to play!\n";
    echo "\nServer URL: " . getServerURL() . "\n";
    echo "Admin Panel: " . getServerURL() . "/tools/admin.php\n";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
