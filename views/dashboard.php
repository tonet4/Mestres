<?php

/**
 * @author Antonio Esteban Lorenzo
 * 
 */

// Include necessary files
require_once '../includes/auth.php';
require_once '../includes/utils.php';
require_once '../api/config.php';

// Verify that the user is authenticated
require_login();

// Array of inspirational phrases
$frases = [
    [
        'texto' => 'La educación es el arma más poderosa que puedes usar para cambiar el mundo.',
        'autor' => 'Nelson Mandela'
    ],
    [
        'texto' => 'El objetivo principal de la educación es crear personas capaces de hacer cosas nuevas y no simplemente repetir lo que otras generaciones hicieron.',
        'autor' => 'Jean Piaget'
    ],
    [
        'texto' => 'Dime y lo olvido, enséñame y lo recuerdo, involúcrame y lo aprendo.',
        'autor' => 'Benjamin Franklin'
    ],
    [
        'texto' => 'La educación no es la preparación para la vida; la educación es la vida misma.',
        'autor' => 'John Dewey'
    ],
    [
        'texto' => 'Los niños son como cemento fresco. Cualquier cosa que caiga sobre ellos deja una impresión.',
        'autor' => 'Haim Ginott'
    ],
    [
        'texto' => 'Enseñar no es transferir conocimiento, sino crear las posibilidades para su producción o construcción.',
        'autor' => 'Paulo Freire'
    ],
    [
        'texto' => 'La inteligencia más valiosa es la que nos hace mejores, no la que acumula conocimientos.',
        'autor' => 'Álex Rovira'
    ]
];

// Select a random phrase
$frase_random = $frases[array_rand($frases)];

// Array of inspiring images
$imagenes = [
    "../img/inspira1.avif",
    "../img/inspira2.webp",
    "../img/inspira3.webp",
    "../img/inspira4.jpg",
    "../img/inspira6.jpg"
];

// Select a random image
$imagen_random = $imagenes[array_rand($imagenes)];

// Get user notes
try {
    $stmt = $conn->prepare("SELECT id, texto, fecha_creacion, fecha_actualizacion FROM notas WHERE usuario_id = :usuario_id AND estado = 'activo' ORDER BY fecha_actualizacion DESC, fecha_creacion DESC");
    $stmt->bindParam(':usuario_id', $_SESSION['user_id']);
    $stmt->execute();
    $notas = $stmt->fetchAll();
} catch (PDOException $e) {
    $notas = [];
}

// Get user's default schedule
try {
    $stmt = $conn->prepare("SELECT id, nombre, descripcion, dias_semana FROM horarios WHERE usuario_id = :usuario_id AND es_predeterminado = 1 LIMIT 1");
    $stmt->bindParam(':usuario_id', $_SESSION['user_id']);
    $stmt->execute();
    $horario_predeterminado = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $bloquesPorDia = [];
    if ($horario_predeterminado) {
        // Get blocks for this schedule
        $stmt = $conn->prepare("SELECT id, dia_semana, hora_inicio, hora_fin, titulo, descripcion, color FROM horarios_bloques WHERE horario_id = :horario_id ORDER BY dia_semana, hora_inicio");
        $stmt->bindParam(':horario_id', $horario_predeterminado['id']);
        $stmt->execute();
        $bloques = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Group blocks by day
        foreach ($bloques as $bloque) {
            $dia = (int)$bloque['dia_semana'];
            if (!isset($bloquesPorDia[$dia])) {
                $bloquesPorDia[$dia] = [];
            }
            $bloquesPorDia[$dia][] = $bloque;
        }
    }
} catch (PDOException $e) {
    $horario_predeterminado = null;
    $bloquesPorDia = [];
}

// Process the addition, editing, or deletion of notes
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'add') {
        $texto = limpiarDatos($_POST['nota_texto'] ?? '');

        if (!empty($texto)) {
            try {
                $stmt = $conn->prepare("INSERT INTO notas (usuario_id, texto) VALUES (:usuario_id, :texto)");
                $stmt->bindParam(':usuario_id', $_SESSION['user_id']);
                $stmt->bindParam(':texto', $texto);
                $stmt->execute();

                // Redirect to prevent form resubmission
                header("Location: dashboard.php");
                exit;
            } catch (PDOException $e) {
            }
        }
    } elseif ($_POST['action'] === 'edit' && isset($_POST['nota_id'])) {
        $nota_id = (int)$_POST['nota_id'];
        $texto = limpiarDatos($_POST['nota_texto'] ?? '');

        if (!empty($texto)) {
            try {
                // Verify that the note belongs to the user
                $stmt = $conn->prepare("UPDATE notas SET texto = :texto, fecha_actualizacion = CURRENT_TIMESTAMP WHERE id = :id AND usuario_id = :usuario_id");
                $stmt->bindParam(':texto', $texto);
                $stmt->bindParam(':id', $nota_id);
                $stmt->bindParam(':usuario_id', $_SESSION['user_id']);
                $stmt->execute();

                // Redirect
                header("Location: dashboard.php");
                exit;
            } catch (PDOException $e) {
            }
        }
    } elseif ($_POST['action'] === 'delete' && isset($_POST['nota_id'])) {
        $nota_id = (int)$_POST['nota_id'];

        try {
            // Verify that the note belongs to the user
            $stmt = $conn->prepare("UPDATE notas SET estado = 'eliminado' WHERE id = :id AND usuario_id = :usuario_id");
            $stmt->bindParam(':id', $nota_id);
            $stmt->bindParam(':usuario_id', $_SESSION['user_id']);
            $stmt->execute();

            // Redirect
            header("Location: dashboard.php");
            exit;
        } catch (PDOException $e) {
        }
    }
}

// Array of day names
$diasSemana = [
    1 => 'Lunes',
    2 => 'Martes',
    3 => 'Miércoles',
    4 => 'Jueves',
    5 => 'Viernes',
    6 => 'Sábado',
    7 => 'Domingo'
];
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QUADERN MESTRES</title>
    <link rel="shortcut icon" href="../img/logo2.png">
    <link rel="stylesheet" href="../style/base.css">
    <link rel="stylesheet" href="../style/dashboard.css">
    <link rel="stylesheet" href="../style/horarios.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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
            <link rel="shortcut icon" href="../img/logo2.png">
        </div>
        <div class="nav-right">
            <div class="user-info">
                <span id="user-name">Bienvenido/a, <?php echo htmlspecialchars($_SESSION['user_nombre']); ?></span>
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
            <li class="active"><a href="dashboard.php"><i class="fas fa-home"></i> Inicio</a></li>
            <li><a href="calendario.php"><i class="fas fa-calendar"></i> Calendario</a></li>
            <li><a href="alumnos.php"><i class="fas fa-users"></i> Alumnado</a></li>
            <li><a href="reuniones.php"><i class="fas fa-comments"></i> Reuniones</a></li>
            <li><a href="asignaturas.php"><i class="fas fa-book"></i> Asignaturas</a></li>
            <li><a href="asistencias.php"><i class="fas fa-book"></i> Asistencias</a></li>
            <li><a href="evaluaciones.php"><i class="fas fa-clipboard-list"></i> Evaluaciones</a></li>
            <li><a href="horarios.php"><i class="fas fa-clock"></i> Horarios</a></li>
            <li><a href="../api/logout.php"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a></li>
        </ul>
    </div>

    <!-- Overlay for when the sidebar is open -->
    <div class="overlay" id="overlay"></div>

    <!-- Main content-->
    <main class="main-content">
        <!-- Horario predeterminado -->
        <div class="horario-dashboard-container">
            <div class="horario-dashboard-header">
                <h2><i class="fas fa-clock"></i> Mi Horario</h2>
                <div class="horario-actions">
                    <?php if ($horario_predeterminado): ?>
                        <a href="editor_horario.php?id=<?php echo $horario_predeterminado['id']; ?>" class="action-btn">
                            <i class="fas fa-edit"></i> Editar
                        </a>
                    <?php endif; ?>
                    <a href="horarios.php" class="action-btn">
                        <i class="fas fa-th-list"></i> Todos mis horarios
                    </a>
                </div>
            </div>
            
            <?php if ($horario_predeterminado): ?>
                <div class="horario-dashboard-content">
                    <div class="horario-dashboard-title">
                        <h3><?php echo htmlspecialchars($horario_predeterminado['nombre']); ?></h3>
                        <?php if (!empty($horario_predeterminado['descripcion'])): ?>
                            <p class="horario-dashboard-descripcion"><?php echo htmlspecialchars($horario_predeterminado['descripcion']); ?></p>
                        <?php endif; ?>
                    </div>
                    
                    <div class="horario-dashboard-grid">
                        <?php 
                        // Get the maximum number of days to show based on the schedule configuration
                        $maxDias = $horario_predeterminado['dias_semana'];
                        
                        // Create a column for each day
                        for ($dia = 1; $dia <= $maxDias; $dia++): 
                        ?>
                            <div class="day-column-dashboard">
                                <!-- Day header -->
                                <div class="day-header-dashboard">
                                    <?php echo $diasSemana[$dia]; ?>
                                </div>
                                
                                <!-- Blocks for this day -->
                                <div class="day-blocks">
                                    <?php 
                                    $blocksForDay = $bloquesPorDia[$dia] ?? [];
                                    if (!empty($blocksForDay)): 
                                        foreach ($blocksForDay as $bloque): 
                                            // Format time
                                            $horaInicio = substr($bloque['hora_inicio'], 0, 5);
                                            $horaFin = substr($bloque['hora_fin'], 0, 5);
                                    ?>
                                        <div class="time-block-dashboard" style="background-color: <?php echo htmlspecialchars($bloque['color'] ?: '#3498db'); ?>; color: <?php echo htmlspecialchars(getLuminanceValue($bloque['color']) > 0.5 ? '#333' : '#fff'); ?>">
                                            <div class="time-block-dashboard-header">
                                                <span class="time-block-dashboard-title"><?php echo htmlspecialchars($bloque['titulo']); ?></span>
                                                <span class="time-block-dashboard-time"><?php echo $horaInicio . ' - ' . $horaFin; ?></span>
                                            </div>
                                            <?php if (!empty($bloque['descripcion'])): ?>
                                                <div class="time-block-dashboard-desc"><?php echo htmlspecialchars($bloque['descripcion']); ?></div>
                                            <?php endif; ?>
                                        </div>
                                    <?php 
                                        endforeach; 
                                    else: 
                                    ?>
                                        <div class="no-blocks-message">
                                            No hay bloques
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endfor; ?>
                    </div>
                </div>
            <?php else: ?>
                <div class="no-horario-message">
                    <p>No tienes un horario predeterminado configurado.</p>
                    <a href="horarios.php" class="action-btn">
                        <i class="fas fa-plus"></i> Crear horario
                    </a>
                </div>
            <?php endif; ?>
        </div>

        <div class="content-wrapper">
            <!-- Left panel - Inspirational quote and image -->
            <div class="panel inspiration-panel">
                <div class="panel-header">
                    <h2>Inspiración del día</h2>
                </div>
                <div class="panel-content">
                    <div class="quote-image" id="random-image">
                        <img src="<?php echo htmlspecialchars($imagen_random); ?>" alt="Imagen inspiradora">
                    </div>
                    <div class="quote-text" id="random-quote">
                        <p><?php echo htmlspecialchars($frase_random['texto']); ?></p>
                        <p class="quote-author">- <?php echo htmlspecialchars($frase_random['autor']); ?></p>
                    </div>
                </div>
            </div>

            <!--Right panel - Notes -->
            <div class="panel tasks-panel">
                <div class="panel-header">
                    <h2>Mis Notas</h2>
                    <button id="add-task-btn" class="add-btn"><i class="fas fa-plus"></i></button>
                </div>
                <div class="panel-content">
                    <div class="task-list" id="task-list">
                        <?php if (empty($notas)): ?>
                            <div class="empty-notes">
                                <p>No tienes notas guardadas. ¡Añade tu primera nota!</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($notas as $nota): ?>
                                <div class="task-item" id="nota-<?php echo $nota['id']; ?>">
                                    <div class="task-text"><?php echo nl2br(htmlspecialchars($nota['texto'])); ?></div>
                                    <div class="task-actions">
                                        <button class="edit-task" data-id="<?php echo $nota['id']; ?>">
                                            <img src="../img/notas.png" alt="editar" class="delete-icon">
                                        </button>
                                        <button class="delete-task" data-id="<?php echo $nota['id']; ?>">
                                            <img src="../img/basura.png" alt="Eliminar" class="delete-icon">
                                        </button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <!-- Form to add notes (initially hidden) -->
                    <div class="add-task-form" id="add-task-form">
                        <form method="POST" action="dashboard.php">
                            <input type="hidden" name="action" value="add">
                            <textarea id="task-input" name="nota_texto" placeholder="Escribe tu nota aquí..."></textarea>
                            <div class="form-buttons">
                                <button type="submit" id="save-task-btn" class="save-btn">Guardar</button>
                                <button type="button" id="cancel-task-btn" class="cancel-btn">Cancelar</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Hidden form for editing notes -->
    <form id="edit-form" method="POST" action="dashboard.php" style="display: none;">
        <input type="hidden" name="action" value="edit">
        <input type="hidden" name="nota_id" id="edit-nota-id">
        <input type="hidden" name="nota_texto" id="edit-nota-texto">
    </form>

    <!-- Hidden form to delete notes -->
    <form id="delete-form" method="POST" action="dashboard.php" style="display: none;">
        <input type="hidden" name="action" value="delete">
        <input type="hidden" name="nota_id" id="delete-nota-id">
    </form>

    <!-- FOOTER -->
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

    <script src="../js/dashboard.js"></script>

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

<?php
/**
 * Helper function to calculate luminance for text color contrast
 */
function getLuminanceValue($hex) {
    // Default to a blue color if none provided
    $hex = $hex ?: '#3498db';
    
    // Convert hex to RGB
    $r = 0;
    $g = 0;
    $b = 0;
    
    // 3 or 6 digits
    if (strlen($hex) === 4) {
        $r = hexdec(substr($hex, 1, 1) . substr($hex, 1, 1));
        $g = hexdec(substr($hex, 2, 1) . substr($hex, 2, 1));
        $b = hexdec(substr($hex, 3, 1) . substr($hex, 3, 1));
    } else if (strlen($hex) === 7) {
        $r = hexdec(substr($hex, 1, 2));
        $g = hexdec(substr($hex, 3, 2));
        $b = hexdec(substr($hex, 5, 2));
    }
    
    // Normalize RGB values
    $r /= 255;
    $g /= 255;
    $b /= 255;
    
    // Calculate luminance
    return 0.2126 * $r + 0.7152 * $g + 0.0722 * $b;
}
?>