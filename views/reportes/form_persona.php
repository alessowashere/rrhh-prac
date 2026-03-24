<?php
// views/reportes/form_persona.php
// Variable: $listaPersonas
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0 text-gray-800">Reporte por Empleado</h1>
</div>

<?php // Display errors if redirected back
 if (isset($_GET['status']) && $_GET['status'] == 'error_empleado'): ?>
    <div class="alert alert-danger">Error: Debe seleccionar un empleado válido.</div>
<?php endif; ?>

<div class="card shadow-sm">
    <div class="card-body">
        <h5 class="card-title">Seleccionar Empleado</h5>
        <p>Elija el empleado para generar su reporte detallado de períodos y vacaciones.</p>
        <form action="index.php" method="GET">
            <input type="hidden" name="controller" value="reporte">
            <input type="hidden" name="action" value="generarPersona">

            <div class="mb-3">
                <label for="persona_id" class="form-label">Empleado:</label>
                <select id="persona_id" name="persona_id" class="form-select" required>
                    <option value="">-- Seleccione --</option>
                    <?php if (isset($listaPersonas) && is_array($listaPersonas)): ?>
                        <?php foreach ($listaPersonas as $persona): ?>
                            <option value="<?php echo htmlspecialchars($persona['id'] ?? ''); ?>">
                                <?php echo htmlspecialchars($persona['nombre_completo'] ?? 'N/A'); ?>
                            </option>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <option disabled>No se cargaron empleados.</option>
                    <?php endif; ?>
                </select>
            </div>

            <button type="submit" class="btn btn-primary" target="_blank">
                <i class="bi bi-eye-fill me-1"></i> Ver Vista Previa
            </button>
             <a href="index.php?controller=reporte&action=index" class="btn btn-secondary ms-2">Volver</a>
        </form>
    </div>
</div>