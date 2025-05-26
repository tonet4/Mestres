<?php
/**
 * Get a specific schedule by ID
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
$horario_id = isset($_GET['id']) ? (int)$_GET['id'] : null;

if (!$horario_id) {
    http_response_code(400);
    echo json_encode(['error' => 'Schedule ID is required']);
    exit;
}

$usuario_id = $_SESSION['user_id'];

try {
    // Verify that the schedule belongs to the user and get its details
    $stmt = $conn->prepare("SELECT id, nombre, descripcion, dias_semana, 
                                  es_predeterminado, activo, fecha_creacion, fecha_actualizacion 
                           FROM horarios 
                           WHERE id = :id AND usuario_id = :usuario_id");
    $stmt->bindParam(':id', $horario_id);
    $stmt->bindParam(':usuario_id', $usuario_id);
    $stmt->execute();
    
    $horario = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$horario) {
        http_response_code(404);
        echo json_encode(['error' => 'Schedule not found or not authorized']);
        exit;
    }
    
    // Transform the days_of_week number to a more readable format
    switch ($horario['dias_semana']) {
        case 5:
            $horario['dias_texto'] = 'Lunes a Viernes';
            break;
        case 6:
            $horario['dias_texto'] = 'Lunes a Sábado';
            break;
        case 7:
            $horario['dias_texto'] = 'Lunes a Domingo';
            break;
        default:
            $horario['dias_texto'] = 'Personalizado';
    }
    
    echo json_encode([
        'status' => 'success',
        'horario' => $horario
    ]);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}