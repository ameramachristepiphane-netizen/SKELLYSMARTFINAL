<?php
// Database configuration
$DB_HOST = '127.0.0.1';
$DB_NAME = 'smarthome';
$DB_USER = 'root';
$DB_PASS = 'root';

try {
    $pdo = new PDO("mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8mb4", $DB_USER, $DB_PASS, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    // Prevent exposing credentials or SQL errors to users
    die('Erreur de connexion à la base de données.');
}
