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

// Get subject ID
$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

// Validate subject ID
if ($id <= 0) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'ID de asignatura inválido']);
    exit;
}

try {
    // First check if the subject belongs to this user
    $stmt = $conn->prepare("SELECT id FROM asignaturas WHERE id = :id AND usuario_id = :usuario_id");
    $stmt->bindParam(':id', $id);
    $stmt->bindParam(':usuario_id', $usuario_id);
    $stmt->execute();
    
    if (!$stmt->fetch(PDO::FETCH_ASSOC)) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'No tienes permiso para eliminar esta asignatura']);
        exit;
    }
    
    // Start transaction
    $conn->beginTransaction();
    
    // Delete from grupos_asignaturas
    $stmt = $conn->prepare("DELETE FROM grupos_asignaturas WHERE asignatura_id = :asignatura_id");
    $stmt->bindParam(':asignatura_id', $id);
    $stmt->execute();
    
    // Mark the subject as inactive instead of physically deleting
    $stmt = $conn->prepare("UPDATE asignaturas SET activo = 0 WHERE id = :id AND usuario_id = :usuario_id");
    $stmt->bindParam(':id', $id);
    $stmt->bindParam(':usuario_id', $usuario_id);
    $stmt->execute();
    
    // Commit transaction
    $conn->commit();
    
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'message' => 'Asignatura eliminada correctamente']);
    
} catch (PDOException $e) {
    // Rollback in case of error
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Error al eliminar la asignatura: ' . $e->getMessage()]);
}
?>