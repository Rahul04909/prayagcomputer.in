<?php
require_once __DIR__ . '/../includes/auth_helper.php';

if (!is_logged_in()) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access.']);
    exit();
}

require_once __DIR__ . '/../../database/db_config.php';

// Helper to generate enrollment number
function generateEnrollmentNumber($pdo) {
    $prefix = "PRC";
    $year = date("Y");
    
    // Find the last serial number for the current year
    $stmt = $pdo->prepare("SELECT enrollment_no FROM students WHERE enrollment_no LIKE ? ORDER BY id DESC LIMIT 1");
    $stmt->execute([$prefix . $year . "%"]);
    $last_enrollment = $stmt->fetchColumn();
    
    $serial = 1;
    if ($last_enrollment) {
        // Extract serial from PRC2026001
        $last_serial = (int)substr($last_enrollment, 7);
        $serial = $last_serial + 1;
    }
    
    return $prefix . $year . str_pad($serial, 3, '0', STR_PAD_LEFT);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // Student Admission
    if ($action === 'add_student') {
        $course_id = $_POST['course_id'] ?? null;
        $student_name = trim($_POST['student_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $mobile = trim($_POST['mobile'] ?? '');
        $father_name = trim($_POST['father_name'] ?? '');
        $mother_name = trim($_POST['mother_name'] ?? '');
        
        // Password
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        
        // Address
        $pincode = trim($_POST['pincode'] ?? '');
        $country = trim($_POST['country'] ?? 'India');
        $state = trim($_POST['state'] ?? '');
        $city = trim($_POST['city'] ?? '');
        $full_address = trim($_POST['full_address'] ?? '');
        
        // Qualification
        $qualification = $_POST['qualification'] ?? '';
        $school_university = trim($_POST['school_university'] ?? '');
        
        // KYC/Other
        $aadhar_number = trim($_POST['aadhar_number'] ?? '');
        
        // Access
        $typing_access = $_POST['typing_access'] ?? 'None';
        $steno_access = $_POST['steno_access'] ?? 'None';
        $punjabi_lms_access = isset($_POST['punjabi_lms_access']) ? 1 : 0;
        
        if (empty($student_name) || empty($mobile)) {
            echo json_encode(['status' => 'error', 'message' => 'Student name and mobile are required.']);
            exit();
        }
        
        if (empty($password)) {
            echo json_encode(['status' => 'error', 'message' => 'Password is required.']);
            exit();
        }
        
        if ($password !== $confirm_password) {
            echo json_encode(['status' => 'error', 'message' => 'Passwords do not match.']);
            exit();
        }

        $enrollment_no = generateEnrollmentNumber($pdo);
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $image = '';
        $qualification_cert = '';
        $aadhar_card_file = '';
        
        $upload_dir = __DIR__ . '/../src/images/students/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

        // Helper function for file upload
        function uploadStudentFile($file_key, $prefix, $upload_dir) {
            if (isset($_FILES[$file_key]) && $_FILES[$file_key]['error'] === 0) {
                $ext = strtolower(pathinfo($_FILES[$file_key]['name'], PATHINFO_EXTENSION));
                $filename = $prefix . '_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
                if (move_uploaded_file($_FILES[$file_key]['tmp_name'], $upload_dir . $filename)) {
                    return 'src/images/students/' . $filename;
                }
            }
            return '';
        }

        $image = uploadStudentFile('image', 'stu_img', $upload_dir);
        $qualification_cert = uploadStudentFile('qualification_cert', 'stu_cert', $upload_dir);
        $aadhar_card_file = uploadStudentFile('aadhar_card_file', 'stu_aadhar', $upload_dir);

        try {
            $sql = "INSERT INTO students (
                enrollment_no, course_id, student_name, email, image, mobile, father_name, mother_name, 
                pincode, country, state, city, full_address, 
                qualification, school_university, qualification_cert, 
                aadhar_number, aadhar_card_file, password,
                typing_access, steno_access, punjabi_lms_access
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $enrollment_no, $course_id, $student_name, $email, $image, $mobile, $father_name, $mother_name,
                $pincode, $country, $state, $city, $full_address,
                $qualification, $school_university, $qualification_cert,
                $aadhar_number, $aadhar_card_file, $hashed_password,
                $typing_access, $steno_access, $punjabi_lms_access
            ]);
            
            echo json_encode(['status' => 'success', 'message' => 'Student admitted with Enrollment No: ' . $enrollment_no]);
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
        }
        exit();
    }
}

echo json_encode(['status' => 'error', 'message' => 'Invalid request.']);
exit();
?>
