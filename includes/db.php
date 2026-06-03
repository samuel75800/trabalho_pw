<?php
define('DB_HOST',    $_ENV['DB_HOST'] ?? getenv('DB_HOST') ?: 'localhost');
define('DB_NAME',    $_ENV['DB_NAME'] ?? getenv('DB_NAME') ?: 'puppyco');
define('DB_USER',    $_ENV['DB_USER'] ?? getenv('DB_USER') ?: 'root');
define('DB_PASS',    $_ENV['DB_PASS'] ?? getenv('DB_PASS') ?: '');
define('DB_CHARSET', 'utf8mb4');

$dsn = 'mysql:host=' . DB_HOST
     . ';dbname='    . DB_NAME
     . ';charset='   . DB_CHARSET;

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
} catch (PDOException $e) {
    error_log('Database connection failed: ' . $e->getMessage());
    die('Connection error. Please try again later.');
}