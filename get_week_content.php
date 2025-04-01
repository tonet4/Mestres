<?php
/**
 * @author Antonio Esteban Lorenzo
 * 
 */
// Include the necessary files
require_once 'includes/auth.php';
require_once 'config.php';

// Verify that the user is authenticated
if (!is_logged_in()) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

// Get parameters
$week = isset($_GET['week']) ? (int)$_GET['week'] : null;
$year = isset($_GET['year']) ? (int)$_GET['year'] : null;
$usuario_id = $_SESSION['user_id'];

// Validate parameters
if (!$week || !$year) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Parámetros inválidos']);
    exit;
}

try {
    //Initialize response array
    $response = [
        'success' => true,
        'notes' => [],
        'saturday' => [],
        'sunday' => []
    ];
    
    // Get notes of the week
    $stmt = $conn->prepare("
        SELECT contenido
        FROM notas_semana
        WHERE usuario_id = :usuario_id AND semana_numero = :semana AND anio = :anio
        LIMIT 1
    ");
    
    $stmt->bindParam(':usuario_id', $usuario_id);
    $stmt->bindParam(':semana', $week);
    $stmt->bindParam(':anio', $year);
    $stmt->execute();
    
    if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        // Trying to decode the JSON
        $notesContent = $row['contenido'];
        if ($notesContent) {
            // Check if it is already a valid JSON array
            $decoded = json_decode($notesContent, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $response['notes'] = $decoded;
            } else {
                // If it is not a valid JSON, create an array with a single element
                $response['notes'] = [
                    [
                        'id' => 1,
                        'text' => $notesContent
                    ]
                ];
            }
        }
    }
    
    // Get Saturday Events
    $stmt = $conn->prepare("
        SELECT contenido
        FROM eventos_fin_semana
        WHERE usuario_id = :usuario_id AND semana_numero = :semana AND anio = :anio AND dia = 'sabado'
        LIMIT 1
    ");
    
    $stmt->bindParam(':usuario_id', $usuario_id);
    $stmt->bindParam(':semana', $week);
    $stmt->bindParam(':anio', $year);
    $stmt->execute();
    
    if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $saturdayContent = $row['contenido'];
        if ($saturdayContent) {
            $decoded = json_decode($saturdayContent, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $response['saturday'] = $decoded;
            } else {
                $response['saturday'] = [
                    [
                        'id' => 1,
                        'text' => $saturdayContent
                    ]
                ];
            }
        }
    }
    
    // Get Sunday Events
    $stmt = $conn->prepare("
        SELECT contenido
        FROM eventos_fin_semana
        WHERE usuario_id = :usuario_id AND semana_numero = :semana AND anio = :anio AND dia = 'domingo'
        LIMIT 1
    ");
    
    $stmt->bindParam(':usuario_id', $usuario_id);
    $stmt->bindParam(':semana', $week);
    $stmt->bindParam(':anio', $year);
    $stmt->execute();
    
    if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $sundayContent = $row['contenido'];
        if ($sundayContent) {
            $decoded = json_decode($sundayContent, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $response['sunday'] = $decoded;
            } else {
                $response['sunday'] = [
                    [
                        'id' => 1,
                        'text' => $sundayContent
                    ]
                ];
            }
        }
    }
    
    header('Content-Type: application/json');
    echo json_encode($response);
    
} catch (PDOException $e) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Error al obtener el contenido de la semana: ' . $e->getMessage()]);
}