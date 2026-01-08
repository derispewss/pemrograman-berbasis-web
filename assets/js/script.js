/**
 * My Daily Journal - JavaScript
 * Enhanced functionality and animations
 */

document.addEventListener('DOMContentLoaded', function () {
    // Initialize all features
    initDateTime();
    initScrollAnimations();
    initSmoothScroll();
    initNavbarScroll();
});

/**
 * Real-time Date and Time Display
 */
function initDateTime() {
    const dateElement = document.getElementById('tanggal');
    const timeElement = document.getElementById('jam');

    if (!dateElement || !timeElement) return;

    function updateDateTime() {
        const now = new Date();

        // Format date
        const options = {
            weekday: 'long',
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        };
        const dateStr = now.toLocaleDateString('id-ID', options);

        // Format time
        const timeStr = now.toLocaleTimeString('id-ID', {
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit'
        });

        dateElement.textContent = dateStr + ' | ';
        timeElement.textContent = timeStr;
    }

    // Update immediately and then every second
    updateDateTime();
    setInterval(updateDateTime, 1000);
}

/**
 * Scroll Animation for Elements
 */
function initScrollAnimations() {
    const fadeElements = document.querySelectorAll('.fade-in');

    if (fadeElements.length === 0) return;

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
            }
        });
    }, {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    });

    fadeElements.forEach(element => {
        observer.observe(element);
    });
}

/**
 * Smooth Scroll for Navigation Links
 */
function initSmoothScroll() {
    const navLinks = document.querySelectorAll('a[href^="#"]');

    navLinks.forEach(link => {
        link.addEventListener('click', function (e) {
            const href = this.getAttribute('href');

            if (href === '#') return;

            const targetElement = document.querySelector(href);

            if (targetElement) {
                e.preventDefault();

                const navbarHeight = document.querySelector('.navbar').offsetHeight;
                const targetPosition = targetElement.getBoundingClientRect().top + window.pageYOffset - navbarHeight;

                window.scrollTo({
                    top: targetPosition,
                    behavior: 'smooth'
                });

                // Close mobile navbar if open
                const navbarCollapse = document.querySelector('.navbar-collapse');
                if (navbarCollapse.classList.contains('show')) {
                    const bsCollapse = new bootstrap.Collapse(navbarCollapse, {
                        toggle: false
                    });
                    bsCollapse.hide();
                }
            }
        });
    });
}

/**
 * Navbar Background Change on Scroll
 */
function initNavbarScroll() {
    const navbar = document.querySelector('.navbar');

    if (!navbar) return;

    function handleScroll() {
        if (window.scrollY > 100) {
            navbar.style.background = 'rgba(255, 255, 255, 0.98)';
            navbar.style.boxShadow = '0 4px 15px rgba(0,0,0,0.1)';
        } else {
            navbar.style.background = 'rgba(255, 255, 255, 0.95)';
            navbar.style.boxShadow = '0 2px 4px rgba(0,0,0,0.05)';
        }
    }

    window.addEventListener('scroll', handleScroll);
    handleScroll(); // Initial check
}

/**
 * Add staggered animation delay to card elements
 */
function addStaggeredDelay() {
    const cards = document.querySelectorAll('.article-card, .schedule-card');

    cards.forEach((card, index) => {
        card.style.animationDelay = `${index * 0.1}s`;
    });
}

// Call staggered delay on load
window.addEventListener('load', addStaggeredDelay);
