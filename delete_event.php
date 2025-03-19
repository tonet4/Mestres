<?php
// Incluir los archivos necesarios
require_once 'includes/auth.php';
require_once 'config.php';

// Verificar que el usuario esté autenticado
if (!is_logged_in()) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

// Verificar que sea una petición POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

// Obtener datos
$event_id = isset($_POST['event_id']) ? (int)$_POST['event_id'] : null;
$usuario_id = $_SESSION['user_id'];

// Validar datos
if (!$event_id) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Falta el ID del evento']);
    exit;
}

try {
    // Verificar que el evento pertenece al usuario
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
    
    // Eliminar evento
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