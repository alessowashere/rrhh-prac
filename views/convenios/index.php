<?php
// views/convenios/index.php
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><?php echo htmlspecialchars($data['titulo'] ?? 'Convenios'); ?></h1>
</div>

<?php 
// Mostrar mensajes de éxito o error
if (isset($_SESSION['mensaje_exito'])) {
    echo '<div class="alert alert-success alert-dismissible fade show" role="alert">' . htmlspecialchars($_SESSION['mensaje_exito']) . '
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
    unset($_SESSION['mensaje_exito']);
}
if (isset($_SESSION['mensaje_error'])) {
    echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">' . htmlspecialchars($_SESSION['mensaje_error']) . '
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
    unset($_SESSION['mensaje_error']);
}
?>

<div class="card shadow-sm mb-4">
    <div class="card-header">
        <ul class="nav nav-tabs card-header-tabs" id="convenioTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="pendientes-tab" data-bs-toggle="tab" data-bs-target="#pendientes" type="button" role="tab">
                    <i class="bi bi-inbox-fill text-primary"></i> Pendientes de Crear 
                    <span class="badge bg-primary rounded-pill ms-1"><?php echo count($data['pendientes']); ?></span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="vigentes-tab" data-bs-toggle="tab" data-bs-target="#vigentes" type="button" role="tab">
                    <i class="bi bi-file-earmark-text-fill text-success"></i> Convenios Vigentes
                     <span class="badge bg-success rounded-pill ms-1"><?php echo count($data['vigentes']); ?></span>
                </button>
            </li>
        </ul>
    </div>
    
    <div class="card-body">
        <div class="tab-content" id="convenioTabsContent">
            
            <div class="tab-pane fade show active" id="pendientes" role="tabpanel">
                <h5 class="mb-3 text-primary"><i class="bi bi-person-plus-fill"></i> Candidatos Aceptados (Pendientes de Convenio)</h5>
                <div class="table-responsive">
                    <table class="table table-sm table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>DNI</th>
                                <th>Apellidos y Nombres</th>
                                <th>Escuela</th>
                                <th>Fecha Aceptado</th>
                                <th class="text-center">Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($data['pendientes'])): ?>
                                <tr><td colspan="5" class="text-center text-muted fst-italic py-3">No hay candidatos pendientes de convenio.</td></tr>
                            <?php else: ?>
                                <?php foreach ($data['pendientes'] as $p): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($p['dni']); ?></td>
                                    <td><?php echo htmlspecialchars($p['apellidos'] . ', ' . $p['nombres']); ?></td>
                                    <td><?php echo htmlspecialchars($p['escuela_nombre']); ?></td>
                                    <td><?php echo date("d/m/Y", strtotime($p['fecha_postulacion'])); ?></td>
                                    <td class="text-center">
                                        <a href="index.php?c=convenios&m=crear&proceso_id=<?php echo $p['proceso_id']; ?>&practicante_id=<?php echo $p['practicante_id']; ?>" class="btn btn-sm btn-primary" title="Crear convenio para este practicante">
                                            <i class="bi bi-plus-circle-fill"></i> Crear Convenio
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="tab-pane fade" id="vigentes" role="tabpanel">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0 text-success"><i class="bi bi-list-check"></i> Listado de Convenios Vigentes</h5>
                    <div class="ms-3" style="width: 300px;">
                        <input type="text" id="buscador-vigentes" class="form-control form-control-sm" placeholder="Filtrar por nombre, área, tipo...">
                    </div>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-sm table-hover align-middle table-striped">
                         <thead class="table-light">
                            <tr>
                                <th>Apellidos y Nombres</th>
                                <th>Tipo</th>
                                <th>Área Actual</th>
                                <th>Inicio Periodo</th>
                                <th>Fin Periodo</th>
                                <th class="text-center">Adendas</th>
                                <th class="text-center">Firma</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="tabla-vigentes">
                             <?php if (empty($data['vigentes'])): ?>
                                <tr id="fila-no-vigentes">
                                    <td colspan="8" class="text-center text-muted fst-italic py-3">No hay convenios vigentes registrados.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($data['vigentes'] as $c): ?>
                                <tr class="<?php echo $c['estado_firma'] == 'Pendiente' ? 'table-warning' : ''; ?>">
                                    <td><?php echo htmlspecialchars($c['apellidos'] . ', ' . $c['nombres']); ?></td>
                                    <td><span class="badge bg-secondary"><?php echo htmlspecialchars($c['tipo_practica']); ?></span></td>
                                    <td><?php echo htmlspecialchars($c['area_actual'] ?? 'N/A'); ?></td>
                                    <td><?php echo $c['fecha_inicio_actual'] ? date("d/m/Y", strtotime($c['fecha_inicio_actual'])) : 'N/A'; ?></td>
                                    <td><?php echo $c['fecha_fin_actual'] ? date("d/m/Y", strtotime($c['fecha_fin_actual'])) : 'N/A'; ?></td>
                                    <td class="text-center">
                                        <?php if ($c['num_adendas'] > 0): ?>
                                            <span class="badge bg-info text-dark position-relative" title="<?php echo $c['num_adendas']; ?> adendas registradas (ampliación, corte, reubicación)">
                                                <i class="bi bi-journal-plus"></i> <?php echo $c['num_adendas']; ?>
                                             </span>
                                        <?php else: ?>
                                             <span class="text-muted small">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                         <?php if ($c['estado_firma'] == 'Firmado'): ?>
                                            <span class="badge bg-success" title="Convenio principal firmado y subido"><i class="bi bi-check-circle-fill"></i> Firmado</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger" title="Falta subir el convenio principal firmado"><i class="bi bi-clock-fill"></i> Pendiente</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm">
                                            <a href="index.php?c=convenios&m=gestionar&id=<?php echo $c['convenio_id']; ?>" class="btn btn-success" title="Gestionar convenio (firmas, adendas, cese)">
                                                <i class="bi bi-pencil-square"></i> Gestionar
                                            </a>
                                            <a href="index.php?c=practicantes&m=ver&id=<?php echo $c['practicante_id']; ?>" class="btn btn-outline-secondary" title="Ver perfil completo del practicante">
                                                <i class="bi bi-person-fill"></i> Perfil
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                             <tr id="fila-no-coincidencias" style="display: none;">
                                <td colspan="8" class="text-center text-muted fst-italic py-3">No se encontraron convenios que coincidan con el filtro.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const buscador = document.getElementById('buscador-vigentes');
    const tabla = document.getElementById('tabla-vigentes');
    const filas = tabla ? tabla.getElementsByTagName('tr') : [];
    const filaNoCoincidencias = document.getElementById('fila-no-coincidencias');
    const filaNoDatos = document.getElementById('fila-no-vigentes');

    function filtrarTablaVigentes() {
        if (!buscador || !tabla) return; // Salir si no existen los elementos

        let textoBusqueda = buscador.value.toLowerCase().trim();
        let filasVisibles = 0;

        for (let fila of filas) {
            // Ignorar las filas de mensajes
            if (fila.id === 'fila-no-coincidencias' || fila.id === 'fila-no-vigentes') continue;

            let textoFila = fila.innerText.toLowerCase();
            
            if (textoBusqueda === '' || textoFila.includes(textoBusqueda)) {
                fila.style.display = ""; 
                filasVisibles++;
            } else {
                fila.style.display = "none";
            }
        }
        
        // --- Lógica mejorada para mostrar mensajes ---
        const hayFilasOriginales = !filaNoDatos || filaNoDatos.style.display === 'none'; // ¿Había datos originalmente?

        // Mostrar "No hay coincidencias"
        if (filaNoCoincidencias) {
            filaNoCoincidencias.style.display = (textoBusqueda !== '' && filasVisibles === 0 && hayFilasOriginales) ? "" : "none";
        }
        
        // Mostrar "No hay convenios vigentes"
        if (filaNoDatos) {
            filaNoDatos.style.display = (textoBusqueda === '' && filasVisibles === 0) ? "" : "none";
        }
    }

    // Evento para el buscador
    if(buscador) {
        buscador.addEventListener('input', filtrarTablaVigentes); // Usar 'input' para respuesta inmediata
    }
    
    // Ejecutar filtro al inicio por si acaso
    filtrarTablaVigentes(); 
});
</script>