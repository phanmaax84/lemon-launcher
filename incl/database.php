<?php
// Database connection
function db() {
    static $pdo = null;
    if ($pdo === null) {
        $host = getenv('DB_HOST') ?: 'localhost';
        $port = getenv('DB_PORT') ?: '3306';
        $dbname = getenv('DB_NAME') ?: 'gdps';
        $user = getenv('DB_USER') ?: 'root';
        $pass = getenv('DB_PASS') ?: '';
        
        try {
            $pdo = new PDO(
                "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4",
                $user, $pass,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
            );
        } catch (PDOException $e) {
            http_response_code(500);
            die("-1");
        }
    }
    return $pdo;
}

function runSetup() {
    $pdo = db();
    
    // Users table
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        userID INT AUTO_INCREMENT PRIMARY KEY,
        userName VARCHAR(255) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        email VARCHAR(255) DEFAULT '',
        isAdmin TINYINT DEFAULT 0,
        isBanned TINYINT DEFAULT 0,
        stars INT DEFAULT 0,
        diamonds INT DEFAULT 0,
        demons INT DEFAULT 0,
        creatorPoints INT DEFAULT 0,
        secretCoins INT DEFAULT 0,
        userCoins INT DEFAULT 0,
        icon INT DEFAULT 0,
        color1 INT DEFAULT 0,
        color2 INT DEFAULT 3,
        iconType INT DEFAULT 0,
        special INT DEFAULT 0,
        accIcon INT DEFAULT 0,
        accShip INT DEFAULT 0,
        accBall INT DEFAULT 0,
        accBird INT DEFAULT 0,
        accDart INT DEFAULT 0,
        accRobot INT DEFAULT 0,
        accGlow INT DEFAULT 0,
        accSpider INT DEFAULT 0,
        accExplosion INT DEFAULT 0,
        accSwing INT DEFAULT 0,
        accJetpack INT DEFAULT 0,
        friendState INT DEFAULT 0,
        messageState INT DEFAULT 0,
        commentState INT DEFAULT 0,
        friendRequestsSent TEXT DEFAULT '',
        friendRequestsRecv TEXT DEFAULT '',
        friends TEXT DEFAULT '',
        blockedUsers TEXT DEFAULT '',
        registerDate TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        lastPlayed TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_username (userName)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    
    // Accounts table (maps game accounts to users)
    $pdo->exec("CREATE TABLE IF NOT EXISTS accounts (
        accountID INT AUTO_INCREMENT PRIMARY KEY,
        userName VARCHAR(255) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        email VARCHAR(255) DEFAULT '',
        userID INT DEFAULT 0,
        isAdmin TINYINT DEFAULT 0,
        isBanned TINYINT DEFAULT 0,
        registerDate TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_username (userName),
        INDEX idx_userID (userID)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    
    // Levels table
    $pdo->exec("CREATE TABLE IF NOT EXISTS levels (
        levelID INT AUTO_INCREMENT PRIMARY KEY,
        levelName VARCHAR(255) NOT NULL DEFAULT 'Unnamed',
        levelDescription TEXT DEFAULT '',
        levelVersion INT DEFAULT 1,
        levelLength INT DEFAULT 0,
        audioTrack INT DEFAULT 0,
        auto TINYINT DEFAULT 0,
        password INT DEFAULT 0,
        original INT DEFAULT 0,
        twoPlayer TINYINT DEFAULT 0,
        songID INT DEFAULT 0,
        objects INT DEFAULT 0,
        coins INT DEFAULT 0,
        requestedStars INT DEFAULT 0,
        unlisted TINYINT DEFAULT 0,
        unlisted2 TINYINT DEFAULT 0,
        ldm TINYINT DEFAULT 0,
        isFeatured TINYINT DEFAULT 0,
        isEpic TINYINT DEFAULT 0,
        isGauntlet TINYINT DEFAULT 0,
        isDaily TINYINT DEFAULT 0,
        isWeekly TINYINT DEFAULT 0,
        starStars INT DEFAULT 0,
        starDifficulty INT DEFAULT 0,
        starDemon INT DEFAULT 0,
        starDemonDiff INT DEFAULT 0,
        starAuto TINYINT DEFAULT 0,
        starCoins INT DEFAULT 0,
        starFeatured INT DEFAULT 0,
        starEpic INT DEFAULT 0,
        downloads INT DEFAULT 0,
        likes INT DEFAULT 0,
        dislikes INT DEFAULT 0,
        levelInfo TEXT DEFAULT '',
        levelString LONGTEXT DEFAULT '',
        levelInfo2 TEXT DEFAULT '',
        uploadDate TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updateDate TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        uploadDateInt INT DEFAULT 0,
        updateDateInt INT DEFAULT 0,
        userID INT DEFAULT 0,
        extID VARCHAR(255) DEFAULT '',
        hash VARCHAR(255) DEFAULT '',
        wt BIGINT DEFAULT 0,
        wt2 BIGINT DEFAULT 0,
        wt3 BIGINT DEFAULT 0,
        INDEX idx_userID (userID),
        INDEX idx_stars (starStars),
        INDEX idx_downloads (downloads),
        INDEX idx_likes (likes),
        INDEX idx_featured (isFeatured)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    
    // Comments table
    $pdo->exec("CREATE TABLE IF NOT EXISTS comments (
        commentID INT AUTO_INCREMENT PRIMARY KEY,
        userID INT DEFAULT 0,
        levelID INT DEFAULT 0,
        comment TEXT DEFAULT '',
        likes INT DEFAULT 0,
        isSpam TINYINT DEFAULT 0,
        timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_levelID (levelID),
        INDEX idx_userID (userID)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    
    // Likes table (track what users have liked)
    $pdo->exec("CREATE TABLE IF NOT EXISTS likes (
        likeID INT AUTO_INCREMENT PRIMARY KEY,
        userID INT DEFAULT 0,
        itemID INT DEFAULT 0,
        type INT DEFAULT 0,
        isLike TINYINT DEFAULT 1,
        timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY unique_like (userID, itemID, type)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    
    // Messages table
    $pdo->exec("CREATE TABLE IF NOT EXISTS messages (
        messageID INT AUTO_INCREMENT PRIMARY KEY,
        senderID INT DEFAULT 0,
        receiverID INT DEFAULT 0,
        subject VARCHAR(255) DEFAULT '',
        body TEXT DEFAULT '',
        isRead TINYINT DEFAULT 0,
        senderDelete TINYINT DEFAULT 0,
        receiverDelete TINYINT DEFAULT 0,
        timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_receiver (receiverID),
        INDEX idx_sender (senderID)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    
    // Friend requests table
    $pdo->exec("CREATE TABLE IF NOT EXISTS friendRequests (
        requestID INT AUTO_INCREMENT PRIMARY KEY,
        accountID INT DEFAULT 0,
        toAccountID INT DEFAULT 0,
        comment TEXT DEFAULT '',
        isNew TINYINT DEFAULT 1,
        timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY unique_request (accountID, toAccountID)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    
    // Songs table (custom songs)
    $pdo->exec("CREATE TABLE IF NOT EXISTS songs (
        songID INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) DEFAULT '',
        authorName VARCHAR(255) DEFAULT '',
        downloadURL TEXT DEFAULT '',
        size VARCHAR(50) DEFAULT '0',
        duration VARCHAR(50) DEFAULT '0',
        reuploadTime TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    
    // Gauntlets table
    $pdo->exec("CREATE TABLE IF NOT EXISTS gauntlets (
        gauntletID INT AUTO_INCREMENT PRIMARY KEY,
        level1 INT DEFAULT 0,
        level2 INT DEFAULT 0,
        level3 INT DEFAULT 0,
        level4 INT DEFAULT 0,
        level5 INT DEFAULT 0
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    
    // Daily/Weekly levels
    $pdo->exec("CREATE TABLE IF NOT EXISTS dailyFeatures (
        featureID INT AUTO_INCREMENT PRIMARY KEY,
        levelID INT DEFAULT 0,
        timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        isWeekly TINYINT DEFAULT 0,
        INDEX idx_level (levelID)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    
    // Level ratings
    $pdo->exec("CREATE TABLE IF NOT EXISTS ratings (
        ratingID INT AUTO_INCREMENT PRIMARY KEY,
        userID INT DEFAULT 0,
        levelID INT DEFAULT 0,
        stars INT DEFAULT 0,
        demon INT DEFAULT 0,
        difficulty INT DEFAULT 0,
        auto INT DEFAULT 0,
        timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY unique_rating (userID, levelID)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
}
