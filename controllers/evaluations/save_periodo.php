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
$nombre = isset($_POST['nombre']) ? trim($_POST['nombre']) : '';
$fecha_inicio = isset($_POST['fecha_inicio']) ? trim($_POST['fecha_inicio']) : '';
$fecha_fin = isset($_POST['fecha_fin']) ? trim($_POST['fecha_fin']) : '';
$descripcion = isset($_POST['descripcion']) ? trim($_POST['descripcion']) : '';

// Validate required fields
if (empty($nombre) || empty($fecha_inicio) || empty($fecha_fin)) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Faltan campos requeridos']);
    exit;
}

try {
    $conn->beginTransaction();
    
    if ($id > 0) {
        // Update existing period
        $stmt = $conn->prepare("
            UPDATE periodos_evaluacion 
            SET nombre = :nombre, 
                fecha_inicio = :fecha_inicio, 
                fecha_fin = :fecha_fin, 
                descripcion = :descripcion
            WHERE id = :id AND usuario_id = :usuario_id
        ");
        
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':usuario_id', $usuario_id);
        $stmt->bindParam(':nombre', $nombre);
        $stmt->bindParam(':fecha_inicio', $fecha_inicio);
        $stmt->bindParam(':fecha_fin', $fecha_fin);
        $stmt->bindParam(':descripcion', $descripcion);
        
        $stmt->execute();
        
        if ($stmt->rowCount() === 0) {
            throw new Exception('No se encontró el período o no tienes permisos para editarlo');
        }
        
        $mensaje = 'Período actualizado correctamente';
    } else {
        // Create new period
        $stmt = $conn->prepare("
            INSERT INTO periodos_evaluacion (usuario_id, nombre, fecha_inicio, fecha_fin, descripcion)
            VALUES (:usuario_id, :nombre, :fecha_inicio, :fecha_fin, :descripcion)
        ");
        
        $stmt->bindParam(':usuario_id', $usuario_id);
        $stmt->bindParam(':nombre', $nombre);
        $stmt->bindParam(':fecha_inicio', $fecha_inicio);
        $stmt->bindParam(':fecha_fin', $fecha_fin);
        $stmt->bindParam(':descripcion', $descripcion);
        
        $stmt->execute();
        
        $id = $conn->lastInsertId();
        $mensaje = 'Período creado correctamente';
    }
    
    // Get the updated/created period
    $stmt = $conn->prepare("
        SELECT * FROM periodos_evaluacion WHERE id = :id
    ");
    
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    
    $periodo = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $conn->commit();
    
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true, 
        'message' => $mensaje, 
        'periodo' => $periodo
    ]);
    
} catch (Exception $e) {
    $conn->rollBack();
    error_log('Error en save_periodo.php: ' . $e->getMessage());
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Error al guardar el período: ' . $e->getMessage()]);
}
?>