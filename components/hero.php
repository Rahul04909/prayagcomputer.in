<?php
// Modern Background-Only Hero Slider for Prayag Computer Center
?>
<section class="hero-slider">
    <div class="slider-container" id="heroSlider">
        <div class="slide active" style="background-image: url('assets/images/hero/banner-1.webp');"></div>
        <div class="slide" style="background-image: url('assets/images/hero/banner-2.webp');"></div>
        <div class="slide" style="background-image: url('assets/images/hero/banner-3.webp');"></div>
    </div>
</section>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const slides = document.querySelectorAll('.slide');
        let currentSlide = 0;
        const totalSlides = slides.length;
        const intervalTime = 5000; // 5 seconds per slide

        function nextSlide() {
            // Remove active class from current slide
            slides[currentSlide].classList.remove('active');
            
            // Increment current slide index
            currentSlide = (currentSlide + 1) % totalSlides;
            
            // Add active class to new current slide
            slides[currentSlide].classList.add('active');
        }

        // Start automatic transition
        setInterval(nextSlide, intervalTime);
    });
</script>
