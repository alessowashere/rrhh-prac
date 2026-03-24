<?php
// views/reportes/index.php
// Variables: $empleados, $listaAnios, $listaAreas (del ReporteController)

// --- INICIO BLOQUE AÑADIDO ---
// Crear un bloque HTML reutilizable para el filtro de período
$filtroPeriodoHtml = '<div class="form-group mb-2">
    <label for="anio_inicio_filter_[ID]" class="form-label-sm">Período (Opcional):</label>
    <select name="anio_inicio" id="anio_inicio_filter_[ID]" class="form-select form-select-sm">
        <option value="">-- Todos los Períodos --</option>';
if (isset($listaAnios) && is_array($listaAnios)) {
    foreach ($listaAnios as $anioInfo) {
        $filtroPeriodoHtml .= '<option value="' . htmlspecialchars($anioInfo['filter_value'] ?? '') . '">' 
                            . htmlspecialchars($anioInfo['display_text'] ?? 'Opción inválida') . '</option>';
    }
} else {
    $filtroPeriodoHtml .= '<option value="" disabled>No se cargaron períodos.</option>';
}
$filtroPeriodoHtml .= '</select></div>';
// --- FIN BLOQUE AÑADIDO ---
?>

<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Módulo de Reportería</h1>
    <p class="mb-4">Seleccione el tipo de reporte que desea generar. Los resultados se mostrarán en una vista previa lista para imprimir.</p>

    <div class="row">

        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-body d-flex flex-column">
                    <h5 class="card-title text-primary fw-bold">
                        <i class="bi bi-globe-americas me-2"></i>Reporte General
                    </h5>
                    <p class="card-text">
                        Listado de todos los empleados activos con sus saldos de vacaciones actuales.
                    </p>
                    
                    <form action="index.php?controller=Reporte&action=generar" method="POST" target="_blank" class="mt-auto">
                        <input type="hidden" name="tipo_reporte" value="general">
                        <button type="submit" class="btn btn-primary w-100">
                            Generar Reporte General
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-body d-flex flex-column">
                    <h5 class="card-title text-info fw-bold">
                        <i class="bi bi-person-fill me-2"></i>Reporte por Persona
                    </h5>
                    <p class="card-text">
                        Historial detallado de períodos y vacaciones de un solo empleado.
                    </p>
                    
                    <form action="index.php?controller=Reporte&action=generar" method="POST" target="_blank" class="mt-auto">
                        <input type="hidden" name="tipo_reporte" value="por_persona">
                        
                        <div class="form-group mb-2">
                            <label for="empleado_id_card" class="form-label-sm">Empleado:</label>
                            <select name="empleado_id" id="empleado_id_card" class="form-select form-select-sm" required>
                                <option value="">-- Seleccione un empleado --</option>
                                <?php foreach ($empleados as $emp): ?>
                                    <option value="<?php echo $emp['id']; ?>">
                                        <?php echo htmlspecialchars($emp['nombre_completo']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label for="fecha_inicio_card" class="form-label-sm">Desde (Opcional):</label>
                                <input type="date" name="fecha_inicio" id="fecha_inicio_card" class="form-control form-control-sm">
                            </div>
                            <div class="col-6">
                                <label for="fecha_fin_card" class="form-label-sm">Hasta (Opcional):</label>
                                <input type="date" name="fecha_fin" id="fecha_fin_card" class="form-control form-control-sm">
                            </div>
                        </div>

                        <?php echo str_replace('[ID]', 'persona', $filtroPeriodoHtml); ?>
                        <button type="submit" class="btn btn-info w-100 mt-2">
                            Generar Reporte de Persona
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-body d-flex flex-column">
                    <h5 class="card-title text-warning fw-bold">
                        <i class="bi bi-calendar-check-fill me-2"></i>Reporte por Período
                    </h5>
                    <p class="card-text">
                        Vacaciones de todos los empleados tomadas dentro de un período de derecho.
                    </p>
                    
                    <form action="index.php?controller=Reporte&action=generar" method="POST" target="_blank" class="mt-auto">
                        <input type="hidden" name="tipo_reporte" value="por_periodo">
                        
                        <div class="form-group mb-3">
                            <label for="anio_inicio_card" class="form-label-sm">Período (Año de Inicio):</label>
                            <select name="anio_inicio" id="anio_inicio_card" class="form-select form-select-sm" required>
                                <option value="">-- Seleccione un período --</option>
                                <?php if (isset($listaAnios) && is_array($listaAnios)): ?>
                                    <?php foreach ($listaAnios as $anioInfo): ?>
                                        <option value="<?php echo htmlspecialchars($anioInfo['filter_value'] ?? ''); ?>">
                                            <?php echo htmlspecialchars($anioInfo['display_text'] ?? 'Opción inválida'); ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <option value="" disabled>No se cargaron períodos.</option>
                                <?php endif; ?>
                            </select>
                        </div>
                        
                        <button type="submit" class="btn btn-warning w-100 text-dark">
                            Generar Reporte de Período
                        </button>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-body d-flex flex-column">
                    <h5 class="card-title text-danger fw-bold">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>Reporte de Saldos
                    </h5>
                    <p class="card-text">
                        Listado de empleados con saldos positivos (pendientes) o negativos (deudas).
                    </p>
                    
                    <form action="index.php?controller=Reporte&action=generar" method="POST" target="_blank" class="mt-auto">
                        <input type="hidden" name="tipo_reporte" value="saldos">

                        <?php echo str_replace('[ID]', 'saldos', $filtroPeriodoHtml); ?>
                        <button type="submit" class="btn btn-danger w-100 mt-2">
                            Generar Reporte de Saldos
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-body d-flex flex-column">

                    <h5 class="card-title fw-bold" style="color: #6f42c1;"> 
                        <i class="bi bi-diagram-3-fill me-2"></i>Reporte por Unidad
                    </h5>
                    <p class="card-text">
                        Saldos o vacaciones de todos los empleados de una unidad específica.
                    </p>

                    <form action="index.php?controller=Reporte&action=generar" method="POST" target="_blank" class="mt-auto">
                        <input type="hidden" name="tipo_reporte" value="por_area">

                        <div class="form-group mb-2">
                            <label for="area_card" class="form-label-sm">Unidad / Área (Dependencia):</label>

                            <select name="area" id="area_card" class="form-select form-select-sm" required>
                                <option value="">-- Seleccione unidad --</option>
                                <?php // $listaAreas viene del ReporteController
                                if (isset($listaAreas) && is_array($listaAreas)): ?>
                                    <?php foreach ($listaAreas as $area): ?>
                                        <option value="<?php echo htmlspecialchars($area); ?>">
                                            <?php echo htmlspecialchars($area); ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <option value="" disabled>No se cargaron áreas.</option>
                                <?php endif; ?>
                            </select>
                        </div>

                        <?php echo str_replace('[ID]', 'area', $filtroPeriodoHtml); ?>

                        <div class="form-group mb-3">
                            <label for="tipo_info_area" class="form-label-sm">Tipo de Información:</label>
                            <select name="tipo_info_area" id="tipo_info_area" class="form-select form-select-sm" required>
                                <option value="saldos" selected>Ver Saldos (Resumen)</option>
                                <option value="programados">Ver Vacaciones (Detalle)</option>
                            </select>
                        </div>

                        <button type="submit" class="btn w-100" style="background-color: #6f42c1; color: white;">
                            Generar Reporte de Unidad
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-body d-flex flex-column">
                    <h5 class="card-title text-success fw-bold">
                        <i class="bi bi-diagram-2-fill me-2"></i>Reporte General por Unidad
                    </h5>
                    <p class="card-text">
                        Listado de saldos de todos los empleados, ordenado por unidad/dependencia.
                    </p>
                    
                    <form action="index.php?controller=Reporte&action=generar" method="POST" target="_blank" class="mt-auto">
                    <input type="hidden" name="tipo_reporte" value="general_por_area">

                    <?php echo str_replace('[ID]', 'general_area', $filtroPeriodoHtml); ?>

                    <div class="form-group mb-3">
                        <label for="tipo_info_general_area" class="form-label-sm">Tipo de Información:</label>
                        <select name="tipo_info_general_area" id="tipo_info_general_area" class="form-select form-select-sm" required>
                            <option value="saldos" selected>Ver Saldos (Resumen)</option>
                            <option value="programados">Ver Vacaciones (Detalle)</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-success w-100">
                        Generar Reporte
                    </button>
                </form>
                </div>
            </div>
        </div>

    </div> </div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const forms = document.querySelectorAll('form[target="_blank"]');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            // La validación 'required' de HTML5 se encargará.
        });
    });
});
</script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Validación de formularios (ya existía)
    const forms = document.querySelectorAll('form[target="_blank"]');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            // La validación 'required' de HTML5 se encargará.
        });
    });

    // --- INICIO: SCRIPT DE SELECT2 CORREGIDO ---
    $(document).ready(function() {

        // 1. Inicializar Select2 para Empleados
        $('#empleado_id_card').select2({
            theme: 'bootstrap-5',
            placeholder: '-- Seleccione un empleado --'
        });

        // 2. Inicializar Select2 para Áreas
        // (Esta es la corrección clave para el buscador)
        $('#area_card').select2({
            theme: 'bootstrap-5',
            placeholder: '-- Seleccione unidad --'
        });

        // 3. Inicializar todos los selectores de período
        // (La clase 'select2-periodo' la pusimos en el HTML reutilizable)
        $('.select2-periodo').select2({
            theme: 'bootstrap-5',
            placeholder: '-- Todos los Períodos --'
        });

    });
    // --- FIN: SCRIPT DE SELECT2 CORREGIDO ---
});
</script>