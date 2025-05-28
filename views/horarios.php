<?php

/**
 * Main view for schedule management
 * 
 * @author Antonio Esteban Lorenzo
 */

// Include necessary files
require_once '../includes/auth.php';
require_once '../includes/utils.php';
require_once '../api/config.php';

// Verify that the user is authenticated
require_login();

// Get user information
$usuario_id = $_SESSION['user_id'];
$usuario_nombre = $_SESSION['user_nombre'];
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QUADERN MESTRES</title>
    <link rel="shortcut icon" href="../img/logo2.png">
    <link rel="stylesheet" href="../style/base.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="../style/schedules.css?v=<?php echo time(); ?>">
    <!--Icon Library-->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!--JS library to take screenshots-->
    <script src="https://html2canvas.hertzen.com/dist/html2canvas.min.js"></script>
</head>

<body>
    <!-- Navbar -->
    <nav class="navbar">
        <div class="nav-left">
            <div class="menu-toggle" id="menu-toggle">
                <i class="fas fa-bars"></i>
            </div>
            <div class="logo">
                <img src="../img/logo2.png" alt="Logo Quadern Mestres">
            </div>
            <h1>QUADERN de Mestres</h1>
        </div>
        <div class="nav-right">
            <div class="user-info">
                <span id="user-name">Bienvenido/a, <?php echo htmlspecialchars($usuario_nombre); ?></span>
            </div>
            <div class="logout-btn" onclick="location.href='../api/logout.php'">
                <img src="../img/salida.png"></img>
            </div>
        </div>
    </nav>

    <!-- Side menu -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <h3>Menú</h3>
            <div class="close-sidebar" id="close-sidebar">
                <i class="fas fa-times"></i>
            </div>
        </div>
        <ul class="sidebar-menu">
            <li><a href="dashboard.php"><i class="fas fa-home"></i> Inicio</a></li>
            <li><a href="calendario.php"><i class="fas fa-calendar"></i> Calendario</a></li>
            <li><a href="alumnos.php"><i class="fas fa-users"></i> Alumnado</a></li>
            <li><a href="reuniones.php"><i class="fas fa-comments"></i> Reuniones</a></li>
            <li><a href="asignaturas.php"><i class="fas fa-book"></i> Asignaturas</a></li>
            <li><a href="asistencias.php"><i class="fas fa-book"></i> Asistencias</a></li>
            <li><a href="evaluaciones.php"><i class="fas fa-clipboard-list"></i> Evaluaciones</a></li>
            <li class="active"><a href="horarios.php"><i class="fas fa-clock"></i> Horarios</a></li>
            <li><a href="../api/logout.php"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a></li>
        </ul>
    </div>

    <!-- Overlay for when the sidebar is open -->
    <div class="overlay" id="overlay"></div>

    <!-- Main content -->
    <main class="main-content">
        <div class="header-section-misHorarios">
            <h2>Mis Horarios</h2>
        </div>
        <div class="header-section-button">
            <button id="nuevo-horario-btn" class="action-btn">
                <i class="fas fa-plus"></i> Nuevo Horario
            </button>
        </div>

        <!-- Horarios list section -->
        <div class="horarios-container">
            <div class="loading-indicator" id="loading">
                <i class="fas fa-spinner fa-spin"></i> Cargando...
            </div>

            <div id="horarios-list" class="horarios-list">
                <!-- Filled with JavaScript -->
            </div>

            <div id="no-horarios" class="no-data-message" style="display: none;">
                <i class="fas fa-clock"></i>
                <p>No tienes horarios creados</p>
                <p>Crea tu primer horario para organizar tus clases y actividades</p>
            </div>
        </div>
    </main>

    <!-- Modal for creating/editing schedules -->
    <div id="horario-modal" class="modal">
        <div class="modal-content">
            <span class="close-modal">&times;</span>
            <h2 id="horario-modal-title" class="modal-title">Nuevo Horario</h2>

            <form id="horario-form">
                <input type="hidden" id="horario-id" name="id" value="">

                <div class="form-group">
                    <label for="horario-nombre">Nombre del horario <span class="required">*</span></label>
                    <input type="text" id="horario-nombre" name="nombre" required placeholder="Ej: Horario Segundo Trimestre">
                </div>

                <div class="form-group">
                    <label for="horario-descripcion">Descripción</label>
                    <textarea id="horario-descripcion" name="descripcion" placeholder="Descripción o notas adicionales"></textarea>
                </div>

                <div class="form-group">
                    <label for="horario-dias">Días de la semana</label>
                    <select id="horario-dias" name="dias_semana">
                        <option value="5">Lunes a Viernes</option>
                        <option value="6">Lunes a Sábado</option>
                        <option value="7">Lunes a Domingo</option>
                    </select>
                </div>

                <div class="modal-buttons">
                    <button type="button" id="delete-horario" class="modal-btn delete" style="display: none;">Eliminar</button>
                    <button type="submit" class="modal-btn save">Guardar</button>
                </div>
            </form>
        </div>
    </div>

    <div id="delete-modal" class="modal">
        <div class="modal-content delete-modal-content">
                <h2 class="modal-title">Eliminar</h2>            
            <div class="modal-message">
                <p>¿Estás seguro de que deseas eliminar el horario? <br><span id="delete-horario-name"></span></p>
                <p class="warning-text">Esta acción no se puede deshacer y se eliminarán todos los bloques asociados.</p>
            </div>
            <div class="modal-buttons">
                <button type="button" id="cancel-delete" class="modal-btn cancel">Cancelar</button>
                <button type="button" id="confirm-delete" class="modal-btn delete">
                    <i class="fas fa-trash"></i> Eliminar
                </button>
            </div>
        </div>
    </div>

    <!-- Footer -->
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
    <script src="../js/horarios.js?v=<?php echo time(); ?>"></script>
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
    <script>
        // Script for the side menu
        document.addEventListener('DOMContentLoaded', function() {
            const menuToggle = document.getElementById('menu-toggle');
            const sidebar = document.getElementById('sidebar');
            const closeSidebar = document.getElementById('close-sidebar');
            const overlay = document.getElementById('overlay');

            // Open side menu
            menuToggle.addEventListener('click', function() {
                sidebar.classList.add('active');
                overlay.classList.add('active');
            });

            // Close side menu (X button)
            closeSidebar.addEventListener('click', function() {
                sidebar.classList.remove('active');
                overlay.classList.remove('active');
            });

            // Close side menu (click on overlay)
            overlay.addEventListener('click', function() {
                sidebar.classList.remove('active');
                overlay.classList.remove('active');
            });
        });
    </script>
    <!-- Scripts for the borderless nav -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            window.addEventListener('scroll', function() {
                const navbar = document.querySelector('.navbar');

                // If we have scrolled more than 10px, we add the shadow class
                if (window.scrollY > 10) {
                    navbar.classList.add('navbar-shadow');
                } else {
                    navbar.classList.remove('navbar-shadow');
                }
            });
        });
    </script>
</body>

</html>