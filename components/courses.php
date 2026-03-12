<?php
require_once __DIR__ . '/../database/db_config.php';

// Fetch courses with their category names
try {
    $stmt = $pdo->prepare("
        SELECT c.*, cat.name as category_name 
        FROM courses c 
        JOIN course_categories cat ON c.category_id = cat.id 
        WHERE c.status = 1 
        ORDER BY c.id DESC
    ");
    $stmt->execute();
    $courses = $stmt->fetchAll();
} catch (PDOException $e) {
    $courses = [];
}
?>

<!-- Add Swiper CSS if not already present -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />

<section class="courses-section" id="courses">
    <div class="container">
        <div class="section-header" data-aos="fade-up">
            <h2>Our Professional Courses</h2>
            <p>Master the skills with our industry-leading computer and steno courses designed for your career success.</p>
        </div>

        <?php if (!empty($courses)): ?>
            <div class="swiper courses-swiper" data-aos="fade-up" data-aos-delay="100">
                <div class="swiper-wrapper">
                    <?php foreach ($courses as $course): 
                        // Random Ratings (4.7 to 5.0)
                        $rating = number_format(4.7 + (mt_rand(0, 3) / 10), 1);
                        // Random Admissions (120 to 2500)
                        $admissions = mt_rand(12, 250) * 10 . "+";
                        
                        $imagePath = !empty($course['featured_image']) ? 'src/images/' . $course['featured_image'] : 'src/images/course-placeholder.jpg';
                        $duration = htmlspecialchars($course['duration'] . ' ' . $course['duration_type']);
                    ?>
                        <div class="swiper-slide">
                            <div class="course-card">
                                <span class="course-badge"><?= htmlspecialchars($course['category_name']) ?></span>
                                <div class="course-image">
                                    <img src="<?= $imagePath ?>" alt="<?= htmlspecialchars($course['title']) ?>" loading="lazy">
                                    <div class="course-overlay">
                                        <div class="rating-info">
                                            <i class="fas fa-star text-warning"></i> <?= $rating ?> (<?= mt_rand(50, 200) ?> Reviews)
                                        </div>
                                    </div>
                                </div>
                                <div class="course-content">
                                    <h3><?= htmlspecialchars($course['title']) ?></h3>
                                    
                                    <div class="course-meta mb-3">
                                        <span class="meta-item"><i class="far fa-clock"></i> <?= $duration ?></span>
                                        <span class="meta-item"><i class="fas fa-user-graduate"></i> <?= $admissions ?> Students</span>
                                    </div>

                                    <div class="course-footer">
                                        <div class="pricing-box">
                                            <span class="sale-price">₹<?= number_format($course['sale_price'], 0) ?></span>
                                            <?php if ($course['mrp'] > $course['sale_price']): ?>
                                                <span class="mrp-price">₹<?= number_format($course['mrp'], 0) ?></span>
                                            <?php endif; ?>
                                        </div>
                                        <a href="course-details.php?slug=<?= $course['slug'] ?>" class="btn-enroll">View Details <i class="fas fa-arrow-right ml-1"></i></a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <!-- Pagination & Navigation -->
                <div class="swiper-pagination"></div>
                <div class="swiper-button-next"></div>
                <div class="swiper-button-prev"></div>
            </div>
        <?php else: ?>
            <div class="text-center py-5">
                <p class="text-muted">No courses available at the moment. Please check back later.</p>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- Swiper JS -->
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const swiper = new Swiper('.courses-swiper', {
            slidesPerView: 1,
            spaceBetween: 20,
            loop: true,
            pagination: {
                el: '.swiper-pagination',
                clickable: true,
            },
            navigation: {
                nextEl: '.swiper-button-next',
                prevEl: '.swiper-button-prev',
            },
            autoplay: {
                delay: 4000,
                disableOnInteraction: false,
            },
            breakpoints: {
                640: {
                    slidesPerView: 1,
                    spaceBetween: 20,
                },
                768: {
                    slidesPerView: 2,
                    spaceBetween: 30,
                },
                1024: {
                    slidesPerView: 3,
                    spaceBetween: 30,
                },
            }
        });
    });
</script>
