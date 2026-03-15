<?php
require_once __DIR__ . '/../includes/auth_helper.php';

if (!is_logged_in()) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access.']);
    exit();
}

require_once __DIR__ . '/../../database/db_config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // Add/Update Typing Category
    if ($action === 'save_typing_category') {
        $id = $_POST['id'] ?? 0;
        $name = trim($_POST['name'] ?? '');
        $status = $_POST['status'] ?? 1;
        $logo = '';

        if (empty($name)) {
            echo json_encode(['status' => 'error', 'message' => 'Category name is required.']);
            exit();
        }

        // Handle Image Upload
        if (isset($_FILES['logo']) && $_FILES['logo']['error'] === 0) {
            $allowed = ['jpg', 'jpeg', 'png', 'svg', 'webp'];
            $ext = strtolower(pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, $allowed)) {
                $filename = 'typing_cat_' . time() . '.' . $ext;
                $upload_dir = __DIR__ . '/../src/images/typing/';
                if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
                if (move_uploaded_file($_FILES['logo']['tmp_name'], $upload_dir . $filename)) {
                    $logo = 'src/images/typing/' . $filename;
                }
            }
        }

        try {
            if ($id > 0) {
                // Update
                if (!empty($logo)) {
                    // Delete old logo if exists
                    $stmt = $pdo->prepare("SELECT logo FROM typing_exam_categories WHERE id = ?");
                    $stmt->execute([$id]);
                    $old_logo = $stmt->fetchColumn();
                    if ($old_logo && file_exists(__DIR__ . '/../' . $old_logo)) {
                        @unlink(__DIR__ . '/../' . $old_logo);
                    }
                    $stmt = $pdo->prepare("UPDATE typing_exam_categories SET name = ?, logo = ?, status = ? WHERE id = ?");
                    $res = $stmt->execute([$name, $logo, $status, $id]);
                } else {
                    $stmt = $pdo->prepare("UPDATE typing_exam_categories SET name = ?, status = ? WHERE id = ?");
                    $res = $stmt->execute([$name, $status, $id]);
                }
                echo json_encode(['status' => 'success', 'message' => 'Category updated successfully!']);
            } else {
                // Insert
                $stmt = $pdo->prepare("INSERT INTO typing_exam_categories (name, logo, status) VALUES (?, ?, ?)");
                $res = $stmt->execute([$name, $logo, $status]);
                echo json_encode(['status' => 'success', 'message' => 'Category added successfully!']);
            }
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
        }
        exit();
    }

    // Delete Typing Category
    if ($action === 'delete_typing_category') {
        $id = $_POST['id'] ?? 0;
        try {
            // Delete logo file first
            $stmt = $pdo->prepare("SELECT logo FROM typing_exam_categories WHERE id = ?");
            $stmt->execute([$id]);
            $logo = $stmt->fetchColumn();
            if ($logo && file_exists(__DIR__ . '/../' . $logo)) {
                @unlink(__DIR__ . '/../' . $logo);
            }

            $stmt = $pdo->prepare("DELETE FROM typing_exam_categories WHERE id = ?");
            if ($stmt->execute([$id])) {
                echo json_encode(['status' => 'success', 'message' => 'Category deleted successfully!']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to delete category.']);
            }
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        exit();
    }

    // Update Status
    if ($action === 'toggle_typing_status') {
        $id = $_POST['id'] ?? 0;
        $status = $_POST['status'] ?? 0;
        try {
            $stmt = $pdo->prepare("UPDATE typing_exam_categories SET status = ? WHERE id = ?");
            if ($stmt->execute([$status, $id])) {
                echo json_encode(['status' => 'success', 'message' => 'Status updated!']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to update status.']);
            }
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        exit();
    }
    // Add Typing Test
    if ($action === 'add_typing_test') {
        $category_id = $_POST['category_id'] ?? 0;
        $title = trim($_POST['title'] ?? '');
        $short_description = trim($_POST['short_description'] ?? '');
        $content = $_POST['content'] ?? '';
        $language = $_POST['language'] ?? 'English';
        $test_type = $_POST['test_type'] ?? 'Typing Test';
        $level = $_POST['level'] ?? 'Medium';
        $test_time = (int)($_POST['test_time'] ?? 5);
        $status = $_POST['status'] ?? 1;

        if (empty($category_id) || empty($title) || empty($content)) {
            echo json_encode(['status' => 'error', 'message' => 'Required fields are missing (Category, Title, Content).']);
            exit();
        }

        try {
            $stmt = $pdo->prepare("INSERT INTO typing_tests (category_id, title, language, test_type, short_description, content, test_time, level, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            if ($stmt->execute([$category_id, $title, $language, $test_type, $short_description, $content, $test_time, $level, $status])) {
                echo json_encode(['status' => 'success', 'message' => 'Typing Test added successfully!']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to add Typing Test.']);
            }
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
        }
        exit();
    }
}

echo json_encode(['status' => 'error', 'message' => 'Invalid request.']);
exit();
?>
