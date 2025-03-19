document.addEventListener('DOMContentLoaded', function() {
    // Menú responsive
    const menuToggle = document.querySelector('.menu-toggle');
    const navbar = document.querySelector('.navbar');
    
    menuToggle.addEventListener('click', function() {
        navbar.classList.toggle('active');
    });
    
    // Cerrar menú al hacer clic fuera de él
    document.addEventListener('click', function(e) {
        if (!navbar.contains(e.target) && navbar.classList.contains('active')) {
            navbar.classList.remove('active');
        }
    });
    
    // Cerrar menú al redimensionar pantalla
    window.addEventListener('resize', function() {
        if (window.innerWidth > 768 && navbar.classList.contains('active')) {
            navbar.classList.remove('active');
        }
    });
});