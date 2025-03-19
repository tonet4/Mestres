<?php
// Iniciar sesión
session_start();

// Eliminar todas las variables de sesión
$_SESSION = array();

// Si se está usando una cookie de sesión, eliminarla
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Eliminar cualquier cookie de "recordar sesión"
if (isset($_COOKIE['remember_user'])) {
    setcookie('remember_user', '', time() - 3600, '/');
}

// Destruir la sesión
session_destroy();

// Redirigir a la página de inicio
header("Location: index.html");
exit;