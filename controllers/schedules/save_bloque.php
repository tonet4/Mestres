<?php
/**
 * Save a new schedule block or update an existing one
 * 
 * @author Antonio Esteban Lorenzo
 */

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
$bloque_id = isset($input['id']) ? (int)$input['id'] : null;
$horario_id = isset($input['horario_id']) ? (int)$input['horario_id'] : null;
$dia_semana = isset($input['dia_semana']) ? (int)$input['dia_semana'] : null;
$hora_inicio = $input['hora_inicio'] ?? null;
$hora_fin = $input['hora_fin'] ?? null;
$titulo = limpiarDatos($input['titulo'] ?? '');
$descripcion = limpiarDatos($input['descripcion'] ?? '');
$color = limpiarDatos($input['color'] ?? '#3498db');

// Validate required fields
if (!$horario_id || !$dia_semana || !$hora_inicio || !$hora_fin || empty($titulo)) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing required fields']);
    exit;
}

// Validate day of the week (1-7)
if ($dia_semana < 1 || $dia_semana > 7) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid day of the week']);
    exit;
}

try {
    // Begin transaction
    $conn->beginTransaction();
    
    // First, verify that the schedule belongs to the user
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
    
    if ($bloque_id) {
        // Update existing block
        $stmt = $conn->prepare("UPDATE horarios_bloques 
                                SET dia_semana = :dia_semana, 
                                    hora_inicio = :hora_inicio, 
                                    hora_fin = :hora_fin, 
                                    titulo = :titulo, 
                                    descripcion = :descripcion, 
                                    color = :color
                                WHERE id = :id AND horario_id = :horario_id");
        $stmt->bindParam(':id', $bloque_id);
        $stmt->bindParam(':horario_id', $horario_id);
        $stmt->bindParam(':dia_semana', $dia_semana);
        $stmt->bindParam(':hora_inicio', $hora_inicio);
        $stmt->bindParam(':hora_fin', $hora_fin);
        $stmt->bindParam(':titulo', $titulo);
        $stmt->bindParam(':descripcion', $descripcion);
        $stmt->bindParam(':color', $color);
        $stmt->execute();
        
        $message = 'Block updated successfully';
    } else {
        // Create new block
        $stmt = $conn->prepare("INSERT INTO horarios_bloques 
                               (horario_id, dia_semana, hora_inicio, hora_fin, titulo, descripcion, color) 
                               VALUES 
                               (:horario_id, :dia_semana, :hora_inicio, :hora_fin, :titulo, :descripcion, :color)");
        $stmt->bindParam(':horario_id', $horario_id);
        $stmt->bindParam(':dia_semana', $dia_semana);
        $stmt->bindParam(':hora_inicio', $hora_inicio);
        $stmt->bindParam(':hora_fin', $hora_fin);
        $stmt->bindParam(':titulo', $titulo);
        $stmt->bindParam(':descripcion', $descripcion);
        $stmt->bindParam(':color', $color);
        $stmt->execute();
        
        $bloque_id = $conn->lastInsertId();
        $message = 'Block created successfully';
    }
    
    // Commit transaction
    $conn->commit();
    
    echo json_encode([
        'status' => 'success',
        'message' => $message,
        'bloque_id' => $bloque_id
    ]);
    
} catch (PDOException $e) {
    $conn->rollBack();
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}