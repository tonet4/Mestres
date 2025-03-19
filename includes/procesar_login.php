<?php
// Función para validar el login de un usuario
function login_usuario($email, $password, $remember = false) {
    // Array para almacenar errores
    $errores = [];
    
    // Validar que todos los campos estén completos
    if(empty($email) || empty($password)) {
        $errores[] = "Todos los campos son obligatorios";
    }
    
    // Validar formato de email
    if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errores[] = "El formato del correo electrónico no es válido";
    }
    
    // Si no hay errores, proceder con la autenticación
    if(empty($errores)) {
        try {
            // Incluir la conexión a la base de datos
            require_once dirname(__FILE__) . '/../config.php';
            
            // Buscar el usuario por email
            $stmt = $conn->prepare("SELECT id, nombre, apellidos, email, password, rol FROM usuarios WHERE email = :email AND activo = 1");
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            
            if($stmt->rowCount() > 0) {
                $usuario = $stmt->fetch();
                
                // Verificar la contraseña
                if(password_verify($password, $usuario['password'])) {
                    // Iniciar sesión
                    session_start();
                    $_SESSION['user_id'] = $usuario['id'];
                    $_SESSION['usuario_id'] = $usuario['id']; // Para compatibilidad con ambos formatos
                    $_SESSION['user_nombre'] = $usuario['nombre'];
                    $_SESSION['usuario_nombre'] = $usuario['nombre']; // Para compatibilidad con ambos formatos
                    $_SESSION['user_apellidos'] = $usuario['apellidos'];
                    $_SESSION['user_email'] = $usuario['email'];
                    $_SESSION['user_rol'] = $usuario['rol'];
                    $_SESSION['loggedin'] = true;
                    
                    // Actualizar última conexión
                    $stmt = $conn->prepare("UPDATE usuarios SET ultima_conexion = NOW() WHERE id = :id");
                    $stmt->bindParam(':id', $usuario['id']);
                    $stmt->execute();
                    
                    // Si se seleccionó "recordar sesión", crear una cookie
                    if($remember) {
                        $token = bin2hex(random_bytes(32));
                        $expiry = time() + (30 * 24 * 60 * 60); // 30 días
                        
                        // En una implementación real, deberíamos guardar este token en la base de datos
                        // Para simplificar, solo guardamos el ID del usuario
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
    
    // Si hay errores, devolverlos
    if(!empty($errores)) {
        return ['success' => false, 'message' => implode('<br>', $errores)];
    }
}