<?php
/**
 * @author Antonio Esteban Lorenzo
 * 
 */

session_start();
// If there is already an active session, redirect to the dashboard
if(isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
}

// Include the login processor
require_once '../includes/procesar_login.php';

// Variables for messages
$error = '';

// Process login form
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $remember = isset($_POST['remember']);
    
    // Process login
    $resultado = login_usuario($email, $password, $remember);
    
    if($resultado['success']) {
        // Redirect to dashboard
        header("Location: dashboard.php");
        exit;
    } else {
        $error = $resultado['message'];
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QUADERN MESTRES</title>
    <link rel="shortcut icon" href="../img/logo2.png">
    <link rel="stylesheet" href="../style/base_part2.css">
    <link rel="stylesheet" href="../style/forms.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <!-- Nav -->
    <nav class="navbar">
        <div class="nav-left">
            <div class="logo">
                <a href="../index.html">
                    <img src="../img/logo2.png" alt="logo">
                </a>
            </div>
            <h1 onclick="window.location.href='index.html'">QUADERN MESTRES</h1>
        </div>
        <div class="nav-right">
            <a href="registro.php" class="btn btn-registro">Registro</a>
            <a href="login.php" class="btn btn-acceso">Acceso</a>
        </div>
        <!-- Hamburger menu for mobile -->
        <div class="menu-toggle">
            <i class="fas fa-bars"></i>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="form-container">
        <section class="form-section">
            <h2>Acceso a tu cuenta</h2>
            
            <?php if($error): ?>
                <div class="error-message">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <form action="login.php" method="post">
                <div class="form-group">
                    <label for="email">Correo electrónico</label>
                    <input type="email" id="email" name="email" value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Contraseña</label>
                    <input type="password" id="password" name="password" required>
                </div>
                
                <div class="form-group remember-group">
                    <input type="checkbox" id="remember" name="remember">
                    <label for="remember">Recordar mi sesión</label>
                </div>
                
                <button type="submit" class="btn btn-acceso btn-block">Iniciar sesión</button>
                
                <div class="form-footer">
                    <p>¿No tienes cuenta? <a href="registro.php">Regístrate aquí</a></p>
                </div>
            </form>
        </section>
    </main>

    <footer class="footer-dark">
        <div class="footer-section">
            <div class="footer-logo">
                <img src="../img/logo2.png" alt="Logo Quadern Mestres">
                <div>
                    <h3>QUADERN de Mestres</h3>
                    <span>v1.0 · 2025</span>
                </div>
            </div>
        </div>

        <div class="footer-section">
            <h4>Enlaces Útiles</h4>
            <div class="footer-links">
                <a href="privacidad.html" target="_blank"><i class="fas fa-shield-alt"></i> Privacidad</a>
                <a href="condiciones.html" target="_blank"><i class="fas fa-file-contract"></i> Condiciones</a>
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
            <p>Proyecto desarrollado por Antonio Esteban Lorenzo · CFGS DAW · IES La Sénia,Paiporta</p>
            <p>&copy; 2025 QUADERN MESTRES - Todos los derechos reservados</p>
            <div class="back-to-top" id="back-to-top">
                <a href="#" aria-label="Volver arriba"><i class="fas fa-arrow-up"></i></a>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="../js/main.js"></script>
    <!-- JavaScript for the up button -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const backToTopButton = document.getElementById('back-to-top');

            window.addEventListener('scroll', function() {
                if (window.scrollY > 300) {
                    backToTopButton.classList.add('show');
                } else {
                    backToTopButton.classList.remove('show');
                }
            });

            backToTopButton.addEventListener('click', function(e) {
                e.preventDefault();
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
            });
        });
    </script>
</body>
</html>