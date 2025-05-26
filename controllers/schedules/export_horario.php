<?php
/**
 * Export schedule data for client-side image generation
 * 
 * This controller returns all the necessary data for the frontend to generate
 * an image of the schedule using HTML2Canvas
 * 
 * @author Antonio Esteban Lorenzo
 */

// Include necessary configuration
require_once '../../includes/auth.php';
require_once '../../api/config.php';

// Check if user is authenticated
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'No authenticated user']);
    exit;
}

// Get the schedule ID from the query parameters
$horario_id = isset($_GET['id']) ? (int)$_GET['id'] : null;

if (!$horario_id) {
    http_response_code(400);
    echo json_encode(['error' => 'Schedule ID is required']);
    exit;
}

$usuario_id = $_SESSION['user_id'];

try {
    // First, verify that the schedule belongs to the user and get its details
    $stmt = $conn->prepare("SELECT id, nombre, descripcion, dias_semana, fecha_actualizacion 
                            FROM horarios 
                            WHERE id = :id AND usuario_id = :usuario_id");
    $stmt->bindParam(':id', $horario_id);
    $stmt->bindParam(':usuario_id', $usuario_id);
    $stmt->execute();
    
    $horario = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$horario) {
        http_response_code(403);
        echo json_encode(['error' => 'Schedule not found or not authorized']);
        exit;
    }
    
    // Get the user's information
    $stmt = $conn->prepare("SELECT nombre, apellidos FROM usuarios WHERE id = :usuario_id");
    $stmt->bindParam(':usuario_id', $usuario_id);
    $stmt->execute();
    
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Get all blocks for the schedule
    $stmt = $conn->prepare("SELECT id, dia_semana, hora_inicio, hora_fin, 
                                  titulo, descripcion, color
                           FROM horarios_bloques 
                           WHERE horario_id = :horario_id 
                           ORDER BY dia_semana, hora_inicio");
    $stmt->bindParam(':horario_id', $horario_id);
    $stmt->execute();
    
    $bloques = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Format days of week for display
    $diasSemana = [
        1 => 'Lunes',
        2 => 'Martes',
        3 => 'Miércoles',
        4 => 'Jueves',
        5 => 'Viernes',
        6 => 'Sábado',
        7 => 'Domingo'
    ];
    
    // Determine which days to show based on the schedule configuration
    $diasMostrar = [];
    $max_dias = $horario['dias_semana'];
    
    for ($i = 1; $i <= $max_dias; $i++) {
        $diasMostrar[] = $diasSemana[$i];
    }
    
    // Group blocks by day
    $bloquesPorDia = [];
    
    foreach ($bloques as $bloque) {
        $dia = (int)$bloque['dia_semana'];
        if (!isset($bloquesPorDia[$dia])) {
            $bloquesPorDia[$dia] = [];
        }
        $bloquesPorDia[$dia][] = $bloque;
    }
    
    // Find the earliest start time and latest end time
    $horaInicio = '23:59:59';
    $horaFin = '00:00:00';
    
    foreach ($bloques as $bloque) {
        if ($bloque['hora_inicio'] < $horaInicio) {
            $horaInicio = $bloque['hora_inicio'];
        }
        if ($bloque['hora_fin'] > $horaFin) {
            $horaFin = $bloque['hora_fin'];
        }
    }
    
    // Format for display
    $fechaActualizacion = new DateTime($horario['fecha_actualizacion']);
    $fechaFormateada = $fechaActualizacion->format('d/m/Y H:i');
    
    // Return all data
    echo json_encode([
        'status' => 'success',
        'horario' => $horario,
        'usuario' => $usuario,
        'bloques' => $bloques,
        'bloquesPorDia' => $bloquesPorDia,
        'diasMostrar' => $diasMostrar,
        'horaInicio' => $horaInicio,
        'horaFin' => $horaFin,
        'fechaActualizacion' => $fechaFormateada
    ]);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}