<?php
// Incluir los archivos necesarios
require_once 'includes/auth.php';
require_once 'includes/utils.php';
require_once 'config.php';

// Verificar que el usuario esté autenticado
require_login();

// Obtener información del usuario
$usuario_id = $_SESSION['user_id'];
$usuario_nombre = $_SESSION['user_nombre'];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calendario - QUADERN MESTRES</title>
    <link rel="stylesheet" href="./estilo/base.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="./estilo/calendar.css?v=<?php echo time(); ?>">
    <!--Biblioteca de iconos-->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!--Biblioteca JS para realizar capturs de pantalla-->
    <script src="https://html2canvas.hertzen.com/dist/html2canvas.min.js"></script>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar">
        <div class="nav-left">
            <div class="menu-toggle" id="menu-toggle">
                <i class="fas fa-bars"></i>
            </div>
            <div class="logo">QUADERN MESTRES</div>
        </div>
        <div class="nav-right">
            <div class="user-info">
                <span id="user-name">Bienvenido/a, <?php echo htmlspecialchars($usuario_nombre); ?></span>
            </div>
            <div class="logout-btn" onclick="location.href='logout.php'">
                <i class="fas fa-sign-out-alt"></i>
            </div>
        </div>
    </nav>

    <!-- Menú lateral -->
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
            <li><a href="#"><i class="fas fa-comments"></i> Reuniones</a></li>
            <li><a href="#"><i class="fas fa-book"></i> Asignaturas</a></li>
            <li><a href="#"><i class="fas fa-clipboard-list"></i> Evaluaciones</a></li>
            <li><a href="#"><i class="fas fa-chart-bar"></i> Estadísticas</a></li>
            <li><a href="#"><i class="fas fa-cog"></i> Configuración</a></li>
            <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a></li>
        </ul>
    </div>

    <!-- Overlay para cuando el sidebar está abierto -->
    <div class="overlay" id="overlay"></div>

    <!-- Contenido principal -->
    <main class="main-content">
        <!-- Selector de vistas de calendario -->
        <div class="calendar-views">
            <a href="#" class="calendar-view-btn" id="yearly-view">
                <i class="fas fa-calendar-alt"></i>
                <span>Anual</span>
            </a>
            <a href="#" class="calendar-view-btn" id="monthly-view">
                <i class="fas fa-calendar-day"></i>
                <span>Mensual</span>
            </a>
            <a href="#" class="calendar-view-btn active" id="weekly-view">
                <i class="fas fa-calendar-week"></i>
                <span>Semanal</span>
            </a>
        </div>

        <!-- Contenedor del calendario -->
        <div class="calendar-container">
            <!-- Cabecera del calendario -->
            <div class="calendar-header">
                <div class="calendar-title" id="calendar-title">
                    <!-- Se rellena con JavaScript -->
                </div>
                <div class="calendar-navigation">
                    <button class="nav-btn" id="prev-week">
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    <button class="nav-btn" id="today">
                        <i class="fas fa-calendar-day"></i>
                    </button>
                    <button class="nav-btn" id="next-week">
                        <i class="fas fa-chevron-right"></i>
                    </button>
                </div>
                <div class="calendar-actions">
                    <button class="action-btn" id="add-hour">
                        <i class="fas fa-plus"></i> Añadir Hora
                    </button>
                    <button class="action-btn export" id="export-calendar">
                        <i class="fas fa-file-export"></i> Exportar
                    </button>
                </div>
            </div>

            <!-- Tabla del calendario -->
            <table class="calendar-table">
                <thead>
                    <tr id="week-day-headers">
                        <th>Hora</th>
                        <th>Lunes</th>
                        <th>Martes</th>
                        <th>Miércoles</th>
                        <th>Jueves</th>
                        <th>Viernes</th>
                    </tr>
                </thead>
                <tbody id="calendar-table-body">
                    <!-- Se rellena con JavaScript -->
                </tbody>
            </table>
        </div>

        <!-- Paneles inferiores -->
        <div class="bottom-panels">
            <!-- Panel de notas de la semana -->
            <div class="panel">
                <div class="panel-header">
                    <h3>Notas de la Semana</h3>
                    <button id="add-note-btn" class="add-btn"><i class="fas fa-plus"></i></button>
                </div>
                <div class="panel-content">
                    <div id="notes-list" class="notes-list">
                        <!-- Se rellena con JavaScript -->
                    </div>
                    <!-- Formulario para añadir/editar notas (inicialmente oculto) -->
                    <div id="add-note-form" class="add-note-form" style="display: none;">
                        <textarea id="note-input" class="notes-editor" placeholder="Escribe aquí tu nota..."></textarea>
                        <div class="form-buttons">
                            <button id="save-note-btn" class="save-btn">Guardar</button>
                            <button id="cancel-note-btn" class="cancel-btn">Cancelar</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Panel de eventos del sábado -->
            <div class="panel">
                <div class="panel-header">
                    <h3>Sábado</h3>
                    <button id="add-saturday-btn" class="add-btn"><i class="fas fa-plus"></i></button>
                </div>
                <div class="panel-content">
                    <div id="saturday-list" class="notes-list">
                        <!-- Se rellena con JavaScript -->
                    </div>
                    <!-- Formulario para añadir/editar eventos (inicialmente oculto) -->
                    <div id="add-saturday-form" class="add-note-form" style="display: none;">
                        <textarea id="saturday-input" class="notes-editor" placeholder="Evento para el sábado..."></textarea>
                        <div class="form-buttons">
                            <button id="save-saturday-btn" class="save-btn">Guardar</button>
                            <button id="cancel-saturday-btn" class="cancel-btn">Cancelar</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Panel de eventos del domingo -->
            <div class="panel">
                <div class="panel-header">
                    <h3>Domingo</h3>
                    <button id="add-sunday-btn" class="add-btn"><i class="fas fa-plus"></i></button>
                </div>
                <div class="panel-content">
                    <div id="sunday-list" class="notes-list">
                        <!-- Se rellena con JavaScript -->
                    </div>
                    <!-- Formulario para añadir/editar eventos (inicialmente oculto) -->
                    <div id="add-sunday-form" class="add-note-form" style="display: none;">
                        <textarea id="sunday-input" class="notes-editor" placeholder="Evento para el domingo..."></textarea>
                        <div class="form-buttons">
                            <button id="save-sunday-btn" class="save-btn">Guardar</button>
                            <button id="cancel-sunday-btn" class="cancel-btn">Cancelar</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Modal para añadir/editar horas -->
    <div id="hour-modal" class="modal">
        <div class="modal-content">
            <span class="close-modal">&times;</span>
            <h2 id="hour-modal-title" class="modal-title">Añadir Hora</h2>
            <form id="hour-form">
                <input type="hidden" id="hour-action" name="action" value="add">
                <input type="hidden" id="hour-id" name="hour_id" value="">
                <input type="hidden" id="reference-hour-id" name="reference_hour_id" value="">
                <input type="hidden" id="hour-input" name="hour">
                
                <div class="form-group">
                    <label for="hour-from">Desde:</label>
                    <input type="time" id="hour-from" name="hour_from" required>
                </div>
                <div class="form-group">
                    <label for="hour-to">Hasta:</label>
                    <input type="time" id="hour-to" name="hour_to" required>
                </div>
                
                <div class="modal-buttons">
                    <button type="button" class="modal-btn cancel">Cancelar</button>
                    <button type="submit" class="modal-btn save">Guardar</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal para añadir/editar eventos -->
    <div id="event-modal" class="modal">
        <div class="modal-content">
            <span class="close-modal">&times;</span>
            <h2 id="event-modal-title" class="modal-title">Añadir Evento</h2>
            <form id="event-form">
                <input type="hidden" id="event-action" name="action" value="add">
                <input type="hidden" id="event-id" name="event_id" value="">
                <input type="hidden" id="event-color" name="color" value="#3498db">
                
                <div class="form-group">
                    <label>Día: <span id="event-day"></span></label>
                </div>
                
                <div class="form-group">
                    <label>Hora: <span id="event-hour"></span></label>
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
                    <button type="button" class="modal-btn cancel">Cancelar</button>
                    <button type="submit" class="modal-btn save">Guardar</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Scripts -->
    <script src="js/calendar.js?v=<?php echo time(); ?>"></script>
    <script>
        // Script para el menú lateral
        document.addEventListener('DOMContentLoaded', function() {
            const menuToggle = document.getElementById('menu-toggle');
            const sidebar = document.getElementById('sidebar');
            const closeSidebar = document.getElementById('close-sidebar');
            const overlay = document.getElementById('overlay');

            // Abrir menú lateral
            menuToggle.addEventListener('click', function() {
                sidebar.classList.add('active');
                overlay.classList.add('active');
            });

            // Cerrar menú lateral (botón X)
            closeSidebar.addEventListener('click', function() {
                sidebar.classList.remove('active');
                overlay.classList.remove('active');
            });

            // Cerrar menú lateral (clic en overlay)
            overlay.addEventListener('click', function() {
                sidebar.classList.remove('active');
                overlay.classList.remove('active');
            });
        });
    </script>
</body>
</html>