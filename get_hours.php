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

// Obtener parámetros
$week = isset($_GET['week']) ? (int)$_GET['week'] : null;
$year = isset($_GET['year']) ? (int)$_GET['year'] : null;
$usuario_id = $_SESSION['user_id'];

// Validar parámetros
if (!$week || !$year) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Parámetros inválidos']);
    exit;
}

try {
    // Obtener horas del calendario para la semana y año especificados
    $stmt = $conn->prepare("
        SELECT id, hora, orden
        FROM horas_calendario
        WHERE usuario_id = :usuario_id AND semana_numero = :semana AND anio = :anio
        ORDER BY orden ASC
    ");
    
    $stmt->bindParam(':usuario_id', $usuario_id);
    $stmt->bindParam(':semana', $week);
    $stmt->bindParam(':anio', $year);
    $stmt->execute();
    
    $hours = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'hours' => $hours]);
    
} catch (PDOException $e) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Error al obtener las horas: ' . $e->getMessage()]);
}