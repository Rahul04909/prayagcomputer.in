<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../database/db_config.php';

$sql = "CREATE TABLE IF NOT EXISTS `courses` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `category_id` int(11) NOT NULL,
    `title` varchar(255) NOT NULL,
    `slug` varchar(255) NOT NULL UNIQUE,
    `description` text DEFAULT NULL,
    `featured_image` varchar(255) DEFAULT NULL,
    `mrp` decimal(10,2) NOT NULL DEFAULT '0.00',
    `sale_price` decimal(10,2) NOT NULL DEFAULT '0.00',
    `seo_title` varchar(255) DEFAULT NULL,
    `seo_description` text DEFAULT NULL,
    `seo_keywords` text DEFAULT NULL,
    `seo_schema` text DEFAULT NULL,
    `status` tinyint(1) NOT NULL DEFAULT '1',
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `category_id` (`category_id`),
    CONSTRAINT `courses_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `course_categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

try {
    if (!isset($pdo)) {
        die("Error: Database connection variable \$pdo is not defined.");
    }
    $pdo->exec($sql);
    echo "Table 'courses' created successfully!";
} catch (PDOException $e) {
    die("Error creating table: " . $e->getMessage());
}
?>
