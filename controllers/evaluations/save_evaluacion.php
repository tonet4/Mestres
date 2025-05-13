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

// Get form data
$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$asignatura_id = isset($_POST['asignatura_id']) ? (int)$_POST['asignatura_id'] : 0;
$periodo_id = isset($_POST['periodo_id']) ? (int)$_POST['periodo_id'] : 0;
$grupo_id = isset($_POST['grupo_id']) ? (int)$_POST['grupo_id'] : 0; // Nuevo campo
$nombre = isset($_POST['nombre']) ? trim($_POST['nombre']) : '';
$descripcion = isset($_POST['descripcion']) ? trim($_POST['descripcion']) : '';
$fecha = isset($_POST['fecha']) ? trim($_POST['fecha']) : '';
$porcentaje = isset($_POST['porcentaje']) ? (float)$_POST['porcentaje'] : 0;

// Validate required fields
if (empty($nombre) || empty($fecha) || $asignatura_id <= 0 || $periodo_id <= 0 || $grupo_id <= 0) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Faltan campos requeridos']);
    exit;
}

try {
    $conn->beginTransaction();
    
    if ($id > 0) {
        // Update existing evaluation
        $stmt = $conn->prepare("
            UPDATE evaluaciones 
            SET asignatura_id = :asignatura_id,
                periodo_id = :periodo_id,
                grupo_id = :grupo_id,
                nombre = :nombre, 
                descripcion = :descripcion,
                fecha = :fecha,
                porcentaje = :porcentaje
            WHERE id = :id AND usuario_id = :usuario_id
        ");
        
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':usuario_id', $usuario_id);
        $stmt->bindParam(':asignatura_id', $asignatura_id);
        $stmt->bindParam(':periodo_id', $periodo_id);
        $stmt->bindParam(':grupo_id', $grupo_id);
        $stmt->bindParam(':nombre', $nombre);
        $stmt->bindParam(':descripcion', $descripcion);
        $stmt->bindParam(':fecha', $fecha);
        $stmt->bindParam(':porcentaje', $porcentaje);
        
        $stmt->execute();
        
        if ($stmt->rowCount() === 0) {
            throw new Exception('No se encontró la evaluación o no tienes permisos para editarla');
        }
        
        $mensaje = 'Evaluación actualizada correctamente';
    } else {
        // Create new evaluation
        $stmt = $conn->prepare("
            INSERT INTO evaluaciones (usuario_id, asignatura_id, periodo_id, grupo_id, nombre, descripcion, fecha, porcentaje)
            VALUES (:usuario_id, :asignatura_id, :periodo_id, :grupo_id, :nombre, :descripcion, :fecha, :porcentaje)
        ");
        
        $stmt->bindParam(':usuario_id', $usuario_id);
        $stmt->bindParam(':asignatura_id', $asignatura_id);
        $stmt->bindParam(':periodo_id', $periodo_id);
        $stmt->bindParam(':grupo_id', $grupo_id);
        $stmt->bindParam(':nombre', $nombre);
        $stmt->bindParam(':descripcion', $descripcion);
        $stmt->bindParam(':fecha', $fecha);
        $stmt->bindParam(':porcentaje', $porcentaje);
        
        $stmt->execute();
        
        $id = $conn->lastInsertId();
        $mensaje = 'Evaluación creada correctamente';
    }
    
    // Get the updated/created evaluation
    $stmt = $conn->prepare("
        SELECT * FROM evaluaciones WHERE id = :id
    ");
    
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    
    $evaluacion = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $conn->commit();
    
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true, 
        'message' => $mensaje, 
        'evaluacion' => $evaluacion
    ]);
    
} catch (Exception $e) {
    $conn->rollBack();
    error_log('Error en save_evaluacion.php: ' . $e->getMessage());
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Error al guardar la evaluación: ' . $e->getMessage()]);
}
?>