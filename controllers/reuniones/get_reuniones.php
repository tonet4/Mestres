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
    // Get all meetings for the current user, ordered by date (newest first)
    $stmt = $conn->prepare("
        SELECT id, titulo, fecha, hora, contenido, fecha_creacion, fecha_actualizacion
        FROM reuniones
        WHERE usuario_id = :usuario_id
        ORDER BY fecha DESC, hora DESC
    ");
    
    $stmt->bindParam(':usuario_id', $usuario_id);
    $stmt->execute();
    
    $reuniones = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Format dates for display
    foreach ($reuniones as &$reunion) {
        $reunion['fecha_formateada'] = date('d/m/Y', strtotime($reunion['fecha']));
        $reunion['hora_formateada'] = $reunion['hora'] ? date('H:i', strtotime($reunion['hora'])) : '';
        $reunion['fecha_creacion_formateada'] = date('d/m/Y H:i', strtotime($reunion['fecha_creacion']));
    }
    
    // Debug
    error_log('Reuniones recuperadas: ' . json_encode($reuniones));
    
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'reuniones' => $reuniones]);
    
} catch (PDOException $e) {
    error_log('Error en get_reuniones.php: ' . $e->getMessage());
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Error al obtener las reuniones: ' . $e->getMessage()]);
}
?>