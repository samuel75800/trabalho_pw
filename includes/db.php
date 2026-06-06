<?php
// ============================================================
//  puppy.co — Database Connection
//  includes/db.php
//  Usage: require_once __DIR__ . '/../includes/db.php';
// ============================================================

define('DB_HOST', 'localhost');
define('DB_NAME', 'puppyco');
define('DB_USER', 'root');
define('DB_PASS', '');          // XAMPP default has no password
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
    // Never expose raw error messages to the browser in production
    error_log('Database connection failed: ' . $e->getMessage());
    try {
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
} catch (PDOException $e) {
    die('Erro: ' . $e->getMessage()); // só durante debug
}
}