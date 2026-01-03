<?php
$host = "localhost";
$dbname = "treasurehunt";
$user = "root";
$pass = ""; // Wamp default = empty password

try {
    $pdo = new PDO(
        "mysql:host=$host;port=3307;dbname=$dbname;charset=utf8mb4",
        $user,
        $pass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}