<?php
// views/importar/preview.php

// Estos datos vienen del controlador ImportarController::previsualizar()
$data_preview = $_SESSION['import_preview_data'] ?? [];
$import_mode = $_SESSION['import_mode'] ?? 'reemplazar';
$contadorPersonas = count($data_preview);
$contadorVacaciones = 0;
foreach ($data_preview as $p) {
    $contadorVacaciones += count($p['vacaciones']);
}
$errores_preview = $errores_preview ?? []; // $errores_preview se define en el controlador

$modo_texto = ($import_mode === 'reemplazar') ? 
    "Reemplazo Total (Se borrarán TODOS los datos actuales)" : 
    "Actualización (Añadir nuevos y actualizar existentes)";
?>

<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Paso 2: Previsualización de Importación</h1>

    <div class="alert alert-info" role="alert">
        <h4 class="alert-heading">Resumen de la Carga</h4>
        <p>El sistema ha leído tu archivo CSV. No se ha guardado nada en la base de datos todavía.</p>
        <hr>
        <p class="mb-0">
            <strong>Modo de Importación Seleccionado:</strong>
            <span class="fw-bold <?php echo ($import_mode === 'reemplazar') ? 'text-danger' : 'text-primary'; ?>">
                <?php echo $modo_texto; ?>
            </span>
        </p>
        <p class="mb-0">
            <strong>Personas a importar:</strong> <?php echo $contadorPersonas; ?><br>
            <strong>Registros de vacaciones a importar:</strong> <?php echo $contadorVacaciones; ?>
        </p>
    </div>

    <?php if (!empty($errores_preview)): ?>
        <div class="alert alert-warning" role="alert">
            <h4 class="alert-heading">Advertencias de Lectura</h4>
            <p>Se encontraron los siguientes problemas al leer el archivo. Estos registros NO se importarán:</p>
            <ul>
                <?php foreach ($errores_preview as $error): ?>
                    <li><?php echo htmlspecialchars($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="card shadow-sm">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 fw-bold text-primary">Muestra de Datos (Primeros 10 registros)</h6>
            <div>
                <form action="index.php?controller=importar&action=ejecutar" method="POST" class="d-inline" id="form-ejecutar">
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-check-circle-fill me-2"></i>Confirmar e Importar
                    </button>
                </form>
                <a href="index.php?controller=importar&action=index" class="btn btn-secondary d-inline">
                    <i class="bi bi-x-circle me-2"></i>Cancelar
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-sm table-striped table-bordered">
                    <thead>
                        <tr>
                            <th>N°</th>
                            <th>N° Empleado (Generado)</th>
                            <th>Nombre</th>
                            <th>Período (Generado)</th>
                            <th>N° Vacaciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($data_preview)): ?>
                            <tr>
                                <td colspan="5" class="text-center">No se encontraron datos válidos para importar.</td>
                            </tr>
                        <?php endif; ?>

                        <?php // Mostrar solo los primeros 10
                        $sample_data = array_slice($data_preview, 0, 10);
                        foreach ($sample_data as $persona):
                        ?>
                            <tr>
                                <td><?php echo htmlspecialchars($persona['id']); ?></td>
                                <td><?php echo htmlspecialchars($persona['numero_empleado']); ?></td>
                                <td><?php echo htmlspecialchars($persona['nombre_completo']); ?></td>
                                <td><?php echo htmlspecialchars($persona['periodo']['periodo_inicio']) . ' al ' . htmlspecialchars($persona['periodo']['periodo_fin']); ?></td>
                                <td class="text-center"><?php echo count($persona['vacaciones']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
// Añadir una confirmación final antes de ejecutar
document.getElementById('form-ejecutar').addEventListener('submit', function(e) {
    const modo = '<?php echo $import_mode; ?>';
    let mensaje = "Vas a importar <?php echo $contadorPersonas; ?> personas.\n\n¿Estás seguro de continuar?";
    
    if (modo === 'reemplazar') {
        mensaje = "¡ADVERTENCIA FINAL!\n\nEstás en modo 'Reemplazo Total'. Se borrarán TODOS los empleados y vacaciones existentes antes de importar <?php echo $contadorPersonas; ?> personas.\n\n¿ESTÁS SEGURO DE CONTINUAR?";
    }
    
    if (!confirm(mensaje)) {
        e.preventDefault(); // Detener el envío
    }
});
</script>