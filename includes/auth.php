<?php
// Iniciar la sesión si no está iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Función para verificar si el usuario está autenticado
function is_logged_in() {
    return isset($_SESSION['user_id']) && isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true;
}

// Función para verificar si el usuario es administrador
function is_admin() {
    return is_logged_in() && isset($_SESSION['user_rol']) && $_SESSION['user_rol'] === 'administrador';
}

// Función para redirigir si no está autenticado
function require_login() {
    if (!is_logged_in()) {
        header("Location: login.php");
        exit;
    }
}

// Función para redirigir si no es administrador
function require_admin() {
    require_login();
    if (!is_admin()) {
        header("Location: dashboard.php");
        exit;
    }
}

// Verificar si existe una cookie de "recordar sesión" y el usuario no está logueado
if(!is_logged_in() && isset($_COOKIE['remember_user'])) {
    // En una implementación real, deberíamos verificar el token en la base de datos
    // Para simplificar, solo verificamos si existe el usuario
    try {
        require_once dirname(__FILE__) . '/../config.php';
        
        $user_id = $_COOKIE['remember_user'];
        
        $stmt = $conn->prepare("SELECT id, nombre, apellidos, email, rol FROM usuarios WHERE id = :id AND activo = 1");
        $stmt->bindParam(':id', $user_id);
        $stmt->execute();
        
        if($stmt->rowCount() > 0) {
            $usuario = $stmt->fetch();
            
            // Iniciar sesión
            $_SESSION['user_id'] = $usuario['id'];
            $_SESSION['user_nombre'] = $usuario['nombre'];
            $_SESSION['user_apellidos'] = $usuario['apellidos'];
            $_SESSION['user_email'] = $usuario['email'];
            $_SESSION['user_rol'] = $usuario['rol'];
            $_SESSION['loggedin'] = true;
            
            // Actualizar última conexión
            $stmt = $conn->prepare("UPDATE usuarios SET ultima_conexion = NOW() WHERE id = :id");
            $stmt->bindParam(':id', $usuario['id']);
            $stmt->execute();
        }
    } catch(PDOException $e) {
        // Error al conectar con la base de datos, no hacemos nada
    }
}