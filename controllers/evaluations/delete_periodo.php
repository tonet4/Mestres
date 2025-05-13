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

// Get period ID
$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

// Validate period ID
if ($id <= 0) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'ID de período inválido']);
    exit;
}

try {
    $conn->beginTransaction();
    
    // First check if the period belongs to this user
    $stmt = $conn->prepare("
        SELECT id FROM periodos_evaluacion 
        WHERE id = :id AND usuario_id = :usuario_id
    ");
    
    $stmt->bindParam(':id', $id);
    $stmt->bindParam(':usuario_id', $usuario_id);
    $stmt->execute();
    
    if ($stmt->rowCount() === 0) {
        throw new Exception('No tienes permiso para eliminar este período');
    }
    
    // Check if there are evaluations for this period
    $stmt = $conn->prepare("
        SELECT COUNT(*) as total FROM evaluaciones
        WHERE periodo_id = :periodo_id AND usuario_id = :usuario_id AND activo = 1
    ");
    
    $stmt->bindParam(':periodo_id', $id);
    $stmt->bindParam(':usuario_id', $usuario_id);
    $stmt->execute();
    
    $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($resultado['total'] > 0) {
        throw new Exception('No se puede eliminar este período porque tiene evaluaciones asociadas');
    }
    
    // Delete final grades for this period
    $stmt = $conn->prepare("
        DELETE FROM notas_finales
        WHERE periodo_id = :periodo_id
    ");
    
    $stmt->bindParam(':periodo_id', $id);
    $stmt->execute();
    
    // Then delete the period (or mark as inactive)
    $stmt = $conn->prepare("
        UPDATE periodos_evaluacion
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
        'message' => 'Período eliminado correctamente'
    ]);
    
} catch (Exception $e) {
    $conn->rollBack();
    error_log('Error en delete_periodo.php: ' . $e->getMessage());
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Error al eliminar el período: ' . $e->getMessage()]);
}
?>