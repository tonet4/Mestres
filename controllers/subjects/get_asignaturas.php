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
    // Get all subjects for the current user
    $stmt = $conn->prepare("
        SELECT id, nombre, descripcion, color, icono
        FROM asignaturas
        WHERE usuario_id = :usuario_id AND activo = 1
        ORDER BY nombre ASC
    ");
    
    $stmt->bindParam(':usuario_id', $usuario_id);
    $stmt->execute();
    
    $asignaturas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get groups assigned to each subject
    foreach ($asignaturas as &$asignatura) {
        $stmt = $conn->prepare("
            SELECT g.id, g.nombre, g.curso_academico
            FROM grupos g
            JOIN grupos_asignaturas ga ON g.id = ga.grupo_id
            WHERE ga.asignatura_id = :asignatura_id AND g.activo = 1
            ORDER BY g.nombre ASC
        ");
        
        $stmt->bindParam(':asignatura_id', $asignatura['id']);
        $stmt->execute();
        
        $asignatura['grupos'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'asignaturas' => $asignaturas]);
    
} catch (PDOException $e) {
    error_log('Error en get_asignaturas.php: ' . $e->getMessage());
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Error al obtener las asignaturas: ' . $e->getMessage()]);
}
?>