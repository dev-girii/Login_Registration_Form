<?php
header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', 0);
error_reporting(E_ALL);

require_once __DIR__ . '/config.php';

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);

if (!is_array($data)) {
    echo json_encode(['success' => false, 'message' => 'Invalid JSON']);
    exit;
}

$fullname = trim($data['fullname'] ?? '');
$username = trim($data['username'] ?? '');
$email    = trim($data['email'] ?? '');
$password = $data['password'] ?? '';

$age     = isset($data['age']) ? (int)$data['age'] : null;
$dob     = $data['dob'] ?? null;
$contact = $data['contact'] ?? null;
$address = $data['address'] ?? null;

if (!$fullname || !$username || !$email || !$password) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

try {
    // ---------- MySQL ----------
    $pdo = new PDO($DSN, $DB_USER, $DB_PASS, $PDO_OPTIONS);

    $check = $pdo->prepare(
        'SELECT id FROM users WHERE email = ? OR username = ? LIMIT 1'
    );
    $check->execute([$email, $username]);

    if ($check->fetch()) {
        echo json_encode([
            'success' => false,
            'message' => 'Email or username already in use'
        ]);
        exit;
    }

    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    $insert = $pdo->prepare(
        'INSERT INTO users (fullname, username, email, password_hash)
         VALUES (?, ?, ?, ?)'
    );
    $insert->execute([$fullname, $username, $email, $password_hash]);

    $userId = (int)$pdo->lastInsertId();

    // ---------- MongoDB (SAFE / OPTIONAL) ----------
    try {
        if (class_exists('MongoDB\\Driver\\Manager')) {
            $manager = new MongoDB\Driver\Manager($MONGO_URI);
            $bulk    = new MongoDB\Driver\BulkWrite;

            $bulk->insert([
                'user_id'    => $userId,
                'age'        => $age,
                'dob'        => $dob,
                'contact'    => $contact,
                'address'    => $address,
                'created_at' => new MongoDB\BSON\UTCDateTime()
            ]);

            $manager->executeBulkWrite(
                "{$MONGO_DB}.profiles",
                $bulk
            );
        }
    } catch (Throwable $e) {
        // Mongo failure should NOT break registration
    }

    echo json_encode([
        'success' => true,
        'userId'  => $userId
    ]);

} catch (Throwable $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Server error'
    ]);
}
