<?php
// Professional Course Carousel for Prayag Computer Center
?>
<section class="courses-section" id="courses">
    <div class="section-header">
        <h2>Expert-Led Courses</h2>
        <p>Advance your career with our job-oriented computer and stenography programs designed for the modern industry.</p>
    </div>

    <!-- Swiper Container -->
    <div class="swiper courses-swiper">
        <div class="swiper-wrapper">
            <!-- Course Card 1 -->
            <div class="swiper-slide">
                <article class="course-card">
                    <div class="course-image">
                        <span class="course-badge">Steno Specail</span>
                        <img src="assets/images/courses/steno.png" alt="Stenography Course Mastery">
                    </div>
                    <div class="course-content">
                        <h3>Stenography Mastery</h3>
                        <p>Comprehensive training in Hindi & English Shorthand with high-speed typing focus for government jobs.</p>
                        <div class="course-footer">
                            <span class="course-duration"><i class="fas fa-clock"></i> 6-12 Months</span>
                            <a href="#contact" class="btn-enroll">Learn More <i class="fas fa-arrow-right"></i></a>
                        </div>
                    </div>
                </article>
            </div>

            <!-- Course Card 2 -->
            <div class="swiper-slide">
                <article class="course-card">
                    <div class="course-image">
                        <span class="course-badge">Diploma</span>
                        <img src="assets/images/courses/dca.png" alt="DCA & PGDCA Diploma Course">
                    </div>
                    <div class="course-content">
                        <h3>DCA & PGDCA</h3>
                        <p>Recognized university diplomas covering Office Automation, Web Design, and Database Management.</p>
                        <div class="course-footer">
                            <span class="course-duration"><i class="fas fa-clock"></i> 12 Months</span>
                            <a href="#contact" class="btn-enroll">Learn More <i class="fas fa-arrow-right"></i></a>
                        </div>
                    </div>
                </article>
            </div>

            <!-- Course Card 3 -->
            <div class="swiper-slide">
                <article class="course-card">
                    <div class="course-image">
                        <span class="course-badge">Accounting</span>
                        <img src="assets/images/courses/tally.png" alt="Tally Prime with GST Course">
                    </div>
                    <div class="course-content">
                        <h3>Tally Prime with GST</h3>
                        <p>Master financial accounting, inventory management, and taxation with the latest Tally Prime software.</p>
                        <div class="course-footer">
                            <span class="course-duration"><i class="fas fa-clock"></i> 3 Months</span>
                            <a href="#contact" class="btn-enroll">Learn More <i class="fas fa-arrow-right"></i></a>
                        </div>
                    </div>
                </article>
            </div>

            <!-- Course Card 4 (Duplicate for Carousel redundancy) -->
            <div class="swiper-slide">
                <article class="course-card">
                    <div class="course-image">
                        <span class="course-badge">Advanced</span>
                        <img src="assets/images/courses/dca.png" alt="ADCA Advanced Computer Course">
                    </div>
                    <div class="course-content">
                        <h3>ADCA Specialist</h3>
                        <p>Advance your skills with Graphics Designing, Hardware & Networking, and Advanced Excel Mastery.</p>
                        <div class="course-footer">
                            <span class="course-duration"><i class="fas fa-clock"></i> 12 Months</span>
                            <a href="#contact" class="btn-enroll">Learn More <i class="fas fa-arrow-right"></i></a>
                        </div>
                    </div>
                </article>
            </div>
        </div>

        <!-- Pagination & Navigation -->
        <div class="swiper-pagination"></div>
        <div class="swiper-button-prev"></div>
        <div class="swiper-button-next"></div>
    </div>
</section>

<!-- Swiper JS Integration -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const courseSwiper = new Swiper('.courses-swiper', {
            slidesPerView: 1,
            spaceBetween: 20,
            loop: true,
            autoplay: {
                delay: 4000,
                disableOnInteraction: false,
                pauseOnMouseEnter: true,
            },
            pagination: {
                el: '.swiper-pagination',
                clickable: true,
            },
            navigation: {
                nextEl: '.swiper-button-next',
                prevEl: '.swiper-button-prev',
            },
            breakpoints: {
                640: {
                    slidesPerView: 2,
                    spaceBetween: 30,
                },
                1024: {
                    slidesPerView: 3,
                    spaceBetween: 40,
                },
                1280: {
                    slidesPerView: 4,
                    spaceBetween: 40,
                },
            }
        });
    });
</script>
