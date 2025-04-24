<?php
/**
 * @author Antonio Esteban Lorenzo
 * 
 */
// Function to validate a user's login
function login_usuario($email, $password, $remember = false) {
    // Array to store errors
    $errores = [];
    
    // Validate that all fields are complete
    if(empty($email) || empty($password)) {
        $errores[] = "Todos los campos son obligatorios";
    }
    
    // Validate email format
    if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errores[] = "El formato del correo electrónico no es válido";
    }
    
    // If there are no errors, proceed with authentication.
    if(empty($errores)) {
        try {
            // Include the connection to the database
            require_once dirname(__FILE__) . '/../api/config.php';
                        
            // Search for the user by email
            $stmt = $conn->prepare("SELECT id, nombre, apellidos, email, password, rol FROM usuarios WHERE email = :email AND activo = 1");
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            
            if($stmt->rowCount() > 0) {
                $usuario = $stmt->fetch();
                
                //Verify password
                if(password_verify($password, $usuario['password'])) {
                    // Login
                    session_start();
                    $_SESSION['user_id'] = $usuario['id'];
                    $_SESSION['usuario_id'] = $usuario['id']; 
                    $_SESSION['user_nombre'] = $usuario['nombre'];
                    $_SESSION['usuario_nombre'] = $usuario['nombre']; 
                    $_SESSION['user_apellidos'] = $usuario['apellidos'];
                    $_SESSION['user_email'] = $usuario['email'];
                    $_SESSION['user_rol'] = $usuario['rol'];
                    $_SESSION['loggedin'] = true;
                    
                    // Update last connection
                    $stmt = $conn->prepare("UPDATE usuarios SET ultima_conexion = NOW() WHERE id = :id");
                    $stmt->bindParam(':id', $usuario['id']);
                    $stmt->execute();
                    
                    // If "remember session" was selected, create a cookie
                    if($remember) {
                        $token = bin2hex(random_bytes(32));
                        $expiry = time() + (30 * 24 * 60 * 60); // 30 días
                        
                        
                        setcookie('remember_user', $usuario['id'], $expiry, '/');
                    }
                    
                    return ['success' => true, 'message' => 'Inicio de sesión exitoso.'];
                } else {
                    $errores[] = "Credenciales incorrectas. Por favor, verifica tu email y contraseña.";
                }
            } else {
                $errores[] = "Credenciales incorrectas. Por favor, verifica tu email y contraseña.";
            }
        } catch(PDOException $e) {
            $errores[] = "Error en la base de datos: " . $e->getMessage();
        }
    }
    
    // If there are errors, return them
    if(!empty($errores)) {
        return ['success' => false, 'message' => implode('<br>', $errores)];
    }
}