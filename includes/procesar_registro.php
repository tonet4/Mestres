<?php
// Función para validar el registro de un nuevo usuario
function registrar_usuario($nombre, $apellidos, $email, $password, $confirm_password) {
    // Array para almacenar errores
    $errores = [];
    
    // Validar que todos los campos estén completos
    if(empty($nombre) || empty($apellidos) || empty($email) || empty($password) || empty($confirm_password)) {
        $errores[] = "Todos los campos son obligatorios";
    }
    
    // Validar que las contraseñas coincidan
    if($password !== $confirm_password) {
        $errores[] = "Las contraseñas no coinciden";
    }
    
    // Validar longitud y complejidad de la contraseña
    if(strlen($password) < 8) {
        $errores[] = "La contraseña debe tener al menos 8 caracteres";
    }
    
    // Validar formato de email
    if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errores[] = "El formato del correo electrónico no es válido";
    }
    
    // Si no hay errores, proceder con el registro
    if(empty($errores)) {
        try {
            // Incluir la conexión a la base de datos
            require_once dirname(__FILE__) . '/../config.php';
            
            // Verificar si el email ya existe
            $stmt = $conn->prepare("SELECT id FROM usuarios WHERE email = :email");
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            
            if($stmt->rowCount() > 0) {
                $errores[] = "Este correo electrónico ya está registrado";
            } else {
                // Hashear la contraseña
                $password_hash = password_hash($password, PASSWORD_DEFAULT);
                
                // Insertar nuevo usuario
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
    
    // Si hay errores, devolverlos
    if(!empty($errores)) {
        return ['success' => false, 'message' => implode('<br>', $errores)];
    }
}