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

// Verify that it is a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

// Get data
$week = isset($_POST['week']) ? (int)$_POST['week'] : null;
$year = isset($_POST['year']) ? (int)$_POST['year'] : null;
$day = isset($_POST['day']) ? limpiarDatos($_POST['day']) : null;
$content = isset($_POST['content']) ? $_POST['content'] : '';
$usuario_id = $_SESSION['user_id'];

// Validate data
if (!$week || !$year || !$day) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Faltan datos requeridos']);
    exit;
}

//Validate that the day is valid (Saturday or Sunday)
if ($day !== 'sabado' && $day !== 'domingo') {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Día no válido']);
    exit;
}

try {
    // Sanitize contents for safe storage
    $sanitized_content = $content;
    
    // If it's not a valid JSON, we convert it to a simple array format
    if (!isValidJSON($content)) {
        $sanitized_content = json_encode([
            [
                'id' => 1,
                'text' => $content
            ]
        ]);
    }
    
    // Check if there are already events for this day
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
        // Update existing events
        $stmt = $conn->prepare("
            UPDATE eventos_fin_semana
            SET contenido = :contenido
            WHERE usuario_id = :usuario_id AND semana_numero = :semana AND anio = :anio AND dia = :dia
        ");
    } else {
        // Insert new events
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

// Function to check if a string is valid JSON
function isValidJSON($string) {
    if (!is_string($string) || trim($string) === '') {
        return false;
    }
    
    json_decode($string);
    return json_last_error() === JSON_ERROR_NONE;
}