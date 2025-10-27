<?php
// views/convenios/gestionar.php
$c = $data['convenio'];
$esVigente = ($c['estado_convenio'] == 'Vigente');
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Gestionar Convenio #<?php echo $c['convenio_id']; ?></h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="index.php?c=practicantes&m=ver&id=<?php echo $c['practicante_id']; ?>" class="btn btn-sm btn-outline-primary me-2" title="Ver Perfil Completo">
            <i class="bi bi-person-fill"></i> Ver Perfil
        </a>
        <a href="index.php?c=convenios" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Volver a Convenios
        </a>
    </div>
</div>

<?php 
// Mensajes
if (isset($_SESSION['mensaje_exito'])) {
    echo '<div class="alert alert-success alert-dismissible fade show" role="alert">' . $_SESSION['mensaje_exito'] . '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
    unset($_SESSION['mensaje_exito']);
}
if (isset($_SESSION['mensaje_error'])) {
    echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">' . $_SESSION['mensaje_error'] . '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
    unset($_SESSION['mensaje_error']);
}
?>

<div class="row">
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header">Practicante</div>
            <div class="card-body">
                <p><strong>DNI:</strong> <?php echo htmlspecialchars($c['dni']); ?></p>
                <p><strong>Nombres:</strong> <?php echo htmlspecialchars($c['apellidos'] . ', ' . $c['nombres']); ?></p>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header">Convenio</div>
            <div class="card-body">
                <p><strong>Tipo:</strong> <?php echo htmlspecialchars($c['tipo_practica']); ?></p>
                <p><strong>Estado:</strong> 
                    <span class="badge <?php echo $esVigente ? 'bg-success' : 'bg-danger'; ?>">
                        <?php echo htmlspecialchars($c['estado_convenio']); ?>
                    </span>
                </p>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <ul class="nav nav-tabs card-header-tabs" id="gestionTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="periodos-tab" data-bs-toggle="tab" data-bs-target="#periodos" type="button"><i class="bi bi-calendar-range"></i> Períodos</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="adendas-tab" data-bs-toggle="tab" data-bs-target="#adendas" type="button"><i class="bi bi-journal-plus"></i> Adendas</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="acciones-tab" data-bs-toggle="tab" data-bs-target="#acciones" type="button" <?php echo $esVigente ? '' : 'disabled'; ?>>
                    <i class="bi bi-gear-fill"></i> Acciones
                </button>
            </li>
        </ul>
    </div>
    <div class="card-body">
        <div class="tab-content" id="gestionTabsContent">
            
            <div class="tab-pane fade show active" id="periodos" role="tabpanel">
                <h5>Historial de Períodos</h5>
                <table class="table table-sm table-bordered">
                    <thead class="table-light">
                        <tr><th>Estado</th><th>Inicio</th><th>Fin</th><th>Área</th><th>Local</th></tr>
                    </thead>
                    <tbody>
                    <?php foreach ($c['periodos'] as $p): ?>
                        <tr class="<?php echo $p['estado_periodo'] == 'Activo' ? 'table-success' : ''; ?>">
                            <td><span class="badge <?php echo $p['estado_periodo'] == 'Activo' ? 'bg-success' : ($p['estado_periodo'] == 'Futuro' ? 'bg-warning text-dark' : 'bg-secondary'); ?>"><?php echo $p['estado_periodo']; ?></span></td>
                            <td><?php echo date("d/m/Y", strtotime($p['fecha_inicio'])); ?></td>
                            <td><?php echo date("d/m/Y", strtotime($p['fecha_fin'])); ?></td>
                            <td><?php echo htmlspecialchars($p['area_nombre'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($p['local_nombre'] ?? 'N/A'); ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <div class="tab-pane fade" id="adendas" role="tabpanel">
                <h5>Historial de Adendas</h5>
                <table class="table table-sm table-bordered">
                    <thead class="table-light">
                        <tr><th>Fecha</th><th>Tipo</th><th>Descripción</th></tr>
                    </thead>
                    <tbody>
                    <?php if (empty($c['adendas'])): ?>
                        <tr><td colspan="3" class="text-center text-muted">Sin adendas registradas.</td></tr>
                    <?php else: ?>
                        <?php foreach ($c['adendas'] as $a): ?>
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

            <div class="tab-pane fade" id="acciones" role="tabpanel">
                <div class="row">
                    <div class="col-md-6 border-end">
                        <h6>Registrar Nuevo Período (Reubicación / Suspensión)</h6>
                        <p><small>Al guardar, el período activo/futuro anterior se marcará como 'Finalizado'.</small></p>
                        <form action="index.php?c=convenios&m=guardarPeriodo" method="POST">
                            <input type="hidden" name="convenio_id" value="<?php echo $c['convenio_id']; ?>">
                            <div class="mb-2">
                                <label class="form-label">Fecha Inicio (Nuevo)</label>
                                <input type="date" name="fecha_inicio" class="form-control form-control-sm" required>
                            </div>
                            <div class="mb-2">
                                <label class="form-label">Fecha Fin (Nuevo)</label>
                                <input type="date" name="fecha_fin" class="form-control form-control-sm" required>
                            </div>
                            <div class="mb-2">
                                <label class="form-label">Nuevo Local</label>
                                <select name="local_id" class="form-select form-select-sm" required>
                                    <?php foreach($data['locales'] as $loc): ?>
                                    <option value="<?php echo $loc['local_id']; ?>"><?php echo htmlspecialchars($loc['nombre']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Nueva Área</label>
                                <select name="area_id" class="form-select form-select-sm" required>
                                     <?php foreach($data['areas'] as $area): ?>
                                    <option value="<?php echo $area['area_id']; ?>"><?php echo htmlspecialchars($area['nombre']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-sm btn-primary">Guardar Nuevo Período</button>
                        </form>
                    </div>
                    
                    <div class="col-md-6">
                        <h6>Registrar Adenda (Justificación)</h6>
                        <p><small>Registra la justificación de un cambio (ej. 'AMPLIACION', 'CORTE').</small></p>
                         <form action="index.php?c=convenios&m=guardarAdenda" method="POST">
                            <input type="hidden" name="convenio_id" value="<?php echo $c['convenio_id']; ?>">
                             <div class="mb-2">
                                <label class="form-label">Fecha Adenda</label>
                                <input type="date" name="fecha_adenda" class="form-control form-control-sm" required>
                            </div>
                             <div class="mb-2">
                                <label class="form-label">Tipo de Acción</label>
                                <select name="tipo_accion" class="form-select form-select-sm" required>
                                    <option value="AMPLIACION">AMPLIACION</option>
                                    <option value="REUBICACION">REUBICACION</option>
                                    <option value="CORTE">CORTE / SUSPENSION</option>
                                    <option value="OTRO">OTRO</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Descripción / Justificación</label>
                                <textarea name="descripcion" class="form-control form-control-sm" rows="3"></textarea>
                            </div>
                            <button type="submit" class="btn btn-sm btn-info text-dark">Guardar Adenda</button>
                        </form>
                        
                        <hr class="my-4">
                        <h6>Finalizar Convenio</h6>
                        <p><small>Esto moverá al practicante a 'Cesado'. Esta acción cierra el convenio.</small></p>
                        <?php 
                        $url_base = "index.php?c=convenios&m=finalizar&convenio_id={$c['convenio_id']}&practicante_id={$c['practicante_id']}";
                        ?>
                        <a href="<?php echo $url_base; ?>&estado=Finalizado" class="btn btn-sm btn-warning" onclick="return confirm('¿Seguro que desea marcar este convenio como FINALIZADO?');">Finalizar Convenio</a>
                        <a href="<?php echo $url_base; ?>&estado=Renuncia" class="btn btn-sm btn-danger" onclick="return confirm('¿Seguro que desea registrar una RENUNCIA?');">Registrar Renuncia</a>
                        <a href="<?php echo $url_base; ?>&estado=Cancelado" class="btn btn-sm btn-dark" onclick="return confirm('¿Seguro que desea CANCELAR este convenio?');">Cancelar Convenio</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>