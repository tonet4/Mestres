<?php
// Mostrar errores para depuración
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Incluir archivos necesarios
require_once '../includes/auth.php';
require_once '../config.php';

// Verificar si estamos autenticados
echo "Verificación de autenticación:<br>";
if (isset($_SESSION['user_id'])) {
    echo "✅ Usuario autenticado: ID " . $_SESSION['user_id'] . "<br>";
} else {
    echo "❌ Usuario no autenticado<br>";
}

// Verificar la tabla de eventos
echo "<br>Verificación de la tabla eventos_calendario_anual:<br>";
try {
    $stmt = $conn->query("SHOW CREATE TABLE eventos_calendario_anual");
    if ($stmt->rowCount() > 0) {
        $tableInfo = $stmt->fetch();
        echo "✅ Estructura de la tabla:<br><pre>" . $tableInfo[1] . "</pre>";
    } else {
        echo "❌ La tabla no existe<br>";
        
        // Crear la tabla si no existe
        echo "Intentando crear la tabla...<br>";
        
        $sql = "CREATE TABLE IF NOT EXISTS `eventos_calendario_anual` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `usuario_id` int(11) NOT NULL,
          `fecha` date NOT NULL,
          `titulo` varchar(255) NOT NULL,
          `descripcion` text DEFAULT NULL,
          `icono` varchar(50) DEFAULT 'calendar',
          `color` varchar(20) DEFAULT '#3498db',
          PRIMARY KEY (`id`),
          KEY `usuario_id` (`usuario_id`),
          CONSTRAINT `eventos_calendario_anual_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
        
        $conn->exec($sql);
        echo "✅ Tabla creada con éxito<br>";
    }
} catch (PDOException $e) {
    echo "❌ Error al verificar/crear la tabla: " . $e->getMessage() . "<br>";
}

// Probar inserción manual de un evento
echo "<br>Probando inserción manual de un evento de prueba:<br>";
try {
    // Solo si estamos autenticados
    if (isset($_SESSION['user_id'])) {
        $usuario_id = $_SESSION['user_id'];
        $fecha = date('Y-m-d');
        $titulo = "Evento de prueba - " . date('H:i:s');
        $descripcion = "Evento creado automáticamente para depuración";
        $icono = "bug";
        $color = "#ff0000";
        
        $stmt = $conn->prepare("
            INSERT INTO eventos_calendario_anual (usuario_id, fecha, titulo, descripcion, icono, color)
            VALUES (:usuario_id, :fecha, :titulo, :descripcion, :icono, :color)");
        
        $stmt->bindParam(':usuario_id', $usuario_id, PDO::PARAM_INT);
        $stmt->bindParam(':fecha', $fecha, PDO::PARAM_STR);
        $stmt->bindParam(':titulo', $titulo, PDO::PARAM_STR);
        $stmt->bindParam(':descripcion', $descripcion, PDO::PARAM_STR);
        $stmt->bindParam(':icono', $icono, PDO::PARAM_STR);
        $stmt->bindParam(':color', $color, PDO::PARAM_STR);
        
        $stmt->execute();
        
        $event_id = $conn->lastInsertId();
        
        echo "✅ Evento creado con ID: " . $event_id . "<br>";
        
        // Verificar si se guardó correctamente
        $stmt = $conn->prepare("SELECT * FROM eventos_calendario_anual WHERE id = :id");
        $stmt->bindParam(':id', $event_id, PDO::PARAM_INT);
        $stmt->execute();
        
        $evento = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo "Datos del evento:<br><pre>" . print_r($evento, true) . "</pre>";
    } else {
        echo "❌ No se puede probar la inserción porque no hay usuario autenticado<br>";
    }
} catch (PDOException $e) {
    echo "❌ Error al insertar evento de prueba: " . $e->getMessage() . "<br>";
}