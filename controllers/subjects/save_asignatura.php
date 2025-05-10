<?php
/**
 * @author Antonio Esteban Lorenzo
 * 
 */
// Include the necessary files
require_once '../../includes/auth.php';
require_once '../../includes/utils.php';
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

// Get and sanitize form data
$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$nombre = isset($_POST['nombre']) ? limpiarDatos($_POST['nombre']) : '';
$descripcion = isset($_POST['descripcion']) ? limpiarDatos($_POST['descripcion']) : '';
$color = isset($_POST['color']) ? limpiarDatos($_POST['color']) : '#3498db';
$icono = isset($_POST['icono']) ? limpiarDatos($_POST['icono']) : 'book';

// Validate required fields
if (empty($nombre)) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'El nombre de la asignatura es obligatorio']);
    exit;
}

// Validate color format (hex code)
if (!preg_match('/^#[a-f0-9]{6}$/i', $color)) {
    $color = '#3498db'; // Default color if invalid
}

// List of allowed icons
$allowedIcons = ['book', 'calculator', 'microscope', 'flask', 'music', 'paint-brush', 'language', 'globe', 'atom', 'history', 'laptop-code', 'running'];
if (!in_array($icono, $allowedIcons)) {
    $icono = 'book'; // Default icon if invalid
}

try {
    // If id is provided, update existing subject
    if ($id > 0) {
        // First check if the subject belongs to this user
        $stmt = $conn->prepare("SELECT id FROM asignaturas WHERE id = :id AND usuario_id = :usuario_id");
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':usuario_id', $usuario_id);
        $stmt->execute();
        
        if (!$stmt->fetch(PDO::FETCH_ASSOC)) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'No tienes permiso para editar esta asignatura']);
            exit;
        }
        
        $stmt = $conn->prepare("
            UPDATE asignaturas 
            SET nombre = :nombre, 
                descripcion = :descripcion, 
                color = :color, 
                icono = :icono
            WHERE id = :id AND usuario_id = :usuario_id
        ");
        
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':usuario_id', $usuario_id);
        $stmt->bindParam(':nombre', $nombre);
        $stmt->bindParam(':descripcion', $descripcion);
        $stmt->bindParam(':color', $color);
        $stmt->bindParam(':icono', $icono);
        
        $stmt->execute();
        
        $mensaje = 'Asignatura actualizada correctamente';
    } 
    // Otherwise, create a new subject
    else {
        $stmt = $conn->prepare("
            INSERT INTO asignaturas (usuario_id, nombre, descripcion, color, icono)
            VALUES (:usuario_id, :nombre, :descripcion, :color, :icono)
        ");
        
        $stmt->bindParam(':usuario_id', $usuario_id);
        $stmt->bindParam(':nombre', $nombre);
        $stmt->bindParam(':descripcion', $descripcion);
        $stmt->bindParam(':color', $color);
        $stmt->bindParam(':icono', $icono);
        
        $stmt->execute();
        
        $id = $conn->lastInsertId();
        $mensaje = 'Asignatura creada correctamente';
    }
    
    // Get the updated/created subject
    $stmt = $conn->prepare("
        SELECT id, nombre, descripcion, color, icono
        FROM asignaturas
        WHERE id = :id
    ");
    
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    
    $asignatura = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Get groups assigned to this subject
    $stmt = $conn->prepare("
        SELECT g.id, g.nombre, g.curso_academico
        FROM grupos g
        JOIN grupos_asignaturas ga ON g.id = ga.grupo_id
        WHERE ga.asignatura_id = :asignatura_id AND g.activo = 1
        ORDER BY g.nombre ASC
    ");
    
    $stmt->bindParam(':asignatura_id', $id);
    $stmt->execute();
    
    $asignatura['grupos'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true, 
        'message' => $mensaje, 
        'asignatura' => $asignatura
    ]);
    
} catch (PDOException $e) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Error al guardar la asignatura: ' . $e->getMessage()]);
}
?>