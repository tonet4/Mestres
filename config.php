<?php
/**
 * @author Antonio Esteban Lorenzo
 * 
 */

// Configuring the database connection
$db_host = 'localhost';   // Database server
$db_name = 'mestres';     // Database name
$db_user = 'root';        // User
$db_pass = '';            // password

try {
    // Create PDO connection
    $conn = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8", $db_user, $db_pass);
    
    // Set error mode to throw exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Set the default fetch mode to associative
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
    
} catch(PDOException $e) {
    // In case of connection error, display message
    die("Error de conexión: " . $e->getMessage());
}