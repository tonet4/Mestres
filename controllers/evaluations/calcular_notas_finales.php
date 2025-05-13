<?php
/**
 * @author Antonio Esteban Lorenzo
 * 
 */
// Include the necessary files
require_once '../../includes/auth.php';
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

// Get parameters
$asignatura_id = isset($_POST['asignatura_id']) ? (int)$_POST['asignatura_id'] : 0;
$periodo_id = isset($_POST['periodo_id']) ? (int)$_POST['periodo_id'] : 0;
$grupo_id = isset($_POST['grupo_id']) ? (int)$_POST['grupo_id'] : 0; // Nuevo parámetro
$alumnos = isset($_POST['alumnos']) ? json_decode($_POST['alumnos'], true) : [];
$calificaciones_enviadas = isset($_POST['calificaciones']) ? json_decode($_POST['calificaciones'], true) : [];

// Log para depuración
error_log('Parámetros recibidos: asignatura_id=' . $asignatura_id . ', periodo_id=' . $periodo_id . ', grupo_id=' . $grupo_id . ', alumnos=' . json_encode($alumnos));
error_log('Calificaciones recibidas: ' . json_encode($calificaciones_enviadas));

// Validate required fields
if ($asignatura_id <= 0 || $periodo_id <= 0 || $grupo_id <= 0 || empty($alumnos)) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Faltan parámetros requeridos']);
    exit;
}

try {
    $conn->beginTransaction();
    
    $usuario_id = $_SESSION['user_id'];
    
    // First get all evaluations for this subject, period and group
    $stmt = $conn->prepare("
        SELECT id, porcentaje FROM evaluaciones 
        WHERE usuario_id = :usuario_id 
        AND asignatura_id = :asignatura_id 
        AND periodo_id = :periodo_id
        AND grupo_id = :grupo_id
        AND activo = 1
    ");
    
    $stmt->bindParam(':usuario_id', $usuario_id);
    $stmt->bindParam(':asignatura_id', $asignatura_id);
    $stmt->bindParam(':periodo_id', $periodo_id);
    $stmt->bindParam(':grupo_id', $grupo_id);
    $stmt->execute();
    
    $evaluaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Log para depuración
    error_log('Evaluaciones encontradas: ' . json_encode($evaluaciones));

    // Check if we have evaluations
    if (empty($evaluaciones)) {
        error_log('No hay evaluaciones definidas para esta asignatura, período y grupo');
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'No hay evaluaciones definidas para esta asignatura, período y grupo']);
        exit;
    }
    
    // Calculate the total percentage of all evaluations
    $total_porcentaje = 0;
    foreach ($evaluaciones as $evaluacion) {
        $total_porcentaje += (float)$evaluacion['porcentaje'];
    }
    
    error_log('Total porcentaje de todas las evaluaciones: ' . $total_porcentaje);
    
    // Process each student
    $notas_finales = [];
    
    foreach ($alumnos as $alumno_id) {
        error_log('Procesando alumno: ' . $alumno_id);
        
        // Check if we have califications for this student
        if (!isset($calificaciones_enviadas[$alumno_id])) {
            error_log("No hay calificaciones enviadas para el alumno $alumno_id");
            continue;
        }
        
        // Calculate final grade
        $nota_final = 0;
        $total_porcentaje_calculado = 0;
        
        foreach ($evaluaciones as $evaluacion) {
            $evaluacion_id = $evaluacion['id'];
            
            // Check if we have a calification for this evaluation
            if (isset($calificaciones_enviadas[$alumno_id][$evaluacion_id])) {
                $valor = (float)$calificaciones_enviadas[$alumno_id][$evaluacion_id];
                
                // Validar el rango de la nota (0-10)
                if ($valor < 0) {
                    $valor = 0;
                } else if ($valor > 10) {
                    $valor = 10;
                }
                
                // Redondear a 2 decimales
                $valor = round($valor, 2);
                
                $porcentaje = (float)$evaluacion['porcentaje'];
                
                // Log de cálculo
                error_log("Alumno $alumno_id, evaluación $evaluacion_id: valor=$valor, porcentaje=$porcentaje");
                
                // Calculate weighted grade
                $contribucion = ($valor * $porcentaje / 100);
                $nota_final += $contribucion;
                $total_porcentaje_calculado += $porcentaje;
                
                error_log("Contribución: $contribucion, Nota acumulada: $nota_final, Porcentaje acumulado: $total_porcentaje_calculado");
                
                // Asegurarse de que la calificación esté guardada en la base de datos
                $stmt = $conn->prepare("
                    INSERT INTO calificaciones (alumno_id, evaluacion_id, valor) 
                    VALUES (:alumno_id, :evaluacion_id, :valor) 
                    ON DUPLICATE KEY UPDATE valor = :valor
                ");
                
                $stmt->bindParam(':alumno_id', $alumno_id);
                $stmt->bindParam(':evaluacion_id', $evaluacion_id);
                $stmt->bindParam(':valor', $valor);
                $stmt->execute();
            } else {
                error_log("No hay calificación enviada para alumno $alumno_id, evaluación $evaluacion_id");
            }
        }
        
        // Adjust final grade if total percentage is not 100%
        if ($total_porcentaje_calculado > 0 && $total_porcentaje_calculado < 100) {
            $nota_final_ajustada = ($nota_final * 100) / $total_porcentaje_calculado;
            error_log("Ajustando nota final: $nota_final -> $nota_final_ajustada (porcentaje: $total_porcentaje_calculado%)");
            $nota_final = $nota_final_ajustada;
        }
        
        // Round to 2 decimal places
        $nota_final = round($nota_final, 2);
        
        error_log("Nota final para alumno $alumno_id: $nota_final");
        
        // Check if a final grade already exists for this student, subject, period and group
        $stmt = $conn->prepare("
            SELECT id FROM notas_finales
            WHERE alumno_id = :alumno_id 
            AND asignatura_id = :asignatura_id 
            AND periodo_id = :periodo_id
            AND grupo_id = :grupo_id
        ");
        
        $stmt->bindParam(':alumno_id', $alumno_id);
        $stmt->bindParam(':asignatura_id', $asignatura_id);
        $stmt->bindParam(':periodo_id', $periodo_id);
        $stmt->bindParam(':grupo_id', $grupo_id);
        $stmt->execute();
        
        $nota_final_existente = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($nota_final_existente) {
            // Update existing final grade
            $stmt = $conn->prepare("
                UPDATE notas_finales 
                SET valor_final = :valor_final,
                    fecha_calculo = CURRENT_TIMESTAMP
                WHERE id = :id
            ");
            
            $id = $nota_final_existente['id'];
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':valor_final', $nota_final);
            
            $stmt->execute();
            error_log("Nota final actualizada para alumno $alumno_id: $nota_final");
        } else {
            // Create new final grade
            $stmt = $conn->prepare("
                INSERT INTO notas_finales (alumno_id, asignatura_id, periodo_id, grupo_id, valor_final)
                VALUES (:alumno_id, :asignatura_id, :periodo_id, :grupo_id, :valor_final)
            ");
            
            $stmt->bindParam(':alumno_id', $alumno_id);
            $stmt->bindParam(':asignatura_id', $asignatura_id);
            $stmt->bindParam(':periodo_id', $periodo_id);
            $stmt->bindParam(':grupo_id', $grupo_id);
            $stmt->bindParam(':valor_final', $nota_final);
            
            $stmt->execute();
            
            $id = $conn->lastInsertId();
            error_log("Nueva nota final creada para alumno $alumno_id: $nota_final, ID: $id");
        }
        
        // Get the final grade
        $stmt = $conn->prepare("
            SELECT * FROM notas_finales WHERE id = :id
        ");
        
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        $notas_finales[$alumno_id] = $stmt->fetch(PDO::FETCH_ASSOC);
        error_log("Nota final obtenida para alumno $alumno_id: " . json_encode($notas_finales[$alumno_id]));
    }
    
    $conn->commit();
    
    error_log("Notas finales calculadas: " . json_encode($notas_finales));
    
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true, 
        'message' => 'Notas finales calculadas correctamente', 
        'notas_finales' => $notas_finales,
        'total_porcentaje' => $total_porcentaje
    ]);
    
} catch (PDOException $e) {
    $conn->rollBack();
    error_log('Error en calcular_notas_finales.php: ' . $e->getMessage());
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Error al calcular las notas finales: ' . $e->getMessage()]);
}
?>