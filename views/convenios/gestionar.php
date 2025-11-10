<?php
// views/convenios/gestionar.php
$c = $data['convenio']; // Datos del convenio
$esVigente = ($c['estado_convenio'] == 'Vigente');
$periodo_activo = $data['periodo_activo']; // Datos del período activo actual (o null si no hay)
$fecha_fin_actual = $data['fecha_fin_actual']; // Fecha fin YYYY-MM-DD del período activo (o hoy)

// --- NUEVA LÓGICA PARA FECHAS Y ADENDAS ---
$periodos = $c['periodos'] ?? [];
$num_adendas = count($c['adendas'] ?? []);
$fecha_inicio_real = 'N/A';
$fecha_fin_real = 'N/A';

if (!empty($periodos)) {
    // Los períodos vienen ordenados por fecha_inicio DESC,
    // así que el último del array es el primero cronológicamente
    $primer_periodo = end($periodos);
    $fecha_inicio_real = date("d/m/Y", strtotime($primer_periodo['fecha_inicio']));
    
    // Y el primero del array es el que tiene la fecha de inicio más reciente
    $ultimo_periodo = reset($periodos);
    $fecha_fin_real = date("d/m/Y", strtotime($ultimo_periodo['fecha_fin']));
}
// --- FIN NUEVA LÓGICA ---
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><?php echo htmlspecialchars($data['titulo']); ?></h1>
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
        <div class="card mb-4 shadow-sm">
            <div class="card-header"><i class="bi bi-person-badge"></i> Practicante</div>
            <div class="card-body">
                <p class="mb-1"><strong>DNI:</strong> <?php echo htmlspecialchars($c['dni']); ?></p>
                <p class="mb-0"><strong>Nombres:</strong> <?php echo htmlspecialchars($c['apellidos'] . ', ' . $c['nombres']); ?></p>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card mb-4 shadow-sm">
            <div class="card-header"><i class="bi bi-file-earmark-text"></i> Convenio</div>
            <div class="card-body">
                <p class="mb-1"><strong>Tipo:</strong> <?php echo htmlspecialchars($c['tipo_practica']); ?></p>
                <p class="mb-1"><strong>Inicio (Original):</strong> <?php echo $fecha_inicio_real; ?></p>
                <p class="mb-1"><strong>Fin (Vigente):</strong> <?php echo $fecha_fin_real; ?></p>
                <p class="mb-1"><strong>Adendas:</strong> 
                    <span class="badge bg-info text-dark"><?php echo $num_adendas; ?></span>
                </p>
                <p class="mb-0"><strong>Estado:</strong> 
                    <span class="badge <?php echo $esVigente ? 'bg-success' : 'bg-secondary'; ?>">
                        <?php echo htmlspecialchars($c['estado_convenio']); ?>
                    </span>
                </p>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-header">
        <ul class="nav nav-tabs card-header-tabs" id="gestionTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="periodos-tab" data-bs-toggle="tab" data-bs-target="#periodos" type="button"><i class="bi bi-calendar-range"></i> Historial de Períodos</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="firmas-tab" data-bs-toggle="tab" data-bs-target="#firmas" type="button"><i class="bi bi-pen-fill"></i> Firmas y Documentos</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="acciones-tab" data-bs-toggle="tab" data-bs-target="#acciones" type="button" <?php echo ($esVigente && $c['estado_firma'] == 'Firmado') ? '' : 'disabled'; ?> title="<?php echo ($c['estado_firma'] != 'Firmado') ? 'Debe subir el convenio firmado primero' : ''; ?>">
                    <i class="bi bi-gear-fill"></i> Acciones (Adendas / Cese)
                </button>
            </li>
        </ul>
    </div>
    <div class="card-body">
        <div class="tab-content" id="gestionTabsContent">
            
            <div class="tab-pane fade show active" id="periodos" role="tabpanel">
                <h5>Historial de Períodos</h5>
                <p class="text-muted small">Muestra la bitácora de fechas, áreas y locales del practicante. El período "Activo" es el vigente.</p>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered table-striped">
                        <thead class="table-light">
                            <tr><th>Estado</th><th>Inicio</th><th>Fin</th><th>Área</th><th>Local</th></tr>
                        </thead>
                        <tbody>
                        <?php if (empty($c['periodos'])): ?>
                             <tr><td colspan="5" class="text-center text-muted fst-italic py-3">No hay períodos registrados.</td></tr>
                        <?php else: ?>
                            <?php foreach ($c['periodos'] as $p): ?>
                                <tr class="<?php echo $p['estado_periodo'] == 'Activo' ? 'table-success fw-bold' : ''; ?>">
                                    <td><span class="badge <?php echo $p['estado_periodo'] == 'Activo' ? 'bg-success' : ($p['estado_periodo'] == 'Futuro' ? 'bg-warning text-dark' : 'bg-secondary'); ?>"><?php echo $p['estado_periodo']; ?></span></td>
                                    <td><?php echo date("d/m/Y", strtotime($p['fecha_inicio'])); ?></td>
                                    <td><?php echo date("d/m/Y", strtotime($p['fecha_fin'])); ?></td>
                                    <td><?php echo htmlspecialchars($p['area_nombre'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($p['local_nombre'] ?? 'N/A'); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="tab-pane fade" id="firmas" role="tabpanel">
                <h5>Gestión de Firmas y Documentos</h5>
                <p class="text-muted small">Aquí se suben los PDFs firmados que validan el convenio y sus modificaciones (adendas).</p>
                
                <table class="table table-bordered table-sm align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Documento</th>
                            <th>Estado / Fecha</th>
                            <th>Acción / Ver PDF</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>
                                <i class="bi bi-file-earmark-text-fill text-primary"></i> <strong>Convenio Principal</strong>
                            </td>
                            <td>
                                <?php if ($c['estado_firma'] == 'Firmado'): ?>
                                    <span class="badge bg-success"><i class="bi bi-check-circle-fill"></i> Firmado</span>
                                <?php else: ?>
                                    <span class="badge bg-danger"><i class="bi bi-clock-fill"></i> Pendiente de Firma</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($c['estado_firma'] == 'Firmado'): ?>
                                    <a href="<?php echo htmlspecialchars($c['documento_convenio_url']); ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-eye-fill"></i> Ver Convenio
                                    </a>
                                <?php else: ?>
                                    <form action="index.php?c=convenios&m=subirConvenioFirmado" method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
                                        <input type="hidden" name="convenio_id" value="<?php echo $c['convenio_id']; ?>">
                                        <input type="hidden" name="practicante_id" value="<?php echo $c['practicante_id']; ?>">
                                        <div class="input-group input-group-sm">
                                            <input type="file" name="documento_convenio" class="form-control" accept=".pdf" required>
                                            <button class="btn btn-success" type="submit" title="Subir Convenio Firmado">
                                               <i class="bi bi-upload"></i> Subir
                                            </button>
                                        </div>
                                         <div class="invalid-feedback">Seleccione un archivo PDF.</div>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                        
                        <?php if (empty($c['adendas'])): ?>
                            <tr>
                                <td colspan="3" class="text-center text-muted fst-italic py-3">No hay adendas registradas.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($c['adendas'] as $a): ?>
                            <tr>
                                <td>
                                    <i class="bi bi-journal-plus text-secondary"></i> <strong>Adenda: <?php echo htmlspecialchars($a['tipo_accion']); ?></strong>
                                </td>
                                <td>
                                   <small class="text-muted"><?php echo date("d/m/Y", strtotime($a['fecha_adenda'])); ?></small>
                                </td>
                                <td>
                                    <?php if (!empty($a['documento_adenda_url'])): ?>
                                        <a href="<?php echo htmlspecialchars($a['documento_adenda_url']); ?>" target="_blank" class="btn btn-sm btn-outline-secondary">
                                            <i class="bi bi-eye-fill"></i> Ver Sustento
                                        </a>
                                        <button class="btn btn-sm btn-outline-success" disabled title="Subir Adenda Firmada (requiere desarrollo)">
                                            <i class="bi bi-upload"></i> Subir Firmada
                                        </button>
                                    <?php else: ?>
                                        <span class="text-muted fst-italic small">(Sin documento adjunto)</span>
                                    <?php endif; ?>
                                     <?php if (!empty($a['descripcion'])): ?>
                                        <p class="small text-muted mb-0 mt-1" title="Descripción/Justificación"><em><?php echo htmlspecialchars($a['descripcion']); ?></em></p>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="tab-pane fade" id="acciones" role="tabpanel">
                
                <div class="alert alert-warning small <?php echo ($esVigente && $c['estado_firma'] == 'Firmado') ? '' : 'd-none'; ?>">
                   <i class="bi bi-exclamation-triangle-fill"></i> Recuerde que cualquier modificación (adenda) o cese requiere subir el documento PDF que lo sustenta (solicitud, memo, etc.).
                </div>
                 <div class="alert alert-danger small <?php echo ($c['estado_firma'] != 'Firmado') ? '' : 'd-none'; ?>">
                   <i class="bi bi-exclamation-octagon-fill"></i> Las acciones están deshabilitadas hasta que suba el Convenio Principal firmado en la pestaña "Firmas y Documentos".
                </div>

                <div class="mb-4 col-md-8 col-lg-6">
                    <label for="tipo_accion_selector" class="form-label fw-bold">Seleccione la Acción a Registrar:</label>
                    <select id="tipo_accion_selector" class="form-select" <?php echo ($esVigente && $c['estado_firma'] == 'Firmado') ? '' : 'disabled'; ?>>
                        <option value="">-- Seleccione --</option>
                        <option value="ampliacion">1. Registrar Adenda de Ampliación</option>
                        <option value="reubicacion">2. Registrar Adenda de Reubicación</option>
                        <option value="corte">3. Registrar Adenda de Corte / Suspensión</option>
                        <option value="cese">4. Registrar Cese (Renuncia / Cancelación)</option>
                    </select>
                </div>
                
                <div id="formularios-accion">

                    <div id="form-ampliacion" class="accion-form p-3 border rounded bg-light shadow-sm" style="display:none;">
                        <h6 class="text-primary mb-3"><i class="bi bi-calendar-plus"></i> Registrar Adenda de Ampliación</h6>
                        <form action="index.php?c=convenios&m=ampliar" method="POST" class="needs-validation" enctype="multipart/form-data" novalidate>
                            <input type="hidden" name="convenio_id" value="<?php echo $c['convenio_id']; ?>">
                            <input type="hidden" name="practicante_id" value="<?php echo $c['practicante_id']; ?>">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Fecha Fin Actual</label>
                                    <input type="date" class="form-control form-control-sm bg-light" id="ampl_fecha_fin_actual" value="<?php echo $fecha_fin_actual; ?>" readonly>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Nueva Fecha de Fin <span class="text-danger">*</span></label>
                                    <input type="date" name="nueva_fecha_fin" class="form-control form-control-sm" id="ampl_fecha_fin_nueva" required>
                                    <div class="btn-group btn-group-sm mt-1" role="group">
                                        <button type="button" class="btn btn-outline-secondary btn-calc-fecha" data-meses="1" data-inicio="ampl_fecha_fin_actual" data-fin="ampl_fecha_fin_nueva">+1 Mes</button>
                                        <button type="button" class="btn btn-outline-secondary btn-calc-fecha" data-meses="3" data-inicio="ampl_fecha_fin_actual" data-fin="ampl_fecha_fin_nueva">+3 Meses</button>
                                        <button type="button" class="btn btn-outline-secondary btn-calc-fecha" data-meses="6" data-inicio="ampl_fecha_fin_actual" data-fin="ampl_fecha_fin_nueva">+6 Meses</button>
                                    </div>
                                    <div class="invalid-feedback">Ingrese la nueva fecha de fin.</div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Fecha de Adenda <span class="text-danger">*</span></label>
                                    <input type="date" name="fecha_adenda" class="form-control form-control-sm" value="<?php echo date('Y-m-d'); ?>" required>
                                     <div class="invalid-feedback">Ingrese la fecha del documento de adenda.</div>
                                </div>
                                 <div class="col-md-6">
                                    <label class="form-label">Doc. de Sustento (Solicitud/Memo) <span class="text-danger">*</span></label>
                                    <input type="file" name="documento_adenda_amp" class="form-control form-control-sm" accept=".pdf" required>
                                     <div class="invalid-feedback">Suba el PDF de la solicitud.</div>
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label">Descripción / Justificación (Opcional)</label>
                                    <textarea name="descripcion_amp" class="form-control form-control-sm" rows="2" placeholder="Ej: Ampliación según documento adjunto..."></textarea>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary mt-3"><i class="bi bi-save"></i> Guardar Ampliación</button>
                        </form>
                    </div>

                    <div id="form-reubicacion" class="accion-form p-3 border rounded bg-light shadow-sm" style="display:none;">
                        <h6 class="text-info mb-3"><i class="bi bi-geo-alt-fill"></i> Registrar Adenda de Reubicación</h6>
                        <p class="text-muted small">Esto finalizará el período actual (el día anterior al inicio del nuevo) y creará uno nuevo con la nueva ubicación/área.</p>
                        <form action="index.php?c=convenios&m=guardarPeriodo" method="POST" class="needs-validation" enctype="multipart/form-data" novalidate>
                            <input type="hidden" name="convenio_id" value="<?php echo $c['convenio_id']; ?>">
                            <input type="hidden" name="practicante_id" value="<?php echo $c['practicante_id']; ?>">
                            <input type="hidden" name="tipo_accion" value="REUBICACION">
                            
                            <div class="row g-3">
                                <p class="mb-1 fw-bold small">Datos del Nuevo Período:</p>
                                <div class="col-md-6">
                                    <label class="form-label">Fecha Inicio (Nuevo Período) <span class="text-danger">*</span></label>
                                    <input type="date" name="fecha_inicio" class="form-control form-control-sm" id="nuevo_fecha_inicio_reub" value="<?php echo date('Y-m-d', strtotime('+1 day')); ?>" required>
                                    <small class="text-muted">El período activo actual se cerrará el día anterior a esta fecha.</small>
                                    <div class="invalid-feedback">Ingrese la fecha de inicio de la reubicación.</div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Fecha Fin (Nuevo Período) <span class="text-danger">*</span></label>
                                    <input type="date" name="fecha_fin" class="form-control form-control-sm" id="nuevo_fecha_fin_reub" value="<?php echo $fecha_fin_actual; // Mantiene la fecha fin original ?>" required>
                                    <div class="btn-group btn-group-sm mt-1" role="group">
                                        <button type="button" class="btn btn-outline-secondary btn-calc-fecha" data-meses="4" data-inicio="nuevo_fecha_inicio_reub" data-fin="nuevo_fecha_fin_reub">4 Meses</button>
                                        <button type="button" class="btn btn-outline-secondary btn-calc-fecha" data-meses="6" data-inicio="nuevo_fecha_inicio_reub" data-fin="nuevo_fecha_fin_reub">6 Meses</button>
                                    </div>
                                    <div class="invalid-feedback">Ingrese la fecha de fin del convenio tras la reubicación.</div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Nuevo Local <span class="text-danger">*</span></label>
                                    <select name="local_id" class="form-select form-select-sm" required>
                                        <option value="">Seleccione...</option>
                                        <?php foreach($data['locales'] as $loc): ?>
                                        <option value="<?php echo $loc['local_id']; ?>" <?php echo ($periodo_activo && $loc['local_id'] == $periodo_activo['local_id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($loc['nombre']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                     <div class="invalid-feedback">Seleccione el nuevo local.</div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Nueva Área <span class="text-danger">*</span></label>
                                    <select name="area_id" class="form-select form-select-sm" required>
                                        <option value="">Seleccione...</option>
                                         <?php foreach($data['areas'] as $area): ?>
                                        <option value="<?php echo $area['area_id']; ?>" <?php echo ($periodo_activo && $area['area_id'] == $periodo_activo['area_id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($area['nombre']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="invalid-feedback">Seleccione la nueva área.</div>
                                </div>
                                
                                <hr class="my-3">
                                <p class="mb-1 fw-bold small">Documento de Sustento (Adenda):</p>
                                
                                <div class="col-md-6">
                                    <label class="form-label">Fecha de Adenda <span class="text-danger">*</span></label>
                                    <input type="date" name="fecha_adenda" class="form-control form-control-sm" value="<?php echo date('Y-m-d'); ?>" required>
                                    <div class="invalid-feedback">Ingrese la fecha del documento.</div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Doc. de Sustento (Solicitud/Memo) <span class="text-danger">*</span></label>
                                    <input type="file" name="documento_adenda_reub" class="form-control form-control-sm" accept=".pdf" required>
                                     <div class="invalid-feedback">Suba el PDF de la solicitud.</div>
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label">Descripción / Justificación (Opcional)</label>
                                    <textarea name="descripcion_reub" class="form-control form-control-sm" rows="2" placeholder="Ej: Reubicado a Contabilidad según Memo Nº..."></textarea>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-info text-dark mt-3"><i class="bi bi-save"></i> Guardar Reubicación</button>
                        </form>
                    </div>
                    
                    <div id="form-corte" class="accion-form p-3 border rounded bg-light shadow-sm" style="display:none;">
                        <h6 class="text-warning mb-3"><i class="bi bi-calendar-minus"></i> Registrar Adenda de Corte / Suspensión</h6>
                        <p class="text-muted small">Esto cerrará el período actual en la fecha que indiques y creará un nuevo período futuro con la fecha de retorno y la nueva fecha fin.</p>
                        <form action="index.php?c=convenios&m=guardarPeriodo" method="POST" class="needs-validation" enctype="multipart/form-data" novalidate>
                            <input type="hidden" name="convenio_id" value="<?php echo $c['convenio_id']; ?>">
                            <input type="hidden" name="practicante_id" value="<?php echo $c['practicante_id']; ?>">
                            <input type="hidden" name="tipo_accion" value="CORTE">
                            <input type="hidden" name="local_id" value="<?php echo $periodo_activo['local_id'] ?? $data['locales'][0]['local_id']; ?>">
                            <input type="hidden" name="area_id" value="<?php echo $periodo_activo['area_id'] ?? $data['areas'][0]['area_id']; ?>">
                            
                            <div class="row g-3">
                                <p class="mb-1 fw-bold small">Datos del Nuevo Período (Tras el Corte/Suspensión):</p>
                                <div class="col-md-6">
                                    <label class="form-label">Fecha de Suspensión (Último día laborado) <span class="text-danger">*</span></label>
                                    <input type="date" name="fecha_suspension" class="form-control form-control-sm" id="corte_fecha_suspension" value="<?php echo date('Y-m-d'); ?>" required>
                                    <small class="text-muted">El período activo se cerrará en esta fecha.</small>
                                    <div class="invalid-feedback">Indique el último día laborado.</div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Fecha de Retorno (Primer día de vuelta) <span class="text-danger">*</span></label>
                                    <input type="date" name="fecha_retorno" class="form-control form-control-sm" id="corte_fecha_retorno" required>
                                    <div class="invalid-feedback">Indique la fecha de retorno.</div>
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label">Nueva Fecha Fin del Convenio (Ajustada) <span class="text-danger">*</span></label>
                                    <input type="date" name="nueva_fecha_fin" class="form-control form-control-sm" id="corte_nueva_fecha_fin" value="<?php echo $fecha_fin_actual; // Por defecto, mantiene la fecha fin original ?>" required>
                                    <small class="text-muted">Debe ser la nueva fecha final total del convenio, considerando los días de suspensión.</small>
                                    <div class="invalid-feedback">Ingrese la nueva fecha final del convenio.</div>
                                </div>
                                
                                <hr class="my-3">
                                <p class="mb-1 fw-bold small">Documento de Sustento (Adenda de Corte/Suspensión):</p>
                                
                                <div class="col-md-6">
                                    <label class="form-label">Fecha de Adenda <span class="text-danger">*</span></label>
                                    <input type="date" name="fecha_adenda" class="form-control form-control-sm" value="<?php echo date('Y-m-d'); ?>" required>
                                    <div class="invalid-feedback">Ingrese la fecha del documento.</div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Doc. de Sustento (Solicitud/Memo) <span class="text-danger">*</span></label>
                                    <input type="file" name="documento_adenda_corte" class="form-control form-control-sm" accept=".pdf" required>
                                    <div class="invalid-feedback">Suba el PDF de la solicitud.</div>
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label">Descripción / Justificación (Opcional)</label>
                                    <textarea name="descripcion_corte" class="form-control form-control-sm" rows="2" placeholder="Ej: Suspensión por 1 mes debido a..., retorna el..."></textarea>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-warning text-dark mt-3"><i class="bi bi-save"></i> Guardar Corte/Suspensión</button>
                        </form>
                    </div>

                    <div id="form-cese" class="accion-form p-3 border rounded bg-light shadow-sm" style="display:none;">
                        <h6 class="text-danger mb-3"><i class="bi bi-sign-stop-fill"></i> Registrar Cese del Convenio</h6>
                        <p class="text-muted small">Esto marcará el convenio como finalizado y al practicante como 'Cesado'.</p>
                        
                        <form action="index.php?c=convenios&m=registrarCese" method="POST" enctype="multipart/form-data" class="needs-validation" novalidate onsubmit="return confirm('¿Está seguro de registrar el cese de este convenio? Esta acción no se puede deshacer fácilmente.');">
                            <input type="hidden" name="convenio_id" value="<?php echo $c['convenio_id']; ?>">
                            <input type="hidden" name="practicante_id" value="<?php echo $c['practicante_id']; ?>">
                            
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Motivo del Cese <span class="text-danger">*</span></label>
                                    <select name="estado" class="form-select form-select-sm" id="motivo_cese" required>
                                        <option value="">Seleccione...</option>
                                        <option value="Renuncia">Renuncia Voluntaria</option>
                                        <option value="Cancelado">Cancelación (Error Admin.)</option>
                                        <option value="Finalizado">Finalización (Término normal)</option>
                                    </select>
                                    <div class="invalid-feedback">Seleccione el motivo del cese.</div>
                                </div>
                                <div class="col-md-6" id="campo_doc_renuncia" style="display:none;">
                                    <label class="form-label">Documento Renuncia (PDF) <span class="text-danger">*</span></label>
                                    <input type="file" name="documento_renuncia" class="form-control form-control-sm" accept=".pdf">
                                     <div class="invalid-feedback">Suba el documento de renuncia.</div>
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label">Descripción / Motivo Detallado <span class="text-danger">*</span></label>
                                    <textarea name="descripcion_cese" class="form-control form-control-sm" rows="2" placeholder="Detallar brevemente el motivo de la renuncia o cancelación" required></textarea>
                                     <div class="invalid-feedback">Ingrese una descripción del motivo.</div>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-danger mt-3"><i class="bi bi-sign-stop-fill"></i> Confirmar Cese</button>
                        </form>
                    </div>
                    
                </div> </div> </div> </div> </div> <script>
document.addEventListener('DOMContentLoaded', function() {

    // --- LÓGICA DEL SELECTOR DE ACCIONES ---
    const selector = document.getElementById('tipo_accion_selector');
    const formularios = document.querySelectorAll('.accion-form');
    const campoDocRenuncia = document.getElementById('campo_doc_renuncia');
    const inputDocRenuncia = campoDocRenuncia ? campoDocRenuncia.querySelector('input[name="documento_renuncia"]') : null;
    const selectMotivoCese = document.getElementById('motivo_cese'); // Selector dentro del form de cese

    function gestionarFormularios() {
        if (!selector) return;
        const valorSeleccionado = selector.value;
        
        formularios.forEach(form => {
            form.style.display = (form.id === 'form-' + valorSeleccionado) ? 'block' : 'none';
        });

        // Lógica específica para el campo de renuncia en el form de cese
        if (valorSeleccionado === 'cese') {
            const motivoCese = selectMotivoCese ? selectMotivoCese.value : '';
            if (motivoCese === 'Renuncia') {
                 if(campoDocRenuncia) campoDocRenuncia.style.display = 'block';
                 if(inputDocRenuncia) inputDocRenuncia.required = true;
            } else {
                 if(campoDocRenuncia) campoDocRenuncia.style.display = 'none';
                 if(inputDocRenuncia) inputDocRenuncia.required = false;
                 if(inputDocRenuncia) inputDocRenuncia.value = ''; // Limpiar si se cambia a "Cancelado"
            }
        } else {
            // Asegurarse que esté oculto y no requerido si no estamos en 'cese'
            if(campoDocRenuncia) campoDocRenuncia.style.display = 'none';
            if(inputDocRenuncia) inputDocRenuncia.required = false;
        }
    }

    if (selector) {
        selector.addEventListener('change', gestionarFormularios);
    }
    if (selectMotivoCese) {
        // También necesita actualizar la visibilidad cuando cambia el motivo DENTRO del form de cese
        selectMotivoCese.addEventListener('change', gestionarFormularios);
    }
    
    // Ejecutar al inicio por si hay algún valor preseleccionado (menos común)
    // gestionarFormularios();


    // --- LÓGICA DE CÁLCULO DE FECHA ---
    function calcularFechaFin(fechaInicioStr, meses) {
        let fechaInicio;
        if (!fechaInicioStr) {
            // Si no hay fecha de inicio (ej. calculando desde fin actual), usar la fecha actual como base
            fechaInicio = new Date(); 
        } else {
            const partes = fechaInicioStr.split('-'); 
            // OJO: Meses en JS son 0-11
            fechaInicio = new Date(parseInt(partes[0], 10), parseInt(partes[1], 10) - 1, parseInt(partes[2], 10));
        }
        
        const fechaFin = new Date(fechaInicio.getTime());
        fechaFin.setMonth(fechaFin.getMonth() + parseInt(meses, 10));
        // Restar un día para que sea inclusivo (ej. 10 Ene + 1 Mes = 9 Feb)
        fechaFin.setDate(fechaFin.getDate() - 1); 
        
        return fechaFin.toISOString().split('T')[0]; // Formato YYYY-MM-DD
    }

    // --- LÓGICA DE CÁLCULO DE FECHA (PARA AMPLIACIÓN) ---
    function calcularFechaFinAmpliacion(fechaBaseStr, meses) {
         if (!fechaBaseStr) return '';
         const partes = fechaBaseStr.split('-'); 
         // Fecha base (que es la FECHA FIN ACTUAL)
         const fechaBase = new Date(parseInt(partes[0], 10), parseInt(partes[1], 10) - 1, parseInt(partes[2], 10));
         
         // 1. Añadir un día para empezar a contar desde el día SIGUIENTE al fin
         fechaBase.setDate(fechaBase.getDate() + 1);
         
         // 2. Añadir los meses
         const fechaFin = new Date(fechaBase.getTime());
         fechaFin.setMonth(fechaFin.getMonth() + parseInt(meses, 10));
         
         // 3. Restar un día
         fechaFin.setDate(fechaFin.getDate() - 1);
         
         return fechaFin.toISOString().split('T')[0];
    }
    
    const botonesCalc = document.querySelectorAll('.btn-calc-fecha');

    botonesCalc.forEach(boton => {
        boton.addEventListener('click', function() {
            const meses = this.getAttribute('data-meses');
            const idInicio = this.getAttribute('data-inicio');
            const idFin = this.getAttribute('data-fin');
            
            const inputFechaInicio = document.getElementById(idInicio);
            const inputFechaFin = document.getElementById(idFin);

            if (inputFechaInicio && inputFechaFin) {
                let fechaInicioBase = inputFechaInicio.value;
                
                // Si el campo de inicio está vacío, rellenarlo con hoy
                if (!fechaInicioBase && !(inputFechaInicio.readOnly || inputFechaInicio.disabled)) {
                    fechaInicioBase = new Date().toISOString().split('T')[0];
                    inputFechaInicio.value = fechaInicioBase;
                } else if (!fechaInicioBase) {
                     fechaInicioBase = new Date().toISOString().split('T')[0];
                }

                // *** DISTINGUIR LÓGICA DE CÁLCULO ***
                if (idInicio === 'ampl_fecha_fin_actual') {
                    // Es una ampliación, usamos la lógica de sumar a la fecha fin
                    inputFechaFin.value = calcularFechaFinAmpliacion(fechaInicioBase, meses);
                } else {
                    // Es un período nuevo, usamos la lógica de inicio + meses
                    inputFechaFin.value = calcularFechaFin(fechaInicioBase, meses);
                }
                
                 // Disparar evento change para compatibilidad con validaciones
                 inputFechaFin.dispatchEvent(new Event('change', { bubbles: true }));
            }
        });
    });

    // --- VALIDACIÓN BOOTSTRAP ---
    (function () {
      'use strict';
      var forms = document.querySelectorAll('.needs-validation');
      Array.prototype.slice.call(forms)
        .forEach(function (form) {
          form.addEventListener('submit', function (event) {
            
            // Re-validar la visibilidad del campo de renuncia ANTES de enviar
            if (selector.value === 'cese') {
                 const motivoCese = selectMotivoCese ? selectMotivoCese.value : '';
                 if (motivoCese === 'Renuncia') {
                    if(inputDocRenuncia) inputDocRenuncia.required = true;
                 } else {
                    if(inputDocRenuncia) inputDocRenuncia.required = false;
                 }
            }

            if (!form.checkValidity()) {
              event.preventDefault();
              event.stopPropagation();
            }
            form.classList.add('was-validated');
          }, false);
        });
    })();
});
</script>