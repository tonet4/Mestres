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
    header('HTTP/1.1 401 Unauthorized');
    echo 'No autorizado';
    exit;
}

// Verify that it is a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('HTTP/1.1 405 Method Not Allowed');
    echo 'Método no permitido';
    exit;
}

// Get data
$content = isset($_POST['content']) ? $_POST['content'] : '';
$notes = isset($_POST['notes']) ? $_POST['notes'] : '';
$saturday = isset($_POST['saturday']) ? $_POST['saturday'] : '';
$sunday = isset($_POST['sunday']) ? $_POST['sunday'] : '';
$week_info = isset($_POST['week_info']) ? limpiarDatos($_POST['week_info']) : '';

// Validate data
if (empty($content)) {
    header('HTTP/1.1 400 Bad Request');
    echo 'No hay contenido para exportar';
    exit;
}

// Create a temporary HTML file that we can then convert to PDF
$html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Calendario - QUADERN MESTRES</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .logo {
            font-size: 24px;
            font-weight: bold;
            color: #3498db;
        }
        .week-info {
            font-size: 18px;
            margin: 10px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #3498db;
            color: white;
        }
        .calendar-event {
            margin-bottom: 5px;
            padding: 5px;
            border-radius: 3px;
        }
        .event-title {
            font-weight: bold;
        }
        .event-description {
            font-size: 0.9em;
        }
        .bottom-panels {
            margin-top: 20px;
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
        }
        .panel {
            flex: 1;
            min-width: 30%;
            border: 1px solid #ddd;
            border-radius: 5px;
            overflow: hidden;
            margin-bottom: 20px;
        }
        .panel-header {
            background-color: #3498db;
            color: white;
            padding: 10px;
            font-weight: bold;
        }
        .panel-content {
            padding: 10px;
        }
        .note-item {
            padding: 10px;
            border-bottom: 1px solid #ddd;
        }
        .note-text {
            margin-bottom: 5px;
        }
        @media print {
            body {
                margin: 0;
                padding: 15px;
            }
            table {
                page-break-inside: avoid;
            }
            .panel {
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo">QUADERN MESTRES</div>
        <div class="week-info">' . $week_info . '</div>
    </div>
    
    <div class="calendar-container">' . $content . '</div>
    
    <div class="bottom-panels">
        <div class="panel">
            <div class="panel-header">Notas de la Semana</div>
            <div class="panel-content">
                ' . $notes . '
            </div>
        </div>
        
        <div class="panel">
            <div class="panel-header">Sábado</div>
            <div class="panel-content">
                ' . $saturday . '
            </div>
        </div>
        
        <div class="panel">
            <div class="panel-header">Domingo</div>
            <div class="panel-content">
                ' . $sunday . '
            </div>
        </div>
    </div>
</body>
</html>';

// Clean up HTML to remove unwanted elements like action buttons
$html = preg_replace('/<button.*?<\/button>/s', '', $html);

// Configuring headers for download
$filename = 'calendario_' . date('Y-m-d') . '.html';
header('Content-Description: File Transfer');
header('Content-Type: text/html; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . strlen($html));

// Send the content
echo $html;
exit;
?>