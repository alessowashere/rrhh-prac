<?php
// views/practicantes/ver.php
$detalle = $data['info']['detalle'];
$convenios = $data['info']['convenios'];
$documentos = $data['info']['documentos'];
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><?php echo htmlspecialchars($detalle['apellidos'] . ', ' . $detalle['nombres']); ?></h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="index.php?c=practicantes&m=editar&id=<?php echo $detalle['practicante_id']; ?>" class="btn btn-sm btn-outline-warning me-2">
            <i class="bi bi-pencil"></i> Editar Datos
        </a>
        <a href="index.php?c=practicantes" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Volver al Listado
        </a>
    </div>
</div>

<?php 
// Mensajes de sesión
if (isset($_SESSION['mensaje_exito'])) {
    echo '<div class="alert alert-success alert-dismissible fade show" role="alert">' . $_SESSION['mensaje_exito'] . '
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
    unset($_SESSION['mensaje_exito']);
}
?>

<div class="row">
    <div class="col-lg-4">
        <div class="card mb-4">
            <div class="card-header">
                <i class="bi bi-person-badge"></i> Datos Personales
            </div>
            <div class="card-body">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between">
                        <strong>DNI:</strong>
                        <span><?php echo htmlspecialchars($detalle['dni']); ?></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <strong>Email:</strong>
                        <span><?php echo htmlspecialchars($detalle['email'] ?? 'N/A'); ?></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <strong>Teléfono:</strong>
                        <span><?php echo htmlspecialchars($detalle['telefono'] ?? 'N/A'); ?></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <strong>Fecha Nac.:</strong>
                        <span><?php echo $detalle['fecha_nacimiento'] ? date("d/m/Y", strtotime($detalle['fecha_nacimiento'])) : 'N/A'; ?></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <strong>Promedio:</strong>
                        <span><?php echo htmlspecialchars($detalle['promedio_general'] ?? 'N/A'); ?></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <strong>Estado General:</strong>
                        <span class="badge <?php echo $detalle['estado_general'] == 'Activo' ? 'bg-success' : 'bg-danger'; ?>">
                            <?php echo htmlspecialchars($detalle['estado_general']); ?>
                        </span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
    
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <ul class="nav nav-tabs card-header-tabs" id="historialTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="convenios-tab" data-bs-toggle="tab" data-bs-target="#convenios-panel" type="button" role="tab">
                            <i class="bi bi-file-earmark-text"></i> Convenios (<?php echo count($convenios); ?>)
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="documentos-tab" data-bs-toggle="tab" data-bs-target="#documentos-panel" type="button" role="tab">
                            <i class="bi bi-folder2-open"></i> Documentos (<?php echo count($documentos); ?>)
                        </button>
                    </li>
                </ul>
            </div>
            
            <div class="card-body">
                <div class="tab-content" id="historialTabsContent">
                    
                    <div class="tab-pane fade show active" id="convenios-panel" role="tabpanel">
                        <?php if (empty($convenios)): ?>
                            <p class="text-center text-muted">No se han registrado convenios para este practicante.</p>
                        <?php else: ?>
                            <div class="accordion" id="accordionConvenios">
                                <?php foreach ($convenios as $i => $conv): $c_info = $conv['info']; ?>
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="heading-<?php echo $c_info['convenio_id']; ?>">
                                        <button class="accordion-button <?php echo $i > 0 ? 'collapsed' : ''; ?>" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-<?php echo $c_info['convenio_id']; ?>">
                                            <strong>Convenio #<?php echo $c_info['convenio_id']; ?></strong>
                                            <span class="ms-2 badge bg-info text-dark me-2"><?php echo htmlspecialchars($c_info['tipo_practica']); ?></span>
                                            <span class="badge bg-secondary"><?php echo htmlspecialchars($c_info['estado_convenio']); ?></span>
                                        </button>
                                    </h2>
                                    <div id="collapse-<?php echo $c_info['convenio_id']; ?>" class="accordion-collapse collapse <?php echo $i == 0 ? 'show' : ''; ?>" data-bs-parent="#accordionConvenios">
                                        <div class="accordion-body">
                                            
                                            <h6><i class="bi bi-calendar-range"></i> Períodos del Convenio</h6>
                                            <table class="table table-sm table-bordered">
                                                <thead class="table-light">
                                                    <tr><th>Inicio</th><th>Fin</th><th>Área</th><th>Local</th><th>Estado</th></tr>
                                                </thead>
                                                <tbody>
                                                <?php foreach ($conv['periodos'] as $p): ?>
                                                    <tr>
                                                        <td><?php echo date("d/m/Y", strtotime($p['fecha_inicio'])); ?></td>
                                                        <td><?php echo date("d/m/Y", strtotime($p['fecha_fin'])); ?></td>
                                                        <td><?php echo htmlspecialchars($p['area_nombre'] ?? 'N/A'); ?></td>
                                                        <td><?php echo htmlspecialchars($p['local_nombre'] ?? 'N/A'); ?></td>
                                                        <td><?php echo htmlspecialchars($p['estado_periodo']); ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                                </tbody>
                                            </table>

                                            <h6 class="mt-3"><i class="bi bi-journal-plus"></i> Adendas del Convenio</h6>
                                            <table class="table table-sm table-bordered">
                                                <thead class="table-light">
                                                    <tr><th>Fecha</th><th>Tipo Acción</th><th>Descripción</th></tr>
                                                </thead>
                                                <tbody>
                                                <?php if (empty($conv['adendas'])): ?>
                                                    <tr><td colspan="3" class="text-center text-muted">Sin adendas</td></tr>
                                                <?php else: ?>
                                                    <?php foreach ($conv['adendas'] as $a): ?>
                                                    <tr>
                                                        <td><?php echo date("d/m/Y", strtotime($a['fecha_adenda'])); ?></td>
                                                        <td><?php echo htmlspecialchars($a['tipo_accion']); ?></td>
                                                        <td><?php echo htmlspecialchars($a['descripcion']); ?></td>
                                                    </tr>
                                                    <?php endforeach; ?>
                                                <?php endif; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="tab-pane fade" id="documentos-panel" role="tabpanel">
                        <?php if (empty($documentos)): ?>
                            <p class="text-center text-muted">No hay documentos cargados para este practicante.</p>
                        <?php else: ?>
                            <div class="list-group">
                                <?php foreach ($documentos as $doc): ?>
                                <a href="<?php echo htmlspecialchars($doc['url_archivo']); ?>" target="_blank" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                    <div>
                                        <i class="bi bi-file-earmark-arrow-down me-2"></i>
                                        <strong><?php echo htmlspecialchars($doc['tipo_documento']); ?></strong>
                                        <small class="d-block text-muted">Cargado el: <?php echo date("d/m/Y H:i", strtotime($doc['fecha_carga'])); ?></small>
                                    </div>
                                    <i class="bi bi-box-arrow-up-right"></i>
                                </a>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                </div>
            </div>
        </div>
    </div>
</div>