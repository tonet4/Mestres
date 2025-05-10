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

try {
    // Get all students for the current user
    $stmt = $conn->prepare("
        SELECT a.*, g.nombre as grupo_nombre, g.id as grupo_id
        FROM alumnos a
        LEFT JOIN alumnos_grupos ag ON a.id = ag.alumno_id
        LEFT JOIN grupos g ON ag.grupo_id = g.id AND g.usuario_id = :usuario_id
        WHERE a.usuario_id = :usuario_id AND a.activo = 1
        ORDER BY a.apellidos ASC, a.nombre ASC
    ");
    
    $stmt->bindParam(':usuario_id', $usuario_id);
    $stmt->execute();
    
    $alumnos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Format dates for display
    foreach ($alumnos as &$alumno) {
        if (!empty($alumno['fecha_nacimiento'])) {
            $alumno['fecha_nacimiento_formateada'] = date('d/m/Y', strtotime($alumno['fecha_nacimiento']));
        } else {
            $alumno['fecha_nacimiento_formateada'] = '';
        }
    }
    
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'alumnos' => $alumnos]);
    
} catch (PDOException $e) {
    error_log('Error en get_alumnos.php: ' . $e->getMessage());
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Error al obtener los alumnos: ' . $e->getMessage()]);
}
?>