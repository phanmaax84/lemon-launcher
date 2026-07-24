<?php
function db() {
    static $pdo = null;
    if ($pdo === null) {
        $dbHost = getenv('DB_HOST');
        if (!empty($dbHost)) {
            $host = $dbHost;
            $port = getenv('DB_PORT') ?: '3306';
            $dbname = getenv('DB_NAME') ?: 'gdps';
            $user = getenv('DB_USER') ?: 'root';
            $pass = getenv('DB_PASS') ?: '';
            try {
                $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4",$user,$pass,[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC]);
            } catch (PDOException $e) { http_response_code(500); die("-1"); }
        } else {
            $dbPath = getenv('DB_PATH') ?: __DIR__ . '/../data/gdps.db';
            $dir = dirname($dbPath);
            if (!is_dir($dir)) @mkdir($dir, 0777, true);
            try {
                $pdo = new PDO("sqlite:$dbPath",null,null,[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC,PDO::ATTR_EMULATE_PREPARES=>false]);
                $pdo->exec("PRAGMA journal_mode=WAL");
            } catch (PDOException $e) { http_response_code(500); die("-1"); }
        }
    }
    return $pdo;
}
function getDbType() { return !empty(getenv('DB_HOST')) ? 'mysql' : 'sqlite'; }
function runSetup() {
    $pdo = db();
    $isMysql = !empty(getenv('DB_HOST'));
    if ($isMysql) {
        $pdo->exec("CREATE TABLE IF NOT EXISTS users (userID INT AUTO_INCREMENT PRIMARY KEY,userName VARCHAR(255) NOT NULL UNIQUE,password VARCHAR(255) NOT NULL,email VARCHAR(255),isAdmin TINYINT DEFAULT 0,isBanned TINYINT DEFAULT 0,stars INT DEFAULT 0,diamonds INT DEFAULT 0,demons INT DEFAULT 0,creatorPoints INT DEFAULT 0,secretCoins INT DEFAULT 0,userCoins INT DEFAULT 0,icon INT DEFAULT 0,color1 INT DEFAULT 0,color2 INT DEFAULT 3,iconType INT DEFAULT 0,special INT DEFAULT 0,accIcon INT DEFAULT 0,accShip INT DEFAULT 0,accBall INT DEFAULT 0,accBird INT DEFAULT 0,accDart INT DEFAULT 0,accRobot INT DEFAULT 0,accGlow INT DEFAULT 0,accSpider INT DEFAULT 0,accExplosion INT DEFAULT 0,accSwing INT DEFAULT 0,accJetpack INT DEFAULT 0,friendState INT DEFAULT 0,messageState INT DEFAULT 0,commentState INT DEFAULT 0,blockedUsers TEXT,registerDate TIMESTAMP DEFAULT CURRENT_TIMESTAMP,lastPlayed TIMESTAMP DEFAULT CURRENT_TIMESTAMP,INDEX idx_username(userName)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        $pdo->exec("CREATE TABLE IF NOT EXISTS accounts (accountID INT AUTO_INCREMENT PRIMARY KEY,userName VARCHAR(255) NOT NULL UNIQUE,password VARCHAR(255) NOT NULL,email VARCHAR(255),userID INT DEFAULT 0,isAdmin TINYINT DEFAULT 0,isBanned TINYINT DEFAULT 0,registerDate TIMESTAMP DEFAULT CURRENT_TIMESTAMP,INDEX idx_username(userName),INDEX idx_userID(userID)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        $pdo->exec("CREATE TABLE IF NOT EXISTS levels (levelID INT AUTO_INCREMENT PRIMARY KEY,levelName VARCHAR(255) NOT NULL DEFAULT 'Unnamed',levelDescription TEXT,levelVersion INT DEFAULT 1,levelLength INT DEFAULT 0,audioTrack INT DEFAULT 0,auto TINYINT DEFAULT 0,password INT DEFAULT 0,original INT DEFAULT 0,twoPlayer TINYINT DEFAULT 0,songID INT DEFAULT 0,objects INT DEFAULT 0,coins INT DEFAULT 0,requestedStars INT DEFAULT 0,unlisted TINYINT DEFAULT 0,ldm TINYINT DEFAULT 0,isFeatured TINYINT DEFAULT 0,isEpic TINYINT DEFAULT 0,starStars INT DEFAULT 0,starDifficulty INT DEFAULT 0,starDemon INT DEFAULT 0,starDemonDiff INT DEFAULT 0,starAuto TINYINT DEFAULT 0,starCoins INT DEFAULT 0,starFeatured INT DEFAULT 0,downloads INT DEFAULT 0,likes INT DEFAULT 0,levelString LONGTEXT,uploadDate TIMESTAMP DEFAULT CURRENT_TIMESTAMP,updateDate TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,uploadDateInt INT DEFAULT 0,updateDateInt INT DEFAULT 0,userID INT DEFAULT 0,extID VARCHAR(255),wt BIGINT DEFAULT 0,wt2 BIGINT DEFAULT 0,wt3 BIGINT DEFAULT 0,INDEX idx_userID(userID),INDEX idx_stars(starStars),INDEX idx_featured(isFeatured)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        $pdo->exec("CREATE TABLE IF NOT EXISTS comments (commentID INT AUTO_INCREMENT PRIMARY KEY,userID INT DEFAULT 0,levelID INT DEFAULT 0,comment TEXT,likes INT DEFAULT 0,timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,INDEX idx_levelID(levelID)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        $pdo->exec("CREATE TABLE IF NOT EXISTS likes (likeID INT AUTO_INCREMENT PRIMARY KEY,userID INT DEFAULT 0,itemID INT DEFAULT 0,type INT DEFAULT 0,isLike TINYINT DEFAULT 1,UNIQUE KEY unique_like(userID,itemID,type)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        $pdo->exec("CREATE TABLE IF NOT EXISTS messages (messageID INT AUTO_INCREMENT PRIMARY KEY,senderID INT DEFAULT 0,receiverID INT DEFAULT 0,subject VARCHAR(255),body TEXT,isRead TINYINT DEFAULT 0,senderDelete TINYINT DEFAULT 0,receiverDelete TINYINT DEFAULT 0,timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,INDEX idx_receiver(receiverID)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        $pdo->exec("CREATE TABLE IF NOT EXISTS friendRequests (requestID INT AUTO_INCREMENT PRIMARY KEY,accountID INT DEFAULT 0,toAccountID INT DEFAULT 0,comment TEXT,isNew TINYINT DEFAULT 1,timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,UNIQUE KEY unique_request(accountID,toAccountID)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        $pdo->exec("CREATE TABLE IF NOT EXISTS songs (songID INT PRIMARY KEY,name VARCHAR(255),authorName VARCHAR(255),downloadURL TEXT,size VARCHAR(50) DEFAULT '0') ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        $pdo->exec("CREATE TABLE IF NOT EXISTS gauntlets (gauntletID INT AUTO_INCREMENT PRIMARY KEY,level1 INT DEFAULT 0,level2 INT DEFAULT 0,level3 INT DEFAULT 0,level4 INT DEFAULT 0,level5 INT DEFAULT 0) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        $pdo->exec("CREATE TABLE IF NOT EXISTS dailyFeatures (featureID INT AUTO_INCREMENT PRIMARY KEY,levelID INT DEFAULT 0,timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,isWeekly TINYINT DEFAULT 0) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        $pdo->exec("CREATE TABLE IF NOT EXISTS ratings (ratingID INT AUTO_INCREMENT PRIMARY KEY,userID INT DEFAULT 0,levelID INT DEFAULT 0,stars INT DEFAULT 0,demon INT DEFAULT 0,difficulty INT DEFAULT 0,auto INT DEFAULT 0,timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,UNIQUE KEY unique_rating(userID,levelID)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    } else {
        $pdo->exec("CREATE TABLE IF NOT EXISTS users (userID INTEGER PRIMARY KEY AUTOINCREMENT,userName TEXT NOT NULL UNIQUE,password TEXT NOT NULL,email TEXT,isAdmin INT DEFAULT 0,isBanned INT DEFAULT 0,stars INT DEFAULT 0,diamonds INT DEFAULT 0,demons INT DEFAULT 0,creatorPoints INT DEFAULT 0,secretCoins INT DEFAULT 0,userCoins INT DEFAULT 0,icon INT DEFAULT 0,color1 INT DEFAULT 0,color2 INT DEFAULT 3,blockedUsers TEXT,registerDate TEXT,lastPlayed TEXT)");
        $pdo->exec("CREATE TABLE IF NOT EXISTS accounts (accountID INTEGER PRIMARY KEY AUTOINCREMENT,userName TEXT NOT NULL UNIQUE,password TEXT NOT NULL,email TEXT,userID INT DEFAULT 0,isAdmin INT DEFAULT 0,isBanned INT DEFAULT 0,registerDate TEXT)");
        $pdo->exec("CREATE TABLE IF NOT EXISTS levels (levelID INTEGER PRIMARY KEY AUTOINCREMENT,levelName TEXT NOT NULL,levelDescription TEXT,levelVersion INT DEFAULT 1,levelLength INT DEFAULT 0,audioTrack INT DEFAULT 0,auto INT DEFAULT 0,password INT DEFAULT 0,songID INT DEFAULT 0,objects INT DEFAULT 0,coins INT DEFAULT 0,isFeatured INT DEFAULT 0,isEpic INT DEFAULT 0,starStars INT DEFAULT 0,starDemon INT DEFAULT 0,starAuto INT DEFAULT 0,starCoins INT DEFAULT 0,starFeatured INT DEFAULT 0,downloads INT DEFAULT 0,likes INT DEFAULT 0,levelString TEXT,uploadDate TEXT,updateDate TEXT,userID INT DEFAULT 0,wt INT DEFAULT 0)");
        $pdo->exec("CREATE TABLE IF NOT EXISTS comments (commentID INTEGER PRIMARY KEY AUTOINCREMENT,userID INT DEFAULT 0,levelID INT DEFAULT 0,comment TEXT,likes INT DEFAULT 0,timestamp TEXT)");
        $pdo->exec("CREATE TABLE IF NOT EXISTS likes (likeID INTEGER PRIMARY KEY AUTOINCREMENT,userID INT DEFAULT 0,itemID INT DEFAULT 0,type INT DEFAULT 0,isLike INT DEFAULT 1,UNIQUE(userID,itemID,type))");
        $pdo->exec("CREATE TABLE IF NOT EXISTS messages (messageID INTEGER PRIMARY KEY AUTOINCREMENT,senderID INT DEFAULT 0,receiverID INT DEFAULT 0,subject TEXT,body TEXT,isRead INT DEFAULT 0,timestamp TEXT)");
        $pdo->exec("CREATE TABLE IF NOT EXISTS friendRequests (requestID INTEGER PRIMARY KEY AUTOINCREMENT,accountID INT DEFAULT 0,toAccountID INT DEFAULT 0,comment TEXT,isNew INT DEFAULT 1,timestamp TEXT,UNIQUE(accountID,toAccountID))");
        $pdo->exec("CREATE TABLE IF NOT EXISTS songs (songID INTEGER PRIMARY KEY,name TEXT,authorName TEXT,downloadURL TEXT)");
        $pdo->exec("CREATE TABLE IF NOT EXISTS gauntlets (gauntletID INTEGER PRIMARY KEY AUTOINCREMENT,level1 INT DEFAULT 0,level2 INT DEFAULT 0,level3 INT DEFAULT 0,level4 INT DEFAULT 0,level5 INT DEFAULT 0)");
        $pdo->exec("CREATE TABLE IF NOT EXISTS dailyFeatures (featureID INTEGER PRIMARY KEY AUTOINCREMENT,levelID INT DEFAULT 0,timestamp TEXT,isWeekly INT DEFAULT 0)");
        $pdo->exec("CREATE TABLE IF NOT EXISTS ratings (ratingID INTEGER PRIMARY KEY AUTOINCREMENT,userID INT DEFAULT 0,levelID INT DEFAULT 0,stars INT DEFAULT 0,timestamp TEXT,UNIQUE(userID,levelID))");
    }
}
