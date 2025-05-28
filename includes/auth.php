<?php
/**
 * @author Antonio Esteban Lorenzo
 * 
 */

// Sign in if you are not signed in
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Function to check if the user is authenticated
function is_logged_in() {
    return isset($_SESSION['user_id']) && isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true;
}

// Function to check if the user is an administrator
function is_admin() {
    return is_logged_in() && isset($_SESSION['user_rol']) && $_SESSION['user_rol'] === 'administrador';
}

// Function to redirect if not authenticated
function require_login() {
    if (!is_logged_in()) {
        header("Location: ../views/login.php");
        exit;
    }
}

// Function to redirect if you are not an administrator
function require_admin() {
    require_login();
    if (!is_admin()) {
        header("Location: ../views/dashboard.php");
        exit;
    }
}

// Check if a "remember session" cookie exists and the user is not logged in
if(!is_logged_in() && isset($_COOKIE['remember_user'])) {
   
    try {
        require_once dirname(__FILE__) . '/../api/config.php';
        
        $user_id = $_COOKIE['remember_user'];
        
        $stmt = $conn->prepare("SELECT id, nombre, apellidos, email, rol FROM usuarios WHERE id = :id AND activo = 1");
        $stmt->bindParam(':id', $user_id);
        $stmt->execute();
        
        if($stmt->rowCount() > 0) {
            $usuario = $stmt->fetch();
            
            // Session start
            $_SESSION['user_id'] = $usuario['id'];
            $_SESSION['user_nombre'] = $usuario['nombre'];
            $_SESSION['user_apellidos'] = $usuario['apellidos'];
            $_SESSION['user_email'] = $usuario['email'];
            $_SESSION['user_rol'] = $usuario['rol'];
            $_SESSION['loggedin'] = true;
            
            // Update last connection
            $stmt = $conn->prepare("UPDATE usuarios SET ultima_conexion = NOW() WHERE id = :id");
            $stmt->bindParam(':id', $usuario['id']);
            $stmt->execute();
        }
    } catch(PDOException $e) {
       
    }
}