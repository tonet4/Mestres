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
    <title>Alumnado - QUADERN MESTRES</title>

    <!-- Styles -->
    <link rel="stylesheet" href="../estilo/base.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="../estilo/alumnos.css?v=<?php echo time(); ?>">
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
            <li class="active"><a href="alumnos.php"><i class="fas fa-users"></i> Alumnado</a></li>
            <li><a href="reuniones.php"><i class="fas fa-comments"></i> Reuniones</a></li>
            <li><a href="asignaturas.php"><i class="fas fa-book"></i> Asignaturas</a></li>
            <li><a href="asistencias.php"><i class="fas fa-book"></i> Asistencias</a></li>
            <li><a href="evaluaciones.php"><i class="fas fa-clipboard-list"></i> Evaluaciones</a></li>
            <li><a href="#"><i class="fas fa-chart-bar"></i> Estadísticas</a></li>
            <li><a href="#"><i class="fas fa-cog"></i> Configuración</a></li>
            <li><a href="../api/logout.php"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a></li>
        </ul>
    </div>

    <!-- Overlay for when the sidebar is open -->
    <div class="overlay" id="overlay"></div>

    <!-- Main content - Vue app -->
    <main class="main-content">
        <div id="alumnos-app">
            <!-- Header with title and action buttons -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="calendar-title">Mi Alumnado</h1>
                <div class="action-buttons">
                    <button class="action-btn grupos-btn" @click="showGruposModal">
                        <i class="fas fa-layer-group"></i> Gestionar Grupos
                    </button>
                    <button class="action-btn" @click="showModal()">
                        <i class="fas fa-plus"></i> Nuevo Alumno
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
                                placeholder="Buscar por nombre...">
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
                <p class="mt-2">Cargando alumnos...</p>
            </div>

            <!-- Empty state -->
            <div v-else-if="alumnos.length === 0" class="text-center">
                <i class="fas fa-users fa-3x text-muted mb-3"></i>
                <h3>No tienes alumnos registrados</h3>
                <p class="text-muted">Crea tu primer alumno haciendo clic en "Nuevo Alumno"</p>
            </div>

            <!-- No search results -->
            <div v-else-if="alumnosFiltrados.length === 0" class="text-center">
                <i class="fas fa-search fa-3x text-muted mb-3"></i>
                <h3>No se encontraron resultados</h3>
                <p class="text-muted" v-if="searchTerm">No se encontraron alumnos para "{{ searchTerm }}"</p>
                <p class="text-muted" v-else-if="grupoFilter">No hay alumnos en el grupo seleccionado</p>
                <button class="btn-outline-primary mt-2" @click="clearFilters">
                    <i class="fas fa-times me-2"></i>Limpiar filtros
                </button>
            </div>

            <!-- Alumnos grid -->
            <div v-else class="alumnos-grid">
                <div v-for="(alumno, index) in alumnosFiltrados" :key="alumno.id" class="alumno-card" :class="{'expanded': alumno.expanded}" :data-id="alumno.id">
                    <div class="card-header" @click="alumno.expanded = !alumno.expanded">
                        <div class="header-content">
                            <div class="alumno-avatar">
                                <img :src="alumno.imagen ? '../img/alumnos/' + alumno.imagen : '../img/user.png'" alt="Foto de perfil">
                            </div>
                            <div class="alumno-info">
                                <h5 class="card-title">{{ alumno.nombre }} {{ alumno.apellidos }}</h5>
                                <div class="grupo-badge" v-if="alumno.grupo_nombre">
                                    <i class="fas fa-users me-1"></i> {{ alumno.grupo_nombre }}
                                </div>
                            </div>
                        </div>
                        <div class="alumno-actions">
                            <button class="btn-link text-primary" @click.stop="showModal(alumno)">
                                <img src="../img/notas.png" alt="editar" class="icon-img">
                            </button>
                            <button class="btn-link text-danger" @click.stop="confirmDelete(alumno)">
                                <img src="../img/basura.png" alt="borrar" class="icon-img">
                            </button>
                            <button class="btn-link text-info toggle-btn">
                                <i :class="['fas', alumno.expanded ? 'fa-chevron-up' : 'fa-chevron-down']"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body" v-show="alumno.expanded">
                        <div class="alumno-details">
                            <div class="detail-row" v-if="alumno.fecha_nacimiento">
                                <i class="fas fa-birthday-cake"></i>
                                <span>{{ formatDate(alumno.fecha_nacimiento) }}</span>
                            </div>
                            <div class="detail-row" v-if="alumno.email">
                                <i class="fas fa-envelope"></i>
                                <span>{{ alumno.email }}</span>
                            </div>
                            <div class="detail-row" v-if="alumno.telefono">
                                <i class="fas fa-phone"></i>
                                <span>{{ alumno.telefono }}</span>
                            </div>
                            <div class="detail-row" v-if="alumno.direccion">
                                <i class="fas fa-map-marker-alt"></i>
                                <span>{{ alumno.direccion }}</span>
                            </div>
                            <div class="detail-row observaciones" v-if="alumno.observaciones">
                                <i class="fas fa-sticky-note"></i>
                                <span v-html="formatText(alumno.observaciones)"></span>
                            </div>
                        </div>
                        <div class="card-actions">
                            <button class="btn-outline-primary" @click="showAsistenciasModal(alumno)">
                                <i class="fas fa-calendar-check"></i> Control de Asistencia
                            </button>
                            <button class="btn-outline-primary" @click="showEvaluacionesModal(alumno)">
                                <i class="fas fa-chart-line"></i> Evaluaciones
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal for adding/editing alumno -->
            <div class="modal" id="alumnoModal" tabindex="-1">
                <div class="modal-dialog modal-wide">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">{{ editMode ? 'Editar Alumno' : 'Nuevo Alumno' }}</h5>
                            <button type="button" class="btn-close" @click="closeModal">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        <div class="modal-body">
                            <form @submit.prevent="saveAlumno">
                                <div class="form-layout">
                                    <!-- Left colum -->
                                    <div class="form-column left-column">
                                        <div class="form-group mb-4">
                                            <div class="avatar-upload">
                                                <div class="avatar-preview" :style="{ 'background-image': 'url(' + previewImage + ')' }">
                                                    <input type="file" id="imagen" @change="onFileChange" accept="image/*">
                                                    <label for="imagen" class="avatar-edit">
                                                        <img src="../img/lapiz.png" alt="Editar" width="16" height="16">
                                                    </label>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label for="nombre" class="form-label">
                                                <span class="icon-img-container">
                                                    <img src="../img/usuario.png" alt="Usuario" class="icon-img">
                                                </span>
                                                Nombre
                                            </label>
                                            <input type="text" class="form-control" id="nombre" v-model="formData.nombre" required placeholder="Nombre del alumno">
                                        </div>

                                        <div class="form-group">
                                            <label for="apellidos" class="form-label">
                                                <span class="icon-img-container">
                                                    <img src="../img/usuario2.png" alt="Apellidos" class="icon-img">
                                                </span>
                                                Apellidos
                                            </label>
                                            <input type="text" class="form-control" id="apellidos" v-model="formData.apellidos" required placeholder="Apellidos del alumno">
                                        </div>

                                        <div class="form-group">
                                            <label for="fecha_nacimiento" class="form-label">
                                                <span class="icon-img-container">
                                                    <img src="../img/pastel.png" alt="Fecha de nacimiento" class="icon-img">
                                                </span>
                                                Fecha de Nacimiento
                                            </label>
                                            <input type="date" class="form-control" id="fecha_nacimiento" v-model="formData.fecha_nacimiento">
                                        </div>

                                        <div class="form-group">
                                            <label for="grupo_id" class="form-label">
                                                <span class="icon-img-container">
                                                    <img src="../img/users.png" alt="Grupo" class="icon-img">
                                                </span>
                                                Grupo
                                            </label>
                                            <select class="form-control" id="grupo_id" v-model="formData.grupo_id">
                                                <option value="">Seleccionar grupo</option>
                                                <option v-for="grupo in grupos" :key="grupo.id" :value="grupo.id">
                                                    {{ grupo.nombre }}
                                                </option>
                                            </select>
                                        </div>
                                    </div>

                                    <!-- Right colum -->
                                    <div class="form-column right-column">
                                        <div class="form-group">
                                            <label for="email" class="form-label">
                                                <span class="icon-img-container">
                                                    <img src="../img/email.png" alt="Email" class="icon-img">
                                                </span>
                                                Email
                                            </label>
                                            <input type="email" class="form-control" id="email" v-model="formData.email" placeholder="Email del alumno o familia">
                                        </div>

                                        <div class="form-group">
                                            <label for="telefono" class="form-label">
                                                <span class="icon-img-container">
                                                    <img src="../img/chat.png" alt="Teléfono" class="icon-img">
                                                </span>
                                                Teléfono
                                            </label>
                                            <input type="tel" class="form-control" id="telefono" v-model="formData.telefono" placeholder="Teléfono de contacto">
                                        </div>

                                        <div class="form-group">
                                            <label for="direccion" class="form-label">
                                                <span class="icon-img-container">
                                                    <img src="../img/localizacion.png" alt="Dirección" class="icon-img">
                                                </span>
                                                Dirección
                                            </label>
                                            <input type="text" class="form-control" id="direccion" v-model="formData.direccion" placeholder="Dirección del alumno">
                                        </div>

                                        <div class="form-group">
                                            <label for="observaciones" class="form-label">
                                                <span class="icon-img-container">
                                                    <img src="../img/notas.png" alt="Observaciones" class="icon-img">
                                                </span>
                                                Observaciones
                                            </label>
                                            <textarea class="form-control" id="observaciones" rows="4" v-model="formData.observaciones" placeholder="Observaciones, notas adicionales..."></textarea>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-actions">
                                    <button type="button" class="btn-secondary" @click="closeModal">
                                        <i class="fas fa-times me-1 btnspace"></i>Cancelar
                                    </button>
                                    <button type="submit" class="btn-primary">
                                        <i class="fas fa-save me-1 btnspace"></i>{{ editMode ? 'Actualizar' : 'Guardar' }}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Modal for managing grupos -->
            <div class="modal" id="gruposModal" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Gestión de Grupos</h5>
                            <button type="button" class="btn-close" @click="closeGruposModal">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="grupos-list">
                                <div v-if="!grupos.length" class="text-center">
                                    <p>No tienes grupos creados. Añade tu primer grupo.</p>
                                </div>
                                <div v-else class="grupo-item" v-for="grupo in grupos" :key="grupo.id">
                                    <div class="grupo-info">
                                        <h5>{{ grupo.nombre }}</h5>
                                        <p v-if="grupo.descripcion">{{ grupo.descripcion }}</p>
                                        <span class="grupo-curso" v-if="grupo.curso_academico">{{ grupo.curso_academico }}</span>
                                    </div>
                                    <div class="grupo-actions">
                                        <button class="btn-link text-primary" @click="editGrupo(grupo)">
                                            <img src="../img/notas.png" alt="editar" class="icon-img">
                                        </button>
                                        <button class="btn-link text-danger" @click="confirmDeleteGrupo(grupo)">
                                            <img src="../img/basura.png" alt="borrar" class="icon-img">
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <form @submit.prevent="saveGrupo" class="grupo-form">
                                <h5>{{ editandoGrupo ? 'Editar Grupo' : 'Nuevo Grupo' }}</h5>
                                <div class="form-group">
                                    <label for="grupoNombre" class="form-label">Nombre</label>
                                    <input type="text" class="form-control" id="grupoNombre" v-model="grupoForm.nombre" required placeholder="Nombre del grupo">
                                </div>
                                <div class="form-group">
                                    <label for="grupoDescripcion" class="form-label">Descripción</label>
                                    <input type="text" class="form-control" id="grupoDescripcion" v-model="grupoForm.descripcion" placeholder="Descripción del grupo">
                                </div>
                                <div class="form-group">
                                    <label for="grupoCurso" class="form-label">Curso Académico</label>
                                    <input type="text" class="form-control" id="grupoCurso" v-model="grupoForm.curso_academico" placeholder="Ej: 2025-2026">
                                </div>
                                <div class="form-actions">
                                    <button type="button" class="btn-secondary" @click="cancelEditGrupo" v-if="editandoGrupo">
                                        <i class="fas fa-times me-1 btnspace"></i>Cancelar
                                    </button>
                                    <button type="submit" class="btn-primary btnspace">
                                        <i class="fas fa-save me-1 btnspace"></i>{{ editandoGrupo ? 'Actualizar' : 'Añadir' }}
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
                        <div class="modal-header delete-header">
                            <h5 class="modal-title">
                                <i class="fas fa-exclamation-triangle me-2"></i>Confirmar
                            </h5>
                            <button type="button" class="btn-close btn-close-white" @click="closeDeleteModal"></button>
                        </div>
                        <div class="modal-body">
                            <p class="mb-1">¿Estás seguro de que deseas eliminar al alumno?</p>
                            <p class="font-weight-bold">{{ selectedAlumno?.nombre }} {{ selectedAlumno?.apellidos }}</p>
                            <p class="text-danger mb-0"><small>Esta acción no se puede deshacer.</small></p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn-outline-secondary" @click="closeDeleteModal">Cancelar</button>
                            <button type="button" class="btn-danger" @click="deleteAlumno">
                                <i class="fas fa-trash me-1"></i>Eliminar
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Confirmation modal for grupo deletion -->
            <div class="modal" id="deleteGrupoModal" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered modal-sm">
                    <div class="modal-content">
                        <div class="modal-header delete-header">
                            <h5 class="modal-title">
                                <i class="fas fa-exclamation-triangle me-2"></i>Confirmar
                            </h5>
                            <button type="button" class="btn-close btn-close-white" @click="closeDeleteGrupoModal"></button>
                        </div>
                        <div class="modal-body">
                            <p class="mb-1">¿Estás seguro de que deseas eliminar el grupo?</p>
                            <p class="font-weight-bold">{{ selectedGrupo?.nombre }}</p>
                            <p class="text-danger mb-0"><small>Esta acción no se puede deshacer y desvinculará a todos los alumnos asociados.</small></p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn-outline-secondary" @click="closeDeleteGrupoModal">Cancelar</button>
                            <button type="button" class="btn-danger" @click="deleteGrupo">
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
    <footer>
        <p>&copy; 2025 QUADERN MESTRES - Todos los derechos reservados</p>
    </footer>

    <!-- Custom JS -->
    <script src="../js/alumnos.js?v=<?php echo time(); ?>"></script>
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