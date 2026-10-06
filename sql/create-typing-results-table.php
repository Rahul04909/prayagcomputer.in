<?php
require_once __DIR__ . '/../database/db_config.php';

try {
    $sql = "CREATE TABLE IF NOT EXISTS typing_results (
        id INT AUTO_INCREMENT PRIMARY KEY,
        student_id INT NOT NULL,
        test_id INT NOT NULL,
        wpm FLOAT NOT NULL,
        net_wpm FLOAT NOT NULL,
        accuracy FLOAT NOT NULL,
        errors INT NOT NULL,
        total_words INT NOT NULL,
        correct_words INT NOT NULL,
        test_time INT NOT NULL COMMENT 'Actual time spent in seconds',
        test_date DATETIME DEFAULT CURRENT_TIMESTAMP,
        INDEX (student_id),
        INDEX (test_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

    $pdo->exec($sql);
    echo "Table 'typing_results' created successfully!";
} catch (PDOException $e) {
    die("Error creating table: " . $e->getMessage());
}
?>
