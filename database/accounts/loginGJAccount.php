<?php
header('Content-Type: text/plain');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }

$host = getenv('DB_HOST');
$port = getenv('DB_PORT') ?: '3306';
$dbname = getenv('DB_NAME') ?: 'gdps';
$user = getenv('DB_USER') ?: 'root';
$pass = getenv('DB_PASS') ?: '';

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4",$user,$pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (Exception $e) { die('-1'); }

function xorCipher($text, $key) {
    $r = '';
    for ($i = 0; $i < strlen($text); $i++) $r .= chr(ord($text[$i]) ^ ord($key[$i % strlen($key)]));
    return $r;
}

$username = $_POST['userName'] ?? '';
$password = $_POST['password'] ?? '';
$gjp2 = $_POST['gjp2'] ?? '';

if (empty($username)) die('-1');
if (!empty($gjp2)) {
    $decoded = xorCipher(base64_decode($gjp2), '37526');
    if ($decoded) $password = $decoded;
}
if (empty($password)) die('-1');

try {
    $stmt = $pdo->prepare("SELECT * FROM accounts WHERE userName = ?");
    $stmt->execute([$username]);
    $account = $stmt->fetch();
    if (!$account) die('-1');
    if (!password_verify($password, $account['password'])) die('-1');
    if ($account['isBanned']) die('-1');

    $userID = $account['userID'] ?: $account['accountID'];
    if (!$account['userID']) {
        $pdo->prepare("UPDATE accounts SET userID = ? WHERE accountID = ?")->execute([$userID, $account['accountID']]);
    }

    $userStmt = $pdo->prepare("SELECT * FROM users WHERE userID = ?");
    $userStmt->execute([$userID]);
    if (!$userStmt->fetch()) {
        $pdo->prepare("INSERT INTO users (userID, userName) VALUES (?, ?)")->execute([$userID, $account['userName']]);
    }

    echo $account['accountID'] . ',' . $userID;
} catch (Exception $e) {
    die('-1');
}
