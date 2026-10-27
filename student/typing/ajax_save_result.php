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
$typed_text = $data['typed_text'] ?? '';

// User's formula:
// Net WPM = Correctly typed words / elapsed time in minutes
// Gross WPM = Total typed words / elapsed time in minutes
// Accuracy = Correctly typed words / total evaluated words * 100
$time_min = ($test_time > 0) ? ($test_time / 60) : 1;
$net_wpm = ($time_min > 0) ? round($correct_words / $time_min) : 0;
$gross_wpm = ($time_min > 0) ? round($total_words / $time_min) : 0;
if ($total_words > 0) {
    $accuracy = round(($correct_words / $total_words) * 100, 1);
}
// Store Gross WPM in wpm if gross_wpm > 0, otherwise keep sent wpm
$wpm = ($gross_wpm > 0) ? $gross_wpm : $wpm;

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
        typed_text LONGTEXT NULL,
        test_date DATETIME DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    $pdo->exec($sql_create);

    // Try adding typed_text if table already exists without it
    try {
        $pdo->exec("ALTER TABLE typing_results ADD COLUMN typed_text LONGTEXT NULL AFTER test_time");
    } catch (PDOException $e) {
        // Column likely exists
    }

    $stmt = $pdo->prepare("INSERT INTO typing_results 
        (student_id, test_id, wpm, net_wpm, accuracy, errors, total_words, correct_words, test_time, typed_text) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    $stmt->execute([
        $student_id, $test_id, $wpm, $net_wpm, $accuracy, $errors, $total_words, $correct_words, $test_time, $typed_text
    ]);

    $result_id = $pdo->lastInsertId();

    echo json_encode(['success' => true, 'net_wpm' => round($net_wpm, 2), 'result_id' => $result_id]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
