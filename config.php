<?php
// GDPS Configuration
// Most settings come from environment variables on Render

// Server settings
define('SERVER_NAME', getenv('SERVER_NAME') ?: 'Lemon GDPS');
define('SERVER_DESCRIPTION', getenv('SERVER_DESC') ?: 'A Geometry Dash Private Server hosted on Render');

// Database settings (from Render environment)
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_PORT', getenv('DB_PORT') ?: '3306');
define('DB_NAME', getenv('DB_NAME') ?: 'gdps');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');

// Admin
define('ADMIN_PASSWORD', getenv('ADMIN_PASSWORD') ?: 'admin123');

// Game settings
define('MAX_LEVEL_SIZE', 1024 * 1024 * 5); // 5MB max level data
define('ALLOW_REGISTRATION', true);
define('ALLOW_UPLOAD', true);
define('AUTO_RATE_LEVELS', true); // Auto-rate levels based on requested stars
