<?php
require_once __DIR__ . '/includes/auth_helper.php';

header('Content-Type: application/json');

if (!is_logged_in()) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access.']);
    exit();
}

$currentUser = get_current_user_data();
$uid = $currentUser['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'update_profile') {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $mobile = trim($_POST['mobile'] ?? '');
        
        if (empty($name) || empty($email)) {
            echo json_encode(['status' => 'error', 'message' => 'Name and Email are required.']);
            exit();
        }

        // Handle Image Upload
        $profile_image = $currentUser['profile_image'];
        if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === 0) {
            $allowed = ['jpg', 'jpeg', 'png', 'webp'];
            $filename = $_FILES['profile_image']['name'];
            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

            if (in_array($ext, $allowed)) {
                $new_filename = 'admin_' . $uid . '_' . time() . '.' . $ext;
                $upload_path = __DIR__ . '/src/images/' . $new_filename;

                if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $upload_path)) {
                    // Delete old image if it's not the default one
                    if ($profile_image && $profile_image !== 'user-avtar.png' && file_exists(__DIR__ . '/src/images/' . $profile_image)) {
                        @unlink(__DIR__ . '/src/images/' . $profile_image);
                    }
                    $profile_image = $new_filename;
                }
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Invalid image format. Allowed: ' . implode(', ', $allowed)]);
                exit();
            }
        }

        // Update Info including Email via PHPAuth if changed
        $email_status = true;
        if ($email !== $currentUser['email']) {
            $change_email = $auth->changeEmail($uid, $email, $currentUser['password']); // This is tricky as we need current pass. 
            // Better approach for simple admin: update email directly if authorized.
            // But PHPAuth manages email. 
            // For now, let's update profile fields first.
        }

        try {
            $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ?, mobile = ?, profile_image = ? WHERE id = ?");
            if ($stmt->execute([$name, $email, $mobile, $profile_image, $uid])) {
                echo json_encode(['status' => 'success', 'message' => 'Profile updated successfully!']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to update profile database.']);
            }
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
        }
        exit();

    } elseif ($action === 'change_password') {
        $current = $_POST['current_password'] ?? '';
        $new = $_POST['new_password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

        if (empty($current) || empty($new) || empty($confirm)) {
            echo json_encode(['status' => 'error', 'message' => 'All password fields are required.']);
            exit();
        }

        if ($new !== $confirm) {
            echo json_encode(['status' => 'error', 'message' => 'New passwords do not match.']);
            exit();
        }

        $result = $auth->changePassword($uid, $current, $new, $confirm);
        if ($result['error']) {
            echo json_encode(['status' => 'error', 'message' => $result['message']]);
        } else {
            echo json_encode(['status' => 'success', 'message' => 'Password updated successfully!']);
        }
        exit();
    }
}

echo json_encode(['status' => 'error', 'message' => 'Invalid request.']);
?>
