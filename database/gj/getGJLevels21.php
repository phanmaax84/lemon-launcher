<?php
// Get levels list endpoint
require_once __DIR__ . '/../incl/lib.php';

header('Content-Type: text/plain');

$type = (int)($_POST['type'] ?? 0);
$str = $_POST['str'] ?? '';
$page = (int)($_POST['page'] ?? 0);
$diff = $_POST['diff'] ?? '-';
$len = $_POST['len'] ?? '-';
$total = $_POST['total'] ?? '0';
$star = $_POST['star'] ?? '';
$uncompleted = $_POST['uncompleted'] ?? '';
$onlyCompleted = $_POST['onlyCompleted'] ?? '';
$featured = $_POST['featured'] ?? '';
$original = $_POST['original'] ?? '';
$twoP = $_POST['twoPlayer'] ?? '';
$coins = $_POST['coins'] ?? '';
$epic = $_POST['epic'] ?? '';
$gauntlet = (int)($_POST['gauntlet'] ?? 0);
$accountID = (int)($_POST['accountID'] ?? 0);
$gjp2 = $_POST['gjp2'] ?? '';
$gameVersion = $_POST['gameVersion'] ?? '21';

$perPage = 10;
$offset = $page * $perPage;

$pdo = db();

$where = ['1=1'];
$params = [];

// Filter by type
switch ($type) {
    case 0: // Search
        if (!empty($str)) {
            if (is_numeric($str)) {
                $where[] = 'levelID = ?';
                $params[] = (int)$str;
            } else {
                $where[] = 'levelName LIKE ?';
                $params[] = '%' . $str . '%';
            }
        }
        break;
    case 1: // Most downloaded
        $orderBy = 'downloads DESC';
        break;
    case 2: // Most liked
        $orderBy = 'likes DESC';
        break;
    case 3: // Trending
        $orderBy = 'likes DESC';
        break;
    case 4: // Recent
        $orderBy = 'levelID DESC';
        break;
    case 5: // User's levels
        if (!empty($str)) {
            $userStmt = $pdo->prepare("SELECT userID FROM users WHERE userName = ?");
            $userStmt->execute([$str]);
            $userData = $userStmt->fetch();
            if ($userData) {
                $where[] = 'userID = ?';
                $params[] = $userData['userID'];
            } else {
                echo '#0:0:0:0';
                exit;
            }
        }
        $orderBy = 'levelID DESC';
        break;
    case 6: // Featured
        $where[] = 'isFeatured = 1';
        $orderBy = 'levelID DESC';
        break;
    case 7: // Magic / Hall of Fame
        $where[] = 'isFeatured = 1';
        $orderBy = 'likes DESC';
        break;
    case 11: // Awarded
        $where[] = 'starStars > 0';
        $orderBy = 'starStars DESC, levelID DESC';
        break;
    case 12: // Followed
        $orderBy = 'likes DESC';
        break;
    case 13: // Friends
        $orderBy = 'levelID DESC';
        break;
    case 16: // Hall of Fame
        $where[] = 'isEpic = 1';
        $orderBy = 'likes DESC';
        break;
    case 17: // Featured
        $where[] = 'isFeatured = 1';
        $orderBy = 'levelID DESC';
        break;
    default:
        $orderBy = 'levelID DESC';
}

// Apply difficulty filter
if (!empty($diff) && $diff !== '-') {
    if ($diff === '-1' || $diff === 'na') {
        $where[] = 'starStars = 0 AND starAuto = 0 AND starDemon = 0';
    } elseif ($diff === 'auto') {
        $where[] = 'starAuto = 1';
    } else {
        $diffs = explode(',', $diff);
        $diffConditions = [];
        foreach ($diffs as $d) {
            switch ($d) {
                case '10': $diffConditions[] = 'starStars BETWEEN 1 AND 2'; break;
                case '20': $diffConditions[] = 'starStars BETWEEN 3 AND 4'; break;
                case '30': $diffConditions[] = 'starStars BETWEEN 5 AND 6'; break;
                case '40': $diffConditions[] = 'starStars BETWEEN 7 AND 8'; break;
                case '50': $diffConditions[] = 'starStars >= 9'; break;
                case '50001': $diffConditions[] = 'starDemon = 1 AND starDemonDiff = 3'; break;
                case '50002': $diffConditions[] = 'starDemon = 1 AND starDemonDiff = 4'; break;
                case '50003': $diffConditions[] = 'starDemon = 1 AND starDemonDiff = 0'; break;
                case '50004': $diffConditions[] = 'starDemon = 1 AND starDemonDiff = 5'; break;
                case '50005': $diffConditions[] = 'starDemon = 1 AND starDemonDiff = 6'; break;
            }
        }
        if (!empty($diffConditions)) {
            $where[] = '(' . implode(' OR ', $diffConditions) . ')';
        }
    }
}

// Length filter
if (!empty($len) && $len !== '-') {
    $lengths = array_map('intval', explode(',', $len));
    $placeholders = implode(',', array_fill(0, count($lengths), '?'));
    $where[] = "levelLength IN ($placeholders)";
    $params = array_merge($params, $lengths);
}

// Star filter
if (!empty($star)) {
    $where[] = 'starStars > 0';
}

// Featured filter
if (!empty($featured)) {
    $where[] = 'isFeatured = 1';
}

// Epic filter
if (!empty($epic)) {
    $where[] = 'isEpic = 1';
}

// Coins filter
if (!empty($coins)) {
    $where[] = 'coins > 0';
}

// Gauntlet filter
if ($gauntlet > 0) {
    $stmt = $pdo->prepare("SELECT * FROM gauntlets WHERE gauntletID = ?");
    $stmt->execute([$gauntlet]);
    $gauntletData = $stmt->fetch();
    if ($gauntletData) {
        $levelIDs = [];
        for ($i = 1; $i <= 5; $i++) {
            if (!empty($gauntletData['level' . $i])) {
                $levelIDs[] = $gauntletData['level' . $i];
            }
        }
        if (!empty($levelIDs)) {
            $placeholders = implode(',', array_fill(0, count($levelIDs), '?'));
            $where[] = "levelID IN ($placeholders)";
            $params = array_merge($params, $levelIDs);
        }
    }
}

if (!isset($orderBy)) $orderBy = 'levelID DESC';

$whereStr = implode(' AND ', $where);

// Count total
$countStmt = $pdo->prepare("SELECT COUNT(*) as cnt FROM levels WHERE $whereStr");
$countStmt->execute($params);
$totalCount = $countStmt->fetch()['cnt'];

// Get levels
$query = "SELECT * FROM levels WHERE $whereStr ORDER BY $orderBy LIMIT $perPage OFFSET $offset";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$levels = $stmt->fetchAll();

// Get user data for levels
$userIDs = array_unique(array_column($levels, 'userID'));
$users = [];
if (!empty($userIDs)) {
    $placeholders = implode(',', array_fill(0, count($userIDs), '?'));
    $userStmt = $pdo->prepare("SELECT * FROM users WHERE userID IN ($placeholders)");
    $userStmt->execute($userIDs);
    foreach ($userStmt->fetchAll() as $u) {
        $users[$u['userID']] = $u;
    }
}

// Build response
if (empty($levels)) {
    echo '#0:0:0:0';
    exit;
}

$levelStrings = [];
$userStrings = [];
$userSeen = [];

foreach ($levels as $level) {
    $levelStrings[] = formatLevelData($level);
    
    if (!empty($level['userID']) && !isset($userSeen[$level['userID']])) {
        $userSeen[$level['userID']] = true;
        if (isset($users[$level['userID']])) {
            $u = $users[$level['userID']];
            $userStrings[] = $u['userID'] . ':' . $u['userName'] . ':' . $u['userID'];
        }
    }
}

$response = implode('|', $levelStrings);

// Creator hash section (empty for simplicity)
$response .= '#';

// User section
$response .= implode('|', $userStrings);

// Song section
$response .= '#';

// Social section (empty)
$response .= '0|0|0|0|0|0|0|0|0|0';

// Page info
$pages = max(1, ceil($totalCount / $perPage));
$response .= '#' . $totalCount . ':' . ($offset) . ':' . $pages;

echo $response;
