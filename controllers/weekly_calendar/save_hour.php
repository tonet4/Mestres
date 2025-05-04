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

// Get form data
$action = isset($_POST['action']) ? limpiarDatos($_POST['action']) : null;
$hour = isset($_POST['hour']) ? limpiarDatos($_POST['hour']) : null;
$week = isset($_POST['week']) ? (int)$_POST['week'] : null;
$year = isset($_POST['year']) ? (int)$_POST['year'] : null;
$hour_id = isset($_POST['hour_id']) ? (int)$_POST['hour_id'] : null;
$reference_hour_id = isset($_POST['reference_hour_id']) ? (int)$_POST['reference_hour_id'] : null;
$position = isset($_POST['position']) ? limpiarDatos($_POST['position']) : 'after'; // Nuevo parámetro
$usuario_id = $_SESSION['user_id'];

// Validate data
if (!$action || !$hour || !$week || !$year) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Faltan datos requeridos']);
    exit;
}

try {
    // Start transaction
    $conn->beginTransaction();
    
    if ($action === 'add') {
        // Default order value
        $orden = 1;
        
        if ($reference_hour_id) {
            $stmt = $conn->prepare("
                SELECT orden
                FROM horas_calendario
                WHERE id = :id AND usuario_id = :usuario_id
                LIMIT 1
            ");
            
            $stmt->bindParam(':id', $reference_hour_id);
            $stmt->bindParam(':usuario_id', $usuario_id);
            $stmt->execute();
            
            if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                // Determine order based on position parameter
                if ($position === 'after') {
                    // After: increment order to insert after reference time
                    $orden = $row['orden'] + 1;
                } else {
                    // Before: use the same order as reference time
                    $orden = $row['orden'];
                }
                
                // Check if we already have a record with this order
                $stmt = $conn->prepare("
                    SELECT COUNT(*) as count
                    FROM horas_calendario
                    WHERE usuario_id = :usuario_id 
                    AND semana_numero = :semana 
                    AND anio = :anio 
                    AND orden = :orden
                ");
                
                $stmt->bindParam(':usuario_id', $usuario_id);
                $stmt->bindParam(':semana', $week);
                $stmt->bindParam(':anio', $year);
                $stmt->bindParam(':orden', $orden);
                $stmt->execute();
                
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                
                // Only update orders if necessary
                if ($result['count'] > 0) {
                    // Update orders for the following hours to make space
                    $stmt = $conn->prepare("
                        UPDATE horas_calendario
                        SET orden = orden + 1
                        WHERE usuario_id = :usuario_id 
                        AND semana_numero = :semana 
                        AND anio = :anio 
                        AND orden >= :orden
                        ORDER BY orden DESC
                    ");
                    
                    $stmt->bindParam(':usuario_id', $usuario_id);
                    $stmt->bindParam(':semana', $week);
                    $stmt->bindParam(':anio', $year);
                    $stmt->bindParam(':orden', $orden);
                    $stmt->execute();
                }
            } else {
                // If the reference time was not found, get the latest order
                $stmt = $conn->prepare("
                    SELECT MAX(orden) as max_orden
                    FROM horas_calendario
                    WHERE usuario_id = :usuario_id AND semana_numero = :semana AND anio = :anio
                ");
                
                $stmt->bindParam(':usuario_id', $usuario_id);
                $stmt->bindParam(':semana', $week);
                $stmt->bindParam(':anio', $year);
                $stmt->execute();
                
                if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $orden = ($row['max_orden'] ?? 0) + 1;
                }
            }
        } else {
            // If there is no reference time, get the latest order
            $stmt = $conn->prepare("
                SELECT MAX(orden) as max_orden
                FROM horas_calendario
                WHERE usuario_id = :usuario_id AND semana_numero = :semana AND anio = :anio
            ");
            
            $stmt->bindParam(':usuario_id', $usuario_id);
            $stmt->bindParam(':semana', $week);
            $stmt->bindParam(':anio', $year);
            $stmt->execute();
            
            if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $orden = ($row['max_orden'] ?? 0) + 1;
            }
        }
        
        // Insert new time
        $stmt = $conn->prepare("
            INSERT INTO horas_calendario (usuario_id, semana_numero, anio, hora, orden)
            VALUES (:usuario_id, :semana, :anio, :hora, :orden)
        ");
        
        $stmt->bindParam(':usuario_id', $usuario_id);
        $stmt->bindParam(':semana', $week);
        $stmt->bindParam(':anio', $year);
        $stmt->bindParam(':hora', $hour);
        $stmt->bindParam(':orden', $orden);
        $stmt->execute();
        
        $new_hour_id = $conn->lastInsertId();
        
    } elseif ($action === 'edit') {
        // Verify that the time belongs to the user
        $stmt = $conn->prepare("
            SELECT id
            FROM horas_calendario
            WHERE id = :id AND usuario_id = :usuario_id
            LIMIT 1
        ");
        
        $stmt->bindParam(':id', $hour_id);
        $stmt->bindParam(':usuario_id', $usuario_id);
        $stmt->execute();
        
        if (!$stmt->fetch(PDO::FETCH_ASSOC)) {
            throw new Exception('No tienes permiso para editar esta hora');
        }
        
        // Update time
        $stmt = $conn->prepare("
            UPDATE horas_calendario
            SET hora = :hora
            WHERE id = :id AND usuario_id = :usuario_id
        ");
        
        $stmt->bindParam(':hora', $hour);
        $stmt->bindParam(':id', $hour_id);
        $stmt->bindParam(':usuario_id', $usuario_id);
        $stmt->execute();
        
        $new_hour_id = $hour_id;
    } else {
        throw new Exception('Acción no válida');
    }
    
    // Confirm transaction
    $conn->commit();
    
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'hour_id' => $new_hour_id]);
    
} catch (Exception $e) {
    // Roll back transaction in case of error
    $conn->rollBack();
    
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}