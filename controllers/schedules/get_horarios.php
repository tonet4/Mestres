<?php
/**
 * Get all schedules for the authenticated user
 * 
 * @author Antonio Esteban Lorenzo
 */

// Establece el tipo de contenido JSON
header('Content-Type: application/json');

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

$usuario_id = $_SESSION['user_id'];

try {
    // Query to get all schedules for the user
    $query = "SELECT id, nombre, descripcion, dias_semana, 
                     es_predeterminado, activo, fecha_creacion, fecha_actualizacion 
              FROM horarios 
              WHERE usuario_id = :usuario_id 
              ORDER BY fecha_actualizacion DESC";
    
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':usuario_id', $usuario_id);
    $stmt->execute();
    
    $horarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Transform the days_of_week number to a more readable format
    foreach ($horarios as &$horario) {
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
    }
    
    echo json_encode([
        'status' => 'success',
        'horarios' => $horarios
    ]);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}