<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/config.php';

$userId = isset($_GET['userId']) ? (int)$_GET['userId'] : 0;
if(!$userId){ echo json_encode(['success'=>false,'message'=>'Missing userId']); exit; }

try {
    $pdo = new PDO($DSN, $DB_USER, $DB_PASS, $PDO_OPTIONS);
    $stmt = $pdo->prepare('SELECT id, fullname, username, email FROM users WHERE id = ? LIMIT 1');
    $stmt->execute([$userId]);
    $user = $stmt->fetch();

    $manager = new MongoDB\Driver\Manager($MONGO_URI);
    $query = new MongoDB\Driver\Query(['user_id' => $userId], []);
    $cursor = $manager->executeQuery("{$MONGO_DB}.profiles", $query);
    $profiles = $cursor->toArray();
    $profile = count($profiles) ? (array)$profiles[0] : null;

    echo json_encode(['success'=>true, 'user'=>$user, 'profile'=>$profile]);
} catch (Exception $e){
    echo json_encode(['success'=>false,'message'=>'Server error: '.$e->getMessage()]);
}
