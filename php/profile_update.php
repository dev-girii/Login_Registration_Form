<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/config.php';

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);
if(!is_array($data)){
    echo json_encode(['success'=>false,'message'=>'Invalid JSON']);
    exit;
}

$userId = isset($data['userId']) ? (int)$data['userId'] : 0;
if(!$userId){ echo json_encode(['success'=>false,'message'=>'Missing userId']); exit; }

$fullname = trim($data['fullname'] ?? '');
$username = trim($data['username'] ?? '');
$email = trim($data['email'] ?? '');
$age = isset($data['age']) ? (int)$data['age'] : null;
$dob = $data['dob'] ?? null;
$contact = $data['contact'] ?? null;
$address = $data['address'] ?? null;

try {
    // update MySQL basic fields (fullname, username, email) using prepared stmt
    $pdo = new PDO($DSN, $DB_USER, $DB_PASS, $PDO_OPTIONS);
    $stmt = $pdo->prepare('UPDATE users SET fullname = ?, username = ?, email = ? WHERE id = ?');
    $stmt->execute([$fullname, $username, $email, $userId]);

    // update MongoDB profile document
    $manager = new MongoDB\Driver\Manager($MONGO_URI);
    $bulk = new MongoDB\Driver\BulkWrite;
    $filter = ['user_id' => $userId];
    $updateDoc = ['$set' => [
        'age' => $age,
        'dob' => $dob,
        'contact' => $contact,
        'address' => $address,
        'updated_at' => new MongoDB\BSON\UTCDateTime()
    ]];
    // update existing document (do not upsert by default)
    $bulk->update($filter, $updateDoc, ['multi' => false, 'upsert' => true]);
    $manager->executeBulkWrite("{$MONGO_DB}.profiles", $bulk);

    echo json_encode(['success'=>true]);

} catch (Exception $e) {
    echo json_encode(['success'=>false,'message'=>'Server error: '.$e->getMessage()]);
}
