<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vista Previa: <?php echo htmlspecialchars($tituloReporte); ?></title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <link href="assets/css/print.css" rel="stylesheet"> 

    <style>
        /* Estilos adicionales solo para la vista previa */
        body {
            background-color: #f8f9fc;
        }
        .report-container {
            max-width: 1100px;
            margin: 20px auto;
            padding: 30px;
            background-color: #fff;
            border: 1px solid #ddd;
            box-shadow: 0 0 15px rgba(0,0,0,0.05);
        }
        .report-header {
            text-align: center;
            margin-bottom: 25px;
            border-bottom: 2px solid #333;
            padding-bottom: 15px;
        }
        .report-header h1 {
            margin: 0;
            color: #333;
        }
        .report-header img {
            max-height: 80px; /* Ajusta el logo de tu empresa */
            margin-bottom: 10px;
        }
        .print-controls {
            text-align: right;
            margin-bottom: 20px;
        }
        .filter-info {
            font-style: italic;
            color: #555;
            margin-bottom: 15px;
        }
        /* Corregir íconos si usas FontAwesome (descomenta tus CSS si los usas) */
        .fas { 
            margin-right: 0.5rem;
        }
    </style>
</head>
<body>

    <div class="report-container">
        
        <div class="print-controls no-print">
            <button onclick="window.print()" class="btn btn-success">
                <i class="bi bi-printer-fill"></i> Imprimir
            </button>
            <button onclick="window.close()" class="btn btn-secondary">
                <i class="bi bi-x-lg"></i> Cerrar
            </button>
        </div>

        <div class="report-header">
            <h1><?php echo htmlspecialchars($tituloReporte); ?></h1>
            <p>Generado el: <?php echo date('d/m/Y H:i:s'); ?></p>
        </div>

        <div class="filter-info">
            <?php if (!empty($filtros['fecha_inicio']) && !empty($filtros['fecha_fin'])): ?>
                <p><strong>Período del reporte:</strong> 
                Desde <?php echo date('d/m/Y', strtotime($filtros['fecha_inicio'])); ?> 
                hasta <?php echo date('d/m/Y', strtotime($filtros['fecha_fin'])); ?>
                </p>
            <?php endif; ?>
            
            <?php // AÑADIDO: Mostrar el filtro de período (año)
            if (!empty($filtros['anio_inicio'])): 
                $anio_fin = (int)htmlspecialchars($filtros['anio_inicio']) + 1;
            ?>
                <p><strong>Período consultado:</strong> <?php echo htmlspecialchars($filtros['anio_inicio']); ?> - <?php echo $anio_fin; ?></p>
            <?php endif; ?>
        </div>
        <div class="report-content"> 
            <?php
            // Aquí se incluye la vista específica del reporte (general.php, por_persona.php, etc.)
            // $vistaReporte fue definida en el controlador
            // $data (extaída) está disponible aquí ($resultados, $info_empleado, etc.)
            if (file_exists($vistaReporte)) {
                include $vistaReporte;
            } else {
                echo "<div class='alert alert-danger'>Error: No se pudo encontrar el archivo de vista del reporte: $vistaReporte</div>";
            }
            ?>
        </div>

    </div>

</body>
</html>