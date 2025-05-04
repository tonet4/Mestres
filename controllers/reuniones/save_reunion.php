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
$titulo = isset($_POST['titulo']) ? limpiarDatos($_POST['titulo']) : '';
$fecha = isset($_POST['fecha']) ? limpiarDatos($_POST['fecha']) : '';
$hora = isset($_POST['hora']) && !empty($_POST['hora']) ? limpiarDatos($_POST['hora']) : NULL;
$contenido = isset($_POST['contenido']) ? limpiarDatos($_POST['contenido']) : '';

// Validate required fields
if (empty($titulo) || empty($fecha)) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'El título y la fecha son obligatorios']);
    exit;
}

try {
    // If id is provided, update existing meeting
    if ($id > 0) {
        $stmt = $conn->prepare("
            UPDATE reuniones
            SET titulo = :titulo, fecha = :fecha, hora = :hora, contenido = :contenido
            WHERE id = :id AND usuario_id = :usuario_id
        ");
        
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':usuario_id', $usuario_id);
        $stmt->bindParam(':titulo', $titulo);
        $stmt->bindParam(':fecha', $fecha);
        $stmt->bindParam(':hora', $hora);
        $stmt->bindParam(':contenido', $contenido);
        $stmt->execute();
        
        $mensaje = 'Reunión actualizada correctamente';
    } 
    // Otherwise, create a new meeting
    else {
        $stmt = $conn->prepare("
            INSERT INTO reuniones (usuario_id, titulo, fecha, hora, contenido)
            VALUES (:usuario_id, :titulo, :fecha, :hora, :contenido)
        ");
        
        $stmt->bindParam(':usuario_id', $usuario_id);
        $stmt->bindParam(':titulo', $titulo);
        $stmt->bindParam(':fecha', $fecha);
        $stmt->bindParam(':hora', $hora);
        $stmt->bindParam(':contenido', $contenido);
        $stmt->execute();
        
        $id = $conn->lastInsertId();
        $mensaje = 'Reunión creada correctamente';
    }
    
    // Return the created/updated meeting
    $stmt = $conn->prepare("
        SELECT id, titulo, fecha, hora, contenido, fecha_creacion, fecha_actualizacion
        FROM reuniones
        WHERE id = :id
    ");
    
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    
    $reunion = $stmt->fetch(PDO::FETCH_ASSOC);
    $reunion['fecha_formateada'] = date('d/m/Y', strtotime($reunion['fecha']));
    $reunion['hora_formateada'] = $reunion['hora'] ? date('H:i', strtotime($reunion['hora'])) : '';
    $reunion['fecha_creacion_formateada'] = date('d/m/Y H:i', strtotime($reunion['fecha_creacion']));
    
    // NUEVA FUNCIONALIDAD: Crear/actualizar evento en el calendario mensual
    // Comprobar si ya existe un evento para esta reunión
    $stmt = $conn->prepare("
        SELECT id FROM eventos_calendario_anual 
        WHERE usuario_id = :usuario_id 
        AND descripcion LIKE :descripcion
    ");
    
    $descripcion_pattern = "%[REUNION_ID:" . $id . "]%";
    $stmt->bindParam(':usuario_id', $usuario_id);
    $stmt->bindParam(':descripcion', $descripcion_pattern);
    $stmt->execute();
    
    // Preparar los datos del evento
    $titulo_evento = $titulo;
    $descripcion_evento = "[REUNION_ID:" . $id . "] ";
    
    if ($hora) {
        $descripcion_evento .= "Hora: " . date('H:i', strtotime($hora)) . " - ";
    }
    
    // Añadir parte del contenido a la descripción
    $contenido_resumido = substr(strip_tags($contenido), 0, 100);
    $descripcion_evento .= $contenido_resumido;
    
    if ($evento = $stmt->fetch(PDO::FETCH_ASSOC)) {
        // Actualizar evento existente
        $stmt = $conn->prepare("
            UPDATE eventos_calendario_anual
            SET fecha = :fecha, 
                titulo = :titulo,
                descripcion = :descripcion
            WHERE id = :id
        ");
        
        $stmt->bindParam(':id', $evento['id']);
        $stmt->bindParam(':fecha', $fecha);
        $stmt->bindParam(':titulo', $titulo_evento);
        $stmt->bindParam(':descripcion', $descripcion_evento);
        $stmt->execute();
    } else {
        // Crear nuevo evento
        $stmt = $conn->prepare("
            INSERT INTO eventos_calendario_anual 
            (usuario_id, fecha, titulo, descripcion, icono, color)
            VALUES 
            (:usuario_id, :fecha, :titulo, :descripcion, 'users', '#9b59b6')
        ");
        
        $stmt->bindParam(':usuario_id', $usuario_id);
        $stmt->bindParam(':fecha', $fecha);
        $stmt->bindParam(':titulo', $titulo_evento);
        $stmt->bindParam(':descripcion', $descripcion_evento);
        $stmt->execute();
    }
    
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true, 
        'message' => $mensaje, 
        'reunion' => $reunion
    ]);
    
} catch (PDOException $e) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Error al guardar la reunión: ' . $e->getMessage()]);
}
?>