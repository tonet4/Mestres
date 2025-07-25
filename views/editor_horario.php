<?php
/**
 * Schedule editor view
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

// Get the schedule ID from the query parameters
$horario_id = isset($_GET['id']) ? (int)$_GET['id'] : null;

if (!$horario_id) {
    // Redirect to the schedules main page if no ID is provided
    header('Location: horarios.php');
    exit;
}

// Check if the schedule belongs to the user
try {
    $stmt = $conn->prepare("SELECT id, nombre, descripcion, dias_semana FROM horarios WHERE id = :id AND usuario_id = :usuario_id");
    $stmt->bindParam(':id', $horario_id);
    $stmt->bindParam(':usuario_id', $usuario_id);
    $stmt->execute();
    
    $horario = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$horario) {
        // Schedule not found or not owned by the user
        header('Location: horarios.php');
        exit;
    }
} catch (PDOException $e) {
    // Error in the database query
    header('Location: horarios.php');
    exit;
}
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
            <li><a href="alumnos.php"><i class="fas fa-users"></i> Alumnos-Grupos</a></li>
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
        <div class="header-section">
            <div class="header-arrow">
                <a href="horarios.php" class="back-link"><i class="fas fa-arrow-left"></i></a>
            </div>
            <div class="header-title">
                
                <h2 id="horario-titulo"><?php echo htmlspecialchars($horario['nombre']); ?></h2>
                <span id="horario-descripcion" class="header-description">
                    <?php echo htmlspecialchars($horario['descripcion'] ?: 'Sin descripción'); ?>
                </span>
            </div>
            <div class="header-actions">
                <button id="export-horario-btn" class="action-btn export">
                    <i class="fas fa-file-export"></i> Exportar
                </button>
                <button id="add-bloque-btn" class="action-btn">
                    <i class="fas fa-plus"></i> Añadir Bloque
                </button>
            </div>
        </div>

        <!-- Schedule grid section -->
        <div class="horario-container">
            <div class="loading-indicator" id="loading">
                <i class="fas fa-spinner fa-spin"></i> Cargando...
            </div>
            
            <div id="horario-grid" class="horario-grid">
                <!-- This will be filled with JavaScript -->
            </div>
            
            <div id="no-bloques" class="no-data-message" style="display: none;">
                <i class="fas fa-exclamation-circle"></i>
                <p>No hay bloques en este horario</p>
                <p>Añade tu primer bloque para comenzar a organizar tu horario</p>
            </div>
        </div>
    </main>

    <!-- Modal for creating/editing blocks -->
    <div id="bloque-modal" class="modal">
        <div class="modal-content">
            <span class="close-modal">&times;</span>
            <h2 id="bloque-modal-title" class="modal-title">Nuevo Bloque Horario</h2>
            
            <form id="bloque-form">
                <input type="hidden" id="bloque-id" name="id" value="">
                <input type="hidden" id="bloque-horario-id" name="horario_id" value="<?php echo $horario_id; ?>">
                <input type="hidden" id="bloque-color" name="color" value="#3498db">
                
                <div class="form-group">
                    <label for="bloque-dia">Día <span class="required">*</span></label>
                    <select id="bloque-dia" name="dia_semana" required>
                        <option value="1">Lunes</option>
                        <option value="2">Martes</option>
                        <option value="3">Miércoles</option>
                        <option value="4">Jueves</option>
                        <option value="5">Viernes</option>
                        <?php if ($horario['dias_semana'] >= 6): ?>
                        <option value="6">Sábado</option>
                        <?php endif; ?>
                        <?php if ($horario['dias_semana'] >= 7): ?>
                        <option value="7">Domingo</option>
                        <?php endif; ?>
                    </select>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="bloque-hora-inicio">Hora inicio <span class="required">*</span></label>
                        <input type="time" id="bloque-hora-inicio" name="hora_inicio" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="bloque-hora-fin">Hora fin <span class="required">*</span></label>
                        <input type="time" id="bloque-hora-fin" name="hora_fin" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="bloque-titulo">Título <span class="required">*</span></label>
                    <input type="text" id="bloque-titulo" name="titulo" required placeholder="Ej: Matemáticas 2º B">
                </div>
                
                <div class="form-group">
                    <label for="bloque-descripcion">Descripción</label>
                    <textarea id="bloque-descripcion" name="descripcion" placeholder="Aula, notas, etc."></textarea>
                </div>
                
                <div class="form-group">
                    <label>Color</label>
                    <div class="color-options">
                        <div class="color-option selected" data-color="#3498db" style="background-color: #3498db;"></div>
                        <div class="color-option" data-color="#2ecc71" style="background-color: #2ecc71;"></div>
                        <div class="color-option" data-color="#f1c40f" style="background-color: #f1c40f;"></div>
                        <div class="color-option" data-color="#e74c3c" style="background-color: #e74c3c;"></div>
                        <div class="color-option" data-color="#9b59b6" style="background-color: #9b59b6;"></div>
                        <div class="color-option" data-color="#1abc9c" style="background-color: #1abc9c;"></div>
                        <div class="color-option" data-color="#34495e" style="background-color: #34495e;"></div>
                        <div class="color-option" data-color="#e67e22" style="background-color: #e67e22;"></div>
                    </div>
                </div>
                
                <div class="modal-buttons">
                    <button type="button" id="delete-bloque" class="modal-btn delete" style="display: none;">Eliminar</button>
                    <button type="submit" class="modal-btn save">Guardar</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal for schedule export preview -->
    <div id="export-modal" class="modal export-modal">
        <div class="modal-content export-modal-content">
            <span class="close-modal">&times;</span>
            <h2 class="modal-title">Vista Previa del Horario</h2>
            
            <div id="export-preview" class="export-preview">
                <!-- This will be filled with JavaScript -->
            </div>
            
            <div class="modal-buttons">
                <button id="download-horario" class="modal-btn save">
                    <i class="fas fa-download"></i> Descargar
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
    <script src="../js/editor_horario.js?v=<?php echo time(); ?>"></script>
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