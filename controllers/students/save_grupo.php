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
$descripcion = isset($_POST['descripcion']) ? limpiarDatos($_POST['descripcion']) : '';
$curso_academico = isset($_POST['curso_academico']) ? limpiarDatos($_POST['curso_academico']) : '';

// Validate required fields
if (empty($nombre)) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'El nombre del grupo es obligatorio']);
    exit;
}

try {
    // If id is provided, update existing group
    if ($id > 0) {
        // First check if the group belongs to this user
        $stmt = $conn->prepare("SELECT id FROM grupos WHERE id = :id AND usuario_id = :usuario_id");
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':usuario_id', $usuario_id);
        $stmt->execute();
        
        if ($stmt->rowCount() == 0) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'No tienes permiso para editar este grupo']);
            exit;
        }
        
        $stmt = $conn->prepare("
            UPDATE grupos 
            SET nombre = :nombre, 
                descripcion = :descripcion, 
                curso_academico = :curso_academico
            WHERE id = :id AND usuario_id = :usuario_id
        ");
        
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':usuario_id', $usuario_id);
        $stmt->bindParam(':nombre', $nombre);
        $stmt->bindParam(':descripcion', $descripcion);
        $stmt->bindParam(':curso_academico', $curso_academico);
        
        $stmt->execute();
        
        $mensaje = 'Grupo actualizado correctamente';
    } 
    // Otherwise, create a new group
    else {
        $stmt = $conn->prepare("
            INSERT INTO grupos (usuario_id, nombre, descripcion, curso_academico)
            VALUES (:usuario_id, :nombre, :descripcion, :curso_academico)
        ");
        
        $stmt->bindParam(':usuario_id', $usuario_id);
        $stmt->bindParam(':nombre', $nombre);
        $stmt->bindParam(':descripcion', $descripcion);
        $stmt->bindParam(':curso_academico', $curso_academico);
        
        $stmt->execute();
        
        $id = $conn->lastInsertId();
        $mensaje = 'Grupo creado correctamente';
    }
    
    // Return the created/updated group
    $stmt = $conn->prepare("SELECT id, nombre, descripcion, curso_academico FROM grupos WHERE id = :id");
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    
    $grupo = $stmt->fetch(PDO::FETCH_ASSOC);
    
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true, 
        'message' => $mensaje, 
        'grupo' => $grupo
    ]);
    
} catch (PDOException $e) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Error al guardar el grupo: ' . $e->getMessage()]);
}
?>