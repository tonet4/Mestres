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

// Get group ID
$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

// Validate group ID
if ($id <= 0) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'ID de grupo inválido']);
    exit;
}

try {
    // First check if the group belongs to this user
    $stmt = $conn->prepare("SELECT id FROM grupos WHERE id = :id AND usuario_id = :usuario_id");
    $stmt->bindParam(':id', $id);
    $stmt->bindParam(':usuario_id', $usuario_id);
    $stmt->execute();
    
    if ($stmt->rowCount() == 0) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'No tienes permiso para eliminar este grupo']);
        exit;
    }
    
    // Start transaction
    $conn->beginTransaction();
    
    // Delete from alumnos_grupos
    $stmt = $conn->prepare("DELETE FROM alumnos_grupos WHERE grupo_id = :grupo_id");
    $stmt->bindParam(':grupo_id', $id);
    $stmt->execute();
    
    // Delete the group
    $stmt = $conn->prepare("UPDATE grupos SET activo = 0 WHERE id = :id AND usuario_id = :usuario_id");
    $stmt->bindParam(':id', $id);
    $stmt->bindParam(':usuario_id', $usuario_id);
    $stmt->execute();
    
    // Commit transaction
    $conn->commit();
    
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'message' => 'Grupo eliminado correctamente']);
    
} catch (PDOException $e) {
    // Rollback in case of error
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Error al eliminar el grupo: ' . $e->getMessage()]);
}
?>