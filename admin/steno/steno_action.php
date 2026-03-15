<?php
require_once __DIR__ . '/../../database/db_config.php';
require_once __DIR__ . '/../includes/auth_helper.php';

if (!is_logged_in()) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access.']);
    exit();
}

$action = $_POST['action'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Add Category
    if ($action === 'add_steno_category') {
        $name = trim($_POST['name'] ?? '');
        $status = $_POST['status'] ?? 1;
        $logo = '';

        if (empty($name)) {
            echo json_encode(['status' => 'error', 'message' => 'Category name is required.']);
            exit();
        }

        if (isset($_FILES['logo']) && $_FILES['logo']['error'] === 0) {
            $allowed = ['jpg', 'jpeg', 'png', 'webp', 'svg'];
            $ext = strtolower(pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, $allowed)) {
                $filename = 'steno_cat_' . time() . '.' . $ext;
                $upload_dir = __DIR__ . '/../src/images/steno/';
                if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
                if (move_uploaded_file($_FILES['logo']['tmp_name'], $upload_dir . $filename)) {
                    $logo = 'steno/' . $filename;
                }
            }
        }

        try {
            $stmt = $pdo->prepare("INSERT INTO steno_exam_categories (name, logo, status) VALUES (?, ?, ?)");
            if ($stmt->execute([$name, $logo, $status])) {
                echo json_encode(['status' => 'success', 'message' => 'Category added successfully!']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to add category.']);
            }
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        exit();
    }

    // Update Category
    if ($action === 'update_steno_category') {
        $id = $_POST['id'] ?? 0;
        $name = trim($_POST['name'] ?? '');
        $status = $_POST['status'] ?? 1;

        if (empty($id) || empty($name)) {
            echo json_encode(['status' => 'error', 'message' => 'ID and Name are required.']);
            exit();
        }

        try {
            $stmt = $pdo->prepare("SELECT logo FROM steno_exam_categories WHERE id = ?");
            $stmt->execute([$id]);
            $currentData = $stmt->fetch();
            $logo = $currentData['logo'];

            if (isset($_FILES['logo']) && $_FILES['logo']['error'] === 0) {
                $allowed = ['jpg', 'jpeg', 'png', 'webp', 'svg'];
                $ext = strtolower(pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION));
                if (in_array($ext, $allowed)) {
                    $filename = 'steno_cat_' . time() . '.' . $ext;
                    $upload_dir = __DIR__ . '/../src/images/steno/';
                    if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
                    if (move_uploaded_file($_FILES['logo']['tmp_name'], $upload_dir . $filename)) {
                        if (!empty($logo) && file_exists(__DIR__ . '/../src/images/' . $logo)) {
                            @unlink(__DIR__ . '/../src/images/' . $logo);
                        }
                        $logo = 'steno/' . $filename;
                    }
                }
            }

            $stmt = $pdo->prepare("UPDATE steno_exam_categories SET name = ?, logo = ?, status = ? WHERE id = ?");
            if ($stmt->execute([$name, $logo, $status, $id])) {
                echo json_encode(['status' => 'success', 'message' => 'Category updated successfully!']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to update category.']);
            }
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        exit();
    }

    // Delete Category
    if ($action === 'delete_steno_category') {
        $id = $_POST['id'] ?? 0;
        try {
            $stmt = $pdo->prepare("SELECT logo FROM steno_exam_categories WHERE id = ?");
            $stmt->execute([$id]);
            $cat = $stmt->fetch();

            $stmt = $pdo->prepare("DELETE FROM steno_exam_categories WHERE id = ?");
            if ($stmt->execute([$id])) {
                if ($cat && !empty($cat['logo']) && file_exists(__DIR__ . '/../src/images/' . $cat['logo'])) {
                    @unlink(__DIR__ . '/../src/images/' . $cat['logo']);
                }
                echo json_encode(['status' => 'success', 'message' => 'Category deleted successfully!']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to delete category.']);
            }
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        exit();
    }
}

echo json_encode(['status' => 'error', 'message' => 'Invalid request.']);
?>
