<?php
require_once __DIR__ . '/database/db_config.php';

// Get course slug from URL
$slug = $_GET['slug'] ?? '';

if (empty($slug)) {
    header("Location: index.php");
    exit();
}

// Fetch course details with category
try {
    $stmt = $pdo->prepare("
        SELECT c.*, cat.name as category_name 
        FROM courses c 
        JOIN course_categories cat ON c.category_id = cat.id 
        WHERE c.slug = ? AND c.status = 1 
        LIMIT 1
    ");
    $stmt->execute([$slug]);
    $course = $stmt->fetch();

    if (!$course) {
        // Handle course not found
        header("Location: index.php");
        exit();
    }
} catch (PDOException $e) {
    die("Error fetching course: " . $e->getMessage());
}

// Page Title and Meta Info
$page_title = htmlspecialchars($course['seo_title'] ?: $course['title']);
$seo_description = htmlspecialchars($course['seo_description']);
$seo_keywords = htmlspecialchars($course['seo_keywords']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?> | Prayag Computer Center</title>
    <meta name="description" content="<?= $seo_description ?>">
    <meta name="keywords" content="<?= $seo_keywords ?>">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Bootstrap CSS (Assuming you use it as per index.php) -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">

    <!-- Existing Styles -->
    <link rel="stylesheet" href="assets/css/header.css">
    <link rel="stylesheet" href="assets/css/footer.css">
    <link rel="stylesheet" href="assets/css/course-details.css">

    <?php if (!empty($course['seo_schema'])): ?>
    <script type="application/ld+json">
    <?= $course['seo_schema'] ?>
    </script>
    <?php endif; ?>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <!-- Course Header Hero -->
    <section class="course-single-header">
        <div class="container">
            <div class="row">
                <div class="col-lg-8">
                    <span class="category-badge"><?= htmlspecialchars($course['category_name']) ?></span>
                    <h1><?= htmlspecialchars($course['title']) ?></h1>
                    
                    <div class="header-meta">
                        <span><i class="far fa-clock"></i> Duration: <?= htmlspecialchars($course['duration'] . ' ' . $course['duration_type']) ?></span>
                        <span><i class="fas fa-certificate"></i> Certified Course</span>
                        <span><i class="fas fa-star"></i> 4.9 (1.2k+ Students)</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Main Content Section -->
    <section class="course-details-section">
        <div class="container">
            <div class="row">
                <!-- Course Main Content -->
                <div class="col-lg-8">
                    <div class="course-main-img">
                        <?php 
                        $imagePath = !empty($course['featured_image']) ? 'admin/src/images/' . $course['featured_image'] : 'admin/assets/img/course-placeholder.jpg';
                        ?>
                        <img src="<?= $imagePath ?>" alt="<?= htmlspecialchars($course['title']) ?>" class="img-fluid">
                    </div>

                    <div class="course-description">
                        <h2>About This Course</h2>
                        <div class="content">
                            <?= $course['description'] ?>
                        </div>
                    </div>
                </div>

                <!-- Sidebar Content -->
                <div class="col-lg-4">
                    <div class="sticky-top">
                        <!-- Pricing Card -->
                        <div class="course-sidebar-card shadow-sm">
                            <div class="price-container">
                                <span class="sale-price">₹<?= number_format($course['sale_price'], 0) ?></span>
                                <?php if ($course['mrp'] > $course['sale_price']): 
                                    $discount = round((($course['mrp'] - $course['sale_price']) / $course['mrp']) * 100);
                                ?>
                                    <span class="mrp-price">₹<?= number_format($course['mrp'], 0) ?></span>
                                    <div class="discount-tag"><?= $discount ?>% OFF - Limited Time</div>
                                <?php endif; ?>
                            </div>

                            <!-- Enquiry Form -->
                            <div class="enquiry-form">
                                <h4>Enroll / Enquiry Now</h4>
                                <form id="courseEnquiryForm" action="process-enquiry.php" method="POST">
                                    <input type="hidden" name="course_id" value="<?= $course['id'] ?>">
                                    <input type="hidden" name="course_name" value="<?= htmlspecialchars($course['title']) ?>">
                                    
                                    <div class="form-group">
                                        <label>Full Name</label>
                                        <input type="text" name="name" class="form-control" placeholder="Enter your name" required>
                                    </div>
                                    <div class="form-group">
                                        <label>Mobile Number</label>
                                        <input type="tel" name="phone" class="form-control" placeholder="Enter mobile number" required pattern="[0-9]{10}">
                                    </div>
                                    <div class="form-group">
                                        <label>Email Address</label>
                                        <input type="email" name="email" class="form-control" placeholder="Enter your email" required>
                                    </div>
                                    <div class="form-group">
                                        <label>Message / Question</label>
                                        <textarea name="message" class="form-control" rows="3" placeholder="Tell us what you want to learn?"></textarea>
                                    </div>

                                    <button type="submit" class="btn-submit">
                                        <i class="fas fa-paper-plane mr-2"></i> Get Free Counselling
                                    </button>
                                </form>
                                <p class="text-center text-muted mt-3 small">
                                    <i class="fas fa-lock text-success mr-1"></i> Your data is 100% secure.
                                </p>
                            </div>
                        </div>

                        <!-- Mini Info -->
                        <div class="course-sidebar-card p-4">
                            <h5 style="font-weight:700; margin-bottom:15px;">Why Join Prayag?</h5>
                            <ul class="list-unstyled mb-0" style="font-size:14px; color:#475569;">
                                <li class="mb-2"><i class="fas fa-check-circle text-success mr-2"></i> Expert Faculty & Individual Care</li>
                                <li class="mb-2"><i class="fas fa-check-circle text-success mr-2"></i> Professional Steno Speed Training</li>
                                <li class="mb-2"><i class="fas fa-check-circle text-success mr-2"></i> 100% Practice Based Learning</li>
                                <li><i class="fas fa-check-circle text-success mr-2"></i> Government Certified Institute</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>

    <!-- Optional JS Support -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(document).ready(function() {
            $('#courseEnquiryForm').on('submit', function(e) {
                // You can add AJAX here later if you want a seamless submission
                // For now, it will use standard form submission to process-enquiry.php
                // Unless you want me to write the AJAX part too
            });
        });
    </script>
</body>
</html>
