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
    }
}

echo json_encode(['status' => 'error', 'message' => 'Invalid request.']);
?>
