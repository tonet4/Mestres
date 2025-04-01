<?php
/**
 * @author Antonio Esteban Lorenzo
 * 
 */
// Include the necessary files
require_once 'includes/auth.php';
require_once 'config.php';

// Verify that the user is authenticated
if (!is_logged_in()) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

// Get time id
$hour_id = isset($_GET['id']) ? (int)$_GET['id'] : null;
$usuario_id = $_SESSION['user_id'];

// Validate parameters
if (!$hour_id) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Parámetros inválidos']);
    exit;
}

try {
    // Get time details
    $stmt = $conn->prepare("
        SELECT id, hora, semana_numero, anio, orden
        FROM horas_calendario
        WHERE id = :id AND usuario_id = :usuario_id
        LIMIT 1
    ");
    
    $stmt->bindParam(':id', $hour_id);
    $stmt->bindParam(':usuario_id', $usuario_id);
    $stmt->execute();
    
    $hour = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$hour) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Hora no encontrada o no tienes permiso para verla']);
        exit;
    }
    
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'hour' => $hour]);
    
} catch (PDOException $e) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Error al obtener la hora: ' . $e->getMessage()]);
}