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

    <!-- Estilos -->
    <link rel="stylesheet" href="../estilo/base.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="../estilo/evaluaciones.css?v=<?php echo time(); ?>">

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
            <li><a href="asistencias.php"><i class="fas fa-user-check"></i> Asistencias</a></li>
            <li class="active"><a href="evaluaciones.php"><i class="fas fa-chart-line"></i> Evaluaciones</a></li>
            <li><a href="../api/logout.php"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a></li>
        </ul>
    </div>

    <!-- Overlay for when the sidebar is open -->
    <div class="overlay" id="overlay"></div>

    <!-- Main content - Vue app -->
    <main class="main-content">
        <div id="evaluaciones-app">
           <!-- Header with title and action buttons -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="calendar-title">Sistema de Evaluaciones</h1>
                <div class="action-buttons">
                    <button class="action-btn criterios-btn" @click="showPeriodosModal">
                        <i class="fas fa-calendar-alt"></i> Períodos
                    </button>
                </div>
            </div>

            <!-- Filters for evaluations -->
            <div class="filters-container">
                <div class="filters-row">
                    <div class="filter-col">
                        <label>
                            <img src="../img/users.png" alt="grupo" class="icon-img">Grupo:
                        </label>
                        <select v-model="selectedGrupo" @change="onGrupoChange" class="filter-select">
                            <option value="">Selecciona un grupo</option>
                            <option v-for="grupo in grupos" :key="grupo.id" :value="grupo.id">
                                {{ grupo.nombre }}
                            </option>
                        </select>
                    </div>
                    <div class="filter-col">
                        <label><img src="../img/libro.png" alt="asignatura" class="icon-img">
                            Asignatura:
                        </label>
                        <select v-model="selectedAsignatura" @change="cargarDatos" class="filter-select">
                            <option value="">Selecciona una asignatura</option>
                            <option v-for="asignatura in asignaturas" :key="asignatura.id" :value="asignatura.id">
                                {{ asignatura.nombre }}
                            </option>
                        </select>
                    </div>
                    <div class="filter-col">
                        <label><img src="../img/calendar.png" alt="periodo" class="icon-img">
                            Período:
                        </label>
                        <select v-model="selectedPeriodo" @change="cargarDatos" class="filter-select">
                            <option value="">Selecciona un período</option>
                            <option v-for="periodo in periodos" :key="periodo.id" :value="periodo.id">
                                {{ periodo.nombre }}
                            </option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Informational message when there is no selection -->
            <div v-if="!selectedGrupo || !selectedAsignatura || !selectedPeriodo" class="info-message">
                <i class="fas fa-info-circle"></i>
                <p>Selecciona un grupo, asignatura y período para comenzar a calificar.</p>
            </div>

            <!-- Loading indicator -->
            <div v-else-if="loading" class="text-center">
                <i class="fas fa-spinner fa-spin fa-2x"></i>
                <p class="mt-2">Cargando datos...</p>
            </div>

           <!-- Ratings table -->
            <div v-else-if="alumnos.length > 0" class="calificaciones-container">
                <div class="table-actions">
                    <button class="action-btn add-btn" @click="showEvaluacionModal">
                        <i class="fas fa-plus"></i> Nueva Evaluación
                    </button>
                    <button class="action-btn calculate-btn" @click="calcularNotasFinales">
                        <i class="fas fa-calculator"></i> Calcular Notas Finales
                    </button>
                </div>

                <div class="table-responsive">
                    <table class="tabla-calificaciones">
                        <thead>
                            <tr>
                                <th class="alumno-header">Alumno</th>
                                <th v-for="evaluacion in evaluaciones" :key="evaluacion.id" class="evaluacion-header">
                                    <div>{{ evaluacion.nombre }}</div>
                                    <div class="evaluacion-fecha">{{ formatDate(evaluacion.fecha) }}</div>
                                    <div class="evaluacion-porcentaje">{{ evaluacion.porcentaje }}%</div>
                                    <div class="evaluacion-actions">
                                        <button class="btn-link text-primary" @click="editarEvaluacion(evaluacion)">
                                            <img src="../img/notas.png" alt="editar" class="icon-img-b">
                                        </button>
                                        <button class="btn-link text-danger" @click="confirmarEliminarEvaluacion(evaluacion)">
                                            <img src="../img/basura.png" alt="eliminar" class="icon-img-b">
                                        </button>
                                    </div>
                                </th>
                                <th class="nota-final-header">Nota Final</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="alumno in alumnos" :key="alumno.id" class="alumno-row">
                                <td class="alumno-cell">
                                    <div class="alumno-info">
                                        <div class="alumno-avatar">
                                            <img :src="alumno.imagen ? '../img/alumnos/' + alumno.imagen : '../img/user.png'" alt="Foto de perfil">
                                        </div>
                                        <div class="alumno-name">
                                            {{ alumno.nombre }} {{ alumno.apellidos }}
                                        </div>
                                    </div>
                                </td>
                                <td v-for="evaluacion in evaluaciones" :key="evaluacion.id" class="calificacion-cell">
                                    <input
                                        type="number"
                                        min="0"
                                        max="10"
                                        step="0.1"
                                        class="calificacion-input"
                                        v-model="calificaciones[alumno.id + '-' + evaluacion.id]"
                                        @change="validarYMarcarComoModificado(alumno.id, evaluacion.id)">
                                </td>
                                <td class="nota-final-cell">
                                    <div class="nota-final" :class="getClaseNotaFinal(alumno.id)">
                                        {{ getNotaFinal(alumno.id) || '-' }}
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- No reviews -->
                <div v-if="evaluaciones.length === 0" class="info-message warning mt-4">
                    <i class="fas fa-exclamation-triangle"></i>
                    <p>No hay evaluaciones definidas. Crea una nueva evaluación para comenzar a calificar.</p>
                </div>
            </div>

           <!-- Message when there are no students in the selected group -->
            <div v-else-if="selectedGrupo && alumnos.length === 0" class="info-message warning">
                <i class="fas fa-exclamation-triangle"></i>
                <p>No hay alumnos asignados al grupo seleccionado.</p>
            </div>

            <!-- Modal for evaluation periods -->
            <div class="modal" id="periodosModal" tabindex="-1">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Períodos de Evaluación</h5>
                            <button type="button" class="btn-close" @click="closePeriodosModal">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        <div class="modal-body periodo-modal-body">
                            <!-- Two-column layout -->
                            <div class="periodo-layout">
                                <!-- Left column: Form to create/edit periods -->
                                <div class="periodo-form-container">
                                    <form @submit.prevent="guardarPeriodo" class="periodo-form">
                                        <h5>{{ periodoForm.id > 0 ? 'Editar Período' : 'Nuevo Período' }}</h5>
                                        <div class="form-group">
                                            <label class="form-label">Nombre del Período</label>
                                            <input type="text" class="form-control" v-model="periodoForm.nombre" required placeholder="Ej: Primer Trimestre">
                                        </div>
                                        <div class="form-row">
                                            <div class="form-col">
                                                <label class="form-label">Fecha Inicio</label>
                                                <input type="date" class="form-control" v-model="periodoForm.fecha_inicio" required>
                                            </div>
                                            <div class="form-col">
                                                <label class="form-label">Fecha Fin</label>
                                                <input type="date" class="form-control" v-model="periodoForm.fecha_fin" required>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label">Descripción</label>
                                            <textarea class="form-control" v-model="periodoForm.descripcion" placeholder="Descripción opcional"></textarea>
                                        </div>
                                        <div class="form-actions">
                                            <button type="button" class="btn-secondary" @click="resetPeriodoForm" v-if="periodoForm.id > 0">
                                                <i class="fas fa-times"></i> Cancelar
                                            </button>
                                            <button type="submit" class="btn-primary">
                                                <i class="fas fa-save"></i> {{ periodoForm.id > 0 ? 'Actualizar' : 'Crear' }}
                                            </button>
                                        </div>
                                    </form>
                                </div>

                                <!-- Right column: List of existing periods -->
                                <div class="periodos-list-container">
                                    <h5>Períodos existentes</h5>
                                    <div class="periodos-list">
                                        <div v-if="periodos.length === 0" class="empty-message">
                                            <i class="fas fa-info-circle"></i>
                                            <p>No hay períodos de evaluación definidos.</p>
                                        </div>
                                        <div v-else v-for="periodo in periodos" :key="periodo.id" class="periodo-item">
                                            <div class="periodo-info">
                                                <h5>{{ periodo.nombre }}</h5>
                                                <p>{{ formatDate(periodo.fecha_inicio) }} - {{ formatDate(periodo.fecha_fin) }}</p>
                                                <p v-if="periodo.descripcion">{{ periodo.descripcion }}</p>
                                            </div>
                                            <div class="periodo-actions">
                                                <button class="btn-link text-primary" @click="editarPeriodo(periodo)">
                                                    <img src="../img/notas.png" alt="editar" class="icon-img-b">
                                                </button>
                                                <button class="btn-link text-danger" @click="confirmarEliminarPeriodo(periodo)">
                                                    <img src="../img/basura.png" alt="eliminar" class="icon-img-b">
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal for evaluations -->
            <div class="modal" id="evaluacionModal" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">{{ evaluacionForm.id > 0 ? 'Editar Evaluación' : 'Nueva Evaluación' }}</h5>
                            <button type="button" class="btn-close" @click="closeEvaluacionModal">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        <div class="modal-body">
                            <form @submit.prevent="guardarEvaluacion">
                                <input type="hidden" v-model="evaluacionForm.grupo_id">
                                <div class="form-group">
                                    <label class="form-label">Nombre de la Evaluación</label>
                                    <input type="text" class="form-control" v-model="evaluacionForm.nombre" required placeholder="Ej: Examen Tema 1">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Fecha</label>
                                    <input type="date" class="form-control" v-model="evaluacionForm.fecha" required>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Descripción</label>
                                    <textarea class="form-control" v-model="evaluacionForm.descripcion" placeholder="Descripción opcional"></textarea>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Porcentaje (%) <span class="total-porcentaje">Total actual: {{ calcularTotalPorcentaje() }}%</span></label>
                                    <input type="number" class="form-control" v-model.number="evaluacionForm.porcentaje" min="0" max="100" required>
                                </div>
                                <div class="form-actions">
                                    <button type="button" class="btn-secondary" @click="closeEvaluacionModal">
                                        <i class="fas fa-times"></i> Cancelar
                                    </button>
                                    <button type="submit" class="btn-primary">
                                        <i class="fas fa-save"></i> {{ evaluacionForm.id > 0 ? 'Actualizar' : 'Crear' }}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Confirmation modal -->
            <div class="modal" id="confirmModal" tabindex="-1">
                <div class="modal-dialog modal-sm">
                    <div class="modal-content">
                        <div class="modal-header delete-header">
                            <h5 class="modal-title">Confirmar</h5>
                            <button type="button" class="btn-close" @click="closeConfirmModal">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        <div class="modal-body">
                            <p>{{ confirmMessage }}</p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn-secondary" @click="closeConfirmModal">
                                <i class="fas fa-times"></i> Cancelar
                            </button>
                            <button type="button" class="btn-danger" @click="confirmarAccion">
                                <i class="fas fa-check"></i> Confirmar
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Notification modal -->
            <div class="modal" id="notificationModal" tabindex="-1">
                <div class="modal-dialog modal-sm">
                    <div class="modal-content">
                        <div class="modal-header" :class="notificationType === 'success' ? 'success-header' : 'error-header'">
                            <h5 class="modal-title">{{ notificationTitle }}</h5>
                            <button type="button" class="btn-close" @click="closeNotificationModal">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        <div class="modal-body">
                            <p>{{ notificationMessage }}</p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn-primary" @click="closeNotificationModal">
                                <i class="fas fa-check"></i> Aceptar
                            </button>
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
    <script src="../js/evaluaciones.js?v=<?php echo time(); ?>"></script>
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