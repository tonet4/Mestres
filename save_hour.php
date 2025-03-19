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
$hour = isset($_POST['hour']) ? limpiarDatos($_POST['hour']) : null;
$week = isset($_POST['week']) ? (int)$_POST['week'] : null;
$year = isset($_POST['year']) ? (int)$_POST['year'] : null;
$hour_id = isset($_POST['hour_id']) ? (int)$_POST['hour_id'] : null;
$reference_hour_id = isset($_POST['reference_hour_id']) ? (int)$_POST['reference_hour_id'] : null;
$usuario_id = $_SESSION['user_id'];

// Validar datos
if (!$action || !$hour || !$week || !$year) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Faltan datos requeridos']);
    exit;
}

try {
    // Iniciar transacción
    $conn->beginTransaction();
    
    if ($action === 'add') {
        // Si hay una hora de referencia, obtener su orden
        $orden = 1; // Valor predeterminado
        
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
                // Incrementar orden para insertar después de la hora de referencia
                $orden = $row['orden'] + 1;
                
                // Actualizar órdenes de las horas siguientes
                $stmt = $conn->prepare("
                    UPDATE horas_calendario
                    SET orden = orden + 1
                    WHERE usuario_id = :usuario_id 
                    AND semana_numero = :semana 
                    AND anio = :anio 
                    AND orden >= :orden
                ");
                
                $stmt->bindParam(':usuario_id', $usuario_id);
                $stmt->bindParam(':semana', $week);
                $stmt->bindParam(':anio', $year);
                $stmt->bindParam(':orden', $orden);
                $stmt->execute();
            } else {
                // Si no se encontró la hora de referencia, obtener el último orden
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
            // Si no hay hora de referencia, obtener el último orden
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
        
        // Insertar nueva hora
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
        // Verificar que la hora pertenece al usuario
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
        
        // Actualizar hora
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
    
    // Confirmar transacción
    $conn->commit();
    
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'hour_id' => $new_hour_id]);
    
} catch (Exception $e) {
    // Revertir transacción en caso de error
    $conn->rollBack();
    
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}