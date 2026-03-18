<?php
require_once __DIR__ . '/../includes/auth_helper.php';

header('Content-Type: application/json');

if (!$auth->isLogged()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$student = get_current_student_data();
$student_id = $student['id'];

$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    echo json_encode(['success' => false, 'message' => 'No data provided']);
    exit();
}

$test_id = (int)$data['test_id'];
$wpm = (float)$data['wpm'];
$accuracy = (float)$data['accuracy'];
$errors = (int)$data['errors'];
$total_words = (int)$data['total_words'];
$correct_words = (int)$data['correct_words'];
$test_time = (int)$data['test_time']; // in seconds

// Calculate Net WPM (Gross WPM - Errors/Time)
// Standard formula: (Total Characters / 5) / Minutes - (Errors / Minutes)
$net_wpm = ($wpm > 0) ? max(0, $wpm - ($errors / ($test_time / 60))) : 0;

try {
    // Ensure table exists (fail-safe if previous script didn't run via CLI)
    $sql_create = "CREATE TABLE IF NOT EXISTS typing_results (
        id INT AUTO_INCREMENT PRIMARY KEY,
        student_id INT NOT NULL,
        test_id INT NOT NULL,
        wpm FLOAT NOT NULL,
        net_wpm FLOAT NOT NULL,
        accuracy FLOAT NOT NULL,
        errors INT NOT NULL,
        total_words INT NOT NULL,
        correct_words INT NOT NULL,
        test_time INT NOT NULL,
        test_date DATETIME DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    $pdo->exec($sql_create);

    $stmt = $pdo->prepare("INSERT INTO typing_results 
        (student_id, test_id, wpm, net_wpm, accuracy, errors, total_words, correct_words, test_time) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    $stmt->execute([
        $student_id, $test_id, $wpm, $net_wpm, $accuracy, $errors, $total_words, $correct_words, $test_time
    ]);

    echo json_encode(['success' => true, 'net_wpm' => round($net_wpm, 2)]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
