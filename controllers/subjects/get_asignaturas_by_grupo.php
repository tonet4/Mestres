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

// Get the grupo_id parameter
$grupo_id = isset($_GET['grupo_id']) ? intval($_GET['grupo_id']) : 0;

// Get user ID
$usuario_id = $_SESSION['user_id'];

if ($grupo_id <= 0) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'ID de grupo no válido']);
    exit;
}

try {
    // Query to get asignaturas associated with the grupo
    $stmt = $conn->prepare("
        SELECT a.* 
        FROM asignaturas a
        JOIN grupos_asignaturas ga ON a.id = ga.asignatura_id
        WHERE ga.grupo_id = :grupo_id AND a.usuario_id = :usuario_id
        ORDER BY a.nombre ASC
    ");
    
    $stmt->bindParam(':grupo_id', $grupo_id, PDO::PARAM_INT);
    $stmt->bindParam(':usuario_id', $usuario_id, PDO::PARAM_INT);
    $stmt->execute();
    
    $asignaturas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'asignaturas' => $asignaturas]);

} catch (PDOException $e) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Error al obtener asignaturas: ' . $e->getMessage()]);
}
?>