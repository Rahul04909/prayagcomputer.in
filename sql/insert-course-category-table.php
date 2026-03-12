<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "Debug: Running from " . __DIR__ . "<br>";

require_once __DIR__ . '/../database/db_config.php';

if (!isset($pdo)) {
    die("Error: Database connection variable \$pdo is not defined.");
}

$sql = "CREATE TABLE IF NOT EXISTS `course_categories` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `name` varchar(255) NOT NULL,
    `slug` varchar(255) NOT NULL UNIQUE,
    `description` text DEFAULT NULL,
    `seo_title` varchar(255) DEFAULT NULL,
    `seo_description` text DEFAULT NULL,
    `seo_keywords` text DEFAULT NULL,
    `seo_schema` text DEFAULT NULL,
    `featured_image` varchar(255) DEFAULT NULL,
    `status` tinyint(1) NOT NULL DEFAULT '1',
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

try {
    $pdo->exec($sql);
    echo "Table 'course_categories' created successfully!";
} catch (PDOException $e) {
    die("Error creating table: " . $e->getMessage());
}
?>
