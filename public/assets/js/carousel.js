document.addEventListener('DOMContentLoaded', function() {
    const carousel = document.querySelector('.carousel');
    if (!carousel) return;

    const slides = Array.from(carousel.querySelectorAll('.carousel-slide'));
    const prevBtn = carousel.querySelector('.carousel-arrow.prev');
    const nextBtn = carousel.querySelector('.carousel-arrow.next');
    let currentSlide = 0;
    let slideInterval;
    let isTransitioning = false;

    function updateCarouselHeight() {
        const viewportHeight = window.innerHeight;
        carousel.style.height = `${viewportHeight}px`;
        slides.forEach(slide => {
            slide.style.height = `${viewportHeight}px`;
        });
    }

    function showSlide(index) {
        if (!slides.length || isTransitioning) return;
        isTransitioning = true;

        const newIndex = (index + slides.length) % slides.length;
        const currentActive = carousel.querySelector('.carousel-slide.active');
        const nextSlide = slides[newIndex];

        if (currentActive) {
            currentActive.style.transition = 'opacity 0.8s ease-in-out, transform 0.8s ease-in-out';
            currentActive.classList.remove('active');
        }

        nextSlide.style.transition = 'opacity 0.8s ease-in-out, transform 6s ease-out';
        nextSlide.classList.add('active');
        currentSlide = newIndex;

        setTimeout(() => {
            isTransitioning = false;
        }, 800);
    }

    function nextSlide() {
        showSlide(currentSlide + 1);
    }

    function prevSlide() {
        showSlide(currentSlide - 1);
    }

    function startSlideShow() {
        stopSlideShow();
        slideInterval = setInterval(nextSlide, 6000);
    }

    function stopSlideShow() {
        if (slideInterval) {
            clearInterval(slideInterval);
            slideInterval = null;
        }
    }

    if (prevBtn) {
        prevBtn.addEventListener('click', (e) => {
            e.preventDefault();
            prevSlide();
            stopSlideShow();
            startSlideShow();
        });
    }

    if (nextBtn) {
        nextBtn.addEventListener('click', (e) => {
            e.preventDefault();
            nextSlide();
            stopSlideShow();
            startSlideShow();
        });
    }

    window.addEventListener('resize', updateCarouselHeight);
    window.addEventListener('orientationchange', updateCarouselHeight);
    updateCarouselHeight();

    showSlide(0);
    startSlideShow();

    carousel.addEventListener('mouseenter', stopSlideShow);
    carousel.addEventListener('mouseleave', startSlideShow);

    document.addEventListener('visibilitychange', () => {
        if (document.hidden) {
            stopSlideShow();
        } else {
            startSlideShow();
        }
    });
});