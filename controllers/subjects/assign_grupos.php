<?php
/**
 * @author Antonio Esteban Lorenzo
 * 
 */
// Include the necessary files
require_once '../../includes/auth.php';
require_once '../../api/config.php';

// Verify that the user is authenticated
if (!is_logged_in()) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

// Check if the request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

// Get user ID
$usuario_id = $_SESSION['user_id'];

// Get and validate data
$asignatura_id = isset($_POST['asignatura_id']) ? (int)$_POST['asignatura_id'] : 0;
$grupos_ids = isset($_POST['grupos_ids']) ? $_POST['grupos_ids'] : [];

// Validate asignatura_id
if ($asignatura_id <= 0) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'ID de asignatura inválido']);
    exit;
}

// Validate grupos_ids format
if (!is_array($grupos_ids)) {
    // Try to decode if it's a JSON string
    $grupos_ids = json_decode($grupos_ids, true);
    
    if (!is_array($grupos_ids)) {
        $grupos_ids = [];
    }
}

try {
    // First check if the subject belongs to this user
    $stmt = $conn->prepare("SELECT id FROM asignaturas WHERE id = :id AND usuario_id = :usuario_id");
    $stmt->bindParam(':id', $asignatura_id);
    $stmt->bindParam(':usuario_id', $usuario_id);
    $stmt->execute();
    
    if (!$stmt->fetch(PDO::FETCH_ASSOC)) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'No tienes permiso para modificar esta asignatura']);
        exit;
    }
    
    // Start transaction
    $conn->beginTransaction();
    
    // Delete existing assignments
    $stmt = $conn->prepare("DELETE FROM grupos_asignaturas WHERE asignatura_id = :asignatura_id");
    $stmt->bindParam(':asignatura_id', $asignatura_id);
    $stmt->execute();
    
    // Add new assignments
    if (!empty($grupos_ids)) {
        $stmt = $conn->prepare("
            INSERT INTO grupos_asignaturas (grupo_id, asignatura_id)
            VALUES (:grupo_id, :asignatura_id)
        ");
        
        $stmt->bindParam(':asignatura_id', $asignatura_id);
        
        foreach ($grupos_ids as $grupo_id) {
            // Verify this group belongs to the user
            $checkStmt = $conn->prepare("SELECT id FROM grupos WHERE id = :id AND usuario_id = :usuario_id AND activo = 1");
            $checkStmt->bindParam(':id', $grupo_id);
            $checkStmt->bindParam(':usuario_id', $usuario_id);
            $checkStmt->execute();
            
            if ($checkStmt->fetch(PDO::FETCH_ASSOC)) {
                $stmt->bindParam(':grupo_id', $grupo_id);
                $stmt->execute();
            }
        }
    }
    
    // Commit transaction
    $conn->commit();
    
    // Get the updated groups for this subject
    $stmt = $conn->prepare("
        SELECT g.id, g.nombre, g.curso_academico
        FROM grupos g
        JOIN grupos_asignaturas ga ON g.id = ga.grupo_id
        WHERE ga.asignatura_id = :asignatura_id AND g.activo = 1
        ORDER BY g.nombre ASC
    ");
    
    $stmt->bindParam(':asignatura_id', $asignatura_id);
    $stmt->execute();
    
    $grupos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true, 
        'message' => 'Grupos asignados correctamente', 
        'grupos' => $grupos
    ]);
    
} catch (PDOException $e) {
    // Rollback in case of error
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Error al asignar grupos: ' . $e->getMessage()]);
}
?>