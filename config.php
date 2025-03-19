<?php
// Configuración de la conexión a la base de datos
$db_host = 'localhost';   // Servidor de la base de datos (por defecto en XAMPP)
$db_name = 'mestres';     // Nombre de la base de datos
$db_user = 'root';        // Usuario por defecto de XAMPP
$db_pass = '';            // Contraseña por defecto de XAMPP (vacía)

try {
    // Crear conexión PDO
    $conn = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8", $db_user, $db_pass);
    
    // Configurar el modo de error para que lance excepciones
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Configurar el modo de obtención predeterminado a asociativo
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
    // Mensaje de conexión exitosa (sólo para debug, quitar en producción)
    // echo "Conexión exitosa a la base de datos";
    
} catch(PDOException $e) {
    // En caso de error en la conexión, mostrar mensaje
    die("Error de conexión: " . $e->getMessage());
}