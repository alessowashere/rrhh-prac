<?php
// views/vacaciones/create.php
// Variable disponible: $listaPersonas

// --- Capturar el ID preseleccionado ---
$persona_id_preselect = $_GET['persona_id_preselect'] ?? null;

// --- NUEVO: Comprobar si estamos en modo modal ---
$esModal = isset($_GET['view']) && $_GET['view'] === 'modal';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0 text-gray-800">Registrar Nueva Vacación</h1>
</div>

<?php if (isset($_GET['status'])): ?>
    <?php
    $status = $_GET['status']; $mensaje = ''; $tipoAlerta = 'danger';
    switch ($status) {
        case 'error_datos': $mensaje = 'Error: Faltan datos obligatorios (*) o son incorrectos.'; break;
        case 'error_periodo': $mensaje = 'Error: No se encontró un período válido para este empleado en las fechas indicadas. Verifique la fecha de inicio o presione "Actualizar Devengados" en la página de Períodos si es un período futuro.'; break;
        case 'error_saldo':
             $req = isset($_GET['req']) ? (int)$_GET['req'] : '?';
             $saldo = isset($_GET['saldo']) ? (int)$_GET['saldo'] : '?';
             $mensaje = "Error: Los días solicitados ({$req}) exceden el saldo disponible ({$saldo}) para el período seleccionado. No se permiten saldos negativos excepto para 'Adelanto'.";
             break;
         case 'error_dias_invalidos': $mensaje = 'Error: La cantidad de días a tomar debe ser mayor a cero.'; break;
        case 'error_guardar': $mensaje = 'Error: No se pudo guardar el registro.'; break;
        case 'error_excepcion': $mensaje = 'Error inesperado del sistema.'; break;
        // --- NUEVO: Mensajes de subida de archivo ---
        case 'error_upload': $mensaje = 'Error al subir el documento adjunto.'; break;
    }
    if ($mensaje) : ?>
    <div class="alert alert-<?php echo $tipoAlerta; ?> alert-dismissible fade show" role="alert">
        <?php echo htmlspecialchars($mensaje); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>
<?php endif; ?>
<div class="card shadow-sm">
    <div class="card-body">

        <form action="index.php?controller=vacacion&action=<?php echo $esModal ? 'storeModal' : 'store'; ?>" method="POST" enctype="multipart/form-data">
            <div class="row g-3">

                <div class="col-md-6">
                    <label for="persona_id" class="form-label">Empleado *</label>
                    <select id="persona_id" name="persona_id" class="form-select" required>
                        <option value="">-- Seleccione un empleado --</option>
                        <?php if (isset($listaPersonas) && is_array($listaPersonas)): ?>
                            <?php foreach ($listaPersonas as $persona): ?>
                                <<?php $selected = (isset($persona['id']) && $persona['id'] == $persona_id_preselect) ? 'selected' : ''; ?>
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
                        <option value="">-- Seleccione un empleado primero --</option>
                        </select>
                     <div class="form-text">Muestra Saldo y Días Totales/Devengados.</div>
                </div>

                <div class="col-md-4">
                    <label for="fecha_inicio" class="form-label">Fecha de Inicio *</label>
                    <input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio" required>
                </div>

                <div class="col-md-4">
                    <label for="fecha_fin" class="form-label">Fecha de Fin *</label>
                    <input type="date" class="form-control" id="fecha_fin" name="fecha_fin" required>
                </div>

                <div class="col-md-4">
                    <label for="dias_tomados" class="form-label">Días a Tomar *</label>
                    <input type="number" class="form-control" id="dias_tomados" name="dias_tomados" required min="1">
                </div>

                <div class="col-md-6">
                    <label for="tipo" class="form-label">Tipo</label>
                    <select id="tipo" name="tipo" class="form-select">
                        <option value="NORMAL" selected>NORMAL</option>
                        <option value="PENDIENTE">PENDIENTE (de período anterior)</option>
                        <option value="ADELANTO">ADELANTO</option>
                    </select>
                </div>

                <div class="col-md-6">
                    <label for="estado" class="form-label">Estado Inicial</label>
                    <select id="estado" name="estado" class="form-select">
                        <option value="PENDIENTE" selected>PENDIENTE</option>
                        <option value="APROBADO">APROBADO</option>
                        <option value="RECHAZADO">RECHAZADO</option>
                    </select>
                </div>

                <div class="col-md-12">
                    <label for="documento" class="form-label">Documento Adjunto (Opcional)</label>
                    <input type="file" class="form-control" id="documento" name="documento">
                    <div class="form-text">Subir un documento PDF, Word o Imagen (ej. Solicitud firmada, boleta, etc.)</div>
                </div>

            </div>

            <hr class="my-4">

            <div class="d-flex justify-content-end">
                <a href="index.php?controller=vacacion&action=index" class="btn btn-secondary me-2">Cancelar</a>
                <button type="submit" class="btn btn-primary"><i class="bi bi-floppy-fill me-1"></i> Guardar</button>
            </div>
        </form>

    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const personaSelect = document.getElementById('persona_id');
    const periodoSelect = document.getElementById('periodo_id');
    const baseUrl = '<?php echo BASE_URL; ?>'; 

    // Obtenemos el ID preseleccionado desde PHP
    const preselectedPersonaId = '<?php echo $persona_id_preselect; ?>';

    /**
     * Función reutilizable para cargar períodos
     */
    function fetchPeriods(personaId) {
        periodoSelect.innerHTML = '<option value="">Cargando...</option>';
        periodoSelect.disabled = true;

        if (!personaId) {
            periodoSelect.innerHTML = '<option value="">-- Seleccione un empleado primero --</option>';
            return;
        }

        fetch(`${baseUrl}index.php?controller=periodo&action=getPeriodosPorPersona&persona_id=${personaId}`)
            .then(response => response.ok ? response.json() : Promise.reject(response))
            .then(data => {
                periodoSelect.innerHTML = ''; 
                if (data.error) {
                    console.error('API Error:', data.error);
                    periodoSelect.innerHTML = `<option value="" disabled>Error: ${data.error}</option>`;
                } else if (!data || data.length === 0) {
                    periodoSelect.innerHTML = '<option value="" disabled>-- No hay períodos para este empleado --</option>';
                } else {
                    periodoSelect.innerHTML = '<option value="">-- Seleccione un período --</option>';
                    data.forEach(periodo => {
                        const option = document.createElement('option');
                        option.value = periodo.id;
                        option.textContent = periodo.text;
                        periodoSelect.appendChild(option);
                    });
                    periodoSelect.disabled = false; 
                }
            })
            .catch(error => {
                console.error('Fetch Error:', error);
                try {
                    error.text().then(text => {
                        console.error("Server Response:", text);
                        periodoSelect.innerHTML = '<option value="" disabled>Error al cargar. Ver consola.</option>';
                    });
                } catch(e) {
                    periodoSelect.innerHTML = '<option value="" disabled>Error al cargar períodos.</option>';
                }
            });
    }

    // 1. Añadimos el listener para cambios manuales
    personaSelect.addEventListener('change', function() {
        fetchPeriods(this.value); // Llama a la función
    });

    // 2. Si hay un empleado preseleccionado, cargamos sus períodos al iniciar
    if (preselectedPersonaId) {
        fetchPeriods(preselectedPersonaId);
    }
});
</script>