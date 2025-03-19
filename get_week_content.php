<?php
// Incluir los archivos necesarios
require_once 'includes/auth.php';
require_once 'config.php';

// Verificar que el usuario esté autenticado
if (!is_logged_in()) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

// Obtener parámetros
$week = isset($_GET['week']) ? (int)$_GET['week'] : null;
$year = isset($_GET['year']) ? (int)$_GET['year'] : null;
$usuario_id = $_SESSION['user_id'];

// Validar parámetros
if (!$week || !$year) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Parámetros inválidos']);
    exit;
}

try {
    // Inicializar array de respuesta
    $response = [
        'success' => true,
        'notes' => [],
        'saturday' => [],
        'sunday' => []
    ];
    
    // Obtener notas de la semana
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
        // Intentar decodificar el JSON
        $notesContent = $row['contenido'];
        if ($notesContent) {
            // Verificar si ya es un array JSON válido
            $decoded = json_decode($notesContent, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $response['notes'] = $decoded;
            } else {
                // Si no es un JSON válido, crear un array con un solo elemento
                $response['notes'] = [
                    [
                        'id' => 1,
                        'text' => $notesContent
                    ]
                ];
            }
        }
    }
    
    // Obtener eventos del sábado
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
    
    // Obtener eventos del domingo
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