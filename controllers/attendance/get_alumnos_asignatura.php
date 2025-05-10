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

// Validate required parameters
if ($asignatura_id <= 0 || $grupo_id <= 0) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'ID de asignatura o grupo inválido']);
    exit;
}

try {
    // Verify the asignatura belongs to the user
    $stmt = $conn->prepare("
        SELECT id, nombre, color, icono 
        FROM asignaturas 
        WHERE id = :asignatura_id AND usuario_id = :usuario_id AND activo = 1
    ");
    $stmt->bindParam(':asignatura_id', $asignatura_id);
    $stmt->bindParam(':usuario_id', $usuario_id);
    $stmt->execute();
    
    $asignatura = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$asignatura) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'No tienes permiso para acceder a esta asignatura']);
        exit;
    }
    
    // Verify the grupo belongs to the user
    $stmt = $conn->prepare("
        SELECT id, nombre 
        FROM grupos 
        WHERE id = :grupo_id AND usuario_id = :usuario_id AND activo = 1
    ");
    $stmt->bindParam(':grupo_id', $grupo_id);
    $stmt->bindParam(':usuario_id', $usuario_id);
    $stmt->execute();
    
    $grupo = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$grupo) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'No tienes permiso para acceder a este grupo']);
        exit;
    }
    
    // Get all alumnos from the grupo
    $stmt = $conn->prepare("
        SELECT a.id, a.nombre, a.apellidos, a.imagen
        FROM alumnos a
        JOIN alumnos_grupos ag ON a.id = ag.alumno_id
        WHERE ag.grupo_id = :grupo_id AND a.activo = 1
        ORDER BY a.apellidos ASC, a.nombre ASC
    ");
    
    $stmt->bindParam(':grupo_id', $grupo_id);
    $stmt->execute();
    
    $alumnos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Return the response
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true, 
        'asignatura' => $asignatura,
        'grupo' => $grupo,
        'alumnos' => $alumnos
    ]);
    
} catch (PDOException $e) {
    error_log('Error en get_alumnos_asignatura.php: ' . $e->getMessage());
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Error al obtener los alumnos: ' . $e->getMessage()]);
}
?>