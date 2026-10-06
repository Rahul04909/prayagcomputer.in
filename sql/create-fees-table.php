<?php
require_once __DIR__ . '/../database/db_config.php';

try {
    // 1. Add total_fees to students table if not exists
    $pdo->exec("ALTER TABLE students ADD COLUMN total_fees decimal(10,2) DEFAULT 0.00 AFTER course_id");
    echo "Added 'total_fees' column to 'students' table.\n";

    // 2. Create student_fees table
    $sql = "CREATE TABLE IF NOT EXISTS `student_fees` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `student_id` int(11) NOT NULL,
        `transaction_no` varchar(50) NOT NULL UNIQUE,
        `amount_paid` decimal(10,2) NOT NULL,
        `payment_method` enum('Cash', 'Online', 'Bank Transfer', 'Other') DEFAULT 'Cash',
        `payment_date` date NOT NULL,
        `remarks` text DEFAULT NULL,
        `created_by` int(11) DEFAULT NULL,
        `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        KEY `student_id` (`student_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    
    $pdo->exec($sql);
    echo "Table 'student_fees' created successfully!\n";

    // 3. Initialize total_fees for existing students based on course price
    $pdo->exec("UPDATE students s 
                JOIN courses c ON s.course_id = c.id 
                SET s.total_fees = c.sale_price 
                WHERE s.total_fees = 0 OR s.total_fees IS NULL");
    echo "Initialized 'total_fees' for existing students.\n";

} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}
?>
