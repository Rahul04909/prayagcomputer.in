<?php
require_once __DIR__ . '/../database/db_config.php';

try {
    // Add enrollment_no and password
    $pdo->exec("ALTER TABLE students ADD COLUMN enrollment_no varchar(50) UNIQUE AFTER id;");
    $pdo->exec("ALTER TABLE students ADD COLUMN password varchar(255) AFTER aadhar_card_file;");
    
    // Convert Steno access to enum
    // 1. Add new column
    $pdo->exec("ALTER TABLE students ADD COLUMN steno_access enum('None', 'Hindi', 'English') DEFAULT 'None' AFTER typing_access;");
    
    // 2. Migrate data
    $pdo->exec("UPDATE students SET steno_access = 'Hindi' WHERE steno_hindi_access = 1;");
    $pdo->exec("UPDATE students SET steno_access = 'English' WHERE steno_english_access = 1;");
    
    // 3. Drop old columns
    $pdo->exec("ALTER TABLE students DROP COLUMN steno_hindi_access, DROP COLUMN steno_english_access;");

    echo "Table 'students' updated successfully with refinements!";
} catch (PDOException $e) {
    die("Error updating table: " . $e->getMessage());
}
?>
