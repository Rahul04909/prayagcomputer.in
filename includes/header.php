<?php
// Modern Responsive Header for Prayag Computer Center
?>
<header class="main-header">
    <div class="header-inner">
        <!-- Logo -->
        <div class="logo-container">
            <a href="index.php">
                <img src="assets/prayag-computer-logo.png" alt="Prayag Computer Center Logo">
                <div class="logo-text">
                    PRAYAG COMPUTER
                    <span>Center & Steno School</span>
                </div>
            </a>
        </div>

        <!-- Navigation Menu -->
        <nav class="nav-menu" id="navMenu">
            <a href="index.php" class="nav-link">Home</a>
            <a href="#courses" class="nav-link">Courses</a>
            <a href="#about" class="nav-link">About Us</a>
            <a href="#gallery" class="nav-link">Gallery</a>
            <a href="#contact" class="nav-link">Contact</a>
        </nav>

        <!-- CTA Actions -->
        <div class="header-actions">
            <a href="tel:+91XXXXXXXXXX" class="btn-contact">Inquiry Now</a>
        </div>

        <!-- Mobile Toggle Button -->
        <button class="mobile-toggle" id="mobileToggle" aria-label="Toggle Menu">
            <span class="hamburger"></span>
        </button>
    </div>
</header>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const mobileToggle = document.getElementById('mobileToggle');
        const navMenu = document.getElementById('navMenu');
        const body = document.body;

        mobileToggle.addEventListener('click', function() {
            mobileToggle.classList.toggle('active');
            navMenu.classList.toggle('active');
            
            // Prevent scroll when menu is open
            if (navMenu.classList.contains('active')) {
                body.style.overflow = 'hidden';
            } else {
                body.style.overflow = 'auto';
            }
        });

        // Close menu when clicking links
        const navLinks = document.querySelectorAll('.nav-link');
        navLinks.forEach(link => {
            link.addEventListener('click', () => {
                mobileToggle.classList.remove('active');
                navMenu.classList.remove('active');
                body.style.overflow = 'auto';
            });
        });
        
        // Close menu when clicking outside
        document.addEventListener('click', (e) => {
            if (!navMenu.contains(e.target) && !mobileToggle.contains(e.target) && navMenu.classList.contains('active')) {
                mobileToggle.classList.remove('active');
                navMenu.classList.remove('active');
                body.style.overflow = 'auto';
            }
        });
    });
</script>
