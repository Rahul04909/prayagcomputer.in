<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../database/db_config.php';

$sql = "ALTER TABLE `courses` ADD `duration` varchar(100) DEFAULT NULL AFTER `description`;";

try {
    if (!isset($pdo)) {
        die("Error: Database connection variable \$pdo is not defined.");
    }
    $pdo->exec($sql);
    echo "Column 'duration' added successfully to 'courses' table!";
} catch (PDOException $e) {
    if ($e->getCode() == '42S21') {
        echo "Column 'duration' already exists.";
    } else {
        die("Error updating table: " . $e->getMessage());
    }
}
?>
