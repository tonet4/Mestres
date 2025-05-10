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

// Get and sanitize parameters
$asignatura_id = isset($_POST['asignatura_id']) ? (int)$_POST['asignatura_id'] : 0;
$fecha = isset($_POST['fecha']) ? $_POST['fecha'] : '';
$alumnos = isset($_POST['alumnos']) ? json_decode($_POST['alumnos'], true) : [];

// Validate required parameters
if ($asignatura_id <= 0 || empty($fecha) || empty($alumnos) || !is_array($alumnos)) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Parámetros inválidos']);
    exit;
}

// Validate date format (YYYY-MM-DD)
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Formato de fecha inválido']);
    exit;
}

// Add time to the date (default to current time if not specified)
$hora = isset($_POST['hora']) ? $_POST['hora'] : date('H:i:s');
$fecha_hora = $fecha . ' ' . $hora;

try {
   // Verify asignatura belongs to user
   $stmt = $conn->prepare("SELECT id FROM asignaturas WHERE id = :id AND usuario_id = :usuario_id");
   $stmt->bindParam(':id', $asignatura_id);
   $stmt->bindParam(':usuario_id', $usuario_id);
   $stmt->execute();
   
   if (!$stmt->fetch(PDO::FETCH_ASSOC)) {
       header('Content-Type: application/json');
       echo json_encode(['success' => false, 'message' => 'No tienes permiso para acceder a esta asignatura']);
       exit;
   }
   
   // Start transaction
   $conn->beginTransaction();
   
   // Prepare insert/update statement
   $stmt = $conn->prepare("
       INSERT INTO asistencias 
       (alumno_id, asignatura_id, fecha_hora, estado, observaciones, registrado_por)
       VALUES 
       (:alumno_id, :asignatura_id, :fecha_hora, :estado, :observaciones, :registrado_por)
       ON DUPLICATE KEY UPDATE
       estado = VALUES(estado),
       observaciones = VALUES(observaciones)
   ");
   
   $stmt->bindParam(':asignatura_id', $asignatura_id);
   $stmt->bindParam(':fecha_hora', $fecha_hora);
   $stmt->bindParam(':registrado_por', $usuario_id);
   
   $cambiosRealizados = 0;
   
   foreach ($alumnos as $alumno) {
       // Validate data
       if (!isset($alumno['id']) || !isset($alumno['estado'])) {
           continue;
       }
       
       $alumno_id = (int)$alumno['id'];
       $estado = limpiarDatos($alumno['estado']);
       $observaciones = isset($alumno['observaciones']) ? limpiarDatos($alumno['observaciones']) : null;
       
       // Validate estado (must be one of: presente, ausente, retraso, justificado)
       if (!in_array($estado, ['presente', 'ausente', 'retraso', 'justificado'])) {
           continue;
       }
       
       // Bind parameters
       $stmt->bindParam(':alumno_id', $alumno_id);
       $stmt->bindParam(':estado', $estado);
       $stmt->bindParam(':observaciones', $observaciones);
       
       // Execute
       $stmt->execute();
       $cambiosRealizados++;
   }
   
   // Commit transaction
   $conn->commit();
   
   header('Content-Type: application/json');
   echo json_encode([
       'success' => true, 
       'message' => 'Asistencias guardadas correctamente',
       'cambios' => $cambiosRealizados
   ]);
   
} catch (PDOException $e) {
   // Rollback in case of error
   if ($conn->inTransaction()) {
       $conn->rollBack();
   }
   
   error_log('Error en save_asistencia.php: ' . $e->getMessage());
   header('Content-Type: application/json');
   echo json_encode(['success' => false, 'message' => 'Error al guardar las asistencias: ' . $e->getMessage()]);
}
?>