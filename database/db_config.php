<?php
<<<<<<< HEAD

define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'prayag_computer-313539bc21');
define('DB_USER', 'prayag');
define('DB_PASS', 'Rd14072003@./');

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );

    echo "Database connected successfully!";

=======
// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'jghfrodu_prayag_computer');
define('DB_USER', 'jghfrodu_prayag_computer');
define('DB_PASS', 'Rd14072003@./');

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
>>>>>>> 1e2705f6361c619d8b701370fbf341d78388bc5c
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>