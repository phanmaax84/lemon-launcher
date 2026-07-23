<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lemon GDPS - Geometry Dash Private Server</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            color: #fff;
        }
        
        .hero {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 20px;
            text-align: center;
        }
        
        .logo {
            font-size: 80px;
            margin-bottom: 10px;
            animation: bounce 2s infinite;
        }
        
        @keyframes bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-20px); }
        }
        
        h1 {
            font-size: 48px;
            margin-bottom: 10px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }
        
        .subtitle {
            font-size: 20px;
            opacity: 0.9;
            margin-bottom: 30px;
        }
        
        .server-url {
            background: rgba(255,255,255,0.2);
            backdrop-filter: blur(10px);
            padding: 20px 40px;
            border-radius: 15px;
            margin: 20px 0;
            border: 2px solid rgba(255,255,255,0.3);
        }
        
        .server-url label {
            display: block;
            font-size: 14px;
            opacity: 0.8;
            margin-bottom: 8px;
        }
        
        .server-url .url {
            font-size: 22px;
            font-weight: bold;
            word-break: break-all;
            cursor: pointer;
            padding: 5px 10px;
            border-radius: 5px;
            transition: background 0.3s;
        }
        
        .server-url .url:hover {
            background: rgba(255,255,255,0.1);
        }
        
        .copy-btn {
            margin-top: 10px;
            padding: 8px 20px;
            background: #ffd93d;
            color: #333;
            border: none;
            border-radius: 25px;
            cursor: pointer;
            font-size: 14px;
            font-weight: bold;
            transition: transform 0.2s;
        }
        
        .copy-btn:hover {
            transform: scale(1.05);
        }
        
        .copy-btn.copied {
            background: #4CAF50;
            color: white;
        }
        
        .instructions {
            background: rgba(255,255,255,0.1);
            backdrop-filter: blur(10px);
            padding: 30px;
            border-radius: 15px;
            margin-top: 30px;
            max-width: 600px;
            text-align: left;
        }
        
        .instructions h2 {
            margin-bottom: 15px;
            color: #ffd93d;
        }
        
        .instructions ol {
            padding-left: 20px;
        }
        
        .instructions li {
            margin: 10px 0;
            line-height: 1.6;
        }
        
        .instructions a {
            color: #ffd93d;
        }
        
        .links {
            margin-top: 30px;
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            justify-content: center;
        }
        
        .links a {
            padding: 12px 24px;
            background: rgba(255,255,255,0.2);
            color: white;
            text-decoration: none;
            border-radius: 25px;
            border: 1px solid rgba(255,255,255,0.3);
            transition: all 0.3s;
        }
        
        .links a:hover {
            background: rgba(255,255,255,0.3);
            transform: translateY(-2px);
        }
        
        .stats-bar {
            display: flex;
            gap: 30px;
            margin: 20px 0;
        }
        
        .stat-item {
            text-align: center;
        }
        
        .stat-item .number {
            font-size: 28px;
            font-weight: bold;
        }
        
        .stat-item .label {
            font-size: 12px;
            opacity: 0.7;
        }
        
        footer {
            margin-top: 40px;
            opacity: 0.6;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="hero">
        <div class="logo">🍋</div>
        <h1>Lemon GDPS</h1>
        <p class="subtitle">Geometry Dash Private Server - Hosted on Render</p>
        
        <div class="server-url">
            <label>SERVER URL (for Geometry Dash):</label>
            <div class="url" id="serverUrl" onclick="copyURL()"></div>
            <button class="copy-btn" id="copyBtn" onclick="copyURL()">📋 Copy URL</button>
        </div>
        
        <div class="stats-bar">
            <?php
            require_once __DIR__ . '/incl/database.php';
            try {
                $pdo = db();
                $users = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
                $levels = $pdo->query("SELECT COUNT(*) FROM levels")->fetchColumn();
                echo "<div class='stat-item'><div class='number'>$users</div><div class='label'>PLAYERS</div></div>";
                echo "<div class='stat-item'><div class='number'>$levels</div><div class='label'>LEVELS</div></div>";
            } catch (Exception $e) {
                echo "<div class='stat-item'><div class='number'>0</div><div class='label'>PLAYERS</div></div>";
                echo "<div class='stat-item'><div class='number'>0</div><div class='label'>LEVELS</div></div>";
            }
            ?>
        </div>
        
        <div class="instructions">
            <h2>🎮 How to Play</h2>
            <ol>
                <li>Copy the server URL above</li>
                <li>Open Geometry Dash</li>
                <li>Use a <strong>GDPS launcher</strong> (like Lemon Launcher) or modify your hosts file</li>
                <li>Paste the server URL as the custom server address</li>
                <li>Create a new account (separate from your main GD account)</li>
                <li>Start playing! 🎉</li>
            </ol>
            <p style="margin-top:15px;font-size:14px;opacity:0.8">
                💡 <strong>Tip:</strong> For mobile users, you can use apps that allow custom GD servers.<br>
                ⚠️ This is a private server - your progress here is separate from the official Geometry Dash.
            </p>
        </div>
        
        <div class="links">
            <a href="tools/admin.php">🔧 Admin Panel</a>
            <a href="tools/setup.php">📦 Run Setup</a>
        </div>
        
        <footer>
            <p>Lemon GDPS &copy; <?= date('Y') ?> | Powered by PHP + MySQL</p>
        </footer>
    </div>
    
    <script>
        // Set server URL
        document.getElementById('serverUrl').textContent = window.location.origin;
        
        function copyURL() {
            const url = window.location.origin;
            navigator.clipboard.writeText(url).then(() => {
                const btn = document.getElementById('copyBtn');
                btn.textContent = '✅ Copied!';
                btn.classList.add('copied');
                setTimeout(() => {
                    btn.textContent = '📋 Copy URL';
                    btn.classList.remove('copied');
                }, 2000);
            });
        }
    </script>
</body>
</html>
