<?php
require_once __DIR__ . '/../database/db_config.php';

$sql = "CREATE TABLE IF NOT EXISTS `enquiries` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `course_id` int(11) DEFAULT NULL,
    `course_name` varchar(255) DEFAULT NULL,
    `name` varchar(255) NOT NULL,
    `phone` varchar(20) NOT NULL,
    `email` varchar(255) NOT NULL,
    `message` text DEFAULT NULL,
    `status` varchar(50) DEFAULT 'Pending',
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

try {
    $pdo->exec($sql);
    echo "Table 'enquiries' created successfully!";
} catch (PDOException $e) {
    die("Error creating table: " . $e->getMessage());
}
?>
