<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/config.php';

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);
if(!is_array($data)){
    echo json_encode(['success'=>false,'message'=>'Invalid JSON']);
    exit;
}

$identifier = trim($data['identifier'] ?? '');
$password = $data['password'] ?? '';
if(!$identifier || !$password){
    echo json_encode(['success'=>false,'message'=>'Missing credentials']);
    exit;
}

try {
    $pdo = new PDO($DSN, $DB_USER, $DB_PASS, $PDO_OPTIONS);
    $stmt = $pdo->prepare('SELECT id, fullname, username, email, password_hash FROM users WHERE email = ? OR username = ? LIMIT 1');
    $stmt->execute([$identifier, $identifier]);
    $user = $stmt->fetch();
    if(!$user){
        echo json_encode(['success'=>false,'message'=>'Invalid credentials']);
        exit;
    }
    if(!password_verify($password, $user['password_hash'])){
        echo json_encode(['success'=>false,'message'=>'Invalid credentials']);
        exit;
    }
    // remove password_hash before returning
    unset($user['password_hash']);
    echo json_encode(['success'=>true, 'user' => $user]);

} catch (Exception $e) {
    echo json_encode(['success'=>false,'message'=>'Server error: '.$e->getMessage()]);
}
