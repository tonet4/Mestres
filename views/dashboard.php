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
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - QUADERN MESTRES</title>
    <link rel="stylesheet" href="../estilo/base.css">
    <link rel="stylesheet" href="../estilo/dashboard.css">
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
            <li><a href="dashboard.php"><i class="fas fa-home"></i> Inicio</a></li>
            <li><a href="calendario.php"><i class="fas fa-calendar"></i> Calendario</a></li>
            <li><a href="#"><i class="fas fa-users"></i> Alumnado</a></li>
            <li><a href="#"><i class="fas fa-comments"></i> Reuniones</a></li>
            <li><a href="#"><i class="fas fa-book"></i> Asignaturas</a></li>
            <li><a href="#"><i class="fas fa-clipboard-list"></i> Evaluaciones</a></li>
            <li><a href="#"><i class="fas fa-chart-bar"></i> Estadísticas</a></li>
            <li><a href="#"><i class="fas fa-cog"></i> Configuración</a></li>
            <li><a href="../api/logout.php"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a></li>
        </ul>
    </div>

    <!-- Overlay for when the sidebar is open -->
    <div class="overlay" id="overlay"></div>

    <!-- Main content-->
    <main class="main-content">
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
                                    <div class="task-text"><?php echo htmlspecialchars($nota['texto']); ?></div>
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

    <footer>
        <p>&copy; 2025 QUADERN MESTRES - Todos los derechos reservados</p>
    </footer>

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