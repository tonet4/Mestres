<?php
// Mostrar errores para depuración
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Probar las conexiones a archivos
echo "Probando inclusiones:<br>";
try {
    require_once '../includes/auth.php';
    echo "✅ Archivo auth.php incluido correctamente<br>";
} catch (Exception $e) {
    echo "❌ Error al incluir auth.php: " . $e->getMessage() . "<br>";
}

try {
    require_once '../config.php';
    echo "✅ Archivo config.php incluido correctamente<br>";
} catch (Exception $e) {
    echo "❌ Error al incluir config.php: " . $e->getMessage() . "<br>";
}

// Probar conexión a la base de datos
echo "<br>Probando conexión a la base de datos:<br>";
try {
    global $conn;
    if ($conn) {
        echo "✅ Conexión a la base de datos establecida<br>";
        
        // Verificar si la tabla existe
        $stmt = $conn->query("SHOW TABLES LIKE 'eventos_calendario_anual'");
        if ($stmt->rowCount() > 0) {
            echo "✅ Tabla eventos_calendario_anual existe<br>";
        } else {
            echo "❌ Tabla eventos_calendario_anual NO existe<br>";
        }
    } else {
        echo "❌ La variable de conexión \$conn no está definida<br>";
    }
} catch (PDOException $e) {
    echo "❌ Error de conexión a la base de datos: " . $e->getMessage() . "<br>";
}

// Mostrar datos de petición
echo "<br>Datos de la petición actual:<br>";
echo "Método: " . $_SERVER['REQUEST_METHOD'] . "<br>";
echo "GET: <pre>" . print_r($_GET, true) . "</pre>";
echo "POST: <pre>" . print_r($_POST, true) . "</pre>";