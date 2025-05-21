<?php
/**
 * @author Antonio Esteban Lorenzo
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
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QUADERN MESTRES</title>
    <link rel="shortcut icon" href="../img/logo2.png"> 

    <!-- Styles -->
    <link rel="stylesheet" href="../estilo/base.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="../estilo/asistencias.css?v=<?php echo time(); ?>">

    <link rel="shortcut icon" href="../img/logo2.png"> 
        <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Vue.js -->
    <script src="https://cdn.jsdelivr.net/npm/vue@2.6.14/dist/vue.js"></script>
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
            <li><a href="calendario.php"><i class="fas fa-calendar"></i> Calendario</a></li>
            <li><a href="alumnos.php"><i class="fas fa-users"></i> Alumnado</a></li>
            <li><a href="reuniones.php"><i class="fas fa-comments"></i> Reuniones</a></li>
            <li><a href="asignaturas.php"><i class="fas fa-book"></i> Asignaturas</a></li>
            <li class="active"><a href="asistencias.php"><i class="fas fa-clipboard-check"></i> Asistencias</a></li>
            <li><a href="evaluaciones.php"><i class="fas fa-clipboard-list"></i> Evaluaciones</a></li>
            <li><a href="../api/logout.php"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a></li>
        </ul>
    </div>

    <!-- Overlay for when the sidebar is open -->
    <div class="overlay" id="overlay"></div>

    <!-- Main content - Vue app -->
    <main class="main-content">
        <div id="asistencias-app">
            <!-- Header with title -->
            <div class="header-container">
                <h1 class="calendar-title">Control de Asistencias</h1>
            </div>

            <!-- Filters and selection -->
             <div class="filters-container">
                <div class="filter-row">
                    <div class="filter-group">
                        <label for="asignatura-select" class="filter-label">
                            <img src="../img/libro.png" alt="libro" class="icon-img">Asignatura
                        </label>
                        <select id="asignatura-select" v-model="selectedAsignatura" @change="onAsignaturaChange" class="filter-select">
                            <option value="">Selecciona una asignatura</option>
                            <option v-for="asignatura in asignaturas" :key="asignatura.id" :value="asignatura.id">
                                {{ asignatura.nombre }}
                            </option>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label for="grupo-select" class="filter-label">
                            <img src="../img/users.png" alt="grupo" class="icon-img">Grupo
                        </label>
                        <select id="grupo-select" v-model="selectedGrupo" @change="onGrupoChange" class="filter-select" :disabled="!selectedAsignatura || gruposFiltrados.length === 0">
                            <option value="">Selecciona un grupo</option>
                            <option v-for="grupo in gruposFiltrados" :key="grupo.id" :value="grupo.id">
                                {{ grupo.nombre }}
                            </option>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label for="fecha-select" class="filter-label">
                           <img src="../img/calendar.png" alt="fecha" class="icon-img">Fecha
                        </label>
                        <input type="date" id="fecha-select" v-model="selectedFecha" class="filter-input">
                    </div>
                    
                    <button class="action-btn" @click="cargarAsistencias" :disabled="!selectedAsignatura || !selectedGrupo || !selectedFecha">
                        <i class="fas fa-search"></i> Cargar Lista
                    </button>
                </div>
            </div>

            <!-- Loading indicator -->
            <div v-if="loading" class="text-center">
                <i class="fas fa-spinner fa-spin fa-2x"></i>
                <p class="mt-2">Cargando datos...</p>
            </div>

            <!-- Empty state (nothing selected) -->
            <div v-else-if="!dataLoaded" class="empty-state">
                <i class="fas fa-clipboard-check fa-3x text-muted mb-3"></i>
                <h3>Selecciona una asignatura, grupo y fecha</h3>
                <p class="text-muted">Para comenzar, selecciona los criterios de búsqueda y haz clic en "Cargar Lista"</p>
            </div>

            <!-- There are no students in the group -->
            <div v-else-if="alumnos.length === 0" class="empty-state">
                <i class="fas fa-users-slash fa-3x text-muted mb-3"></i>
                <h3>No hay alumnos en este grupo</h3>
                <p class="text-muted">El grupo seleccionado no tiene alumnos asignados</p>
                <button class="btn-outline-primary mt-2" @click="goToAlumnos">
                    <i class="fas fa-users me-2"></i>Ir a Gestión de Alumnos
                </button>
            </div>

            <!-- Attendance list -->
            <div v-else>
                <!-- List header -->
                <div class="attendance-header">
                    <div class="attendance-info">
                        <h3>
                            <span v-if="asignaturaActual">{{ asignaturaActual.nombre }}</span>
                            <span v-if="grupoActual"> - {{ grupoActual.nombre }}</span>
                            <span v-if="selectedFecha"> - {{ formatDate(selectedFecha) }}</span>
                        </h3>
                    </div>
                    <div class="attendance-actions">
                        <button class="action-btn secondary-btn" @click="seleccionarTodos('presente')" title="Marcar todos como presentes">
                            <i class="fas fa-check-circle"></i> Todos Presentes
                        </button>
                        <button class="action-btn save-btn" @click="guardarAsistencias" :disabled="!hayAlgunCambio">
                            <i class="fas fa-save"></i> Guardar Cambios
                        </button>
                    </div>
                </div>

                <!-- Status Legend -->
                <div class="legend-container">
                    <div class="legend-item">
                        <span class="status-indicator presente"></span>
                        <span class="legend-text">Presente</span>
                    </div>
                    <div class="legend-item">
                        <span class="status-indicator ausente"></span>
                        <span class="legend-text">Ausente</span>
                    </div>
                    <div class="legend-item">
                        <span class="status-indicator retraso"></span>
                        <span class="legend-text">Retraso</span>
                    </div>
                    <div class="legend-item">
                        <span class="status-indicator justificado"></span>
                        <span class="legend-text">Justificado</span>
                    </div>
                </div>

                <!-- List of students -->
                <div class="students-list">
                    <div v-for="alumno in alumnos" :key="alumno.id" class="student-card"
                         :class="{ 'has-observation': alumno.observaciones }">
                        <div class="student-info">
                            <div class="student-avatar">
                                <img :src="alumno.imagen ? '../img/alumnos/' + alumno.imagen : '../img/user.png'" alt="Foto de perfil">
                            </div>
                            <div class="student-details">
                                <h4>{{ alumno.nombre }} {{ alumno.apellidos }}</h4>
                            </div>
                        </div>
                        <div class="attendance-controls">
                            <div class="status-buttons">
                                <button
                                    v-for="status in ['presente', 'ausente', 'retraso', 'justificado']"
                                    :key="status"
                                    @click="cambiarEstado(alumno, status)"
                                    class="status-btn"
                                    :class="[status, {active: alumno.estado === status}]"
                                    :title="status.charAt(0).toUpperCase() + status.slice(1)">
                                    <i :class="getIconForStatus(status)"></i>
                                </button>
                            </div>
                            <button 
                                class="observation-btn" 
                                @click="showObservacionModal(alumno)"
                                :class="{'has-notes': alumno.observaciones}">
                                <i class="fas fa-sticky-note"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Save button below -->
                <div class="bottom-actions">
                    <button class="action-btn save-btn" @click="guardarAsistencias" :disabled="!hayAlgunCambio">
                        <i class="fas fa-save"></i> Guardar Cambios
                    </button>
                </div>
            </div>

            <!-- Modal to add comments -->
            <div class="modal" id="observacionModal" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">
                                <i class="fas fa-sticky-note me-2"></i>Observaciones
                            </h5>
                            <button type="button" class="btn-close" @click="closeObservacionModal">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        <div class="modal-body">
                            <p v-if="selectedAlumno">
                                Añadir observación para <strong>{{ selectedAlumno.nombre }} {{ selectedAlumno.apellidos }}</strong>
                            </p>
                            <div class="form-group">
                                <textarea 
                                    class="form-control" 
                                    rows="4" 
                                    v-model="observacionText" 
                                    placeholder="Escribe aquí tu observación..."></textarea>
                            </div>
                            <div class="form-actions">
                                <button type="button" class="btn-secondary" @click="closeObservacionModal">
                                    <i class="fas fa-times me-1"></i>Cancelar
                                </button>
                                <button type="button" class="btn-primary" @click="guardarObservacion">
                                    <i class="fas fa-save me-1"></i>Guardar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Notification modal -->
            <div class="modal" id="notificationModal" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered modal-sm">
                    <div class="modal-content">
                        <div class="modal-header" :class="notificationType === 'success' ? 'success-header' : 'error-header'">
                            <h5 class="modal-title text-white">
                                <i :class="['fas', notificationType === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle', 'me-2']"></i>
                                {{ notificationTitle }}
                            </h5>
                            <button type="button" class="btn-close btn-close-white" @click="closeNotification"></button>
                        </div>
                        <div class="modal-body text-center">
                            <p class="mb-0">{{ notificationMessage }}</p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn-primary w-100" @click="closeNotification">Aceptar</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer>
        <p>&copy; 2025 QUADERN MESTRES - Todos los derechos reservados</p>
    </footer>

    <!-- Custom JS -->
    <script src="../js/asistencias.js?v=<?php echo time(); ?>"></script>
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
</body>

</html>