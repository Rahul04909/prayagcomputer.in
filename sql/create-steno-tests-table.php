<?php
require_once __DIR__ . '/../database/db_config.php';

$sql = "CREATE TABLE IF NOT EXISTS `steno_tests` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `category_id` int(11) NOT NULL,
    `title` varchar(255) NOT NULL,
    `language` enum('English', 'Hindi') NOT NULL DEFAULT 'English',
    `short_description` text DEFAULT NULL,
    `content` longtext NOT NULL,
    `audio_file` varchar(255) DEFAULT NULL,
    `test_duration` int(5) NOT NULL,
    `buffer_time` int(2) NOT NULL DEFAULT '1',
    `level` enum('Easy', 'Medium', 'Hard') NOT NULL DEFAULT 'Medium',
    `status` tinyint(1) NOT NULL DEFAULT '1',
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `category_id` (`category_id`),
    CONSTRAINT `steno_tests_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `steno_exam_categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

try {
    $pdo->exec($sql);
    echo "Table 'steno_tests' created successfully!";
} catch (PDOException $e) {
    die("Error creating table: " . $e->getMessage());
}
?>
