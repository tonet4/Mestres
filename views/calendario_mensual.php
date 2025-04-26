<?php

/**
 * @author AntonioEsteban Lorenzo
 * 
 */

// Include the necessary files
require_once '../includes/auth.php';
require_once '../includes/utils.php';
require_once '../api/config.php';


// Verify that the user is authenticated
require_login();

// Get user information
$usuario_id = $_SESSION['user_id'];
$usuario_nombre = $_SESSION['user_nombre'];

// Get current month and year (or requested)
$month = isset($_GET['month']) ? intval($_GET['month']) : date('n');
$year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');

// Make sure month is between 1 and 12
if ($month < 1) {
    $month = 12;
    $year--;
} else if ($month > 12) {
    $month = 1;
    $year++;
}

// Get month name in Spanish
$monthNames = [
    1 => 'Enero',
    2 => 'Febrero',
    3 => 'Marzo',
    4 => 'Abril',
    5 => 'Mayo',
    6 => 'Junio',
    7 => 'Julio',
    8 => 'Agosto',
    9 => 'Septiembre',
    10 => 'Octubre',
    11 => 'Noviembre',
    12 => 'Diciembre'
];
$monthName = $monthNames[$month];
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calendario Mensual - QUADERN MESTRES</title>
    <link rel="stylesheet" href="../estilo/base.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="../estilo/calendar.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="../estilo/calendar_mensual.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="../estilo/calendar_responsive.css?v=<?php echo time(); ?>">
    <!--Icon Library-->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body>
    <!-- Navbar -->
    <nav class="navbar">
        <div class="nav-left">
            <div class="menu-toggle" id="menu-toggle">
                <i class="fas fa-bars"></i>
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
            <li class="active"><a href="calendario.php"><i class="fas fa-calendar"></i> Calendario</a></li>
            <li><a href="#"><i class="fas fa-users"></i> Alumnado</a></li>
            <li><a href="reuniones.php"><i class="fas fa-comments"></i> Reuniones</a></li>
            <li><a href="#"><i class="fas fa-book"></i> Asignaturas</a></li>
            <li><a href="#"><i class="fas fa-clipboard-list"></i> Evaluaciones</a></li>
            <li><a href="#"><i class="fas fa-chart-bar"></i> Estadísticas</a></li>
            <li><a href="#"><i class="fas fa-cog"></i> Configuración</a></li>
            <li><a href="../api/logout.php"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a></li>
        </ul>
    </div>

    <!-- Overlay for when the sidebar is open -->
    <div class="overlay" id="overlay"></div>

    <!-- Main content -->
    <main class="main-content">
        <!-- Calendar view selector -->
        <div class="calendar-views">
            <a href="calendario_anual.php" class="calendar-view-btn" id="yearly-view">
                <img class="iconos-calendario" src="../img/calendario.png" alt="anual">
                <h2>Anual</h2>
            </a>
            <a href="calendario_mensual.php" class="calendar-view-btn active" id="monthly-view">
                <img class="iconos-calendario" src="../img/calendarioo.png" alt="mensual">
                <h2>Mensual</h2>
            </a>
            <a href="calendario.php" class="calendar-view-btn" id="weekly-view">
                <img class="iconos-calendario" src="../img/7-dias.png" alt="dias">
                <h2>Semanal</h2>
            </a>
        </div>

        <!-- Calendar header -->
        <div class="calendar-monthly-header">
            <div class="calendar-title">
                <h2><?php echo $monthName . ' ' . $year; ?></h2>
            </div>
            <div class="calendar-navigation">
                <button class="nav-btn" id="prev-month">
                    <i class="fas fa-chevron-left"></i>
                </button>
                <button class="nav-btn" id="current-month">
                    <i class="fas fa-calendar"></i>
                </button>
                <button class="nav-btn" id="next-month">
                     <i class="fas fa-chevron-right"></i>
                </button>
            </div>
            <div class="add-event-btn">
                <button id="add-event-btn" class="action-btn">
                    <i class="fas fa-plus"></i> Añadir Evento
                </button>
            </div>
        </div>


        <!-- Icon Filter -->
        <div class="icon-filter">
            <h3>Filtrar por icono:</h3>
            <div class="icon-options">
                <button class="icon-option active" data-icon="all">
                    <i class="fas fa-border-all"></i> Todos
                </button>
                <button class="icon-option" data-icon="star">
                    <img src="../img/star.png" alt="favourite"> Favoritos
                </button>
                <button class="icon-option" data-icon="book">
                    <img src="../img/book.png"> Académico
                </button>
                <button class="icon-option" data-icon="users">
                    <img src="../img/users.png" alt="reunion"> Reunión
                </button>
                <button class="icon-option" data-icon="graduation-cap">
                    <img src="../img/graduation-cap.png"> Evaluaciones
                </button>
                <button class="icon-option" data-icon="calendar">
                    <img src="../img/calendar.png"> Eventos
                </button>
                <button class="icon-option" data-icon="flag">
                    <img src="../img/flag.png" alt="festivos"> Festivos
                </button>
            </div>
        </div>

        <!-- Monthly Calendar Container -->
        <div class="monthly-calendar-container">
            <div class="month-calendar">
                <div class="weekdays">
                    <div>Lun</div>
                    <div>Mar</div>
                    <div>Mié</div>
                    <div>Jue</div>
                    <div>Vie</div>
                    <div>Sáb</div>
                    <div>Dom</div>
                </div>
                <div class="days-grid" id="days-grid">
                    <!-- Will be populated by JavaScript -->
                </div>
            </div>
        </div>

        <!-- Event List for Selected Day -->
        <div class="day-events" id="day-events">
            <h3 id="selected-date">Eventos para hoy</h3>
            <div class="events-list" id="events-list">
                <!-- Will be populated by JavaScript -->
            </div>
        </div>
    </main>

    <!-- Modal to add/edit events -->
    <div id="event-modal" class="modal">
        <div class="modal-content">
            <span class="close-modal">&times;</span>
            <h2 id="event-modal-title" class="modal-title">Añadir Evento</h2>
            <form id="event-form">
                <input type="hidden" id="event-action" name="action" value="add">
                <input type="hidden" id="event-id" name="event_id" value="">
                <input type="hidden" id="event-color" name="color" value="#3498db">

                <div class="form-group">
                    <label for="event-date">Fecha:</label>
                    <input type="date" id="event-date" name="event_date" required>
                </div>

                <div class="form-group">
                    <label for="event-title">Título:</label>
                    <input type="text" id="event-title" name="title" placeholder="Título del evento" required>
                </div>

                <div class="form-group">
                    <label for="event-description">Descripción:</label>
                    <textarea id="event-description" name="description" placeholder="Descripción del evento"></textarea>
                </div>

                <div class="form-group">
                    <label>Icono:</label>
                    <div class="icon-selection">
                        <div class="icon-option selected" data-icon="calendar">
                            <img src="../img/calendar.png"> Eventos
                        </div>
                        <div class="icon-option" data-icon="star">
                            <img src="../img/star.png" alt="favourite"> Favoritos
                        </div>
                        <div class="icon-option" data-icon="book">
                            <img src="../img/book.png"> Académico
                        </div>
                        <div class="icon-option" data-icon="users">
                            <img src="../img/users.png" alt="reunion"> Reunión
                        </div>
                        <div class="icon-option" data-icon="graduation-cap">
                            <img src="../img/graduation-cap.png"> Evaluaciones
                        </div>
                        <div class="icon-option" data-icon="flag">
                            <img src="../img/flag.png" alt="festivos"> Festivos
                        </div>
                    </div>
                    <input type="hidden" id="event-icon" name="icon" value="calendar">
                </div>

                <div class="form-group">
                    <label>Color:</label>
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

                    <div class="color-picker-container">
                        <label for="custom-color-picker">Color personalizado:</label>
                        <input type="color" id="custom-color-picker" class="color-picker" value="#3498db">
                    </div>
                </div>

                <div class="modal-buttons">
                    <button type="button" id="delete-event" class="modal-btn delete">Eliminar</button>
                    <button type="submit" class="modal-btn save">Guardar</button>
                </div>
            </form>
        </div>
    </div>

    <div id="custom-modal" class="modal custom-modal">
        <div class="modal-content">
            <h2 id="custom-modal-title">Título</h2>
            <p id="custom-modal-message">Mensaje</p>
            <div class="modal-buttons">
                <button id="custom-modal-cancel" class="modal-btn cancel">Cancelar</button>
                <button id="custom-modal-confirm" class="modal-btn confirm">Confirmar</button>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer>
        <p>&copy; 2025 QUADERN MESTRES - Todos los derechos reservados</p>
    </footer>

    <!-- Scripts -->
    <script src="../js/calendar_mensual.js?v=<?php echo time(); ?>"></script>
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