<?php
require_once __DIR__ . '/database/db_config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $course_id = $_POST['course_id'] ?? null;
    $course_name = $_POST['course_name'] ?? '';
    $name = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if (empty($name) || empty($phone) || empty($email)) {
        echo json_encode(['status' => 'error', 'message' => 'Please fill all required fields.']);
        exit();
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO enquiries (course_id, course_name, name, phone, email, message) VALUES (?, ?, ?, ?, ?, ?)");
        if ($stmt->execute([$course_id, $course_name, $name, $phone, $email, $message])) {
            echo json_encode(['status' => 'success', 'message' => 'Thank you! Your enquiry has been submitted. Our team will contact you soon.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Something went wrong. Please try again later.']);
        }
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    header("Location: index.php");
    exit();
}
?>
