<?php
/**
 * @author Antonio Esteban Lorenzo
 * 
 */
/**
 * Function to sanitize input data
* Prevents XSS injections and other security issues
 */
function limpiarDatos($datos) {
    $datos = trim($datos);
    $datos = stripslashes($datos);
    $datos = htmlspecialchars($datos, ENT_QUOTES, 'UTF-8');
    return $datos;
}

/**
 * Function to format dates
 */
function formatearFecha($fecha) {
    $timestamp = strtotime($fecha);
    return date('d/m/Y H:i', $timestamp);
}

/**
 * Function to generate a text summary
 */
function resumirTexto($texto, $longitud = 100) {
    if (strlen($texto) > $longitud) {
        return substr($texto, 0, $longitud) . '...';
    }
    return $texto;
}