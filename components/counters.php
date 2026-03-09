<?php
// Interactive Counter Component for Prayag Computer Center
?>
<section class="stats-section">
    <div class="stats-container" id="statsContainer">
        <!-- Stat Item 1 -->
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-award"></i></div>
            <span class="stat-number" data-target="10">0</span>
            <span class="stat-label">Years of Excellence</span>
        </div>

        <!-- Stat Item 2 -->
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-user-graduate"></i></div>
            <span class="stat-number" data-target="5000">0</span>
            <span class="stat-label">Students Trained</span>
        </div>

        <!-- Stat Item 3 -->
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-chalkboard-teacher"></i></div>
            <span class="stat-number" data-target="25">0</span>
            <span class="stat-label">Expert Faculty</span>
        </div>

        <!-- Stat Item 4 -->
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-book-open"></i></div>
            <span class="stat-number" data-target="15">0</span>
            <span class="stat-label">Job-Ready Courses</span>
        </div>
    </div>
</section>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const stats = document.querySelectorAll('.stat-number');
        const statsContainer = document.getElementById('statsContainer');
        let animated = false;

        const animateStats = () => {
            stats.forEach(stat => {
                const target = parseInt(stat.getAttribute('data-target'));
                const increment = target / 50; // Controls speed

                const updateCount = () => {
                    const count = parseInt(stat.innerText);
                    if (count < target) {
                        stat.innerText = Math.ceil(count + increment);
                        setTimeout(updateCount, 30);
                    } else {
                        stat.innerText = target + '+';
                    }
                };
                updateCount();
            });
        };

        // Use Intersection Observer to trigger animation when visible
        const observer = new IntersectionObserver((entries) => {
            if (entries[0].isIntersecting && !animated) {
                animateStats();
                animated = true;
            }
        }, { threshold: 0.5 });

        if (statsContainer) {
            observer.observe(statsContainer);
        }
    });
</script>
