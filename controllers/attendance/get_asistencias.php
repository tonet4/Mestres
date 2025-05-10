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

// Check if the request is POST or GET
if ($_SERVER['REQUEST_METHOD'] !== 'POST' && $_SERVER['REQUEST_METHOD'] !== 'GET') {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

// Get user ID
$usuario_id = $_SESSION['user_id'];

// Get and sanitize parameters
$asignatura_id = isset($_REQUEST['asignatura_id']) ? (int)$_REQUEST['asignatura_id'] : 0;
$grupo_id = isset($_REQUEST['grupo_id']) ? (int)$_REQUEST['grupo_id'] : 0;
$fecha = isset($_REQUEST['fecha']) ? $_REQUEST['fecha'] : '';

// Validate required parameters
if ($asignatura_id <= 0 || $grupo_id <= 0 || empty($fecha)) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Parámetros inválidos']);
    exit;
}

// Validate date format (YYYY-MM-DD)
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Formato de fecha inválido']);
    exit;
}

try {
    // Verify asignatura belongs to user
    $stmt = $conn->prepare("SELECT id FROM asignaturas WHERE id = :id AND usuario_id = :usuario_id");
    $stmt->bindParam(':id', $asignatura_id);
    $stmt->bindParam(':usuario_id', $usuario_id);
    $stmt->execute();
    
    if (!$stmt->fetch(PDO::FETCH_ASSOC)) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'No tienes permiso para acceder a esta asignatura']);
        exit;
    }
    
    // Get attendance records for the specified parameters
    $stmt = $conn->prepare("
        SELECT a.alumno_id, a.estado, a.observaciones
        FROM asistencias a
        WHERE a.asignatura_id = :asignatura_id AND a.fecha_hora LIKE :fecha AND a.registrado_por = :usuario_id
    ");
    
    $fechaPattern = $fecha . '%'; // Add % for LIKE query
    $stmt->bindParam(':asignatura_id', $asignatura_id);
    $stmt->bindParam(':fecha', $fechaPattern);
    $stmt->bindParam(':usuario_id', $usuario_id);
    $stmt->execute();
    
    $asistencias = [];
    
    // Format results as an associative array with alumno_id as key
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $asistencias[$row['alumno_id']] = [
            'estado' => $row['estado'],
            'observaciones' => $row['observaciones']
        ];
    }
    
    // Si no hay asistencias, devolver un objeto vacío en lugar de un array
    if (empty($asistencias)) {
        $asistencias = new stdClass(); // Esto se convertirá en {} en JSON
    }
    
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'asistencias' => $asistencias]);
    
} catch (PDOException $e) {
    error_log('Error en get_asistencias.php: ' . $e->getMessage());
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Error al obtener las asistencias: ' . $e->getMessage()]);
}
?>