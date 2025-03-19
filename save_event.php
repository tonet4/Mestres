<?php
// Incluir los archivos necesarios
require_once 'includes/auth.php';
require_once 'includes/utils.php';
require_once 'config.php';

// Verificar que el usuario esté autenticado
if (!is_logged_in()) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

// Verificar que sea una petición POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

// Obtener datos del formulario
$action = isset($_POST['action']) ? limpiarDatos($_POST['action']) : null;
$title = isset($_POST['title']) ? limpiarDatos($_POST['title']) : null;
$description = isset($_POST['description']) ? limpiarDatos($_POST['description']) : null;
$color = isset($_POST['color']) ? limpiarDatos($_POST['color']) : '#3498db';
$week = isset($_POST['week']) ? (int)$_POST['week'] : null;
$year = isset($_POST['year']) ? (int)$_POST['year'] : null;
$day = isset($_POST['day']) ? (int)$_POST['day'] : null;
$hour_id = isset($_POST['hour_id']) ? (int)$_POST['hour_id'] : null;
$event_id = isset($_POST['event_id']) ? (int)$_POST['event_id'] : null;
$usuario_id = $_SESSION['user_id'];

// Validar datos
if (!$action || !$title || !$week || !$year || !$day || !$hour_id) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Faltan datos requeridos']);
    exit;
}

// Validar que el día sea válido (1-5, lunes a viernes)
if ($day < 1 || $day > 5) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Día no válido']);
    exit;
}

try {
    // Verificar que la hora pertenece al usuario
    $stmt = $conn->prepare("
        SELECT id
        FROM horas_calendario
        WHERE id = :id AND usuario_id = :usuario_id AND semana_numero = :semana AND anio = :anio
        LIMIT 1
    ");
    
    $stmt->bindParam(':id', $hour_id);
    $stmt->bindParam(':usuario_id', $usuario_id);
    $stmt->bindParam(':semana', $week);
    $stmt->bindParam(':anio', $year);
    $stmt->execute();
    
    if (!$stmt->fetch(PDO::FETCH_ASSOC)) {
        throw new Exception('La hora seleccionada no es válida o no pertenece a esta semana');
    }
    
    if ($action === 'add') {
        // Insertar nuevo evento
        $stmt = $conn->prepare("
            INSERT INTO eventos_calendario (
                usuario_id, semana_numero, anio, dia_semana, hora_id, titulo, descripcion, color
            ) VALUES (
                :usuario_id, :semana, :anio, :dia, :hora_id, :titulo, :descripcion, :color
            )
        ");
        
        $stmt->bindParam(':usuario_id', $usuario_id);
        $stmt->bindParam(':semana', $week);
        $stmt->bindParam(':anio', $year);
        $stmt->bindParam(':dia', $day);
        $stmt->bindParam(':hora_id', $hour_id);
        $stmt->bindParam(':titulo', $title);
        $stmt->bindParam(':descripcion', $description);
        $stmt->bindParam(':color', $color);
        $stmt->execute();
        
        $new_event_id = $conn->lastInsertId();
        
    } elseif ($action === 'edit') {
        // Verificar que el evento pertenece al usuario
        $stmt = $conn->prepare("
            SELECT id
            FROM eventos_calendario
            WHERE id = :id AND usuario_id = :usuario_id
            LIMIT 1
        ");
        
        $stmt->bindParam(':id', $event_id);
        $stmt->bindParam(':usuario_id', $usuario_id);
        $stmt->execute();
        
        if (!$stmt->fetch(PDO::FETCH_ASSOC)) {
            throw new Exception('No tienes permiso para editar este evento');
        }
        
        // Actualizar evento
        $stmt = $conn->prepare("
            UPDATE eventos_calendario
            SET titulo = :titulo, descripcion = :descripcion, color = :color, dia_semana = :dia, hora_id = :hora_id
            WHERE id = :id AND usuario_id = :usuario_id
        ");
        
        $stmt->bindParam(':titulo', $title);
        $stmt->bindParam(':descripcion', $description);
        $stmt->bindParam(':color', $color);
        $stmt->bindParam(':dia', $day);
        $stmt->bindParam(':hora_id', $hour_id);
        $stmt->bindParam(':id', $event_id);
        $stmt->bindParam(':usuario_id', $usuario_id);
        $stmt->execute();
        
        $new_event_id = $event_id;
    } else {
        throw new Exception('Acción no válida');
    }
    
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'event_id' => $new_event_id]);
    
} catch (Exception $e) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}