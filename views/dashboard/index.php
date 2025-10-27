<?php
// views/dashboard/index.php
// $data contiene ahora todos los contadores y listas
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Panel de Control</h1>
</div>

<div class="row">
    
    <div class="col-lg-8">

        <div class="row">
            <div class="col-md-4">
                <div class="card text-white bg-primary mb-3 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="card-title mb-0">ACTIVOS</h5>
                                <p class="card-text fs-1 fw-bold"><?php echo htmlspecialchars($data['practicantes_activos']); ?></p>
                            </div>
                            <i class="bi bi-people-fill" style="font-size: 3.5rem; opacity: 0.5;"></i>
                        </div>
                    </div>
                    <a href="index.php?c=practicantes" class="card-footer text-white text-decoration-none">
                        Ver listado <i class="bi bi-arrow-right-circle float-end"></i>
                    </a>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-dark bg-warning mb-3 shadow-sm">
                    <div class="card-body">
                         <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="card-title mb-0">EN PROCESO</h5>
                                <p class="card-text fs-1 fw-bold"><?php echo htmlspecialchars($data['candidatos_proceso']); ?></p>
                            </div>
                            <i class="bi bi-person-plus-fill" style="font-size: 3.5rem; opacity: 0.5;"></i>
                        </div>
                    </div>
                     <a href="index.php?c=reclutamiento" class="card-footer text-dark text-decoration-none">
                        Gestionar <i class="bi bi-arrow-right-circle float-end"></i>
                    </a>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-white bg-danger mb-3 shadow-sm">
                    <div class="card-body">
                         <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="card-title mb-0">POR VENCER</h5>
                                <p class="card-text fs-1 fw-bold"><?php echo htmlspecialchars($data['convenios_por_vencer']); ?></p>
                            </div>
                            <i class="bi bi-calendar-x" style="font-size: 3.5rem; opacity: 0.5;"></i>
                        </div>
                    </div>
                     <a href="#alertas-vencimiento" class="card-footer text-white text-decoration-none">
                        Revisar <i class="bi bi-arrow-right-circle float-end"></i>
                    </a>
                </div>
            </div>
        </div>
        <div class="card shadow-sm mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-inbox-fill me-2"></i> Bandeja de Entrada (Pendientes de Convenio)</h5>
            </div>
            <div class="card-body">
                <?php if (empty($data['pendientes_convenio'])): ?>
                    <p class="text-center text-muted mb-0">¡Excelente! No hay candidatos pendientes.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover table-sm align-middle mb-0">
                            <thead>
                                <tr><th>Candidato</th><th>Escuela</th><th>Acción</th></tr>
                            </thead>
                            <tbody>
                            <?php foreach($data['pendientes_convenio'] as $p): ?>
                                <tr>
                                    <td>
                                        <div class="fw-bold"><?php echo htmlspecialchars($p['apellidos'] . ', ' . $p['nombres']); ?></div>
                                        <small class="text-muted">DNI: <?php echo htmlspecialchars($p['dni']); ?></small>
                                    </td>
                                    <td><?php echo htmlspecialchars($p['escuela_nombre']); ?></td>
                                    <td>
                                        <a href="index.php?c=convenios&m=crear&proceso_id=<?php echo $p['proceso_id']; ?>&practicante_id=<?php echo $p['practicante_id']; ?>" class="btn btn-sm btn-primary">
                                            <i class="bi bi-plus-circle"></i> Crear Convenio
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="card shadow-sm mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-clock-history me-2"></i> Actividad Reciente (Últimos Registrados)</h5>
            </div>
            <ul class="list-group list-group-flush">
                <?php if (empty($data['ultimos_registrados'])): ?>
                    <li class="list-group-item text-center text-muted">No hay actividad reciente.</li>
                <?php else: ?>
                    <?php foreach($data['ultimos_registrados'] as $u): ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <div>
                            <a href="index.php?c=practicantes&m=ver&id=<?php echo $u['practicante_id']; ?>" class="text-decoration-none fw-bold">
                                <?php echo htmlspecialchars($u['apellidos'] . ', ' . $u['nombres']); ?>
                            </a>
                            <small class="d-block text-muted">
                                Asignado a <?php echo htmlspecialchars($u['area_nombre'] ?? 'N/A'); ?>
                                <span class="badge bg-info-subtle text-info-emphasis rounded-pill ms-1"><?php echo htmlspecialchars($u['tipo_practica']); ?></span>
                            </small>
                        </div>
                        <a href="index.php?c=convenios&m=gestionar&id=<?php echo $u['convenio_id']; ?>" class="btn btn-sm btn-outline-secondary">
                            Gestionar
                        </a>
                    </li>
                    <?php endforeach; ?>
                <?php endif; ?>
            </ul>
        </div>

    </div>
    <div class="col-lg-4">

        <div class="card shadow-sm mb-4" id="alertas-vencimiento">
            <div class="card-header bg-danger text-white">
                <h5 class="mb-0"><i class="bi bi-exclamation-triangle-fill me-2"></i> Alertas (Próximos Vencimientos)</h5>
            </div>
            <ul class="list-group list-group-flush">
                 <?php if (empty($data['lista_por_vencer'])): ?>
                    <li class="list-group-item text-center text-muted">No hay vencimientos en los próximos 30 días.</li>
                <?php else: ?>
                    <?php 
                    $today = new DateTime();
                    foreach($data['lista_por_vencer'] as $v): 
                        $fecha_fin = new DateTime($v['fecha_fin']);
                        $dias_faltantes = $today->diff($fecha_fin)->days;
                    ?>
                    <li class="list-group-item list-group-item-action">
                         <a href="index.php?c=practicantes&m=ver&id=<?php echo $v['practicante_id']; ?>" class="text-decoration-none d-flex justify-content-between align-items-center">
                            <div>
                                <div class="fw-bold text-dark"><?php echo htmlspecialchars($v['apellidos'] . ', ' . $v['nombres']); ?></div>
                                <small class="text-danger">Vence en <?php echo $dias_faltantes; ?> días (<?php echo $fecha_fin->format('d/m/Y'); ?>)</small>
                            </div>
                            <i class="bi bi-chevron-right text-muted"></i>
                        </a>
                    </li>
                    <?php endforeach; ?>
                <?php endif; ?>
            </ul>
        </div>
        
        <div class="card shadow-sm">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-lightning-fill me-2"></i> Accesos Rápidos</h5>
            </div>
            <div class="list-group list-group-flush">
                <a href="index.php?c=reclutamiento&m=nuevo" class="list-group-item list-group-item-action d-flex align-items-center py-3">
                    <i class="bi bi-person-plus fs-4 me-3 text-primary"></i>
                    <div>
                        <strong>Registrar Nuevo Candidato</strong>
                        <small class="d-block text-muted">Iniciar un nuevo proceso de selección.</small>
                    </div>
                </a>
                <a href="index.php?c=practicantes" class="list-group-item list-group-item-action d-flex align-items-center py-3">
                    <i class="bi bi-search fs-4 me-3 text-primary"></i>
                    <div>
                        <strong>Buscar Practicante</strong>
                        <small class="d-block text-muted">Consultar historial y convenios.</small>
                    </div>
                </a>
                 <a href="index.php?c=convenios" class="list-group-item list-group-item-action d-flex align-items-center py-3">
                    <i class="bi bi-file-earmark-text fs-4 me-3 text-primary"></i>
                    <div>
                        <strong>Gestionar Convenios</strong>
                        <small class="d-block text-muted">Ver convenios vigentes y pendientes.</small>
                    </div>
                </a>
            </div>
        </div>
    </div>
    </div>