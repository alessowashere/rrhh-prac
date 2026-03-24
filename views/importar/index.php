<?php
// views/importar/index.php
$status = $_GET['status'] ?? null;
$msg = $_GET['msg'] ?? '';
$count_p = $_GET['count_p'] ?? 0;
$count_v = $_GET['count_v'] ?? 0;
?>
<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Importar Datos desde CSV</h1>

    <?php if ($status === 'success'): ?>
        <div class="alert alert-success" role="alert">
            <h4 class="alert-heading">¡Importación Exitosa!</h4>
            <p>Se procesaron y guardaron los datos correctamente.</p>
            <hr>
            <p class="mb-0">
                <strong>Personas creadas/actualizadas:</strong> <?php echo (int)$count_p; ?><br>
                <strong>Registros de vacaciones creados:</strong> <?php echo (int)$count_v; ?>
            </p>
        </div>
    <?php endif; ?>
    
    <?php if ($status === 'error'): ?>
        <div class="alert alert-danger" role="alert">
            <h4 class="alert-heading">¡Error en la Importación!</h4>
            <p>No se pudo completar el proceso. Se revirtieron todos los cambios (si estabas en modo Reemplazo).</p>
            <hr>
            <p class="mb-0">
                <strong>Mensaje del sistema:</strong> <?php echo htmlspecialchars(urldecode($msg)); ?>
            </p>
        </div>
    <?php endif; ?>


    <div class="card shadow-sm">
        <div class="card-header py-3">
            <h6 class="m-0 fw-bold text-primary">Paso 1: Cargar Archivo</h6>
        </div>
        <div class="card-body">
            <p>Este módulo te permite cargar masivamente los datos desde tu archivo CSV (Rol de Vacaciones).</p>
            
            <form action="index.php?controller=importar&action=previsualizar" 
                  method="POST" 
                  enctype="multipart/form-data" 
                  id="form-importar">
                
                <div class="mb-3">
                    <label for="archivo_csv" class="form-label">Seleccionar archivo CSV:</label>
                    <input class="form-control" type="file" id="archivo_csv" name="archivo_csv" accept=".csv, text/csv" required>
                    <div class="form-text">
                        Asegúrate de que el archivo sea el CSV exportado de "ROL DE VACACIONES (1).xlsx".
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Modo de Importación:</label>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="import_mode" id="mode_reemplazar" value="reemplazar" checked>
                        <label class="form-check-label" for="mode_reemplazar">
                            <strong>Reemplazo Total (Full):</strong> Borra TODOS los empleados, períodos y vacaciones actuales y carga los del archivo.
                        </label>
                        <div class="form-text text-danger">¡Cuidado! Esta acción no se puede deshacer. Útil para restaurar la BD.</div>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="import_mode" id="mode_actualizar" value="actualizar" disabled>
                        <label class="form-check-label" for="mode_actualizar">
                            <strong>Añadir y Actualizar (Incremental):</strong> (Próximamente) Añade solo registros nuevos y actualiza los existentes.
                        </label>
                    </div>
                </div>

                <hr>
                
                <button type="submit" class="btn btn-success btn-lg">
                    <i class="bi bi-eye-fill me-2"></i> Cargar y Previsualizar
                </button>
            </form>
        </div>
    </div>
</div>