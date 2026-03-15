<?php
require_once __DIR__ . '/../database/db_config.php';

$sql = "CREATE TABLE IF NOT EXISTS `typing_tests` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `category_id` int(11) NOT NULL,
    `title` varchar(255) NOT NULL,
    `short_description` text DEFAULT NULL,
    `content` longtext NOT NULL,
    `language` varchar(50) NOT NULL DEFAULT 'English',
    `test_type` varchar(50) NOT NULL DEFAULT 'Typing Test',
    `level` varchar(20) NOT NULL DEFAULT 'Medium',
    `test_time` int(11) NOT NULL DEFAULT '5',
    `status` tinyint(1) NOT NULL DEFAULT '1',
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    CONSTRAINT `fk_typing_test_category` FOREIGN KEY (`category_id`) REFERENCES `typing_exam_categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

try {
    $pdo->exec($sql);
    echo "Table 'typing_tests' created successfully!";
} catch (PDOException $e) {
    die("Error creating table: " . $e->getMessage());
}
?>
