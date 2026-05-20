<?php
/**
 * Database Connection using PDO
 * fixitC_db
 */

$host = 'localhost';
$db   = 'fixitC_db';
$user = 'root'; // Default XAMPP/WAMP user
$pass = '';     // Default XAMPP/WAMP password
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
     $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
     // For production, you'd log this and show a generic error.
     // For development, we'll show the message.
     die("Database connection failed: " . $e->getMessage());
}
?>
