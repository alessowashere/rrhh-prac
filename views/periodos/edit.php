<?php
// views/periodos/edit.php
// Variables available: $periodo (array), $listaPersonas (array)

// --- Optional: Add a quick check and debug output at the top of the view ---
/*
echo "<pre style='background-color: #e9ecef; border: 1px solid #ced4da; padding: 10px; margin: 5px;'>DEBUGGING (views/periodos/edit.php):\n";
echo "Data received in \$periodo variable:\n";
var_dump($periodo);
echo "\nData received in \$listaPersonas variable:\n";
var_dump($listaPersonas); // Check if the employee list is also arriving
echo "</pre>";
*/
// --- End Optional Debug ---

// --- Default values in case $periodo is somehow incomplete ---
$periodo_id = isset($periodo['id']) ? htmlspecialchars($periodo['id']) : '';
$persona_id_selected = isset($periodo['persona_id']) ? $periodo['persona_id'] : null;
// Handle different date/year formats if needed, assuming YYYY-MM-DD from DB
$periodo_inicio_val = isset($periodo['periodo_inicio']) ? htmlspecialchars($periodo['periodo_inicio']) : '';
$periodo_fin_val = isset($periodo['periodo_fin']) ? htmlspecialchars($periodo['periodo_fin']) : '';
// If your form expects only YYYY, uncomment these lines:
// $periodo_inicio_val = isset($periodo['periodo_inicio']) ? date('Y', strtotime($periodo['periodo_inicio'])) : '';
// $periodo_fin_val = isset($periodo['periodo_fin']) ? date('Y', strtotime($periodo['periodo_fin'])) : '';
$total_dias_val = isset($periodo['total_dias']) ? htmlspecialchars($periodo['total_dias']) : '30';

?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0 text-gray-800">Editar Período</h1>
</div>

<div class="card shadow-sm">
    <div class="card-body">

        <form action="index.php?controller=periodo&action=update" method="POST">

            <input type="hidden" name="id" value="<?php echo $periodo_id; ?>">

            <div class="row g-3">

                <div class="col-md-12">
                    <label for="persona_id" class="form-label">Empleado *</label>
                    <select id="persona_id" name="persona_id" class="form-select" required>
                        <option value="">-- Seleccione un empleado --</option>
                        <?php if (isset($listaPersonas) && is_array($listaPersonas)): ?>
                            <?php foreach ($listaPersonas as $persona): ?>
                                <?php // Use array access for $persona as well ?>
                                <?php $selected = (isset($persona['id']) && $persona['id'] == $persona_id_selected) ? 'selected' : ''; ?>
                                <option value="<?php echo htmlspecialchars($persona['id'] ?? ''); ?>" <?php echo $selected; ?>>
                                    <?php echo htmlspecialchars($persona['nombre_completo'] ?? 'Nombre no disponible'); ?>
                                </option>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <option value="" disabled>Error: No se pudo cargar la lista de empleados.</option>
                        <?php endif; ?>
                    </select>
                </div>

                <div class="col-md-4">
                    <label for="periodo_inicio" class="form-label">Fecha Inicio (YYYY-MM-DD) *</label>
                    <input type="date" class="form-control" id="periodo_inicio" name="periodo_inicio" required
                           value="<?php echo $periodo_inicio_val; ?>">
                </div>

                <div class="col-md-4">
                     <label for="periodo_fin" class="form-label">Fecha Fin (YYYY-MM-DD) *</label>
                    <input type="date" class="form-control" id="periodo_fin" name="periodo_fin" required
                           value="<?php echo $periodo_fin_val; ?>">
                </div>

                <div class="col-md-4">
                    <label for="total_dias" class="form-label">Total Días Derecho *</label>
                    <input type="number" class="form-control" id="total_dias" name="total_dias" required
                           value="<?php echo $total_dias_val; ?>">
                </div>

            </div>

            <hr class="my-4">

            <div class="d-flex justify-content-end">
                <a href="index.php?controller=periodo&action=index" class="btn btn-secondary me-2">
                    Cancelar
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-floppy-fill me-1"></i> Actualizar Período
                </button>
            </div>
        </form>

    </div>
</div>