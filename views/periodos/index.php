<?php
// views/periodos/index.php
// Variables available: $listaPeriodos, $listaAnios, $periodo_filtro_anio, $errorMessage (optional)
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0 text-gray-800">Gestión de Períodos</h1>
    <div> <a href="index.php?controller=periodo&action=actualizarProximos" class="btn btn-info me-2" title="Verifica/Crea el próximo período para todos y actualiza días devengados">
            <i class="bi bi-arrow-clockwise me-1"></i> Actualizar Devengados
        </a>
        <a href="index.php?controller=periodo&action=create" class="btn btn-primary">
             <i class="bi bi-plus-lg me-1"></i> Crear Nuevo Período
        </a>
    </div>
</div>

<?php if (isset($_GET['status'])): ?>
    <?php
    $status = $_GET['status'];
    $count = isset($_GET['count']) ? (int)$_GET['count'] : 0;
    $mensaje = '';
    $tipoAlerta = 'success'; // Default

    switch ($status) {
        case 'creado': $mensaje = 'Período registrado con éxito.'; break;
        case 'actualizado': $mensaje = 'Período actualizado con éxito.'; break;
        case 'eliminado': $mensaje = 'Período eliminado correctamente.'; break;
        case 'proximos_actualizados':
            $mensaje = "Se verificaron/actualizaron los períodos en progreso para {$count} empleados.";
            $tipoAlerta = 'success';
            break;
        case 'error_actualizando':
            $mensaje = "Se intentó actualizar los períodos en progreso ({$count} procesados), pero ocurrieron errores. Revise los logs si es posible.";
            $tipoAlerta = 'warning';
            break;
        case 'error_datos':
             $mensaje = 'Error: Faltan datos o son incorrectos en el formulario.';
             $tipoAlerta = 'danger';
             break;
        case 'error_guardar':
             $mensaje = 'Error: No se pudo guardar el registro en la base de datos.';
             $tipoAlerta = 'danger';
             break;
        case 'error_eliminar':
             $mensaje = 'Error: No se pudo eliminar el registro de la base de datos.';
             $tipoAlerta = 'danger';
             break;
        case 'error_id':
             $mensaje = 'Error: ID inválido o no proporcionado.';
             $tipoAlerta = 'warning';
             break;
        case 'error_excepcion':
             $mensaje = 'Error inesperado del sistema durante la operación.';
             $tipoAlerta = 'danger';
             break;
        case 'error': // Generic error fallback
            $mensaje = 'Ocurrió un error con la operación.';
            $tipoAlerta = 'danger';
            break;
    }
    ?>
    <div class="alert alert-<?php echo $tipoAlerta; ?> alert-dismissible fade show" role="alert">
        <?php echo htmlspecialchars($mensaje); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>
<?php // Display controller-level errors if passed
 if (isset($errorMessage) && !empty($errorMessage)): ?>
    <div class="alert alert-danger" role="alert">
        <?php echo htmlspecialchars($errorMessage); ?>
    </div>
<?php endif; ?>
<div class="card shadow-sm mb-4">
    <div class="card-body">
        <form action="index.php" method="GET" class="row g-3 align-items-end">
            <input type="hidden" name="controller" value="periodo">
            <input type="hidden" name="action" value="index">

            <div class="col-md-5">
                <label for="anio_inicio_filter" class="form-label">Mostrar Período (Año Inicio):</label>
                <select name="anio_inicio" id="anio_inicio_filter" class="form-select">
                    <option value="">-- Ver Todos --</option>
                    <?php if (isset($listaAnios) && is_array($listaAnios)): ?>
                        <?php foreach ($listaAnios as $anioInfo): ?>
                            <?php
                                $valor = $anioInfo['filter_value'] ?? ''; // YEAR (e.g., 2024)
                                $texto = $anioInfo['display_text'] ?? 'Opción inválida'; // YYYY - YYYY (En Progreso)
                                $current_filter = $periodo_filtro_anio ?? null; // Year filter from controller
                                $selected = ($current_filter == $valor && $valor !== '') ? 'selected' : ''; // Check YEAR match
                            ?>
                            <option value="<?php echo htmlspecialchars($valor); ?>" <?php echo $selected; ?>>
                                <?php echo htmlspecialchars($texto); ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn btn-success">
                    <i class="bi bi-filter me-1"></i>Mostrar
                </button>
                 <a href="index.php?controller=periodo&action=index" class="btn btn-outline-secondary ms-2" title="Mostrar todos los períodos">Limpiar</a>
            </div>
        </form>
    </div>
</div>

<div class="card shadow-sm mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 fw-bold text-primary">Períodos Registrados</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover table-bordered align-middle">
                <thead class="table-light">
                     <tr>
                        <th>Empleado</th>
                        <th>Fecha Ingreso</th>
                        <th>Período</th>
                        <th class="text-center">Días Adquiridos / Devengados</th>
                        <th class="text-center">Días Usados</th>
                        <th class="text-center">Saldo</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php // Check if $listaPeriodos is set and not empty
                     if (!isset($listaPeriodos) || empty($listaPeriodos)): ?>
                        <tr>
                            <td colspan="7" class="text-center">No hay períodos registrados (o para el filtro seleccionado).</td>
                        </tr>
                    <?php else: ?>
                        <?php
                             // Define today once, handle DateTime errors
                             try { $hoy_dt = new DateTime(); $hoy_dt->setTime(0,0,0); }
                             catch (Exception $e) { $hoy_dt = null; /* Log error */ }
                        ?>
                        <?php foreach ($listaPeriodos as $periodo): ?>
                            <?php
                                // Initialize variables for safety
                                $isCurrentEarningPeriod = false;
                                $saldo_display = '-';
                                $saldo_class = 'text-muted';
                                $total_dias = $periodo['total_dias'] ?? 0;
                                $dias_usados = $periodo['dias_usados_calculados'] ?? 0;
                                $periodo_id = $periodo['id'] ?? null; // Get ID for links

                                // Perform date calculations safely
                                if ($hoy_dt && isset($periodo['periodo_inicio']) && isset($periodo['periodo_fin'])) {
                                    try {
                                        $periodo_inicio_dt = new DateTime($periodo['periodo_inicio']);
                                        $periodo_fin_dt = new DateTime($periodo['periodo_fin']);
                                        // A period is the current earning one if today falls within its calculated anniversary range
                                        // or slightly after its start if the update process ran correctly.
                                        $isCurrentEarningPeriod = ($hoy_dt >= $periodo_inicio_dt && $periodo_fin_dt >= $periodo_inicio_dt);

                                        // Calculate saldo only if it's a completed period (>= 30 days earned)
                                        // or if it's the current earning period but somehow has 30 days already
                                        if ($total_dias >= 30 || !$isCurrentEarningPeriod) {
                                             $saldo = $total_dias - $dias_usados;
                                             $saldo_display = htmlspecialchars($saldo);
                                             $saldo_class = $saldo >= 0 ? 'text-success' : 'text-danger';
                                        } else {
                                             // It's the current earning period and has less than 30 days devengados
                                             $saldo_display = '-';
                                             $saldo_class = 'text-muted';
                                        }

                                    } catch (Exception $e) {
                                        error_log("Date error processing period ID {$periodo_id}: " . $e->getMessage());
                                        $saldo_display = 'Error Fecha'; $saldo_class = 'text-danger';
                                    }
                                } else {
                                     $saldo_display = 'N/A'; $saldo_class = 'text-muted';
                                }
                            ?>
                            <tr>
                                <td><?php echo htmlspecialchars($periodo['nombre_completo'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($periodo['fecha_ingreso'] ?? 'N/A'); ?></td>
                                <td><?php echo (isset($periodo['periodo_inicio']) && isset($periodo['periodo_fin'])) ? htmlspecialchars($periodo['periodo_inicio'] . ' al ' . $periodo['periodo_fin']) : 'Fechas Inválidas'; ?></td>
                                <td class="text-center">
                                    <?php echo htmlspecialchars($total_dias); ?>
                                    <?php // Show "En Progreso" if it's the current earning cycle AND hasn't reached 30 days yet
                                     if($isCurrentEarningPeriod && $total_dias < 30): ?>
                                         <span class="badge bg-info text-dark ms-1" title="Días devengados hasta hoy">En Progreso</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center"><?php echo htmlspecialchars($dias_usados); ?></td>
                                <td class="text-center fw-bold">
                                     <span class="<?php echo $saldo_class; ?>" title="<?php echo ($saldo_display == '-') ? 'Saldo no aplicable hasta completar período' : ''; ?>">
                                         <?php echo $saldo_display; ?>
                                     </span>
                                </td>
                                <td class="text-center">
                                <?php if ($periodo_id): // Only show buttons if ID exists ?>
                                    <a href="index.php?controller=periodo&action=edit&id=<?php echo $periodo_id; ?>" class="btn btn-warning btn-sm" title="Editar Período">
                                        <i class="bi bi-pencil-fill"></i>
                                    </a>

                                    <?php
                                        // --- CÓDIGO MODIFICADO PARA EL MODAL ---
                                        // Preparamos los filtros para la URL del Modal
                                        $filtro_nombre = urlencode($periodo['nombre_completo'] ?? '');
                                        $filtro_anio = '';
                                        if (isset($periodo['periodo_inicio'])) {
                                            try { $filtro_anio = date('Y', strtotime($periodo['periodo_inicio'])); } 
                                            catch (Exception $e) { $filtro_anio = ''; }
                                        }
                                        
                                        // ¡NUEVO! Obtenemos el ID de la persona
                                        $persona_id_filtro = $periodo['persona_id'] ?? '';

                                        // Esta es la URL que cargaremos en el iframe
                                        $modal_url = "index.php?controller=vacacion&action=indexModal&search_nombre={$filtro_nombre}&anio_inicio={$filtro_anio}&persona_id_filtro={$persona_id_filtro}";
                                        ?>

                                    <button type="button" 
                                            class="btn btn-info btn-sm btn-ver-vacaciones" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#detalleVacacionesModal"
                                            data-url="<?php echo $modal_url; ?>"
                                            title="Ver Vacaciones de <?php echo htmlspecialchars($periodo['nombre_completo'] ?? ''); ?> (Período <?php echo $filtro_anio; ?>)">
                                        <i class="bi bi-calendar-range-fill"></i>
                                    </button>
                                    <?php // --- FIN DE CÓDIGO MODIFICADO --- ?>

                                    <a href="index.php?controller=periodo&action=delete&id=<?php echo $periodo_id; ?>"
                                    class="btn btn-danger btn-sm"
                                    title="Eliminar Período"
                                    onclick="return confirm('¿Estás seguro de que deseas eliminar este período? Se borrarán también las vacaciones asociadas.');">
                                        <i class="bi bi-trash-fill"></i>
                                    </a>
                                <?php endif; ?>
                            </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
             <small class="text-muted">Para períodos "En Progreso", la columna "Días Adquiridos / Devengados" indica los días generados hasta la fecha actual.</small>
        </div>
    </div>
</div> <div class="modal fade" id="detalleVacacionesModal" tabindex="-1" aria-labelledby="detalleVacacionesModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-scrollable"> <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="detalleVacacionesModalLabel">Detalle de Vacaciones</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-0">
        <iframe src="about:blank" 
                style="width: 100%; height: 60vh; border: none;" 
                allowfullscreen>
        </iframe>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var detalleModal = document.getElementById('detalleVacacionesModal');
    var iframe = detalleModal.querySelector('iframe');

    // Escuchar cuando el modal está a punto de mostrarse
    detalleModal.addEventListener('show.bs.modal', function (event) {
        // 'event.relatedTarget' es el botón que disparó el modal
        var button = event.relatedTarget; 
        
        // Obtener la URL del atributo 'data-url' del botón
        var url = button.getAttribute('data-url');
        
        // Asignar esa URL al 'src' del iframe
        if (iframe && url) {
            iframe.setAttribute('src', url);
        }
        
        // Opcional: Cambiar el título del modal
        var titulo = button.getAttribute('title');
        var modalTitle = detalleModal.querySelector('.modal-title');
        if (modalTitle) {
            modalTitle.textContent = titulo;
        }
    });

    // Escuchar cuando el modal se oculta
    detalleModal.addEventListener('hidden.bs.modal', function () {
        // Limpiar el iframe para detener cualquier proceso (videos, etc.)
        // y para que cargue de nuevo si se abre otra persona
        if (iframe) {
            iframe.setAttribute('src', 'about:blank');
        }
    });
});
</script>