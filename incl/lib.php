<?php
// Core library functions for GDPS

require_once __DIR__ . '/database.php';

// GD XOR key for password encoding
define('XOR_KEY_PASS', '37526');
define('XOR_KEY_MSG', '29482');
define('XOR_KEY_LEVEL', '26364');
define('XOR_KEY_COMMENT', '29482');
define('XOR_KEY_LEVELDESC', '26364');

// XOR encrypt/decrypt
function xorCipher($text, $key) {
    $result = '';
    for ($i = 0; $i < strlen($text); $i++) {
        $result .= chr(ord($text[$i]) ^ ord($key[$i % strlen($key)]));
    }
    return $result;
}

// Hash check for various GD operations
function hashLevel($levelID, $stars, $isFeatured, $level) {
    $str = $levelID . $level['likes'] . $level['downloads'] . ($stars > 0 ? $stars : '0') . $isFeatured;
    return sha1($str . 'xI25fpAapCQg');
}

function hashProfile($userData) {
    $str = $userData['userName'] . $userData['userID'] . 'secret';
    return sha1($str);
}

function hashComment($commentID, $userID, $levelID, $percent) {
    $str = $userID . $levelID . $commentID . $percent . '0xPT6iUrtl0J6fp';
    return sha1($str);
}

function hashLike($itemID, $type, $isLike, $userID, $udid) {
    $str = $itemID . $type . ($isLike ? 1 : 0) . $userID . $udid . 'ysg6pUrtfd0R';
    return sha1($str);
}

function hashRate($levelID, $stars, $demon, $auto, $diff, $userID, $udid) {
    $str = $levelID . $stars . $demon . $auto . $diff . $userID . $udid . 'ysg6pUrtfd0R';
    return sha1($str);
}

function hashSuggest($levelID, $stars, $feature, $auto, $demon) {
    $str = $levelID . $stars . $feature . $auto . $demon;
    return sha1($str . 'xI25fpAapCQg');
}

// Get level difficulty string
function getDifficultyString($stars, $demon, $auto, $diff) {
    if ($auto) return 'auto';
    if ($demon) {
        $demonDiffs = ['', 'Easy Demon', 'Medium Demon', 'Hard Demon', 'Insane Demon', 'Extreme Demon'];
        return $demonDiffs[$diff] ?? 'Hard Demon';
    }
    if ($stars == 0) return 'N/A';
    if ($stars <= 2) return 'Easy';
    if ($stars <= 4) return 'Normal';
    if ($stars <= 6) return 'Hard';
    if ($stars <= 8) return 'Harder';
    return 'Insane';
}

// Get numeric difficulty
function getDifficultyNum($stars, $demon, $auto, $diff) {
    if ($auto) return 50000;
    if ($demon) {
        $demonDiffs = [0, 50001, 50002, 50003, 50004, 50005];
        return $demonDiffs[$diff] ?? 50003;
    }
    if ($stars == 0) return 0;
    if ($stars <= 2) return 10;
    if ($stars <= 4) return 20;
    if ($stars <= 6) return 30;
    if ($stars <= 8) return 40;
    return 50;
}

// Get face ID for difficulty display
function getFaceID($diff) {
    $faces = [
        0 => 0, 10 => 1, 20 => 2, 30 => 3, 40 => 4, 50 => 5,
        50000 => 12, 50001 => 6, 50002 => 7, 50003 => 8, 50004 => 9, 50005 => 10
    ];
    return $faces[$diff] ?? 0;
}

// Encode level description for GD
function encodeLevelDesc($desc) {
    if (empty($desc)) return '';
    $encoded = base64_encode(gzipEncode($desc));
    return $encoded;
}

function decodeLevelDesc($encoded) {
    if (empty($encoded)) return '';
    $decoded = @base64_decode($encoded);
    if ($decoded === false) return $encoded;
    $decompressed = @gzdecode($decoded);
    return $decompressed !== false ? $decompressed : $encoded;
}

function gzipEncode($data) {
    return gzencode($data);
}

// Validate and sanitize input
function validateInput($input, $maxLength = 255) {
    return substr(htmlspecialchars(strip_tags($input), ENT_QUOTES, 'UTF-8'), 0, $maxLength);
}

// Generate a random salt
function generateSalt($length = 16) {
    return bin2hex(random_bytes($length));
}

// Hash password for game accounts
function hashGamePassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

// Verify game password
function verifyGamePassword($password, $hash) {
    return password_verify($password, $hash);
}

// Get account from username and password
function getAccount($username, $password) {
    $pdo = db();
    $stmt = $pdo->prepare("SELECT * FROM accounts WHERE userName = ?");
    $stmt->execute([$username]);
    $account = $stmt->fetch();
    
    if (!$account) return false;
    if (!verifyGamePassword($password, $account['password'])) return false;
    
    return $account;
}

// Get user data
function getUser($userID) {
    $pdo = db();
    $stmt = $pdo->prepare("SELECT * FROM users WHERE userID = ?");
    $stmt->execute([$userID]);
    return $stmt->fetch();
}

function getUserByName($userName) {
    $pdo = db();
    $stmt = $pdo->prepare("SELECT * FROM users WHERE userName = ?");
    $stmt->execute([$userName]);
    return $stmt->fetch();
}

// Format level for GD response
function formatLevelData($level) {
    $desc = '';
    if (!empty($level['levelDescription'])) {
        $desc = $level['levelDescription'];
        // Already base64 encoded when stored
        if (base64_decode($desc, true) === false) {
            $desc = base64_encode(gzipEncode($desc));
        }
    }
    
    $parts = [
        1 => $level['levelID'],
        2 => $level['levelName'],
        3 => $desc,
        4 => $level['levelString'],
        5 => $level['levelVersion'],
        6 => $level['userID'],
        8 => $level['likes'],
        9 => getDifficultyNum($level['starStars'], $level['starDemon'], $level['starAuto'], $level['starDifficulty']),
        10 => $level['downloads'],
        11 => $level['starStars'] ?? 0,
        12 => $level['audioTrack'],
        13 => $level['audioTrack'],
        15 => $level['levelLength'],
        17 => ($level['starDemon'] ?? 0) ? 1 : 0,
        18 => ($level['starStars'] ?? 0) > 0 ? 1 : 0,
        19 => $level['starFeatured'] ?? 0,
        25 => ($level['starAuto'] ?? 0) ? 1 : 0,
        28 => $level['uploadDateInt'] ?? 0,
        29 => $level['updateDateInt'] ?? 0,
        30 => $level['levelVersion'],
        31 => $level['isFeatured'] ?? 0,
        35 => $level['songID'] ?? 0,
        36 => $level['songID'] ?? 0,
        37 => $level['coins'] ?? 0,
        38 => $level['starCoins'] ?? 0,
        39 => $level['requestedStars'] ?? 0,
        40 => $level['twoPlayer'] ?? 0,
        41 => $level['ldm'] ?? 0,
        42 => $level['isEpic'] ?? 0,
        43 => $level['starDemonDiff'] ?? 0,
        45 => $level['unlisted'] ?? 0,
        46 => 0,
        47 => $level['objects'] ?? 0,
        48 => 0,
        49 => 0,
        50 => $level['wt'] ?? 0,
        51 => 0,
        52 => '',
        53 => '',
        54 => '',
    ];
    
    $result = '';
    foreach ($parts as $key => $value) {
        $result .= $key . ':' . $value . ':';
    }
    return rtrim($result, ':');
}

// Format level list response
function formatLevelListResponse($levels, $page, $total, $users = []) {
    if (empty($levels)) {
        return '#0:0:0:0';
    }
    
    $levelStrings = [];
    $userStrings = [];
    $userSeen = [];
    $songStrings = [];
    $songSeen = [];
    $creatorStrings = [];
    
    foreach ($levels as $level) {
        $levelStrings[] = formatLevelData($level);
        
        // Collect user info
        if (!empty($level['userID']) && !isset($userSeen[$level['userID']])) {
            $userSeen[$level['userID']] = true;
            if (isset($users[$level['userID']])) {
                $u = $users[$level['userID']];
                $userStrings[] = $u['userID'] . ':' . $u['userName'] . ':' . $u['userID'];
            }
        }
    }
    
    $response = implode('|', $levelStrings);
    $response .= '#' . implode('|', $creatorStrings);
    $response .= '#' . implode('|', $userStrings);
    
    // Social section (friend requests etc) - empty for now
    $response .= '#0|0|0|0|0|0|0|0|0|0';
    
    // Page info
    $perPage = 10;
    $pages = ceil($total / $perPage);
    if ($pages < 1) $pages = 1;
    $response .= '#' . $total . ':' . (0) . ':' . $pages;
    
    return $response;
}

// Format user info for GD response  
function formatUserInfo($user, $account = null) {
    if (!$user) return '-1';
    
    $parts = [
        1 => $user['userName'],
        2 => $user['userID'],
        3 => $user['stars'],
        4 => $user['demons'],
        6 => 1, // ranking
        7 => $user['userID'],
        8 => $user['creatorPoints'],
        9 => getDifficultyNum($user['stars'] > 0 ? min($user['stars'], 10) : 0, 0, 0, 0), // user face
        10 => $user['color1'],
        11 => $user['color2'],
        13 => $user['secretCoins'],
        16 => $user['userID'],
        17 => $user['userCoins'],
        18 => $user['messageState'] ?? 0,
        19 => $user['friendState'] ?? 0,
        20 => $user['youtube'] ?? '',
        21 => $user['icon'] ?? 0,
        22 => $user['accShip'] ?? 0,
        23 => $user['accBall'] ?? 0,
        24 => $user['accBird'] ?? 0,
        25 => $user['accDart'] ?? 0,
        26 => $user['accRobot'] ?? 0,
        28 => $user['accGlow'] ?? 0,
        29 => 1, // isRegistered
        30 => 1, // globalRank
        31 => $user['friendState'] ?? 0,
        38 => $user['messageState'] ?? 0,
        39 => $user['friendState'] ?? 0,
        40 => $user['commentState'] ?? 0,
        41 => 0, // twitter
        42 => 0, // twitch
        43 => $user['diamonds'] ?? 0,
        44 => $user['accExplosion'] ?? 0,
        45 => 0, // modlevel
        46 => 1, // isCreator
        48 => 0, // newMessages count
        49 => 0, // friendReq count
        50 => 0, // friendCount
        51 => 0, // age of account
    ];
    
    $result = '';
    foreach ($parts as $key => $value) {
        $result .= $key . ':' . $value . ':';
    }
    return rtrim($result, ':');
}

// Get the GD server URL
function getServerURL() {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? getenv('RENDER_EXTERNAL_HOSTNAME') ?? 'localhost';
    return $protocol . '://' . $host;
}
