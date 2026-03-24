<?php
// views/layout/header.php
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Vacaciones</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />
    </head>
</head>
<body class="bg-light">

<div class="d-flex">

    <aside class="sidebar vh-100 p-3 bg-primary-dark">
        <h2 class="text-white text-center fs-5 mb-4">Gestión RRHH</h2>
        
        <nav class="nav nav-pills flex-column">
            <a class="nav-link text-white" href="<?php echo BASE_URL; ?>index.php?controller=dashboard&action=index">
                <i class="bi bi-grid-fill me-2"></i>Dashboard
            </a>
            <a class="nav-link text-white" href="<?php echo BASE_URL; ?>index.php?controller=persona&action=index">
                <i class="bi bi-people-fill me-2"></i>Personal
            </a>
            <a class="nav-link text-white" href="<?php echo BASE_URL; ?>index.php?controller=periodo&action=index">
                <i class="bi bi-calendar-check-fill me-2"></i>Períodos
            </a>
            <a class="nav-link text-white" href="<?php echo BASE_URL; ?>index.php?controller=vacacion&action=index">
                <i class="bi bi-calendar-range-fill me-2"></i>Vacaciones
            </a>
            <a class="nav-link text-white" href="<?php echo BASE_URL; ?>index.php?controller=reporte&action=index">
                <i class="bi bi-file-earmark-text-fill me-2"></i>Reportes
            </a>
        </nav>
    </aside>

    <main class="main-content flex-grow-1 p-4">
        <div class="container-fluid">