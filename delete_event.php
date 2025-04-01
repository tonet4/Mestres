<?php
/**
 * @author Antonio Esteban Lorenzo
 * 
 */

// Include the necessary file
require_once 'includes/auth.php';
require_once 'config.php';

// Verify that the user is authenticated
if (!is_logged_in()) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

// Verify that it is a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

// Get data
$event_id = isset($_POST['event_id']) ? (int)$_POST['event_id'] : null;
$usuario_id = $_SESSION['user_id'];

// Validate data
if (!$event_id) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Falta el ID del evento']);
    exit;
}

try {
    // Verify that the event belongs to the user
    $stmt = $conn->prepare("
        SELECT id
        FROM eventos_calendario
        WHERE id = :id AND usuario_id = :usuario_id
        LIMIT 1
    ");
    
    $stmt->bindParam(':id', $event_id);
    $stmt->bindParam(':usuario_id', $usuario_id);
    $stmt->execute();
    
    if (!$stmt->fetch(PDO::FETCH_ASSOC)) {
        throw new Exception('No tienes permiso para eliminar este evento');
    }
    
    // Delete event
    $stmt = $conn->prepare("
        DELETE FROM eventos_calendario
        WHERE id = :id AND usuario_id = :usuario_id
    ");
    
    $stmt->bindParam(':id', $event_id);
    $stmt->bindParam(':usuario_id', $usuario_id);
    $stmt->execute();
    
    header('Content-Type: application/json');
    echo json_encode(['success' => true]);
    
} catch (Exception $e) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}