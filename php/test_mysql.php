<?php
require_once __DIR__ . '/config.php';
header('Content-Type: text/plain');

try {
    $pdo = new PDO($DSN, $DB_USER, $DB_PASS, $PDO_OPTIONS);
    echo "MySQL connected\n";

    $stmt = $pdo->query("SELECT COUNT(*) FROM users");
    echo "Users count: " . $stmt->fetchColumn();

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage();
}
