<?php
session_start();
// Si ya hay una sesión activa, redirigir al dashboard
if(isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
}

// Incluir el procesador de login
require_once 'includes/procesar_login.php';

// Variables para mensajes
$error = '';

// Procesar formulario de login
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $remember = isset($_POST['remember']);
    
    // Procesar login
    $resultado = login_usuario($email, $password, $remember);
    
    if($resultado['success']) {
        // Redirigir al dashboard
        header("Location: dashboard.php");
        exit;
    } else {
        $error = $resultado['message'];
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso - QUADERN MESTRES</title>
    <link rel="stylesheet" href="./estilo/estilo.css">
    <link rel="stylesheet" href="./estilo/forms.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <!-- Navegación -->
    <nav class="navbar">
        <div class="nav-left">
            <div class="logo">
                <a href="index.html"><img src="img/logo.png" alt="Logo Quadern Mestres"></a>
            </div>
            <h1><a href="index.html">QUADERN MESTRES</a></h1>
        </div>
        <div class="nav-right">
            <a href="registro.php" class="btn btn-registro">Registro</a>
            <a href="login.php" class="btn btn-acceso">Acceso</a>
        </div>
        <!-- Menú hamburguesa para móviles -->
        <div class="menu-toggle">
            <i class="fas fa-bars"></i>
        </div>
    </nav>

    <!-- Contenido Principal -->
    <main class="form-container">
        <section class="form-section">
            <h2>Acceso a tu cuenta</h2>
            
            <?php if($error): ?>
                <div class="error-message">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <form action="login.php" method="post">
                <div class="form-group">
                    <label for="email">Correo electrónico</label>
                    <input type="email" id="email" name="email" value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Contraseña</label>
                    <input type="password" id="password" name="password" required>
                </div>
                
                <div class="form-group remember-group">
                    <input type="checkbox" id="remember" name="remember">
                    <label for="remember">Recordar mi sesión</label>
                </div>
                
                <button type="submit" class="btn btn-acceso btn-block">Iniciar sesión</button>
                
                <div class="form-footer">
                    <p>¿No tienes cuenta? <a href="registro.php">Regístrate aquí</a></p>
                    <p><a href="recuperar-password.php">¿Olvidaste tu contraseña?</a></p>
                </div>
            </form>
        </section>
    </main>

    <!-- Pie de página -->
    <footer>
        <p>&copy; 2025 QUADERN MESTRES - Todos los derechos reservados</p>
    </footer>

    <!-- Scripts -->
    <script src="js/main.js"></script>
</body>
</html>