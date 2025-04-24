<?php
/**
 * @author Antonio Esteban Lorenzo
 * 
 */
// Include the necessary files
require_once '../includes/auth.php';
require_once '../api/config.php';

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

// Get data
$hour_id = isset($_POST['hour_id']) ? (int)$_POST['hour_id'] : null;
$week = isset($_POST['week']) ? (int)$_POST['week'] : null;
$year = isset($_POST['year']) ? (int)$_POST['year'] : null;
$usuario_id = $_SESSION['user_id'];

// Validate data
if (!$hour_id || !$week || !$year) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Faltan datos requeridos']);
    exit;
}

try {
    // Start transaction
    $conn->beginTransaction();
    
    // Verify that the time belongs to the user
    $stmt = $conn->prepare("
        SELECT id, orden
        FROM horas_calendario
        WHERE id = :id AND usuario_id = :usuario_id AND semana_numero = :semana AND anio = :anio
        LIMIT 1
    ");
    
    $stmt->bindParam(':id', $hour_id);
    $stmt->bindParam(':usuario_id', $usuario_id);
    $stmt->bindParam(':semana', $week);
    $stmt->bindParam(':anio', $year);
    $stmt->execute();
    
    $hora = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$hora) {
        throw new Exception('No tienes permiso para eliminar esta hora');
    }
    
    // Delete events associated with this time
    $stmt = $conn->prepare("
        DELETE FROM eventos_calendario
        WHERE hora_id = :hora_id AND usuario_id = :usuario_id
    ");
    
    $stmt->bindParam(':hora_id', $hour_id);
    $stmt->bindParam(':usuario_id', $usuario_id);
    $stmt->execute();
    
    // Delete time
    $stmt = $conn->prepare("
        DELETE FROM horas_calendario
        WHERE id = :id AND usuario_id = :usuario_id
    ");
    
    $stmt->bindParam(':id', $hour_id);
    $stmt->bindParam(':usuario_id', $usuario_id);
    $stmt->execute();
    
    // Reorder the remaining hours
    $stmt = $conn->prepare("
        UPDATE horas_calendario
        SET orden = orden - 1
        WHERE usuario_id = :usuario_id 
        AND semana_numero = :semana 
        AND anio = :anio 
        AND orden > :orden
    ");
    
    $stmt->bindParam(':usuario_id', $usuario_id);
    $stmt->bindParam(':semana', $week);
    $stmt->bindParam(':anio', $year);
    $stmt->bindParam(':orden', $hora['orden']);
    $stmt->execute();
    
    // Confirm transaction
    $conn->commit();
    
    header('Content-Type: application/json');
    echo json_encode(['success' => true]);
    
} catch (Exception $e) {
    //Roll back transaction in case of error
    $conn->rollBack();
    
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}