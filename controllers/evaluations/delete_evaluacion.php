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

// Get evaluation ID
$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

// Validate evaluation ID
if ($id <= 0) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'ID de evaluación inválido']);
    exit;
}

try {
    $conn->beginTransaction();
    
    // First check if the evaluation belongs to this user
    $stmt = $conn->prepare("
        SELECT id FROM evaluaciones 
        WHERE id = :id AND usuario_id = :usuario_id
    ");
    
    $stmt->bindParam(':id', $id);
    $stmt->bindParam(':usuario_id', $usuario_id);
    $stmt->execute();
    
    if ($stmt->rowCount() === 0) {
        throw new Exception('No tienes permiso para eliminar esta evaluación');
    }
    
    // Delete related grades
    $stmt = $conn->prepare("
        DELETE FROM calificaciones
        WHERE evaluacion_id = :evaluacion_id
    ");
    
    $stmt->bindParam(':evaluacion_id', $id);
    $stmt->execute();
    
    // Then delete the evaluation (or mark as inactive)
    $stmt = $conn->prepare("
        UPDATE evaluaciones
        SET activo = 0
        WHERE id = :id AND usuario_id = :usuario_id
    ");
    
    $stmt->bindParam(':id', $id);
    $stmt->bindParam(':usuario_id', $usuario_id);
    $stmt->execute();
    
    $conn->commit();
    
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true, 
        'message' => 'Evaluación eliminada correctamente'
    ]);
    
} catch (Exception $e) {
    $conn->rollBack();
    error_log('Error en delete_evaluacion.php: ' . $e->getMessage());
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Error al eliminar la evaluación: ' . $e->getMessage()]);
}
?>