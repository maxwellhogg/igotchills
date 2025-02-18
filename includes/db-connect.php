<?php
// Database connection using PDO
$host    = 'localhost';
$db      = 'i_got_chills';
$user    = 'root';
$pass    = '*!nlGh--220717!*';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

// PDO options for error handling, fetch mode, and emulation mode
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Throw exceptions on errors
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Fetch associative arrays
    PDO::ATTR_EMULATE_PREPARES   => false,                  // Use native prepared statements
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    // For production, log this error instead of displaying details
    throw new PDOException($e->getMessage(), (int)$e->getCode());
}
