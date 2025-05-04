<?php
/**
 * @author Antonio Esteban Lorenzo
 * 
 */
// Include the necessary files
require_once '../../includes/auth.php';
require_once '../../api/config.php';
require_once '../../includes/utils.php';

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

// Get form data
$prev_week = isset($_POST['prev_week']) ? (int)$_POST['prev_week'] : null;
$prev_year = isset($_POST['prev_year']) ? (int)$_POST['prev_year'] : null;
$current_week = isset($_POST['current_week']) ? (int)$_POST['current_week'] : null;
$current_year = isset($_POST['current_year']) ? (int)$_POST['current_year'] : null;
$usuario_id = $_SESSION['user_id'];

// Validate data
if (!$prev_week || !$prev_year || !$current_week || !$current_year) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Faltan datos requeridos']);
    exit;
}

try {
    // Start transaction
    $conn->beginTransaction();
    
    // Check if previous week has hours
    $stmt = $conn->prepare("
        SELECT COUNT(*) as count
        FROM horas_calendario
        WHERE usuario_id = :usuario_id 
        AND semana_numero = :semana 
        AND anio = :anio
    ");
    
    $stmt->bindParam(':usuario_id', $usuario_id);
    $stmt->bindParam(':semana', $prev_week);
    $stmt->bindParam(':anio', $prev_year);
    $stmt->execute();
    
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result['count'] === 0) {
        throw new Exception('No hay horas definidas en la semana anterior para copiar.');
    }
    
    // Delete current week hours first
    $stmt = $conn->prepare("
        DELETE FROM horas_calendario
        WHERE usuario_id = :usuario_id 
        AND semana_numero = :semana 
        AND anio = :anio
    ");
    
    $stmt->bindParam(':usuario_id', $usuario_id);
    $stmt->bindParam(':semana', $current_week);
    $stmt->bindParam(':anio', $current_year);
    $stmt->execute();
    
    // Also delete events in current week (optional, depends on requirements)
    $stmt = $conn->prepare("
        DELETE FROM eventos_calendario
        WHERE usuario_id = :usuario_id 
        AND semana_numero = :semana 
        AND anio = :anio
    ");
    
    $stmt->bindParam(':usuario_id', $usuario_id);
    $stmt->bindParam(':semana', $current_week);
    $stmt->bindParam(':anio', $current_year);
    $stmt->execute();
    
    // Copy hours from previous week to current week
    $stmt = $conn->prepare("
        INSERT INTO horas_calendario (usuario_id, semana_numero, anio, hora, orden)
        SELECT usuario_id, :new_semana, :new_anio, hora, orden
        FROM horas_calendario
        WHERE usuario_id = :usuario_id 
        AND semana_numero = :old_semana 
        AND anio = :old_anio
        ORDER BY orden ASC
    ");
    
    $stmt->bindParam(':usuario_id', $usuario_id);
    $stmt->bindParam(':old_semana', $prev_week);
    $stmt->bindParam(':old_anio', $prev_year);
    $stmt->bindParam(':new_semana', $current_week);
    $stmt->bindParam(':new_anio', $current_year);
    $stmt->execute();
    
    // Confirm transaction
    $conn->commit();
    
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'message' => 'Horario copiado correctamente.']);
    
} catch (Exception $e) {
    // Roll back transaction in case of error
    $conn->rollBack();
    
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}