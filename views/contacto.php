<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QUADERN MESTRES</title>
    <link rel="shortcut icon" href="../img/logo2.png">
    <link rel="stylesheet" href="../style/base.css">
    <link rel="stylesheet" href="../style/dashboard.css">
        <link rel="stylesheet" href="../style/enlaces.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body>
    <!-- Navbar -->
    <nav class="navbar">
        <div class="nav-left">
            <div class="logo">
                <a href="../index.html">
                    <img src="../img/logo2.png" alt="logo">
                </a>
            </div>
            <h1>QUADERN de Mestres</h1>
        </div>
        <div class="nav-right">
            <div class="user-info">
                <span id="user-name">Bienvenido/a, <?php echo htmlspecialchars($_SESSION['user_nombre'] ?? 'Usuario'); ?></span>
            </div>
            <div class="logout-btn" onclick="location.href='../api/logout.php'">
                <img src="../img/salida.png"></img>
            </div>
        </div>
    </nav>

    <!-- Main -->
    <main class="main-content">
        
        <h1 class="page-title">Contacto</h1>
        
        <div id="success-message" class="success-message">
            Tu mensaje ha sido enviado correctamente. Nos pondremos en contacto contigo lo antes posible.
        </div>
        
        <div id="error-message" class="error-message">
            Ha ocurrido un error al enviar el mensaje. Por favor, inténtalo de nuevo más tarde.
        </div>
        
        <div class="contact-container">
            <div class="contact-form-container">
                <h2>Envíanos un mensaje</h2>
                <form class="contact-form" id="contact-form" method="POST" action="../api/send_contact.php">
                    <div class="form-group">
                        <label for="name">Nombre <span class="required-field">*</span></label>
                        <input type="text" id="name" name="name" required placeholder="Tu nombre">
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Correo electrónico <span class="required-field">*</span></label>
                        <input type="email" id="email" name="email" required placeholder="correo@ejemplo.com">
                    </div>
                    
                    <div class="form-group">
                        <label for="subject">Asunto <span class="required-field">*</span></label>
                        <select id="subject" name="subject" required>
                            <option value="" disabled selected>Selecciona una opción</option>
                            <option value="soporte">Soporte técnico</option>
                            <option value="sugerencia">Sugerencia</option>
                            <option value="funcionalidad">Nueva funcionalidad</option>
                            <option value="error">Reportar error</option>
                            <option value="otro">Otro</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="message">Mensaje <span class="required-field">*</span></label>
                        <textarea id="message" name="message" required placeholder="Escribe tu mensaje aquí..."></textarea>
                    </div>
                    
                    <button type="submit">Enviar mensaje</button>
                </form>
            </div>
            
            <div class="contact-info-container">
                <h2>Información de contacto</h2>
                
                <div class="contact-info-item">
                    <h3><i class="fas fa-envelope"></i> Correo</h3>
                    <p>antonio.esteban.lorenzo.88@gmail.com</p>
                </div>
                
                <div class="contact-info-item">
                    <h3><i class="fas fa-phone"></i> Teléfono</h3>
                    <p>(34) 610 30 20 52</p>
                    <p>Lunes a Viernes: 9:00 - 18:00</p>
                </div>
                
                
                <div class="contact-social">
                    <h3>Síguenos</h3>
                    <div class="social-links">
                        <a href="https://twitter.com/quadernmestres" target="_blank" rel="noopener noreferrer" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
                        <a href="https://instagram.com/quadernmestres" target="_blank" rel="noopener noreferrer" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                        <a href="https://github.com/tone4" target="_blank" rel="noopener noreferrer" aria-label="GitHub"><i class="fab fa-github"></i></a>
                        <a href="https://linkedin.com/company/quadernmestres" target="_blank" rel="noopener noreferrer" aria-label="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!--FOOTER-->
    <footer class="footer-dark">
        <div class="footer-section">
            <div class="footer-logo">
                <a href="../index.html">
                    <img src="../img/logo2.png" alt="Logo Quadern Mestres">
                </a>
                <div>
                    <h3>QUADERN de Mestres</h3>
                    <span>v1.0 · 2025</span>
                </div>
            </div>
        </div>

        <div class="footer-section">
            <h4>Enlaces Útiles</h4>
            <div class="footer-links">
                <a href="privacidad.php" target="_blank"><i class="fas fa-shield-alt"></i> Privacidad</a>
                <a href="condiciones.php" target="_blank"><i class="fas fa-file-contract"></i> Condiciones</a>
                <a href="contacto.php" target="_blank"><i class="fas fa-envelope"></i> Contacto</a>
            </div>
        </div>

        <div class="footer-section">
            <h4>Síguenos</h4>
            <div class="social-icons">
                <a href="https://twitter.com/quadernmestres" target="_blank" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
                <a href="https://instagram.com/quadernmestres" target="_blank" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                <a href="https://github.com/tone4" target="_blank" aria-label="GitHub"><i class="fab fa-github"></i></a>
                <a href="https://linkedin.com/company/quadernmestres" target="_blank" aria-label="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
            </div>
        </div>

        <div class="footer-section credits-section">
            <p>Proyecto desarrollado por Antonio Esteban Lorenzo · CFGS DAW · IES La Sénia, Paiporta</p>
            <p>&copy; 2025 QUADERN MESTRES - Todos los derechos reservados</p>
        </div>
    </footer>

    <div class="back-to-top-fixed" id="back-to-top">
        <a href="#" aria-label="Volver arriba"><i class="fas fa-arrow-up"></i></a>
    </div>

    <!-- JavaScript para el botón de subir arriba y el formulario -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Botón volver arriba
            const backToTopButton = document.getElementById('back-to-top');

            // Mostrar u ocultar el botón dependiendo del scroll
            window.addEventListener('scroll', function() {
                if (window.scrollY > 100) {
                    backToTopButton.classList.add('show');
                } else {
                    backToTopButton.classList.remove('show');
                }
            });

            if (window.scrollY > 100) {
                backToTopButton.classList.add('show');
            }

            backToTopButton.addEventListener('click', function(e) {
                e.preventDefault();
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
            });
            
            const contactForm = document.getElementById('contact-form');
            const successMessage = document.getElementById('success-message');
            const errorMessage = document.getElementById('error-message');
            
            if (contactForm) {
                contactForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    
                                        setTimeout(function() {
                        successMessage.style.display = 'block';
                        contactForm.reset();
                        
                        setTimeout(function() {
                            successMessage.style.display = 'none';
                        }, 5000);
                    }, 1000);
                });
            }
        });
    </script>
</body>

</html>