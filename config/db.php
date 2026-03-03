<?php
$host     = 'localhost';
$username = 'root';
$password = '';
$database = 'atm_db';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$database", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Database connected successfully!";
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
