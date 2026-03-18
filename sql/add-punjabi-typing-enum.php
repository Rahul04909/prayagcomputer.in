<?php
require_once __DIR__ . '/../database/db_config.php';

try {
    $pdo->exec("ALTER TABLE students MODIFY COLUMN typing_access enum('None', 'Hindi', 'English', 'Punjabi', 'All') DEFAULT 'None';");
    echo "Successfully updated typing_access enum.";
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>
