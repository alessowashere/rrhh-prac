<?php
// views/layout/modal_header.php
// Un layout ligero solo para contenido de modales (iframes)

// Asegurarnos de que BASE_URL esté definida
if (!defined('BASE_URL')) {
    // Si no existe, la cargamos desde el config.php
    // La ruta __DIR__ retrocede 2 niveles (desde /layout) hasta /htdocs
    require_once __DIR__ . '/../../config/config.php';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalle</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <style>
        /* Un fondo blanco simple para el cuerpo del modal */
        body { background-color: #fff; }
    </style>
</head>
<body>
<div class="container-fluid p-3">