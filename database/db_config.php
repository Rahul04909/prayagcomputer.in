<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'jhdindus_prayag_computer');
define('DB_USER', 'jhdindus_prayag_computer');
define('DB_PASS', 'Rd14072003@./');

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>
