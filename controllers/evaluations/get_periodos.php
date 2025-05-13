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
    // Get evaluation periods
    $stmt = $conn->prepare("
        SELECT * FROM periodos_evaluacion 
        WHERE usuario_id = :usuario_id AND activo = 1
        ORDER BY fecha_inicio DESC
    ");
    
    $stmt->bindParam(':usuario_id', $usuario_id);
    $stmt->execute();
    
    $periodos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'periodos' => $periodos]);
    
} catch (PDOException $e) {
    error_log('Error en get_periodos.php: ' . $e->getMessage());
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Error al obtener los períodos: ' . $e->getMessage()]);
}
?>