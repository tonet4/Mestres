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

// Obtener datos
$week = isset($_POST['week']) ? (int)$_POST['week'] : null;
$year = isset($_POST['year']) ? (int)$_POST['year'] : null;
$day = isset($_POST['day']) ? limpiarDatos($_POST['day']) : null;
$content = isset($_POST['content']) ? $_POST['content'] : '';
$usuario_id = $_SESSION['user_id'];

// Validar datos
if (!$week || !$year || !$day) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Faltan datos requeridos']);
    exit;
}

// Validar que el día sea válido (sábado o domingo)
if ($day !== 'sabado' && $day !== 'domingo') {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Día no válido']);
    exit;
}

try {
    // Sanitizar el contenido para almacenamiento seguro
    $sanitized_content = $content;
    
    // Si no es un JSON válido, lo convertimos a un formato de array simple
    if (!isValidJSON($content)) {
        $sanitized_content = json_encode([
            [
                'id' => 1,
                'text' => $content
            ]
        ]);
    }
    
    // Verificar si ya existen eventos para este día
    $stmt = $conn->prepare("
        SELECT id
        FROM eventos_fin_semana
        WHERE usuario_id = :usuario_id AND semana_numero = :semana AND anio = :anio AND dia = :dia
        LIMIT 1
    ");
    
    $stmt->bindParam(':usuario_id', $usuario_id);
    $stmt->bindParam(':semana', $week);
    $stmt->bindParam(':anio', $year);
    $stmt->bindParam(':dia', $day);
    $stmt->execute();
    
    $exists = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($exists) {
        // Actualizar eventos existentes
        $stmt = $conn->prepare("
            UPDATE eventos_fin_semana
            SET contenido = :contenido
            WHERE usuario_id = :usuario_id AND semana_numero = :semana AND anio = :anio AND dia = :dia
        ");
    } else {
        // Insertar nuevos eventos
        $stmt = $conn->prepare("
            INSERT INTO eventos_fin_semana (usuario_id, semana_numero, anio, dia, contenido)
            VALUES (:usuario_id, :semana, :anio, :dia, :contenido)
        ");
    }
    
    $stmt->bindParam(':contenido', $sanitized_content);
    $stmt->bindParam(':usuario_id', $usuario_id);
    $stmt->bindParam(':semana', $week);
    $stmt->bindParam(':anio', $year);
    $stmt->bindParam(':dia', $day);
    $stmt->execute();
    
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'content' => $sanitized_content]);
    
} catch (Exception $e) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

// Función para verificar si una cadena es un JSON válido
function isValidJSON($string) {
    if (!is_string($string) || trim($string) === '') {
        return false;
    }
    
    json_decode($string);
    return json_last_error() === JSON_ERROR_NONE;
}