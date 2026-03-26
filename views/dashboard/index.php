<?php
// views/dashboard/index.php
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="bi bi-speedometer2 text-primary"></i> Panel de Control Activo</h1>
</div>

<?php if (isset($_SESSION['mensaje_info'])): ?>
    <div class="alert alert-info alert-dismissible fade show shadow-sm border-start border-4 border-info" role="alert">
        <i class="bi bi-robot me-2"></i> <strong>Automatización:</strong> <?php echo $_SESSION['mensaje_info']; unset($_SESSION['mensaje_info']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="card h-100 border-0 shadow-sm bg-primary text-white">
            <div class="card-body">
                <h6 class="text-uppercase fw-bold opacity-75 small">Activos</h6>
                <div class="d-flex align-items-center justify-content-between">
                    <span class="display-5 fw-bold"><?php echo $data['kpis']['activos']; ?></span>
                    <i class="bi bi-people-fill fs-1 opacity-25"></i>
                </div>
            </div>
            <a href="index.php?c=practicantes" class="card-footer bg-transparent border-0 text-white small">Ver listado <i class="bi bi-arrow-right"></i></a>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card h-100 border-0 shadow-sm bg-danger text-white">
            <div class="card-body">
                <h6 class="text-uppercase fw-bold opacity-75 small">Críticos (7 días)</h6>
                <div class="d-flex align-items-center justify-content-between">
                    <span class="display-5 fw-bold"><?php echo $data['kpis']['criticos']; ?></span>
                    <i class="bi bi-alarm-fill fs-1 opacity-25"></i>
                </div>
            </div>
            <div class="card-footer bg-transparent border-0 text-white small">Requieren atención inmediata</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card h-100 border-0 shadow-sm bg-warning text-dark">
            <div class="card-body">
                <h6 class="text-uppercase fw-bold opacity-75 small">Por Vencer (30 días)</h6>
                <div class="d-flex align-items-center justify-content-between">
                    <span class="display-5 fw-bold"><?php echo $data['kpis']['por_vencer']; ?></span>
                    <i class="bi bi-calendar-event fs-1 opacity-25"></i>
                </div>
            </div>
            <div class="card-footer bg-transparent border-0 text-dark small">Pendientes de renovación</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card h-100 border-0 shadow-sm bg-success text-white">
            <div class="card-body">
                <h6 class="text-uppercase fw-bold opacity-75 small">En Selección</h6>
                <div class="d-flex align-items-center justify-content-between">
                    <span class="display-5 fw-bold"><?php echo $data['kpis']['en_proceso']; ?></span>
                    <i class="bi bi-person-plus-fill fs-1 opacity-25"></i>
                </div>
            </div>
            <a href="index.php?c=reclutamiento" class="card-footer bg-transparent border-0 text-white small">Gestionar candidatos <i class="bi bi-arrow-right"></i></a>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3 border-0">
                <h5 class="mb-0 fw-bold text-dark"><i class="bi bi-exclamation-triangle-fill text-danger me-2"></i> Próximos Vencimientos</h5>
            </div>
            <div class="card-body pt-0">
                <?php if (empty($data['lista_por_vencer'])): ?>
                    <p class="text-center text-muted py-3 mb-0">No hay vencimientos en los próximos 30 días.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr><th>Practicante</th><th>Vencimiento</th><th>Acción</th></tr>
                            </thead>
                            <tbody>
                                <?php foreach($data['lista_por_vencer'] as $v): 
                                    $diff = (new DateTime())->diff(new DateTime($v['fecha_fin']))->days;
                                    $color = ($diff <= 7) ? 'danger' : 'warning';
                                ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($v['apellidos'] . ', ' . $v['nombres']); ?></strong></td>
                                        <td><span class="badge bg-<?php echo $color; ?>-subtle text-<?php echo $color; ?> p-2">Vence en <?php echo $diff; ?> días (<?php echo date('d/m/Y', strtotime($v['fecha_fin'])); ?>)</span></td>
                                        <td><a href="index.php?c=convenios&m=gestionar&id=<?php echo $v['convenio_id']; ?>" class="btn btn-sm btn-outline-primary">Gestionar</a></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3 border-0">
                <h5 class="mb-0 fw-bold text-dark"><i class="bi bi-plus-circle-fill text-primary me-2"></i> Pendientes de Convenio</h5>
            </div>
            <div class="card-body pt-0">
                <?php if (empty($data['pendientes_convenio'])): ?>
                    <p class="text-center text-muted py-3 mb-0">No hay candidatos aceptados pendientes de convenio.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr><th>Candidato</th><th>Escuela</th><th>Acción</th></tr>
                            </thead>
                            <tbody>
                                <?php foreach($data['pendientes_convenio'] as $p): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($p['apellidos'] . ', ' . $p['nombres']); ?></td>
                                        <td><?php echo htmlspecialchars($p['escuela_nombre']); ?></td>
                                        <td><a href="index.php?c=convenios&m=crear&proceso_id=<?php echo $p['proceso_id']; ?>&practicante_id=<?php echo $p['practicante_id']; ?>" class="btn btn-sm btn-primary">Crear Convenio</a></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card border-0 shadow-sm mb-4 bg-dark text-white">
            <div class="card-body">
                <h5 class="fw-bold mb-3"><i class="bi bi-lightning-fill text-warning me-2"></i> Accesos Rápidos</h5>
                <div class="d-grid gap-2">
                    <a href="index.php?c=reclutamiento&m=nuevo" class="btn btn-primary text-start"><i class="bi bi-person-plus-fill me-2"></i> Registrar Candidato</a>
                    <a href="index.php?c=practicantes" class="btn btn-outline-light text-start"><i class="bi bi-search me-2"></i> Buscar Practicante</a>
                    <a href="index.php?c=convenios" class="btn btn-outline-light text-start"><i class="bi bi-file-earmark-text me-2"></i> Gestión de Convenios</a>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3 border-0">
                <h5 class="mb-0 fw-bold text-dark"><i class="bi bi-clock-history me-2"></i> Actividad Reciente</h5>
            </div>
            <div class="list-group list-group-flush">
                <?php if (empty($data['ultimos_registrados'])): ?>
                    <div class="p-3 text-center text-muted">Sin actividad reciente.</div>
                <?php else: ?>
                    <?php foreach($data['ultimos_registrados'] as $u): ?>
                        <div class="list-group-item border-0 px-3">
                            <div class="d-flex w-100 justify-content-between">
                                <h6 class="mb-1 fw-bold"><?php echo htmlspecialchars($u['apellidos'] . ', ' . $u['nombres']); ?></h6>
                            </div>
                            <small class="text-muted">
                                <?php echo htmlspecialchars($u['tipo_practica'] ?? 'N/A'); ?> - 
                                <?php echo htmlspecialchars($u['area_nombre'] ?? 'Área no asignada'); ?>
                            </small>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>