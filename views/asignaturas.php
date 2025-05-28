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
    <link rel="stylesheet" href="../style/base.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="../style/subject.css?v=<?php echo time(); ?>">

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
            <li class="active"><a href="asignaturas.php"><i class="fas fa-book"></i> Asignaturas</a></li>
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
        <div id="asignaturas-app">
            <!-- Header with title and action buttons -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="calendar-title">Mis Asignaturas</h1>
                <div class="action-buttons">
                    <button class="action-btn" @click="showModal()">
                        <i class="fas fa-plus"></i> Nueva Asignatura
                    </button>
                </div>
            </div>

            <!-- Search and filter bar -->
            <div class="search-container">
                <div class="search-row">
                    <div class="search-col">
                        <div class="search-input-group">
                            <span class="search-icon">
                                <i class="fas fa-search"></i>
                            </span>
                            <input
                                type="text"
                                class="search-input"
                                v-model="searchTerm"
                                placeholder="Buscar por nombre de asignatura...">
                            <button
                                class="search-clear-btn"
                                @click="clearSearch"
                                v-if="searchTerm">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                    <div class="search-col">
                        <div class="search-input-group">
                            <span class="search-icon">
                                <i class="fas fa-filter"></i>
                            </span>
                            <select class="search-select" v-model="grupoFilter">
                                <option value="">Todos los grupos</option>
                                <option v-for="grupo in grupos" :key="grupo.id" :value="grupo.id">
                                    {{ grupo.nombre }}
                                </option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Loading indicator -->
            <div v-if="loading" class="text-center">
                <i class="fas fa-spinner fa-spin fa-2x"></i>
                <p class="mt-2">Cargando asignaturas...</p>
            </div>

            <!-- Empty state -->
            <div v-else-if="asignaturas.length === 0" class="text-center">
                <i class="fas fa-book fa-3x text-muted mb-3"></i>
                <h3>No tienes asignaturas registradas</h3>
                <p class="text-muted">Crea tu primera asignatura haciendo clic en "Nueva Asignatura"</p>
            </div>

            <!-- No search results -->
            <div v-else-if="asignaturasFiltradas.length === 0" class="text-center">
                <i class="fas fa-search fa-3x text-muted mb-3"></i>
                <h3>No se encontraron resultados</h3>
                <p class="text-muted" v-if="searchTerm">No se encontraron asignaturas para "{{ searchTerm }}"</p>
                <p class="text-muted" v-else-if="grupoFilter">No hay asignaturas en el grupo seleccionado</p>
                <button class="btn-outline-primary mt-2" @click="clearFilters">
                    <i class="fas fa-times me-2"></i>Limpiar filtros
                </button>
            </div>

            <!-- Asignaturas grid -->
            <div v-else class="asignaturas-grid">
                <div v-for="(asignatura, index) in asignaturasFiltradas" :key="asignatura.id"
                    class="asignatura-card"
                    :class="{'expanded': asignatura.expanded}"
                    :data-id="asignatura.id"
                    :style="{ borderColor: asignatura.color }">
                    <div class="card-header" @click="asignatura.expanded = !asignatura.expanded" :style="{ background: asignatura.color }">
                        <div class="header-content">
                            <div class="asignatura-icon">
                                <!-- Imagen en lugar de icono de Font Awesome -->
                                <img :src="'../img/' + asignatura.icono" :alt="asignatura.nombre" class="icon-img-d">
                            </div>
                            <div class="asignatura-info">
                                <h5 class="card-title">{{ asignatura.nombre }}</h5>
                                <div class="grupos-container" v-if="asignatura.grupos && asignatura.grupos.length">
                                    <span class="grupo-badge" v-for="grupo in asignatura.grupos" :key="grupo.id">
                                        <i class="fas fa-users me-1"></i> {{ grupo.nombre }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="asignatura-actions">
                            <button class="btn-link text-primary" @click.stop="showModal(asignatura)">
                                <img src="../img/notas.png" alt="editar" class="icon-img-b">
                            </button>
                            <button class="btn-link text-danger" @click.stop="confirmDelete(asignatura)">
                                <img src="../img/basura.png" alt="borrar" class="icon-img-b">
                            </button>
                            <button class="btn-link text-info toggle-btn">
                                <i :class="['fas', asignatura.expanded ? 'fa-chevron-up' : 'fa-chevron-down']"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body" v-show="asignatura.expanded">
                        <div class="asignatura-details">
                            <div class="detail-row descripcion" v-if="asignatura.descripcion">
                                <i class="fas fa-info-circle"></i>
                                <span v-html="formatText(asignatura.descripcion)"></span>
                            </div>
                            <div class="grupos-section" v-if="asignatura.grupos && asignatura.grupos.length">
                                <h6>Grupos asignados:</h6>
                                <div class="grupos-badges">
                                    <span class="grupo-badge" v-for="grupo in asignatura.grupos" :key="grupo.id">
                                        {{ grupo.nombre }}
                                    </span>
                                </div>
                            </div>
                            <div v-else class="text-muted">
                                <i>No hay grupos asignados a esta asignatura</i>
                            </div>
                        </div>
                        <div class="card-actions">
                            <button class="btn-outline-primary" @click.stop="showGruposModal(asignatura)">
                                <i class="fas fa-users"></i> Asignar Grupos
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal for adding/editing asignatura -->
            <div class="modal" id="asignaturaModal" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header" :style="{ background: previewColor || '#3498db' }">
                            <h5 class="modal-title">{{ editMode ? 'Editar Asignatura' : 'Nueva Asignatura' }}</h5>
                            <button type="button" class="btn-close" @click="closeModal">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        <div class="modal-body">
                            <form @submit.prevent="saveAsignatura">
                                <div class="form-group">
                                    <label for="nombre" class="form-label">
                                        <img src="../img/etiqueta.png" alt="nombre" class="icon-img">Nombre
                                    </label>
                                    <input type="text" class="form-control" id="nombre" v-model="formData.nombre" required placeholder="Nombre de la asignatura">
                                </div>

                                <div class="form-group">
                                    <label for="descripcion" class="form-label">
                                        <img src="../img/descripcion.png" alt="descripcion" class="icon-img"></i>Descripción
                                    </label>
                                    <textarea class="form-control" id="descripcion" rows="3" v-model="formData.descripcion" placeholder="Descripción, detalles, objetivos..."></textarea>
                                </div>

                                <div class="form-row">
                                    <div class="form-col">
                                        <div class="form-group">
                                            <label for="color" class="form-label">
                                                <img src="../img/color.png" alt="color" class="icon-img">Color
                                            </label>
                                            <div class="color-selector">
                                                <input type="color" id="color" v-model="formData.color" class="form-control color-input" @input="previewColor = formData.color">
                                                <div class="color-preview" :style="{ backgroundColor: formData.color }"></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-col">
                                        <div class="form-group">
                                            <label for="icono" class="form-label">
                                               Selecciona un ícono
                                            </label>

                                            <div class="icon-grid-container">
                                                <div class="icon-grid">
                                                    <div v-for="icon in iconos"
                                                        :key="icon"
                                                        class="icon-option"
                                                        :class="{'selected': formData.icono === icon}"
                                                        @click="formData.icono = icon">
                                                        <img :src="'../img/' + icon" :alt="icon" class="icon-preview-img">
                                                    </div>
                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                </div>

                                <div class="form-actions">
                                    <button type="button" class="btn-secondary" @click="closeModal">
                                        <i class="fas fa-times me-1 icon-img-c"></i>Cancelar
                                    </button>
                                    <button type="submit" class="btn-primary">
                                        <i class="fas fa-save me-1 icon-img-c"></i>Guardar
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal for managing grupos for an asignatura -->
            <div class="modal" id="gruposAsignaturaModal" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header" :style="{ background: selectedAsignatura?.color || '#3498db' }">
                            <h5 class="modal-title">
                                <i class="fas fa-users me-2"></i>Asignar Grupos
                            </h5>
                            <button type="button" class="btn-close" @click="closeGruposModal">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        <div class="modal-body">
                            <p v-if="selectedAsignatura">Asigna grupos a la asignatura: <strong>{{ selectedAsignatura.nombre }}</strong></p>

                            <div v-if="!grupos.length" class="text-center">
                                <p>No hay grupos disponibles. Primero debes crear grupos en la sección de Alumnado.</p>
                                <button class="btn-outline-primary mt-2" @click="goToAlumnos">
                                    <i class="fas fa-users me-1"></i>Ir a Gestión de Alumnos
                                </button>
                            </div>

                            <div v-else>
                                <div class="grupo-selector" v-for="grupo in grupos" :key="grupo.id">
                                    <div class="form-check">
                                        <input
                                            type="checkbox"
                                            :id="'grupo-' + grupo.id"
                                            :value="grupo.id"
                                            v-model="selectedGrupos"
                                            class="form-check-input">
                                        <label :for="'grupo-' + grupo.id" class="form-check-label">
                                            {{ grupo.nombre }}
                                            <span v-if="grupo.curso_academico" class="grupo-curso">{{ grupo.curso_academico }}</span>
                                        </label>
                                    </div>
                                </div>

                                <div class="form-actions">
                                    <button type="button" class="btn-secondary" @click="closeGruposModal">
                                        <i class="fas fa-times me-1"></i>Cancelar
                                    </button>
                                    <button type="button" class="btn-primary" @click="saveGruposAsignatura">
                                        <i class="fas fa-save me-1"></i>Guardar
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Confirmation modal for deletion -->
            <div class="modal" id="deleteModal" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered modal-sm">
                    <div class="modal-content">
                        <div class="modal-header-delete delete-header">
                            <h5 class="modal-title-delete">Eliminar</h5>
                            <button type="button" class="btn-close btn-close-white" @click="closeDeleteModal"></button>
                        </div>
                        <div class="modal-body">
                            <p class="mb-1">¿Estás seguro de que deseas eliminar la asignatura? </p>
                            <p class="font-weight-bold">{{ selectedAsignatura?.nombre }}</p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn-outline-secondary-delete icon-img-c" @click="closeDeleteModal">Cancelar</button>
                            <button type="button" class="btn-danger-delete" @click="deleteAsignatura">
                                <i class="fas fa-trash me-1"></i>Eliminar
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal de notificación -->
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

    <!-- Custom JS -->
    <script src="../js/asignaturas.js?v=<?php echo time(); ?>"></script>
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