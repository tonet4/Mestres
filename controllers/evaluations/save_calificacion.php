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

// Get form data
$alumno_id = isset($_POST['alumno_id']) ? (int)$_POST['alumno_id'] : 0;
$evaluacion_id = isset($_POST['evaluacion_id']) ? (int)$_POST['evaluacion_id'] : 0;
$valor = isset($_POST['valor']) ? (float)$_POST['valor'] : 0;
$observaciones = isset($_POST['observaciones']) ? trim($_POST['observaciones']) : '';

// Validate the grade range (0-10)
if ($valor < 0) {
    $valor = 0;
} else if ($valor > 10) {
    $valor = 10;
}

$valor = round($valor, 2);

// Validate required fields
if ($alumno_id <= 0 || $evaluacion_id <= 0) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Faltan campos requeridos']);
    exit;
}

try {
    // Check if the student and evaluation exist and belong to the current user
    $stmt = $conn->prepare("
        SELECT a.id FROM alumnos a
        WHERE a.id = :alumno_id AND a.usuario_id = :usuario_id
    ");
    
    $usuario_id = $_SESSION['user_id'];
    $stmt->bindParam(':alumno_id', $alumno_id);
    $stmt->bindParam(':usuario_id', $usuario_id);
    $stmt->execute();
    
    if ($stmt->rowCount() === 0) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'El alumno no existe o no tienes permisos']);
        exit;
    }
    
    $stmt = $conn->prepare("
        SELECT e.id FROM evaluaciones e
        WHERE e.id = :evaluacion_id AND e.usuario_id = :usuario_id
    ");
    
    $stmt->bindParam(':evaluacion_id', $evaluacion_id);
    $stmt->bindParam(':usuario_id', $usuario_id);
    $stmt->execute();
    
    if ($stmt->rowCount() === 0) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'La evaluación no existe o no tienes permisos']);
        exit;
    }
    
    // Check if a grade already exists for this student and evaluation
    $stmt = $conn->prepare("
        SELECT id FROM calificaciones
        WHERE alumno_id = :alumno_id AND evaluacion_id = :evaluacion_id
    ");
    
    $stmt->bindParam(':alumno_id', $alumno_id);
    $stmt->bindParam(':evaluacion_id', $evaluacion_id);
    $stmt->execute();
    
    $calificacion_existente = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($calificacion_existente) {
        // Update existing grade
        $stmt = $conn->prepare("
            UPDATE calificaciones 
            SET valor = :valor, observaciones = :observaciones
            WHERE id = :id
        ");
        
        $id = $calificacion_existente['id'];
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':valor', $valor);
        $stmt->bindParam(':observaciones', $observaciones);
        
        $stmt->execute();
        
        $mensaje = 'Calificación actualizada correctamente';
    } else {
        // Create new grade
        $stmt = $conn->prepare("
            INSERT INTO calificaciones (alumno_id, evaluacion_id, valor, observaciones)
            VALUES (:alumno_id, :evaluacion_id, :valor, :observaciones)
        ");
        
        $stmt->bindParam(':alumno_id', $alumno_id);
        $stmt->bindParam(':evaluacion_id', $evaluacion_id);
        $stmt->bindParam(':valor', $valor);
        $stmt->bindParam(':observaciones', $observaciones);
        
        $stmt->execute();
        
        $id = $conn->lastInsertId();
        $mensaje = 'Calificación guardada correctamente';
    }
    
    // Get the updated/created grade
    $stmt = $conn->prepare("
        SELECT * FROM calificaciones WHERE id = :id
    ");
    
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    
    $calificacion = $stmt->fetch(PDO::FETCH_ASSOC);
    
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true, 
        'message' => $mensaje, 
        'calificacion' => $calificacion
    ]);
    
} catch (PDOException $e) {
    error_log('Error en save_calificacion.php: ' . $e->getMessage());
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Error al guardar la calificación: ' . $e->getMessage()]);
}
?>