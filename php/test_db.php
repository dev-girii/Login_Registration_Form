<?php
require_once __DIR__ . '/config.php';

header('Content-Type: text/plain; charset=utf-8');

try {
    $pdo = new PDO($DSN, $DB_USER, $DB_PASS, $PDO_OPTIONS);
    $stmt = $pdo->query("SELECT 1");
    $one = $stmt->fetchColumn();
    echo "DB connection OK. SELECT 1 => " . $one . "\n";
} catch (Exception $e) {
    echo "DB connection failed: " . $e->getMessage() . "\n";
    echo "DSN used: " . (defined('DEBUG_DSN') ? $DSN : preg_replace('/(password=[^;]*)/', 'password=****', $DSN)) . "\n";
}
