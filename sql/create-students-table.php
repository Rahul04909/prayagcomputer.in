<?php
require_once __DIR__ . '/../database/db_config.php';

$sql = "CREATE TABLE IF NOT EXISTS `students` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `course_id` int(11) DEFAULT NULL,
    `student_name` varchar(255) NOT NULL,
    `email` varchar(255) DEFAULT NULL,
    `image` varchar(255) DEFAULT NULL,
    `mobile` varchar(20) NOT NULL,
    `father_name` varchar(255) DEFAULT NULL,
    `mother_name` varchar(255) DEFAULT NULL,
    `pincode` varchar(10) DEFAULT NULL,
    `country` varchar(100) DEFAULT 'India',
    `state` varchar(100) DEFAULT NULL,
    `city` varchar(100) DEFAULT NULL,
    `full_address` text DEFAULT NULL,
    `qualification` varchar(100) DEFAULT NULL,
    `school_university` varchar(255) DEFAULT NULL,
    `qualification_cert` varchar(255) DEFAULT NULL,
    `aadhar_number` varchar(20) DEFAULT NULL,
    `aadhar_card_file` varchar(255) DEFAULT NULL,
    `typing_access` enum('None', 'Hindi', 'English') DEFAULT 'None',
    `steno_hindi_access` tinyint(1) DEFAULT 0,
    `steno_english_access` tinyint(1) DEFAULT 0,
    `punjabi_lms_access` tinyint(1) DEFAULT 0,
    `status` tinyint(1) NOT NULL DEFAULT 1,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

try {
    $pdo->exec($sql);
    echo "Table 'students' created successfully!";
} catch (PDOException $e) {
    die("Error creating table: " . $e->getMessage());
}
?>
