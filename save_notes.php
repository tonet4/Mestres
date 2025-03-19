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
$content = isset($_POST['content']) ? $_POST['content'] : '';
$usuario_id = $_SESSION['user_id'];

// Validar datos
if (!$week || !$year) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Faltan datos requeridos']);
    exit;
}

try {
    // Sanitizar el contenido para almacenamiento seguro
    // Si ya es un JSON, mantenerlo como string pero sanitizarlo
    // Si no, convertirlo a formato JSON de un solo elemento
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
    
    // Verificar si ya existen notas para esta semana
    $stmt = $conn->prepare("
        SELECT id
        FROM notas_semana
        WHERE usuario_id = :usuario_id AND semana_numero = :semana AND anio = :anio
        LIMIT 1
    ");
    
    $stmt->bindParam(':usuario_id', $usuario_id);
    $stmt->bindParam(':semana', $week);
    $stmt->bindParam(':anio', $year);
    $stmt->execute();
    
    $exists = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($exists) {
        // Actualizar notas existentes
        $stmt = $conn->prepare("
            UPDATE notas_semana
            SET contenido = :contenido
            WHERE usuario_id = :usuario_id AND semana_numero = :semana AND anio = :anio
        ");
    } else {
        // Insertar nuevas notas
        $stmt = $conn->prepare("
            INSERT INTO notas_semana (usuario_id, semana_numero, anio, contenido)
            VALUES (:usuario_id, :semana, :anio, :contenido)
        ");
    }
    
    $stmt->bindParam(':contenido', $sanitized_content);
    $stmt->bindParam(':usuario_id', $usuario_id);
    $stmt->bindParam(':semana', $week);
    $stmt->bindParam(':anio', $year);
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