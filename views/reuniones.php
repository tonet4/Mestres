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

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../style/base.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="../style/meetings.css?v=<?php echo time(); ?>">

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
            <li><a href="alumnos.php"><i class="fas fa-users"></i> Alumnos-Grupos</a></li>
            <li class="active"><a href="reuniones.php"><i class="fas fa-comments"></i> Reuniones</a></li>
            <li><a href="asignaturas.php"><i class="fas fa-book"></i> Asignaturas</a></li>
            <li><a href="asistencias.php"><i class="fas fa-book"></i> Asistencias</a></li>
            <li><a href="evaluaciones.php"><i class="fas fa-clipboard-list"></i> Evaluaciones</a></li>
            <li><a href="horarios.php"><i class="fas fa-clock"></i> Horarios</a></li>
            <li><a href="../api/logout.php"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a></li>
        </ul>
    </div>

    <!-- Overlay for when the sidebar is open -->
    <div class="overlay" id="overlay"></div>

    <!-- Main content - Vue app -->
    <main class="main-content">
        <div id="reuniones-app">
            <!-- Header with title and action button -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="calendar-title">Mis Reuniones</h1>
                <button class="action-btn" @click="showModal()">
                    <i class="fas fa-plus"></i> Nueva Reunión
                </button>
            </div>
            <!-- Search bar -->
            <div class="search-container bg-white p-3 rounded-3 shadow-sm">
                <div class="input-group">
                    <span class="input-group-text bg-light">
                        <i class="fas fa-search"></i>
                    </span>
                    <input
                        type="text"
                        class="form-control"
                        v-model="searchTerm"
                        placeholder="Buscar por título o fecha (dd/mm/yyyy)">
                    <button
                        class="btn btn-outline-secondary"
                        type="button"
                        @click="clearSearch"
                        v-if="searchTerm">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>

            <!-- Loading indicator -->
            <div v-if="loading" class="text-center py-5">
                <i class="fas fa-spinner fa-spin fa-2x"></i>
                <p class="mt-2">Cargando reuniones...</p>
            </div>

            <!-- Empty state -->
            <div v-else-if="reuniones.length === 0" class="text-center py-5">
                <i class="fas fa-comments fa-3x text-muted mb-3"></i>
                <h3>No tienes reuniones registradas</h3>
                <p class="text-muted">Crea tu primera reunión haciendo clic en "Nueva Reunión"</p>
            </div>

            <!-- No reunions found -->
            <div v-else-if="reuniones.length === 0" class="text-center py-5">
                <i class="fas fa-comments fa-3x text-muted mb-3"></i>
                <h3>No tienes reuniones registradas</h3>
                <p class="text-muted">Crea tu primera reunión haciendo clic en "Nueva Reunión"</p>
            </div>

            <!-- No search results -->
            <div v-else-if="reunionesFiltradas.length === 0" class="text-center py-5">
                <i class="fas fa-search fa-3x text-muted mb-3"></i>
                <h3>No se encontraron resultados</h3>
                <p class="text-muted">No se encontraron reuniones para "{{ searchTerm }}"</p>
                <button class="btn btn-outline-primary mt-2" @click="clearSearch">
                    <i class="fas fa-times me-2"></i>Limpiar búsqueda
                </button>
            </div>

            <!-- Reuniones grid -->
            <div v-else class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                <div v-for="(reunion, index) in reunionesFiltradas" :key="reunion.id" class="col">
                    <div class="card reunion-card" :class="{'expanded': reunion.expanded}" :data-id="reunion.id">
                        <div class="card-header" @click="reunion.expanded = !reunion.expanded">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="header-content">
                                    <div class="fecha-badge mb-2">
                                        <i class="fas fa-calendar-day me-1"></i>
                                        {{ reunion.fecha_formateada }}
                                        <span v-if="reunion.hora_formateada" class="hora-badge">
                                            <i class="fas fa-clock me-1"></i>
                                            {{ reunion.hora_formateada }}
                                        </span>
                                    </div>
                                    <h5 class="card-title">{{ reunion.titulo }}</h5>
                                </div>
                                <div class="reunion-actions">
                                    <button class="btn btn-sm btn-link text-primary" @click.stop="showModal(reunion)">
                                        <img src="../img/notas.png" alt="editar" class="icon-img-b">
                                    </button>
                                    <button class="btn btn-sm btn-link text-danger" @click.stop="confirmDelete(reunion)">
                                        <img src="../img/basura.png" alt="borrar" class="icon-img-b">
                                    </button>
                                    <button class="btn btn-sm btn-link text-info toggle-btn">
                                        <i :class="['fas', reunion.expanded ? 'fa-chevron-up' : 'fa-chevron-down']"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="card-body" v-show="reunion.expanded">
                            <p class="card-text" v-if="expandedReunion === reunion.id" v-html="formatText(reunion.contenido)"></p>
                            <p class="card-text" v-else v-html="formatText(truncateText(reunion.contenido, 100))"></p>
                            <button v-if="reunion.contenido && reunion.contenido.length > 100"
                                class="btn btn-sm btn-outline-primary mt-2"
                                @click.stop="toggleExpand(reunion.id)">
                                {{ expandedReunion === reunion.id ? 'Ver menos' : 'Ver más' }}
                            </button>
                        </div>
                        <div class="card-footer text-muted small" v-show="reunion.expanded">
                            <i class="fas fa-clock me-1"></i> Creada: {{ reunion.fecha_creacion_formateada }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal for adding/editing reunion -->
            <div class="modal" id="reunionModal" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">{{ editMode ? 'Editar Reunión' : 'Nueva Reunión' }}</h5>
                            <button type="button" class="btn-close" @click="closeModal">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        <div class="modal-body">
                            <form @submit.prevent="saveReunion">
                                <div class="mb-4">
                                    <label for="titulo" class="form-label">
                                        <img src="../img/titular.png" alt="titulo" class="icon-img">Título
                                    </label>
                                    <input type="text" class="form-control" id="titulo" v-model="formData.titulo" required placeholder="Título de la reunión">
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-4">
                                        <label for="fecha" class="form-label">
                                            <img src="../img/calendario.png" alt="fecha" class="icon-img"></i>Fecha
                                        </label>
                                        <input type="date" class="form-control" id="fecha" v-model="formData.fecha" required>
                                    </div>
                                    <div class="col-md-6 mb-4">
                                        <label for="hora" class="form-label">
                                            <img src="../img/reloj.png" alt="hora" class="icon-img">Hora
                                        </label>
                                        <input type="time" class="form-control" id="hora" v-model="formData.hora">
                                    </div>
                                </div>
                                <div class="mb-4">
                                    <label for="contenido" class="form-label">
                                        <img src="../img/contenido.png" alt="contenido" class="icon-img">Contenido
                                    </label>
                                    <textarea class="form-control" id="contenido" rows="5" v-model="formData.contenido" placeholder="Detalles de la reunión..."></textarea>
                                </div>
                                <div class="d-flex justify-content-end gap-3 mt-4">
                                    <button type="button" class="btn btn-secondary" @click="closeModal">
                                        <i class="fas fa-times me-1"></i>Cancelar
                                    </button>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-1"></i>Guardar
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Confirmation modal for deletion -->
            <div class="modal" id="deleteModal" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered modal-sm">
                    <div class="modal-content">
                        <div class="modal-header bg-danger text-white">
                            <h5 class="modal-title">Eliminar</h5>
                            <button type="button" class="btn-close btn-close-white" @click="closeDeleteModal"></button>
                        </div>
                        <div class="modal-body">
                            <p class="mb-1">¿Estás seguro de que deseas eliminar la reunión?</p>
                            <p class="font-weight-bold">{{ selectedReunion?.titulo }}</p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-sm btn-outline-secondary" @click="closeDeleteModal">Cancelar</button>
                            <button type="button" class="btn btn-sm btn-danger" @click="deleteReunion">
                                <i class="fas fa-trash me-1"></i>Eliminar
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Notification modal -->
            <div class="modal" id="notificationModal" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered modal-sm">
                    <div class="modal-content">
                        <div class="modal-header" :class="notificationType === 'success' ? 'bg-success' : 'bg-danger'">
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
                            <button type="button" class="btn btn-sm btn-primary w-100" @click="closeNotification">Aceptar</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

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

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
    <script src="../js/reuniones.js?v=<?php echo time(); ?>"></script>
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