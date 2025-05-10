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

// Get user ID
$usuario_id = $_SESSION['user_id'];

try {
    // Get all groups for the current user
    $stmt = $conn->prepare("
        SELECT id, nombre, descripcion, curso_academico
        FROM grupos
        WHERE usuario_id = :usuario_id AND activo = 1
        ORDER BY nombre ASC
    ");
    
    $stmt->bindParam(':usuario_id', $usuario_id);
    $stmt->execute();
    
    $grupos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'grupos' => $grupos]);
    
} catch (PDOException $e) {
    error_log('Error en get_grupos.php: ' . $e->getMessage());
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Error al obtener los grupos: ' . $e->getMessage()]);
}
?>