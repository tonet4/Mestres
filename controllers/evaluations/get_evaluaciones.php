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

// Get filter parameters
$asignatura_id = isset($_GET['asignatura_id']) ? (int)$_GET['asignatura_id'] : 0;
$periodo_id = isset($_GET['periodo_id']) ? (int)$_GET['periodo_id'] : 0;
$grupo_id = isset($_GET['grupo_id']) ? (int)$_GET['grupo_id'] : 0; // Nuevo parámetro

// Check for required parameters
if ($asignatura_id <= 0 || $periodo_id <= 0 || $grupo_id <= 0) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Faltan parámetros requeridos']);
    exit;
}

try {
    // Get evaluations based on filters
    $stmt = $conn->prepare("
        SELECT * FROM evaluaciones 
        WHERE usuario_id = :usuario_id 
        AND asignatura_id = :asignatura_id 
        AND periodo_id = :periodo_id
        AND grupo_id = :grupo_id
        AND activo = 1
        ORDER BY fecha ASC, nombre ASC
    ");
    
    $stmt->bindParam(':usuario_id', $usuario_id);
    $stmt->bindParam(':asignatura_id', $asignatura_id);
    $stmt->bindParam(':periodo_id', $periodo_id);
    $stmt->bindParam(':grupo_id', $grupo_id);
    $stmt->execute();
    
    $evaluaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'evaluaciones' => $evaluaciones]);
    
} catch (PDOException $e) {
    error_log('Error en get_evaluaciones.php: ' . $e->getMessage());
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Error al obtener las evaluaciones: ' . $e->getMessage()]);
}
?>