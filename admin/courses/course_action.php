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
    } elseif ($action === 'add_course') {
        $category_id = $_POST['category_id'] ?? 0;
        $title = trim($_POST['title'] ?? '');
        $duration = trim($_POST['duration'] ?? '');
        $duration_type = trim($_POST['duration_type'] ?? 'Months');
        $slug = trim($_POST['slug'] ?? '');
        $description = $_POST['description'] ?? '';
        $mrp = floatval($_POST['mrp'] ?? 0);
        $sale_price = floatval($_POST['sale_price'] ?? 0);
        
        // SEO Info
        $seo_title = trim($_POST['seo_title'] ?? '');
        $seo_description = trim($_POST['seo_description'] ?? '');
        $seo_keywords = trim($_POST['seo_keywords'] ?? '');
        $seo_schema = $_POST['seo_schema'] ?? '';

        if (empty($category_id) || empty($title) || empty($slug)) {
            echo json_encode(['status' => 'error', 'message' => 'Category, Title and Slug are required.']);
            exit();
        }

        // Handle Image Upload
        $featured_image = '';
        if (isset($_FILES['featured_image']) && $_FILES['featured_image']['error'] === 0) {
            $allowed = ['jpg', 'jpeg', 'png', 'webp'];
            $file_name = $_FILES['featured_image']['name'];
            $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

            if (in_array($ext, $allowed)) {
                $new_filename = 'course_' . time() . '.' . $ext;
                $upload_dir = __DIR__ . '/../src/images/courses/';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }
                if (move_uploaded_file($_FILES['featured_image']['tmp_name'], $upload_dir . $new_filename)) {
                    $featured_image = 'courses/' . $new_filename;
                }
            }
        }

        try {
            $stmt = $pdo->prepare("INSERT INTO courses (category_id, title, duration, duration_type, slug, description, featured_image, mrp, sale_price, seo_title, seo_description, seo_keywords, seo_schema) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            if ($stmt->execute([$category_id, $title, $duration, $duration_type, $slug, $description, $featured_image, $mrp, $sale_price, $seo_title, $seo_description, $seo_keywords, $seo_schema])) {
                echo json_encode(['status' => 'success', 'message' => 'Course added successfully!']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to add course.']);
            }
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                echo json_encode(['status' => 'error', 'message' => 'Slug already exists. Please try a different title.']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
            }
        }
        exit();
    } elseif ($action === 'delete_course') {
        $id = $_POST['id'] ?? 0;
        if (empty($id)) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid ID.']);
            exit();
        }
        try {
            $stmt = $pdo->prepare("SELECT featured_image FROM courses WHERE id = ?");
            $stmt->execute([$id]);
            $course = $stmt->fetch();
            $stmt = $pdo->prepare("DELETE FROM courses WHERE id = ?");
            if ($stmt->execute([$id])) {
                if ($course && !empty($course['featured_image']) && file_exists(__DIR__ . '/../src/images/' . $course['featured_image'])) {
                    @unlink(__DIR__ . '/../src/images/' . $course['featured_image']);
                }
                echo json_encode(['status' => 'success', 'message' => 'Course deleted successfully!']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to delete course.']);
            }
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
        }
        exit();
    } elseif ($action === 'update_course') {
        $id = $_POST['id'] ?? 0;
        $category_id = $_POST['category_id'] ?? 0;
        $title = trim($_POST['title'] ?? '');
        $duration = trim($_POST['duration'] ?? '');
        $duration_type = trim($_POST['duration_type'] ?? 'Months');
        $description = $_POST['description'] ?? '';
        $mrp = floatval($_POST['mrp'] ?? 0);
        $sale_price = floatval($_POST['sale_price'] ?? 0);
        $status = $_POST['status'] ?? 1;
        $seo_title = trim($_POST['seo_title'] ?? '');
        $seo_description = trim($_POST['seo_description'] ?? '');
        $seo_keywords = trim($_POST['seo_keywords'] ?? '');
        $seo_schema = $_POST['seo_schema'] ?? '';

        if (empty($id) || empty($category_id) || empty($title)) {
            echo json_encode(['status' => 'error', 'message' => 'ID, Category and Title are required.']);
            exit();
        }

        try {
            $stmt = $pdo->prepare("SELECT featured_image FROM courses WHERE id = ?");
            $stmt->execute([$id]);
            $currentData = $stmt->fetch();
            if (!$currentData) {
                echo json_encode(['status' => 'error', 'message' => 'Course not found.']);
                exit();
            }
            $featured_image = $currentData['featured_image'];
            if (isset($_FILES['featured_image']) && $_FILES['featured_image']['error'] === 0) {
                $allowed = ['jpg', 'jpeg', 'png', 'webp'];
                $ext = strtolower(pathinfo($_FILES['featured_image']['name'], PATHINFO_EXTENSION));
                if (in_array($ext, $allowed)) {
                    $new_filename = 'course_' . time() . '.' . $ext;
                    $upload_dir = __DIR__ . '/../src/images/courses/';
                    if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
                    if (move_uploaded_file($_FILES['featured_image']['tmp_name'], $upload_dir . $new_filename)) {
                        if (!empty($featured_image) && file_exists(__DIR__ . '/../src/images/' . $featured_image)) {
                            @unlink(__DIR__ . '/../src/images/' . $featured_image);
                        }
                        $featured_image = 'courses/' . $new_filename;
                    }
                }
            }
            $stmt = $pdo->prepare("UPDATE courses SET category_id = ?, title = ?, duration = ?, duration_type = ?, description = ?, featured_image = ?, mrp = ?, sale_price = ?, seo_title = ?, seo_description = ?, seo_keywords = ?, seo_schema = ?, status = ? WHERE id = ?");
            if ($stmt->execute([$category_id, $title, $duration, $duration_type, $description, $featured_image, $mrp, $sale_price, $seo_title, $seo_description, $seo_keywords, $seo_schema, $status, $id])) {
                echo json_encode(['status' => 'success', 'message' => 'Course updated successfully!']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Database update failed.']);
            }
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
        }
        exit();
    } elseif ($action === 'delete_enquiry') {
        $id = $_POST['id'] ?? 0;
        try {
            $stmt = $pdo->prepare("DELETE FROM enquiries WHERE id = ?");
            if ($stmt->execute([$id])) {
                echo json_encode(['status' => 'success', 'message' => 'Enquiry deleted successfully!']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to delete enquiry.']);
            }
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
        }
        exit();
    } elseif ($action === 'update_enquiry_status') {
        $id = $_POST['id'] ?? 0;
        $status = $_POST['status'] ?? 'Pending';
        try {
            $stmt = $pdo->prepare("UPDATE enquiries SET status = ? WHERE id = ?");
            if ($stmt->execute([$status, $id])) {
                echo json_encode(['status' => 'success', 'message' => 'Status updated successfully!']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to update status.']);
            }
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
        }
        exit();
    }
}

echo json_encode(['status' => 'error', 'message' => 'Invalid request.']);
?>
