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

// Get meeting ID
$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

// Validate meeting ID
if ($id <= 0) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'ID de reunión inválido']);
    exit;
}

try {
    // NUEVA FUNCIONALIDAD: Eliminar eventos asociados del calendario mensual
    $stmt = $conn->prepare("
        DELETE FROM eventos_calendario_anual
        WHERE usuario_id = :usuario_id 
        AND descripcion LIKE :descripcion
    ");
    
    $descripcion_pattern = "%[REUNION_ID:" . $id . "]%";
    $stmt->bindParam(':usuario_id', $usuario_id);
    $stmt->bindParam(':descripcion', $descripcion_pattern);
    $stmt->execute();
    
    // Delete the meeting
    $stmt = $conn->prepare("
        DELETE FROM reuniones
        WHERE id = :id AND usuario_id = :usuario_id
    ");
    
    $stmt->bindParam(':id', $id);
    $stmt->bindParam(':usuario_id', $usuario_id);
    $stmt->execute();
    
    // Check if any row was affected
    if ($stmt->rowCount() > 0) {
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => 'Reunión eliminada correctamente']);
    } else {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'No se encontró la reunión o no tienes permisos para eliminarla']);
    }
    
} catch (PDOException $e) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Error al eliminar la reunión: ' . $e->getMessage()]);
}
?>