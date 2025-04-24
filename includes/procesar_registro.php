<?php
/**
 * @author Antonio Esteban Lorenzo
 * 
 */
// Function to validate the registration of a new user
function registrar_usuario($nombre, $apellidos, $email, $password, $confirm_password) {
    // Array to store errors
    $errores = [];
    
    // Validate that all fields are complete
    if(empty($nombre) || empty($apellidos) || empty($email) || empty($password) || empty($confirm_password)) {
        $errores[] = "Todos los campos son obligatorios";
    }
    
    // Validate that the passwords match
    if($password !== $confirm_password) {
        $errores[] = "Las contraseñas no coinciden";
    }
    
    // Validate password length and complexity
    if(strlen($password) < 8) {
        $errores[] = "La contraseña debe tener al menos 8 caracteres";
    }

    // Verify that the password contains at least one capital letter
    if(!preg_match('/[A-Z]/', $password)) {
        $errores[] = "La contraseña debe contener al menos una letra mayúscula";
    }

    //Verify that the password contains at least one lowercase letter
    if(!preg_match('/[a-z]/', $password)) {
        $errores[] = "La contraseña debe contener al menos una letra minúscula";
    }

    // Verify that the password contains at least one number
    if(!preg_match('/[0-9]/', $password)) {
        $errores[] = "La contraseña debe contener al menos un número";
    }

    // Verify that the password contains at least one special character
    if(!preg_match('/[!@#$%^&*(),.?":{}|<>]/', $password)) {
        $errores[] = "La contraseña debe contener al menos un carácter especial (!@#$%^&*(),.?\":{}|<>)";
    }

    
    // Validate email format
    if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errores[] = "El formato del correo electrónico no es válido";
    }
    
    // If there are no errors, proceed with registration.
    if(empty($errores)) {
        try {
            // Include the connection to the database
            require_once dirname(__FILE__) . '/../api/config.php';
            
            // Check if the email already exists
            $stmt = $conn->prepare("SELECT id FROM usuarios WHERE email = :email");
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            
            if($stmt->rowCount() > 0) {
                $errores[] = "Este correo electrónico ya está registrado";
            } else {
                // Hash the password
                $password_hash = password_hash($password, PASSWORD_DEFAULT);
                
                // Insert new user
                $stmt = $conn->prepare("INSERT INTO usuarios (nombre, apellidos, email, password) VALUES (:nombre, :apellidos, :email, :password)");
                $stmt->bindParam(':nombre', $nombre);
                $stmt->bindParam(':apellidos', $apellidos);
                $stmt->bindParam(':email', $email);
                $stmt->bindParam(':password', $password_hash);
                
                if($stmt->execute()) {
                    return ['success' => true, 'message' => 'Registro exitoso. Puedes iniciar sesión ahora.'];
                } else {
                    $errores[] = "Hubo un error al procesar tu registro. Inténtalo de nuevo.";
                }
            }
        } catch(PDOException $e) {
            $errores[] = "Error en la base de datos: " . $e->getMessage();
        }
    }
    
    //If there are errors, return them
    if(!empty($errores)) {
        return ['success' => false, 'message' => implode('<br>', $errores)];
    }
}