<?php
/**
 * @author Antonio Esteban Lorenzo
 * 
 */
// Include the necessary files
require_once '../../includes/auth.php';
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

// Get student ID
$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

// Validate student ID
if ($id <= 0) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'ID de alumno inválido']);
    exit;
}

try {
    // First check if the student belongs to this user and get image filename
    $stmt = $conn->prepare("SELECT imagen FROM alumnos WHERE id = :id AND usuario_id = :usuario_id");
    $stmt->bindParam(':id', $id);
    $stmt->bindParam(':usuario_id', $usuario_id);
    $stmt->execute();
    
    $alumno = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$alumno) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'No tienes permiso para eliminar este alumno']);
        exit;
    }
    
    // Delete the student's image if it's not the default
    if (!empty($alumno['imagen']) && $alumno['imagen'] != 'user.png') {
        $image_path = '../../img/alumnos/' . $alumno['imagen'];
        if (file_exists($image_path)) {
            unlink($image_path);
        }
    }
    
    // Delete from alumnos_grupos
    $stmt = $conn->prepare("DELETE FROM alumnos_grupos WHERE alumno_id = :alumno_id");
    $stmt->bindParam(':alumno_id', $id);
    $stmt->execute();
    
    // Instead of physically deleting, mark as inactive
    $stmt = $conn->prepare("UPDATE alumnos SET activo = 0 WHERE id = :id AND usuario_id = :usuario_id");
    $stmt->bindParam(':id', $id);
    $stmt->bindParam(':usuario_id', $usuario_id);
    $stmt->execute();
    
    // Check if any row was affected
    if ($stmt->rowCount() > 0) {
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => 'Alumno eliminado correctamente']);
    } else {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'No se encontró el alumno o no tienes permisos para eliminarlo']);
    }
    
} catch (PDOException $e) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Error al eliminar el alumno: ' . $e->getMessage()]);
}
?>