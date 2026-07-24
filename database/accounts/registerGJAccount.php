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

$username = trim($_POST['userName'] ?? '');
$password = $_POST['password'] ?? '';
$email = trim($_POST['email'] ?? '');

if (empty($username) || empty($password)) die('-1');
if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) die('-8');
if (strlen($username) < 3) die('-9');
if (strlen($password) < 6) die('-8');

try {
    $stmt = $pdo->prepare("SELECT accountID FROM accounts WHERE userName = ?");
    $stmt->execute([$username]);
    if ($stmt->fetch()) die('-2');

    if (!empty($email)) {
        $stmt2 = $pdo->prepare("SELECT accountID FROM accounts WHERE email = ?");
        $stmt2->execute([$email]);
        if ($stmt2->fetch()) die('-3');
    }

    $hashed = password_hash($password, PASSWORD_DEFAULT);
    $pdo->prepare("INSERT INTO accounts (userName, password, email) VALUES (?, ?, ?)")->execute([$username, $hashed, $email]);
    $accountID = $pdo->lastInsertId();

    $pdo->prepare("INSERT INTO users (userID, userName) VALUES (?, ?)")->execute([$accountID, $username]);
    $pdo->prepare("UPDATE accounts SET userID = ? WHERE accountID = ?")->execute([$accountID, $accountID]);

    echo $accountID;
} catch (Exception $e) {
    die('-1');
}
