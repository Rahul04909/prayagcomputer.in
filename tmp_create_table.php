<?php
require 'database/db_config.php';
$sql = "CREATE TABLE IF NOT EXISTS steno_results (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    test_id INT NOT NULL,
    wpm FLOAT NOT NULL,
    accuracy FLOAT NOT NULL,
    errors INT NOT NULL,
    total_words INT NOT NULL,
    correct_words INT NOT NULL,
    transcribed_text TEXT,
    test_time INT NOT NULL,
    test_date DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
$pdo->exec($sql);
echo "Table steno_results created successfully\n";
?>
