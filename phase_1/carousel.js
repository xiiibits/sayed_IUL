document.addEventListener('DOMContentLoaded', function() {
    let slides = document.querySelectorAll('.carousel-slide');
    let currentSlide = 0;
    
    function showSlide(n) {
        slides.forEach(slide => slide.classList.remove('active'));
        currentSlide = (n + slides.length) % slides.length;
        slides[currentSlide].classList.add('active');
    }
    
    setInterval(() => {
        showSlide(currentSlide + 1);
    }, 5000);
});