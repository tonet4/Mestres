<?php
/**
 * Delete a schedule
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

// Check if the request is a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Get JSON body from the request
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid request data']);
    exit;
}

$usuario_id = $_SESSION['user_id'];
$horario_id = (int)$input['id'];

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
    
    // Delete the schedule (cascade will delete blocks)
    $stmt = $conn->prepare("DELETE FROM horarios WHERE id = :id");
    $stmt->bindParam(':id', $horario_id);
    $stmt->execute();
    
    // Commit transaction
    $conn->commit();
    
    echo json_encode([
        'status' => 'success',
        'message' => 'Schedule deleted successfully'
    ]);
    
} catch (PDOException $e) {
    $conn->rollBack();
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}