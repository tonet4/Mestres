<?php
/**
 * Get all blocks for a specific schedule
 * 
 * @author Antonio Esteban Lorenzo
 */

// Include necessary configuration
require_once '../../includes/auth.php';
require_once '../../api/config.php';

// Check if user is authenticated
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'No authenticated user']);
    exit;
}

// Get the schedule ID from the query parameters
$horario_id = isset($_GET['horario_id']) ? (int)$_GET['horario_id'] : null;

if (!$horario_id) {
    http_response_code(400);
    echo json_encode(['error' => 'Schedule ID is required']);
    exit;
}

$usuario_id = $_SESSION['user_id'];

try {
    // First, verify that the schedule belongs to the user
    $stmt = $conn->prepare("SELECT id FROM horarios WHERE id = :id AND usuario_id = :usuario_id");
    $stmt->bindParam(':id', $horario_id);
    $stmt->bindParam(':usuario_id', $usuario_id);
    $stmt->execute();
    
    if (!$stmt->fetch()) {
        http_response_code(403);
        echo json_encode(['error' => 'Schedule not found or not authorized']);
        exit;
    }
    
    // Get all blocks for the schedule
    $stmt = $conn->prepare("SELECT id, horario_id, dia_semana, hora_inicio, hora_fin, 
                                   titulo, descripcion, color, fecha_creacion
                            FROM horarios_bloques 
                            WHERE horario_id = :horario_id 
                            ORDER BY dia_semana, hora_inicio");
    $stmt->bindParam(':horario_id', $horario_id);
    $stmt->execute();
    
    $bloques = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Group blocks by day of the week
    $bloquesPorDia = [
        1 => [], // Monday
        2 => [], // Tuesday
        3 => [], // Wednesday
        4 => [], // Thursday
        5 => [], // Friday
        6 => [], // Saturday
        7 => []  // Sunday
    ];
    
    foreach ($bloques as $bloque) {
        $dia = (int)$bloque['dia_semana'];
        if (isset($bloquesPorDia[$dia])) {
            $bloquesPorDia[$dia][] = $bloque;
        }
    }
    
    echo json_encode([
        'status' => 'success',
        'bloques' => $bloques,
        'bloquesPorDia' => $bloquesPorDia
    ]);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}