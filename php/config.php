<?php
// Basic DB config - read from environment variables in production
// MySQL (PDO)
$DB_HOST = getenv('DB_HOST') ?: '127.0.0.1';
$DB_NAME = getenv('DB_NAME') ?: 'login_demo';
$DB_USER = getenv('DB_USER') ?: 'root';
$DB_PASS = getenv('DB_PASS') ?: '';
$DSN = "mysql:host={$DB_HOST};dbname={$DB_NAME};charset=utf8mb4";
$PDO_OPTIONS = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

// MongoDB
$MONGO_URI = getenv('MONGO_URI') ?: 'mongodb://127.0.0.1:27017';
$MONGO_DB = getenv('MONGO_DB') ?: 'login_demo';

// Notes:
// - Create MySQL table `users` before use:
// CREATE TABLE users (
//   id INT AUTO_INCREMENT PRIMARY KEY,
//   fullname VARCHAR(255) NOT NULL,
//   username VARCHAR(100) NOT NULL UNIQUE,
//   email VARCHAR(255) NOT NULL UNIQUE,
//   password_hash VARCHAR(255) NOT NULL,
//   created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
// );

// - MongoDB will use collection `profiles` in the same database.
?>
