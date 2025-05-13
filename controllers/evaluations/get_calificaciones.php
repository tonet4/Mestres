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

// Get user ID
$usuario_id = $_SESSION['user_id'];

// Get filter parameters
$asignatura_id = isset($_GET['asignatura_id']) ? (int)$_GET['asignatura_id'] : 0;
$periodo_id = isset($_GET['periodo_id']) ? (int)$_GET['periodo_id'] : 0;
$grupo_id = isset($_GET['grupo_id']) ? (int)$_GET['grupo_id'] : 0;

// Check for required parameters
if ($asignatura_id <= 0 || $periodo_id <= 0 || $grupo_id <= 0) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Faltan parámetros requeridos']);
    exit;
}

try {
    // First get all evaluations for this subject, period and group
    $stmt = $conn->prepare("
        SELECT * FROM evaluaciones 
        WHERE usuario_id = :usuario_id 
        AND asignatura_id = :asignatura_id 
        AND periodo_id = :periodo_id
        AND grupo_id = :grupo_id
        AND activo = 1
        ORDER BY fecha ASC, nombre ASC
    ");
    
    $stmt->bindParam(':usuario_id', $usuario_id);
    $stmt->bindParam(':asignatura_id', $asignatura_id);
    $stmt->bindParam(':periodo_id', $periodo_id);
    $stmt->bindParam(':grupo_id', $grupo_id);
    $stmt->execute();
    
    $evaluaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get all students from the group
    $stmt = $conn->prepare("
        SELECT a.* 
        FROM alumnos a
        JOIN alumnos_grupos ag ON a.id = ag.alumno_id
        WHERE ag.grupo_id = :grupo_id 
        AND a.usuario_id = :usuario_id
        AND a.activo = 1
        ORDER BY a.apellidos ASC, a.nombre ASC
    ");
    
    $stmt->bindParam(':usuario_id', $usuario_id);
    $stmt->bindParam(':grupo_id', $grupo_id);
    $stmt->execute();
    
    $alumnos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get all grades
    $calificaciones = [];
    
    if (!empty($evaluaciones) && !empty($alumnos)) {
        // Get all evaluation IDs
        $evaluacion_ids = array_column($evaluaciones, 'id');
        $evaluacion_ids_str = implode(',', $evaluacion_ids);
        
        // Get all student IDs
        $alumno_ids = array_column($alumnos, 'id');
        $alumno_ids_str = implode(',', $alumno_ids);
        
        $stmt = $conn->prepare("
            SELECT c.* 
            FROM calificaciones c
            WHERE c.evaluacion_id IN (" . $evaluacion_ids_str . ")
            AND c.alumno_id IN (" . $alumno_ids_str . ")
        ");
        
        $stmt->execute();
        
        $calificaciones_raw = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Organize califications by student and evaluation
        foreach ($calificaciones_raw as $calificacion) {
            $calificaciones[$calificacion['alumno_id'] . '-' . $calificacion['evaluacion_id']] = $calificacion;
        }
    }
    
    // Get final grades if they exist
    $notas_finales = [];
    
    if (!empty($alumnos)) {
        $alumno_ids = array_column($alumnos, 'id');
        $alumno_ids_str = implode(',', $alumno_ids);
        
        $stmt = $conn->prepare("
            SELECT * FROM notas_finales
            WHERE alumno_id IN (" . $alumno_ids_str . ")
            AND asignatura_id = :asignatura_id
            AND periodo_id = :periodo_id
            AND grupo_id = :grupo_id
        ");
        
        $stmt->bindParam(':asignatura_id', $asignatura_id);
        $stmt->bindParam(':periodo_id', $periodo_id);
        $stmt->bindParam(':grupo_id', $grupo_id);
        $stmt->execute();
        
        $notas_finales_raw = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Organize final grades by student
        foreach ($notas_finales_raw as $nota_final) {
            $notas_finales[$nota_final['alumno_id']] = $nota_final;
        }
    }
    
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true, 
        'evaluaciones' => $evaluaciones,
        'alumnos' => $alumnos,
        'calificaciones' => $calificaciones,
        'notas_finales' => $notas_finales
    ]);
    
} catch (PDOException $e) {
    error_log('Error en get_calificaciones.php: ' . $e->getMessage());
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Error al obtener las calificaciones: ' . $e->getMessage()]);
}
?>