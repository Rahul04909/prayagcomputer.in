<?php
require_once __DIR__ . '/includes/auth_helper.php';

if (!is_student_logged_in()) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized.']);
    exit();
}

$student = get_current_student_data();

header('Content-Type: application/json');

$action = $_POST['action'] ?? '';

// ── Change Password ───────────────────────────────────────────────────────────
if ($action === 'change_password') {
    $current  = $_POST['current_password']  ?? '';
    $new_pass = $_POST['new_password']      ?? '';
    $confirm  = $_POST['confirm_password']  ?? '';

    if (empty($current) || empty($new_pass) || empty($confirm)) {
        echo json_encode(['status' => 'error', 'message' => 'All fields are required.']);
        exit();
    }
    if ($new_pass !== $confirm) {
        echo json_encode(['status' => 'error', 'message' => 'New passwords do not match.']);
        exit();
    }
    if (strlen($new_pass) < 6) {
        echo json_encode(['status' => 'error', 'message' => 'New password must be at least 6 characters.']);
        exit();
    }

    // Verify current password
    $stmt = $pdo->prepare("SELECT password FROM students WHERE id = ?");
    $stmt->execute([$student['id']]);
    $row  = $stmt->fetch();

    if (!$row || !password_verify($current, $row['password'])) {
        echo json_encode(['status' => 'error', 'message' => 'Current password is incorrect.']);
        exit();
    }

    // Update password
    $hashed = password_hash($new_pass, PASSWORD_DEFAULT);
    $upd    = $pdo->prepare("UPDATE students SET password = ? WHERE id = ?");
    if ($upd->execute([$hashed, $student['id']])) {
        echo json_encode(['status' => 'success', 'message' => 'Password updated successfully!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to update password. Please try again.']);
    }
    exit();
}

echo json_encode(['status' => 'error', 'message' => 'Invalid request.']);
exit();
?>
