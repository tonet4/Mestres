<?php
/**
 * Función para limpiar datos de entrada
 * Evita inyecciones XSS y otros problemas de seguridad
 */
function limpiarDatos($datos) {
    $datos = trim($datos);
    $datos = stripslashes($datos);
    $datos = htmlspecialchars($datos, ENT_QUOTES, 'UTF-8');
    return $datos;
}

/**
 * Función para formatear fechas
 */
function formatearFecha($fecha) {
    $timestamp = strtotime($fecha);
    return date('d/m/Y H:i', $timestamp);
}

/**
 * Función para generar un resumen de texto
 */
function resumirTexto($texto, $longitud = 100) {
    if (strlen($texto) > $longitud) {
        return substr($texto, 0, $longitud) . '...';
    }
    return $texto;
}