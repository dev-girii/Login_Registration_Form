<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/config.php';

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);
if(!is_array($data)){
    echo json_encode(['success'=>false,'message'=>'Invalid JSON']);
    exit;
}

$fullname = trim($data['fullname'] ?? '');
$username = trim($data['username'] ?? '');
$email = trim($data['email'] ?? '');
$password = $data['password'] ?? '';
$age = isset($data['age']) ? (int)$data['age'] : null;
$dob = $data['dob'] ?? null;
$contact = $data['contact'] ?? null;
$address = $data['address'] ?? null;

if(!$fullname || !$username || !$email || !$password){
    echo json_encode(['success'=>false,'message'=>'Missing required fields']);
    exit;
}

try {
    $pdo = new PDO($DSN, $DB_USER, $DB_PASS, $PDO_OPTIONS);

    // check existing user by email or username
    $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ? OR username = ? LIMIT 1');
    $stmt->execute([$email, $username]);
    if($stmt->fetch()){
        echo json_encode(['success'=>false,'message'=>'Email or username already in use']);
        exit;
    }

    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    $insert = $pdo->prepare('INSERT INTO users (fullname, username, email, password_hash) VALUES (?, ?, ?, ?)');
    $insert->execute([$fullname, $username, $email, $password_hash]);
    $userId = (int)$pdo->lastInsertId();

    // insert profile into MongoDB (profiles collection)
    $manager = new MongoDB\Driver\Manager($MONGO_URI);
    $bulk = new MongoDB\Driver\BulkWrite;
    $profileDoc = [
        'user_id' => $userId,
        'age' => $age,
        'dob' => $dob,
        'contact' => $contact,
        'address' => $address,
        'created_at' => new MongoDB\BSON\UTCDateTime()
    ];
    $bulk->insert($profileDoc);
    $manager->executeBulkWrite("{$MONGO_DB}.profiles", $bulk);

    echo json_encode(['success'=>true, 'userId' => $userId]);

} catch (Exception $e) {
    // Do not leak DB internals in production
    echo json_encode(['success'=>false, 'message' => 'Server error: '.$e->getMessage()]);
}
