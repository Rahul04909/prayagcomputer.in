<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../database/db_config.php';

$sql = "ALTER TABLE `courses` ADD `duration_type` varchar(50) DEFAULT 'Months' AFTER `duration`;";

try {
    if (!isset($pdo)) {
        die("Error: Database connection variable \$pdo is not defined.");
    }
    $pdo->exec($sql);
    echo "Column 'duration_type' added successfully to 'courses' table!";
} catch (PDOException $e) {
    if ($e->getCode() == '42S21') {
        echo "Column 'duration_type' already exists.";
    } else {
        die("Error updating table: " . $e->getMessage());
    }
}
?>
