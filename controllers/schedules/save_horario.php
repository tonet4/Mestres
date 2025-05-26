<?php
/**
 * Save a new schedule or update an existing one
 * 
 * @author Antonio Esteban Lorenzo
 */

// Establece el tipo de contenido JSON
header('Content-Type: application/json');

// Include necessary configuration
require_once '../../includes/auth.php';
require_once '../../includes/utils.php';
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

// Check if the request is a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Get JSON body from the request
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid JSON data']);
    exit;
}

$usuario_id = $_SESSION['user_id'];
$horario_id = isset($input['id']) ? (int)$input['id'] : null;
$nombre = limpiarDatos($input['nombre'] ?? '');
$descripcion = limpiarDatos($input['descripcion'] ?? '');
$dias_semana = isset($input['dias_semana']) ? (int)$input['dias_semana'] : 5;
$activo = isset($input['activo']) ? (int)$input['activo'] : 1;

// Validate required fields
if (empty($nombre)) {
    http_response_code(400);
    echo json_encode(['error' => 'Schedule name is required']);
    exit;
}

try {
    // Begin transaction
    $conn->beginTransaction();
    
    if ($horario_id) {
        // Check if the schedule belongs to the user
        $stmt = $conn->prepare("SELECT id FROM horarios WHERE id = :id AND usuario_id = :usuario_id");
        $stmt->bindParam(':id', $horario_id);
        $stmt->bindParam(':usuario_id', $usuario_id);
        $stmt->execute();
        
        if (!$stmt->fetch()) {
            $conn->rollBack();
            http_response_code(403);
            echo json_encode(['error' => 'Schedule not found or not authorized']);
            exit;
        }
        
        // Update existing schedule
        $stmt = $conn->prepare("UPDATE horarios 
                                SET nombre = :nombre, 
                                    descripcion = :descripcion, 
                                    dias_semana = :dias_semana, 
                                    activo = :activo
                                WHERE id = :id");
        $stmt->bindParam(':id', $horario_id);
        $stmt->bindParam(':nombre', $nombre);
        $stmt->bindParam(':descripcion', $descripcion);
        $stmt->bindParam(':dias_semana', $dias_semana);
        $stmt->bindParam(':activo', $activo);
        $stmt->execute();
        
        $message = 'Schedule updated successfully';
    } else {
        // Create new schedule
        $stmt = $conn->prepare("INSERT INTO horarios 
                               (usuario_id, nombre, descripcion, dias_semana, activo) 
                               VALUES 
                               (:usuario_id, :nombre, :descripcion, :dias_semana, :activo)");
        $stmt->bindParam(':usuario_id', $usuario_id);
        $stmt->bindParam(':nombre', $nombre);
        $stmt->bindParam(':descripcion', $descripcion);
        $stmt->bindParam(':dias_semana', $dias_semana);
        $stmt->bindParam(':activo', $activo);
        $stmt->execute();
        
        $horario_id = $conn->lastInsertId();
        $message = 'Schedule created successfully';
    }
    
    // Commit transaction
    $conn->commit();
    
    echo json_encode([
        'status' => 'success',
        'message' => $message,
        'horario_id' => $horario_id
    ]);
    
} catch (PDOException $e) {
    $conn->rollBack();
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}