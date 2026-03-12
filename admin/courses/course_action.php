<?php
require_once __DIR__ . '/../includes/auth_helper.php';

header('Content-Type: application/json');

if (!is_logged_in()) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access.']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add_category') {
        $name = trim($_POST['name'] ?? '');
        $description = $_POST['description'] ?? '';
        $status = $_POST['status'] ?? 1;
        
        // SEO Info
        $seo_title = trim($_POST['seo_title'] ?? '');
        $seo_description = trim($_POST['seo_description'] ?? '');
        $seo_keywords = trim($_POST['seo_keywords'] ?? '');
        $seo_schema = $_POST['seo_schema'] ?? '';

        if (empty($name)) {
            echo json_encode(['status' => 'error', 'message' => 'Category Name is required.']);
            exit();
        }

        // Generate Slug
        $slug = strtolower(preg_replace('/[^a-z0-9-]+/', '-', $name));
        $slug = trim($slug, '-');

        // Check if slug exists
        $stmt = $pdo->prepare("SELECT id FROM course_categories WHERE slug = ?");
        $stmt->execute([$slug]);
        if ($stmt->fetch()) {
            $slug .= '-' . time();
        }

        // Handle Image Upload
        $featured_image = '';
        if (isset($_FILES['featured_image']) && $_FILES['featured_image']['error'] === 0) {
            $allowed = ['jpg', 'jpeg', 'png', 'webp'];
            $filename = $_FILES['featured_image']['name'];
            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

            if (in_array($ext, $allowed)) {
                $new_filename = 'cat_' . time() . '.' . $ext;
                // Create directory if not exists
                $upload_dir = __DIR__ . '/../src/images/courses/';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }
                
                $upload_path = $upload_dir . $new_filename;

                if (move_uploaded_file($_FILES['featured_image']['tmp_name'], $upload_path)) {
                    $featured_image = 'courses/' . $new_filename;
                }
            }
        }

        try {
            $stmt = $pdo->prepare("INSERT INTO course_categories (name, slug, description, seo_title, seo_description, seo_keywords, seo_schema, featured_image, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            if ($stmt->execute([$name, $slug, $description, $seo_title, $seo_description, $seo_keywords, $seo_schema, $featured_image, $status])) {
                echo json_encode(['status' => 'success', 'message' => 'Course Category added successfully!']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Database insertion failed.']);
            }
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
        }
        exit();
    } elseif ($action === 'delete_category') {
        $id = $_POST['id'] ?? 0;

        if (empty($id)) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid ID.']);
            exit();
        }

        try {
            // Get image to delete file
            $stmt = $pdo->prepare("SELECT featured_image FROM course_categories WHERE id = ?");
            $stmt->execute([$id]);
            $cat = $stmt->fetch();

            $stmt = $pdo->prepare("DELETE FROM course_categories WHERE id = ?");
            if ($stmt->execute([$id])) {
                if ($cat && !empty($cat['featured_image']) && file_exists(__DIR__ . '/../src/images/' . $cat['featured_image'])) {
                    @unlink(__DIR__ . '/../src/images/' . $cat['featured_image']);
                }
                echo json_encode(['status' => 'success', 'message' => 'Category deleted successfully!']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to delete category.']);
            }
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
        }
        exit();
    } elseif ($action === 'update_category') {
        $id = $_POST['id'] ?? 0;
        $name = trim($_POST['name'] ?? '');
        $description = $_POST['description'] ?? '';
        $status = $_POST['status'] ?? 1;
        
        // SEO Info
        $seo_title = trim($_POST['seo_title'] ?? '');
        $seo_description = trim($_POST['seo_description'] ?? '');
        $seo_keywords = trim($_POST['seo_keywords'] ?? '');
        $seo_schema = $_POST['seo_schema'] ?? '';

        if (empty($id) || empty($name)) {
            echo json_encode(['status' => 'error', 'message' => 'ID and Category Name are required.']);
            exit();
        }

        try {
            // Get existing data
            $stmt = $pdo->prepare("SELECT featured_image, slug FROM course_categories WHERE id = ?");
            $stmt->execute([$id]);
            $currentData = $stmt->fetch();

            if (!$currentData) {
                echo json_encode(['status' => 'error', 'message' => 'Category not found.']);
                exit();
            }

            // Handle Image Upload
            $featured_image = $currentData['featured_image'];
            if (isset($_FILES['featured_image']) && $_FILES['featured_image']['error'] === 0) {
                $allowed = ['jpg', 'jpeg', 'png', 'webp'];
                $ext = strtolower(pathinfo($_FILES['featured_image']['name'], PATHINFO_EXTENSION));

                if (in_array($ext, $allowed)) {
                    $new_filename = 'cat_' . time() . '.' . $ext;
                    $upload_dir = __DIR__ . '/../src/images/courses/';
                    if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
                    
                    if (move_uploaded_file($_FILES['featured_image']['tmp_name'], $upload_dir . $new_filename)) {
                        // Delete old image
                        if (!empty($featured_image) && file_exists(__DIR__ . '/../src/images/' . $featured_image)) {
                            @unlink(__DIR__ . '/../src/images/' . $featured_image);
                        }
                        $featured_image = 'courses/' . $new_filename;
                    }
                }
            }

            $stmt = $pdo->prepare("UPDATE course_categories SET name = ?, description = ?, seo_title = ?, seo_description = ?, seo_keywords = ?, seo_schema = ?, featured_image = ?, status = ? WHERE id = ?");
            if ($stmt->execute([$name, $description, $seo_title, $seo_description, $seo_keywords, $seo_schema, $featured_image, $status, $id])) {
                echo json_encode(['status' => 'success', 'message' => 'Course Category updated successfully!']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Database update failed.']);
            }
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
        }
        exit();
    }
}

echo json_encode(['status' => 'error', 'message' => 'Invalid request.']);
?>
