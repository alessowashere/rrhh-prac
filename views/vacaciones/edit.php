<?php
// views/vacaciones/edit.php
// Variables: $vacacion, $listaPersonas

// --- NUEVO: Comprobar si estamos en modo modal ---
$esModal = isset($_GET['view']) && $_GET['view'] === 'modal';

$vacacion_id = isset($vacacion['id']) ? htmlspecialchars($vacacion['id']) : '';
// ... (el resto de variables) ...
?>
<?php
// views/vacaciones/edit.php
// Variables: $vacacion, $listaPersonas
$vacacion_id = isset($vacacion['id']) ? htmlspecialchars($vacacion['id']) : '';
$persona_id_selected = isset($vacacion['persona_id']) ? $vacacion['persona_id'] : null;
$periodo_id_selected = isset($vacacion['periodo_id']) ? $vacacion['periodo_id'] : null;
$fecha_inicio_val = isset($vacacion['fecha_inicio']) ? htmlspecialchars($vacacion['fecha_inicio']) : '';
$fecha_fin_val = isset($vacacion['fecha_fin']) ? htmlspecialchars($vacacion['fecha_fin']) : '';
$dias_tomados_val = isset($vacacion['dias_tomados']) ? htmlspecialchars($vacacion['dias_tomados']) : '0';
$tipo_selected = isset($vacacion['tipo']) ? $vacacion['tipo'] : 'NORMAL';
$estado_selected = isset($vacacion['estado']) ? $vacacion['estado'] : 'PENDIENTE';
// --- NUEVO: variable para doc existente ---
$documento_existente = $vacacion['documento_adjunto'] ?? null;
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0 text-gray-800">Editar Registro de Vacación</h1>
</div>

<?php if (isset($_GET['status'])): ?>
    <?php
    $status = $_GET['status']; $mensaje = ''; $tipoAlerta = 'danger';
     switch ($status) {
        case 'error_datos': $mensaje = 'Error: Faltan datos obligatorios (*) o son incorrectos.'; break;
        case 'error_periodo': $mensaje = 'Error: No se encontró un período válido para este empleado en las fechas indicadas.'; break;
        case 'error_saldo':
             $req = isset($_GET['req']) ? (int)$_GET['req'] : '?';
             $saldo = isset($_GET['saldo']) ? (int)$_GET['saldo'] : '?';
             $mensaje = "Error: Los días solicitados ({$req}) exceden el saldo disponible ({$saldo}, ajustado por esta edición) para el período seleccionado. No se permiten saldos negativos excepto para 'Adelanto'.";
             break;
         case 'error_dias_invalidos': $mensaje = 'Error: La cantidad de días a tomar debe ser mayor a cero.'; break;
        case 'error_guardar': $mensaje = 'Error: No se pudo guardar el registro.'; break;
        case 'error_excepcion': $mensaje = 'Error inesperado del sistema.'; break;
        // --- NUEVO: Mensajes de subida de archivo ---
        case 'error_upload': $mensaje = 'Error al subir el nuevo documento adjunto.'; break;
    }
    if ($mensaje) : ?>
    <div class="alert alert-<?php echo $tipoAlerta; ?> alert-dismissible fade show" role="alert"><?php echo htmlspecialchars($mensaje); ?><button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>
    <?php endif; ?>
<?php endif; ?>
<div class="card shadow-sm">
    <div class="card-body">

        <form action="index.php?controller=vacacion&action=<?php echo $esModal ? 'updateModal' : 'update'; ?>" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?php echo $vacacion_id; ?>">
            <div class="row g-3">

                <div class="col-md-6">
                    <label for="persona_id" class="form-label">Empleado *</label>
                    <select id="persona_id" name="persona_id" class="form-select" required>
                        <option value="">-- Seleccione un empleado --</option>
                        <?php if (isset($listaPersonas) && is_array($listaPersonas)): ?>
                            <?php foreach ($listaPersonas as $persona): ?>
                                <?php $selected = (isset($persona['id']) && $persona['id'] == $persona_id_selected) ? 'selected' : ''; ?>
                                <option value="<?php echo htmlspecialchars($persona['id'] ?? ''); ?>" <?php echo $selected; ?>>
                                    <?php echo htmlspecialchars($persona['nombre_completo'] ?? 'N/A'); ?>
                                </option>
                            <?php endforeach; ?>
                         <?php else: ?>
                             <option value="" disabled>Error: No se cargaron empleados.</option>
                        <?php endif; ?>
                    </select>
                </div>

                <div class="col-md-6">
                    <label for="periodo_id" class="form-label">Período a Afectar *</label>
                    <select id="periodo_id" name="periodo_id" class="form-select" required disabled>
                        <option value="">-- Cargando períodos... --</option>
                        </select>
                     <div class="form-text">Muestra Saldo y Días Totales/Devengados.</div>
                </div>

                <div class="col-md-4"><label for="fecha_inicio" class="form-label">Fecha Inicio *</label><input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio" required value="<?php echo $fecha_inicio_val; ?>"></div>
                <div class="col-md-4"><label for="fecha_fin" class="form-label">Fecha Fin *</label><input type="date" class="form-control" id="fecha_fin" name="fecha_fin" required value="<?php echo $fecha_fin_val; ?>"></div>
                <div class="col-md-4"><label for="dias_tomados" class="form-label">Días *</label><input type="number" class="form-control" id="dias_tomados" name="dias_tomados" required min="1" value="<?php echo $dias_tomados_val; ?>"></div>
                <div class="col-md-6"><label for="tipo" class="form-label">Tipo</label><select id="tipo" name="tipo" class="form-select"><option value="NORMAL" <?php echo ($tipo_selected == 'NORMAL') ? 'selected' : ''; ?>>NORMAL</option><option value="PENDIENTE" <?php echo ($tipo_selected == 'PENDIENTE') ? 'selected' : ''; ?>>PENDIENTE</option><option value="ADELANTO" <?php echo ($tipo_selected == 'ADELANTO') ? 'selected' : ''; ?>>ADELANTO</option></select></div>
                <div class="col-md-6"><label for="estado" class="form-label">Estado</label><select id="estado" name="estado" class="form-select"><option value="PENDIENTE" <?php echo ($estado_selected == 'PENDIENTE') ? 'selected' : ''; ?>>PENDIENTE</option><option value="APROBADO" <?php echo ($estado_selected == 'APROBADO') ? 'selected' : ''; ?>>APROBADO</option><option value="RECHAZADO" <?php echo ($estado_selected == 'RECHAZADO') ? 'selected' : ''; ?>>RECHAZADO</option><option value="GOZADO" <?php echo ($estado_selected == 'GOZADO') ? 'selected' : ''; ?>>GOZADO</option></select></div>
                
                <div class="col-md-12">
                    <label for="documento" class="form-label">Documento Adjunto</label>
                    
                    <?php if ($documento_existente): ?>
                        <div class="mb-2">
                            <a href="<?php echo htmlspecialchars($documento_existente); ?>" target="_blank" class="btn btn-info btn-sm">
                                <i class="bi bi-file-earmark-arrow-down-fill me-1"></i> Ver Documento Actual
                            </a>
                            <span class="text-muted ms-2">(<?php echo htmlspecialchars(basename($documento_existente)); ?>)</span>
                        </div>
                    <?php endif; ?>
                    
                    <input type="file" class="form-control" id="documento" name="documento">
                    <div class="form-text">
                        <?php echo $documento_existente ? 'Subir un archivo nuevo reemplazará al actual.' : 'Subir un documento PDF, Word o Imagen.'; ?>
                    </div>
                </div>

            </div>
            <hr class="my-4">
            <div class="d-flex justify-content-end"><a href="index.php?controller=vacacion&action=index" class="btn btn-secondary me-2">Cancelar</a><button type="submit" class="btn btn-primary"><i class="bi bi-floppy-fill me-1"></i> Actualizar</button></div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const personaSelect = document.getElementById('persona_id');
    const periodoSelect = document.getElementById('periodo_id');
    const baseUrl = '<?php echo BASE_URL; ?>';
    // Use PHP variables defined at the top of the file
    const initialPersonaId = '<?php echo $persona_id_selected ?? ''; ?>';
    const initialPeriodoId = '<?php echo $periodo_id_selected ?? ''; ?>';

    function loadPeriods(personaId, selectedPeriodoId) {
        periodoSelect.innerHTML = '<option value="">Cargando...</option>';
        periodoSelect.disabled = true;

        if (!personaId) {
            periodoSelect.innerHTML = '<option value="">-- Seleccione un empleado --</option>';
            return;
        }

        fetch(`${baseUrl}index.php?controller=periodo&action=getPeriodosPorPersona&persona_id=${personaId}`)
            .then(response => response.ok ? response.json() : Promise.reject(response))
            .then(data => {
                periodoSelect.innerHTML = ''; // Clear

                if (data.error) {
                    console.error('API Error:', data.error);
                    periodoSelect.innerHTML = `<option value="" disabled>Error: ${data.error}</option>`;
                } else if (!data || data.length === 0) { // Check if data is null or empty array
                    periodoSelect.innerHTML = '<option value="" disabled>-- No hay períodos --</option>';
                } else {
                    periodoSelect.innerHTML = '<option value="">-- Seleccione un período --</option>';
                    data.forEach(periodo => {
                        const option = document.createElement('option');
                        option.value = periodo.id;
                        option.textContent = periodo.text;
                        // Pre-select the correct period if editing
                        if (periodo.id == selectedPeriodoId) { // Use == for potential type difference
                            option.selected = true;
                        }
                        periodoSelect.appendChild(option);
                    });
                    periodoSelect.disabled = false; // Enable
                }
            })
            .catch(error => {
                console.error('Fetch Error:', error);
                 error.text().then(text => { console.error("Server Response:", text); periodoSelect.innerHTML = '<option value="" disabled>Error. Ver consola.</option>';
                 }).catch(() => { periodoSelect.innerHTML = '<option value="" disabled>Error al cargar.</option>'; });
            });
    }

    // Load periods for the initially selected person
    if (initialPersonaId) {
        loadPeriods(initialPersonaId, initialPeriodoId);
    } else {
         periodoSelect.innerHTML = '<option value="">-- Seleccione un empleado --</option>';
    }

    // Add event listener for future changes
    personaSelect.addEventListener('change', function() {
        loadPeriods(this.value, null); // Don't pre-select when user changes person
    });
});
</script>