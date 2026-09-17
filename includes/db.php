<?php
/**
 * Shared database connection (PDO).
 * Every other PHP file includes this one file — never connect to the
 * database in more than one place.
 */

$DB_HOST = 'localhost';
$DB_NAME = 'mapoly_bookshop_db';
$DB_USER = 'root';       // default XAMPP MySQL user
$DB_PASS = '';           // default XAMPP MySQL password is blank

try {
    $pdo = new PDO(
        "mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8mb4",
        $DB_USER,
        $DB_PASS,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // throw real errors instead of silently failing
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,                  // let MySQL run real prepared statements
        ]
    );
} catch (PDOException $e) {
    // In production, log this instead of showing it to the user.
    die('Database connection failed: ' . $e->getMessage());
}
