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

// Verificar que sea una petición POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

// Obtener datos
$hour_id = isset($_POST['hour_id']) ? (int)$_POST['hour_id'] : null;
$week = isset($_POST['week']) ? (int)$_POST['week'] : null;
$year = isset($_POST['year']) ? (int)$_POST['year'] : null;
$usuario_id = $_SESSION['user_id'];

// Validar datos
if (!$hour_id || !$week || !$year) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Faltan datos requeridos']);
    exit;
}

try {
    // Iniciar transacción
    $conn->beginTransaction();
    
    // Verificar que la hora pertenece al usuario
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
    
    // Eliminar eventos asociados a esta hora
    $stmt = $conn->prepare("
        DELETE FROM eventos_calendario
        WHERE hora_id = :hora_id AND usuario_id = :usuario_id
    ");
    
    $stmt->bindParam(':hora_id', $hour_id);
    $stmt->bindParam(':usuario_id', $usuario_id);
    $stmt->execute();
    
    // Eliminar hora
    $stmt = $conn->prepare("
        DELETE FROM horas_calendario
        WHERE id = :id AND usuario_id = :usuario_id
    ");
    
    $stmt->bindParam(':id', $hour_id);
    $stmt->bindParam(':usuario_id', $usuario_id);
    $stmt->execute();
    
    // Reordenar las horas restantes
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
    
    // Confirmar transacción
    $conn->commit();
    
    header('Content-Type: application/json');
    echo json_encode(['success' => true]);
    
} catch (Exception $e) {
    // Revertir transacción en caso de error
    $conn->rollBack();
    
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}