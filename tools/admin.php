<?php
// Admin panel for GDPS
require_once __DIR__ . '/../incl/lib.php';

// Simple auth - change this password!
$ADMIN_PASSWORD = getenv('ADMIN_PASSWORD') ?: 'admin123';

session_start();

if (isset($_POST['logout'])) {
    unset($_SESSION['admin_logged_in']);
    header('Location: ?');
    exit;
}

// Login check
if (isset($_POST['password']) && $_POST['password'] === $ADMIN_PASSWORD) {
    $_SESSION['admin_logged_in'] = true;
}

$loggedIn = $_SESSION['admin_logged_in'] ?? false;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GDPS Admin Panel</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #1a1a2e; color: #eee; margin: 0; padding: 20px; }
        .container { max-width: 1000px; margin: 0 auto; }
        h1 { color: #e94560; text-align: center; }
        .login-box { max-width: 400px; margin: 100px auto; background: #16213e; padding: 30px; border-radius: 10px; }
        .login-box input { width: 100%; padding: 10px; margin: 10px 0; border: 1px solid #0f3460; border-radius: 5px; background: #1a1a2e; color: #eee; }
        .login-box button { width: 100%; padding: 10px; background: #e94560; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; }
        .panel { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top: 20px; }
        .card { background: #16213e; padding: 20px; border-radius: 10px; }
        .card h3 { color: #e94560; margin-top: 0; }
        .btn { padding: 8px 16px; background: #e94560; color: white; border: none; border-radius: 5px; cursor: pointer; margin: 5px; }
        .btn-danger { background: #dc3545; }
        .btn-success { background: #28a745; }
        .btn-info { background: #17a2b8; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { padding: 8px; text-align: left; border-bottom: 1px solid #0f3460; }
        th { color: #e94560; }
        input, select, textarea { padding: 8px; border: 1px solid #0f3460; border-radius: 5px; background: #1a1a2e; color: #eee; margin: 5px 0; }
        .full-width { grid-column: 1 / -1; }
        .stats { display: flex; gap: 20px; justify-content: center; margin: 20px 0; }
        .stat { background: #0f3460; padding: 15px 25px; border-radius: 10px; text-align: center; }
        .stat .number { font-size: 24px; font-weight: bold; color: #e94560; }
        .stat .label { font-size: 12px; color: #999; }
        .message { padding: 10px; margin: 10px 0; border-radius: 5px; }
        .message-success { background: #28a745; }
        .message-error { background: #dc3545; }
        a { color: #e94560; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🍋 Lemon GDPS Admin Panel</h1>
        
        <?php if (!$loggedIn): ?>
            <div class="login-box">
                <form method="post">
                    <h3 style="text-align:center">Admin Login</h3>
                    <input type="password" name="password" placeholder="Admin Password" required>
                    <button type="submit">Login</button>
                </form>
                <p style="text-align:center;color:#666;margin-top:15px">Default: admin123 (change in environment!)</p>
            </div>
        <?php else: ?>
            <?php
            $pdo = db();
            
            // Handle actions
            $actionMsg = '';
            
            if (isset($_POST['action'])) {
                switch ($_POST['action']) {
                    case 'rateLevel':
                        $levelID = (int)$_POST['levelID'];
                        $stars = (int)$_POST['stars'];
                        $featured = (int)($_POST['featured'] ?? 0);
                        $epic = (int)($_POST['epic'] ?? 0);
                        $demon = (int)($_POST['demon'] ?? 0);
                        $auto = (int)($_POST['auto'] ?? 0);
                        $diff = (int)($_POST['difficulty'] ?? 0);
                        
                        $pdo->prepare("UPDATE levels SET starStars = ?, starFeatured = ?, isFeatured = ?, isEpic = ?, starDemon = ?, starAuto = ?, starDifficulty = ? WHERE levelID = ?")
                            ->execute([$stars, $featured, $featured, $epic, $demon, $auto, $diff, $levelID]);
                        $actionMsg = '<div class="message message-success">Level rated successfully!</div>';
                        break;
                        
                    case 'featureLevel':
                        $levelID = (int)$_POST['levelID'];
                        $pdo->prepare("UPDATE levels SET isFeatured = 1, starFeatured = 1 WHERE levelID = ?")->execute([$levelID]);
                        $actionMsg = '<div class="message message-success">Level featured!</div>';
                        break;
                        
                    case 'setDaily':
                        $levelID = (int)$_POST['levelID'];
                        $isWeekly = (int)($_POST['isWeekly'] ?? 0);
                        $pdo->prepare("INSERT INTO dailyFeatures (levelID, isWeekly) VALUES (?, ?)")->execute([$levelID, $isWeekly]);
                        $actionMsg = '<div class="message message-success">Daily level set!</div>';
                        break;
                        
                    case 'banUser':
                        $userID = (int)$_POST['userID'];
                        $pdo->prepare("UPDATE users SET isBanned = 1 WHERE userID = ?")->execute([$userID]);
                        $actionMsg = '<div class="message message-success">User banned!</div>';
                        break;
                        
                    case 'unbanUser':
                        $userID = (int)$_POST['userID'];
                        $pdo->prepare("UPDATE users SET isBanned = 0 WHERE userID = ?")->execute([$userID]);
                        $actionMsg = '<div class="message message-success">User unbanned!</div>';
                        break;
                        
                    case 'deleteLevel':
                        $levelID = (int)$_POST['levelID'];
                        $pdo->prepare("DELETE FROM levels WHERE levelID = ?")->execute([$levelID]);
                        $pdo->prepare("DELETE FROM comments WHERE levelID = ?")->execute([$levelID]);
                        $actionMsg = '<div class="message message-success">Level deleted!</div>';
                        break;
                        
                    case 'makeAdmin':
                        $accountID = (int)$_POST['accountID'];
                        $pdo->prepare("UPDATE accounts SET isAdmin = 1 WHERE accountID = ?")->execute([$accountID]);
                        $actionMsg = '<div class="message message-success">User made admin!</div>';
                        break;
                }
            }
            
            // Get stats
            $stats = [];
            $stats['users'] = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
            $stats['levels'] = $pdo->query("SELECT COUNT(*) FROM levels")->fetchColumn();
            $stats['comments'] = $pdo->query("SELECT COUNT(*) FROM comments")->fetchColumn();
            $stats['messages'] = $pdo->query("SELECT COUNT(*) FROM messages")->fetchColumn();
            
            // Handle tab
            $tab = $_GET['tab'] ?? 'dashboard';
            ?>
            
            <div class="stats">
                <div class="stat"><div class="number"><?= $stats['users'] ?></div><div class="label">USERS</div></div>
                <div class="stat"><div class="number"><?= $stats['levels'] ?></div><div class="label">LEVELS</div></div>
                <div class="stat"><div class="number"><?= $stats['comments'] ?></div><div class="label">COMMENTS</div></div>
                <div class="stat"><div class="number"><?= $stats['messages'] ?></div><div class="label">MESSAGES</div></div>
            </div>
            
            <div style="text-align:center;margin:10px">
                <a href="?tab=dashboard" class="btn">Dashboard</a>
                <a href="?tab=levels" class="btn">Levels</a>
                <a href="?tab=users" class="btn">Users</a>
                <a href="?tab=rate" class="btn btn-info">Rate Level</a>
                <a href="?tab=daily" class="btn btn-info">Set Daily</a>
                <form method="post" style="display:inline"><button type="submit" name="logout" value="1" class="btn btn-danger">Logout</button></form>
            </div>
            
            <?= $actionMsg ?>
            
            <div class="panel">
                <?php if ($tab === 'dashboard'): ?>
                <div class="card full-width">
                    <h3>📊 Dashboard</h3>
                    <p>Welcome to the Lemon GDPS Admin Panel!</p>
                    <p><strong>Server URL:</strong> <?= getServerURL() ?></p>
                    <p><strong>Players should use this URL as their custom server in Geometry Dash.</strong></p>
                </div>
                
                <?php elseif ($tab === 'levels'): ?>
                <div class="card full-width">
                    <h3>🎮 Levels (Latest 50)</h3>
                    <table>
                        <tr><th>ID</th><th>Name</th><th>Creator</th><th>Stars</th><th>Likes</th><th>Featured</th><th>Actions</th></tr>
                        <?php
                        $levels = $pdo->query("SELECT l.*, u.userName FROM levels l LEFT JOIN users u ON l.userID = u.userID ORDER BY l.levelID DESC LIMIT 50")->fetchAll();
                        foreach ($levels as $l):
                        ?>
                        <tr>
                            <td><?= $l['levelID'] ?></td>
                            <td><?= htmlspecialchars($l['levelName']) ?></td>
                            <td><?= htmlspecialchars($l['userName'] ?? 'Unknown') ?></td>
                            <td><?= $l['starStars'] ?></td>
                            <td><?= $l['likes'] ?></td>
                            <td><?= $l['isFeatured'] ? '⭐' : '' ?></td>
                            <td>
                                <form method="post" style="display:inline">
                                    <input type="hidden" name="action" value="featureLevel">
                                    <input type="hidden" name="levelID" value="<?= $l['levelID'] ?>">
                                    <button class="btn btn-info" type="submit">Feature</button>
                                </form>
                                <form method="post" style="display:inline" onsubmit="return confirm('Delete?')">
                                    <input type="hidden" name="action" value="deleteLevel">
                                    <input type="hidden" name="levelID" value="<?= $l['levelID'] ?>">
                                    <button class="btn btn-danger" type="submit">Delete</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                </div>
                
                <?php elseif ($tab === 'users'): ?>
                <div class="card full-width">
                    <h3>👥 Users (Latest 50)</h3>
                    <table>
                        <tr><th>ID</th><th>Username</th><th>Stars</th><th>Demons</th><th>Admin</th><th>Banned</th><th>Actions</th></tr>
                        <?php
                        $users = $pdo->query("SELECT * FROM users ORDER BY userID DESC LIMIT 50")->fetchAll();
                        foreach ($users as $u):
                        ?>
                        <tr>
                            <td><?= $u['userID'] ?></td>
                            <td><?= htmlspecialchars($u['userName']) ?></td>
                            <td><?= $u['stars'] ?></td>
                            <td><?= $u['demons'] ?></td>
                            <td><?= $u['isAdmin'] ? '🔑' : '' ?></td>
                            <td><?= $u['isBanned'] ? '🚫' : '' ?></td>
                            <td>
                                <?php if (!$u['isBanned']): ?>
                                <form method="post" style="display:inline">
                                    <input type="hidden" name="action" value="banUser">
                                    <input type="hidden" name="userID" value="<?= $u['userID'] ?>">
                                    <button class="btn btn-danger" type="submit">Ban</button>
                                </form>
                                <?php else: ?>
                                <form method="post" style="display:inline">
                                    <input type="hidden" name="action" value="unbanUser">
                                    <input type="hidden" name="userID" value="<?= $u['userID'] ?>">
                                    <button class="btn btn-success" type="submit">Unban</button>
                                </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                </div>
                
                <?php elseif ($tab === 'rate'): ?>
                <div class="card full-width">
                    <h3>⭐ Rate Level</h3>
                    <form method="post">
                        <input type="hidden" name="action" value="rateLevel">
                        <label>Level ID: <input type="number" name="levelID" required></label>
                        <label>Stars: <input type="number" name="stars" min="0" max="10" value="0"></label>
                        <label>Difficulty: 
                            <select name="difficulty">
                                <option value="0">N/A</option>
                                <option value="10">Easy</option>
                                <option value="20">Normal</option>
                                <option value="30">Hard</option>
                                <option value="40">Harder</option>
                                <option value="50">Insane</option>
                            </select>
                        </label>
                        <label><input type="checkbox" name="featured" value="1"> Featured</label>
                        <label><input type="checkbox" name="epic" value="1"> Epic</label>
                        <label><input type="checkbox" name="demon"> Demon</label>
                        <label><input type="checkbox" name="auto"> Auto</label>
                        <br><br>
                        <button class="btn btn-success" type="submit">Rate Level</button>
                    </form>
                </div>
                
                <?php elseif ($tab === 'daily'): ?>
                <div class="card full-width">
                    <h3>📅 Set Daily/Weekly Level</h3>
                    <form method="post">
                        <input type="hidden" name="action" value="setDaily">
                        <label>Level ID: <input type="number" name="levelID" required></label>
                        <label><input type="checkbox" name="isWeekly" value="1"> Weekly (default: Daily)</label>
                        <br><br>
                        <button class="btn btn-success" type="submit">Set Level</button>
                    </form>
                </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
