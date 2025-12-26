<?php

/* =========================
   MySQL (PDO)
========================= */

$DB_HOST = getenv('DB_HOST');
$DB_PORT = getenv('DB_PORT') ?: 3306;
$DB_NAME = getenv('DB_NAME');
$DB_USER = getenv('DB_USER');
$DB_PASS = getenv('DB_PASS');

if (!$DB_HOST || !$DB_NAME || !$DB_USER) {
    die("Database environment variables not set");
}

try {
    $pdo = new PDO(
        "mysql:host=$DB_HOST;port=$DB_PORT;dbname=$DB_NAME;charset=utf8mb4",
        $DB_USER,
        $DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
} catch (PDOException $e) {
    die("Database connection failed");
}

/* =========================
   MongoDB
========================= */

$MONGO_URI = getenv('MONGO_URI');
$MONGO_DB  = getenv('MONGO_DB') ?: 'login_demo';

if (!$MONGO_URI) {
    die("MongoDB URI not set");
}

/*
MySQL table required:

CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  fullname VARCHAR(255) NOT NULL,
  username VARCHAR(100) NOT NULL UNIQUE,
  email VARCHAR(255) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

MongoDB:
- Database: login_demo
- Collection: profiles
*/
