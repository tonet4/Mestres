<?php
/**
 * @author Antonio Esteban Lorenzo
 * 
 */
// Include the necessary files
require_once '../../includes/auth.php';
require_once '../../includes/utils.php';
require_once '../../api/config.php';

// Verify that the user is authenticated
if (!is_logged_in()) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

// Check if the request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

// Get user ID
$usuario_id = $_SESSION['user_id'];

// Get and sanitize form data
$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$nombre = isset($_POST['nombre']) ? limpiarDatos($_POST['nombre']) : '';
$apellidos = isset($_POST['apellidos']) ? limpiarDatos($_POST['apellidos']) : '';
$fecha_nacimiento = isset($_POST['fecha_nacimiento']) && !empty($_POST['fecha_nacimiento']) ? limpiarDatos($_POST['fecha_nacimiento']) : NULL;
$email = isset($_POST['email']) ? limpiarDatos($_POST['email']) : '';
$telefono = isset($_POST['telefono']) ? limpiarDatos($_POST['telefono']) : '';
$direccion = isset($_POST['direccion']) ? limpiarDatos($_POST['direccion']) : '';
$observaciones = isset($_POST['observaciones']) ? limpiarDatos($_POST['observaciones']) : '';
$grupo_id = isset($_POST['grupo_id']) && !empty($_POST['grupo_id']) ? (int)$_POST['grupo_id'] : NULL;

// Validate required fields
if (empty($nombre) || empty($apellidos)) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'El nombre y los apellidos son obligatorios']);
    exit;
}

// Process uploaded image
$imagen = NULL;
if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] == 0) {
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
    $max_size = 2 * 1024 * 1024; // 2MB
    
    if (!in_array($_FILES['imagen']['type'], $allowed_types)) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'El tipo de archivo no está permitido. Solo se permiten JPG, PNG y GIF']);
        exit;
    }
    
    if ($_FILES['imagen']['size'] > $max_size) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'El tamaño del archivo excede el límite de 2MB']);
        exit;
    }
    
    // Create directory if it doesn't exist
    $upload_dir = '../../img/alumnos/';
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    // Generate a unique filename
    $extension = pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION);
    $imagen = 'alumno_' . time() . '_' . uniqid() . '.' . $extension;
    $upload_path = $upload_dir . $imagen;
    
    // Move the uploaded file
    if (!move_uploaded_file($_FILES['imagen']['tmp_name'], $upload_path)) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Error al subir la imagen']);
        exit;
    }
}

try {
    // If id is provided, update existing student
    if ($id > 0) {
        // First check if the student belongs to this user
        $stmt = $conn->prepare("SELECT id, imagen FROM alumnos WHERE id = :id AND usuario_id = :usuario_id");
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':usuario_id', $usuario_id);
        $stmt->execute();
        
        $alumno = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$alumno) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'No tienes permiso para editar este alumno']);
            exit;
        }
        
        // If a new image is uploaded, delete the old one
        if ($imagen && !empty($alumno['imagen']) && $alumno['imagen'] != 'user.png') {
            $old_image_path = '../../img/alumnos/' . $alumno['imagen'];
            if (file_exists($old_image_path)) {
                unlink($old_image_path);
            }
        }
        
        // If no new image is uploaded, keep the existing one
        if (!$imagen) {
            $imagen = $alumno['imagen'];
        }
        
        $stmt = $conn->prepare("
            UPDATE alumnos 
            SET nombre = :nombre, 
                apellidos = :apellidos, 
                fecha_nacimiento = :fecha_nacimiento, 
                email = :email,
                telefono = :telefono,
                direccion = :direccion,
                observaciones = :observaciones,
                imagen = :imagen
            WHERE id = :id AND usuario_id = :usuario_id
        ");
        
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':usuario_id', $usuario_id);
        $stmt->bindParam(':nombre', $nombre);
        $stmt->bindParam(':apellidos', $apellidos);
        $stmt->bindParam(':fecha_nacimiento', $fecha_nacimiento);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':telefono', $telefono);
        $stmt->bindParam(':direccion', $direccion);
        $stmt->bindParam(':observaciones', $observaciones);
        $stmt->bindParam(':imagen', $imagen);
        
        $stmt->execute();
        
        $mensaje = 'Alumno actualizado correctamente';
    } 
    // Otherwise, create a new student
    else {
        $stmt = $conn->prepare("
            INSERT INTO alumnos (usuario_id, nombre, apellidos, fecha_nacimiento, email, telefono, direccion, observaciones, imagen)
            VALUES (:usuario_id, :nombre, :apellidos, :fecha_nacimiento, :email, :telefono, :direccion, :observaciones, :imagen)
        ");
        
        $stmt->bindParam(':usuario_id', $usuario_id);
        $stmt->bindParam(':nombre', $nombre);
        $stmt->bindParam(':apellidos', $apellidos);
        $stmt->bindParam(':fecha_nacimiento', $fecha_nacimiento);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':telefono', $telefono);
        $stmt->bindParam(':direccion', $direccion);
        $stmt->bindParam(':observaciones', $observaciones);
        $stmt->bindParam(':imagen', $imagen);
        
        $stmt->execute();
        
        $id = $conn->lastInsertId();
        $mensaje = 'Alumno creado correctamente';
    }
    
    // Handle group assignment
    if ($grupo_id) {
        // First, check if the group belongs to the user
        $stmt = $conn->prepare("SELECT id FROM grupos WHERE id = :grupo_id AND usuario_id = :usuario_id");
        $stmt->bindParam(':grupo_id', $grupo_id);
        $stmt->bindParam(':usuario_id', $usuario_id);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            // Delete existing assignment if any
            $stmt = $conn->prepare("DELETE FROM alumnos_grupos WHERE alumno_id = :alumno_id");
            $stmt->bindParam(':alumno_id', $id);
            $stmt->execute();
            
            // Create new assignment
            $stmt = $conn->prepare("INSERT INTO alumnos_grupos (alumno_id, grupo_id) VALUES (:alumno_id, :grupo_id)");
            $stmt->bindParam(':alumno_id', $id);
            $stmt->bindParam(':grupo_id', $grupo_id);
            $stmt->execute();
        }
    } else {
        // If no group is selected, remove from any group
        $stmt = $conn->prepare("DELETE FROM alumnos_grupos WHERE alumno_id = :alumno_id");
        $stmt->bindParam(':alumno_id', $id);
        $stmt->execute();
    }
    
    // Return the created/updated student
    $stmt = $conn->prepare("
        SELECT a.*, g.nombre as grupo_nombre, g.id as grupo_id
        FROM alumnos a
        LEFT JOIN alumnos_grupos ag ON a.id = ag.alumno_id
        LEFT JOIN grupos g ON ag.grupo_id = g.id
        WHERE a.id = :id
    ");
    
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    
    $alumno = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!empty($alumno['fecha_nacimiento'])) {
        $alumno['fecha_nacimiento_formateada'] = date('d/m/Y', strtotime($alumno['fecha_nacimiento']));
    } else {
        $alumno['fecha_nacimiento_formateada'] = '';
    }
    
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true, 
        'message' => $mensaje, 
        'alumno' => $alumno
    ]);
    
} catch (PDOException $e) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Error al guardar el alumno: ' . $e->getMessage()]);
}
?>