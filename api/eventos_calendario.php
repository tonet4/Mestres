<?php
// Mostrar errores para depuración
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Log de depuración
file_put_contents('api_log.txt', date('Y-m-d H:i:s') . ' - Solicitud recibida' . PHP_EOL, FILE_APPEND);
file_put_contents('api_log.txt', date('Y-m-d H:i:s') . ' - GET: ' . json_encode($_GET) . PHP_EOL, FILE_APPEND);
file_put_contents('api_log.txt', date('Y-m-d H:i:s') . ' - POST: ' . json_encode($_POST) . PHP_EOL, FILE_APPEND);

// Include the necessary files
require_once '../includes/auth.php';
require_once '../config.php';

// Log de estado de autenticación
file_put_contents('api_log.txt', date('Y-m-d H:i:s') . ' - Autenticación: ' . (isset($_SESSION['user_id']) ? 'Sí' : 'No') . PHP_EOL, FILE_APPEND);

// Respuesta por defecto
$response = [
    'success' => false,
    'debug_info' => [
        'get' => $_GET,
        'post' => $_POST,
        'session' => isset($_SESSION) ? $_SESSION : null,
        'auth' => isset($_SESSION['user_id']) ? true : false
    ],
    'message' => 'Procesando solicitud...'
];

// Verificar autenticación
if (!isset($_SESSION['user_id'])) {
    $response['error'] = 'Usuario no autenticado';
    $response['message'] = 'Error de autenticación';
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

// Get user information
$usuario_id = $_SESSION['user_id'];

// Get the action
$action = null;
if (isset($_GET['action'])) {
    $action = $_GET['action'];
} else if (isset($_POST['action'])) {
    $action = $_POST['action'];
} else if (isset($_POST['event_action'])) {
    // Manejar el caso donde se usa event_action en lugar de action
    $action = $_POST['event_action'] === 'add' ? 'add_event' : 'update_event';
}

// Log de acción determinada
file_put_contents('api_log.txt', date('Y-m-d H:i:s') . ' - Acción determinada: ' . ($action ?? 'ninguna') . PHP_EOL, FILE_APPEND);

$response['action'] = $action;

// Solo como prueba, siempre intentar añadir un evento si hay datos
if ((isset($_POST['title']) || isset($_POST['event_title'])) && (isset($_POST['event_date']) || isset($_POST['fecha']))) {
    $title = isset($_POST['title']) ? $_POST['title'] : ($_POST['event_title'] ?? 'Sin título');
    $date = isset($_POST['event_date']) ? $_POST['event_date'] : ($_POST['fecha'] ?? date('Y-m-d'));
    $description = isset($_POST['description']) ? $_POST['description'] : ($_POST['event_description'] ?? '');
    $icon = isset($_POST['icon']) ? $_POST['icon'] : ($_POST['event_icon'] ?? 'calendar');
    $color = isset($_POST['color']) ? $_POST['color'] : ($_POST['event_color'] ?? '#3498db');

    try {
        $stmt = $conn->prepare("
            INSERT INTO eventos_calendario_anual (usuario_id, fecha, titulo, descripcion, icono, color)
            VALUES (:usuario_id, :fecha, :titulo, :descripcion, :icono, :color)");
        
        $stmt->bindParam(':usuario_id', $usuario_id, PDO::PARAM_INT);
        $stmt->bindParam(':fecha', $date, PDO::PARAM_STR);
        $stmt->bindParam(':titulo', $title, PDO::PARAM_STR);
        $stmt->bindParam(':descripcion', $description, PDO::PARAM_STR);
        $stmt->bindParam(':icono', $icon, PDO::PARAM_STR);
        $stmt->bindParam(':color', $color, PDO::PARAM_STR);
        
        $result = $stmt->execute();
        
        if ($result) {
            $event_id = $conn->lastInsertId();
            $response['success'] = true;
            $response['message'] = 'Evento guardado con éxito';
            $response['data'] = [
                'id' => $event_id,
                'usuario_id' => $usuario_id,
                'fecha' => $date,
                'titulo' => $title,
                'descripcion' => $description,
                'icono' => $icon,
                'color' => $color
            ];
        } else {
            $response['error'] = 'Error al guardar el evento';
            $response['message'] = 'Error en la base de datos';
        }
    } catch (PDOException $e) {
        $response['error'] = 'Error de base de datos: ' . $e->getMessage();
        $response['message'] = 'Error en la base de datos';
        file_put_contents('api_log.txt', date('Y-m-d H:i:s') . ' - Error BD: ' . $e->getMessage() . PHP_EOL, FILE_APPEND);
    }
} else {
    // Si no hay datos para guardar un evento, manejamos otras acciones
    switch ($action) {
        case 'get_events_by_year':
            $year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');
            
            try {
                $stmt = $conn->prepare("
                    SELECT * FROM eventos_calendario_anual 
                    WHERE usuario_id = :usuario_id 
                    AND YEAR(fecha) = :year
                    ORDER BY fecha ASC");
                
                $stmt->bindParam(':usuario_id', $usuario_id, PDO::PARAM_INT);
                $stmt->bindParam(':year', $year, PDO::PARAM_INT);
                $stmt->execute();
                
                $eventos = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                $response['success'] = true;
                $response['message'] = 'Eventos obtenidos correctamente';
                $response['data'] = $eventos;
            } catch (PDOException $e) {
                $response['error'] = 'Error al obtener eventos: ' . $e->getMessage();
                $response['message'] = 'Error en la base de datos';
            }
            break;

            case 'get_events_by_month':
                $month = isset($_GET['month']) ? intval($_GET['month']) : date('n');
                $year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');
                
                try {
                    $stmt = $conn->prepare("
                        SELECT * FROM eventos_calendario_anual 
                        WHERE usuario_id = :usuario_id 
                        AND YEAR(fecha) = :year 
                        AND MONTH(fecha) = :month
                        ORDER BY fecha ASC");
                    
                    $stmt->bindParam(':usuario_id', $usuario_id, PDO::PARAM_INT);
                    $stmt->bindParam(':year', $year, PDO::PARAM_INT);
                    $stmt->bindParam(':month', $month, PDO::PARAM_INT);
                    $stmt->execute();
                    
                    $eventos = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    $response['success'] = true;
                    $response['message'] = 'Eventos obtenidos correctamente';
                    $response['data'] = $eventos;
                } catch (PDOException $e) {
                    $response['error'] = 'Error al obtener eventos: ' . $e->getMessage();
                    $response['message'] = 'Error en la base de datos';
                }
                break;
                case 'delete_event':
                    // Delete an event
                    $event_id = isset($_POST['event_id']) ? intval($_POST['event_id']) : null;
                    
                    if (!$event_id) {
                        $response['error'] = 'ID de evento no proporcionado';
                        break;
                    }
                    
                    try {
                        // Verify that the event belongs to the user
                        $stmt = $conn->prepare("
                            SELECT id FROM eventos_calendario_anual 
                            WHERE id = :id AND usuario_id = :usuario_id");
                        
                        $stmt->bindParam(':id', $event_id, PDO::PARAM_INT);
                        $stmt->bindParam(':usuario_id', $usuario_id, PDO::PARAM_INT);
                        $stmt->execute();
                        
                        if ($stmt->rowCount() == 0) {
                            $response['error'] = 'Evento no encontrado o no autorizado';
                            break;
                        }
                        
                        // Delete the event
                        $stmt = $conn->prepare("
                            DELETE FROM eventos_calendario_anual 
                            WHERE id = :id AND usuario_id = :usuario_id");
                        
                        $stmt->bindParam(':id', $event_id, PDO::PARAM_INT);
                        $stmt->bindParam(':usuario_id', $usuario_id, PDO::PARAM_INT);
                        
                        $stmt->execute();
                        
                        $response['success'] = true;
                        $response['data'] = ['id' => $event_id];
                    } catch (PDOException $e) {
                        $response['error'] = 'Error al eliminar evento: ' . $e->getMessage();
                    }
                    break;
            
        default:
            $response['error'] = 'Acción no válida o no se recibieron datos para guardar un evento';
            $response['message'] = 'Acción no reconocida';
            break;
    }
}

// Return the response as JSON
header('Content-Type: application/json');
echo json_encode($response);
file_put_contents('api_log.txt', date('Y-m-d H:i:s') . ' - Respuesta: ' . json_encode($response) . PHP_EOL . PHP_EOL, FILE_APPEND);