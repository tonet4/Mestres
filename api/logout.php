<?php
/**
 * @author Antonio Esteban Lorenzo
 * 
 */

session_start();

// Delete all session variables
$_SESSION = array();

// If a session cookie is being used, delete it
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Delete any "session remember" cookies
if (isset($_COOKIE['remember_user'])) {
    setcookie('remember_user', '', time() - 3600, '/');
}

// Destroy the session
session_destroy();

// Redirect to the home page
header("Location: ../index.html");
exit;