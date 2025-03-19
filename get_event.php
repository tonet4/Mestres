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

// Obtener id del evento
$event_id = isset($_GET['id']) ? (int)$_GET['id'] : null;
$usuario_id = $_SESSION['user_id'];

// Validar parámetros
if (!$event_id) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Parámetros inválidos']);
    exit;
}

try {
    // Obtener detalles del evento
    $stmt = $conn->prepare("
        SELECT id, dia_semana, hora_id, titulo, descripcion, color, semana_numero, anio
        FROM eventos_calendario
        WHERE id = :id AND usuario_id = :usuario_id
        LIMIT 1
    ");
    
    $stmt->bindParam(':id', $event_id);
    $stmt->bindParam(':usuario_id', $usuario_id);
    $stmt->execute();
    
    $event = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$event) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Evento no encontrado o no tienes permiso para verlo']);
        exit;
    }
    
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'event' => $event]);
    
} catch (PDOException $e) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Error al obtener el evento: ' . $e->getMessage()]);
}