<?php
// views/convenios/gestionar.php
$c = $data['convenio'];
$esVigente = ($c['estado_convenio'] == 'Vigente');
// Fecha fin del período activo (para el placeholder de ampliación)
$fecha_fin_actual = $data['fecha_fin_actual'] ?? date('Y-m-d');
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
        <div class="card mb-4 shadow-sm">
            <div class="card-header">Practicante</div>
            <div class="card-body">
                <p><strong>DNI:</strong> <?php echo htmlspecialchars($c['dni']); ?></p>
                <p><strong>Nombres:</strong> <?php echo htmlspecialchars($c['apellidos'] . ', ' . $c['nombres']); ?></p>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card mb-4 shadow-sm">
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
                <button class="nav-link" id="acciones-tab" data-bs-toggle="tab" data-bs-target="#acciones" type="button" <?php echo $esVigente ? '' : 'disabled'; ?>>
                    <i class="bi bi-gear-fill"></i> Acciones (Adendas / Cese)
                </button>
            </li>
        </ul>
    </div>
    <div class="card-body">
        <div class="tab-content" id="gestionTabsContent">
            
            <div class="tab-pane fade show active" id="periodos" role="tabpanel">
                <h5>Historial de Períodos</h5>
                <p class="text-muted">Muestra la bitácora de fechas y áreas del practicante. El período "Activo" es el vigente.</p>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered table-hover">
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
            </div>

            <div class="tab-pane fade" id="firmas" role="tabpanel">
                <h5>Gestión de Firmas y Documentos</h5>
                <p class="text-muted">Aquí se suben los documentos firmados que validan el convenio y sus adendas.</p>
                
                <table class="table table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>Documento</th>
                            <th>Estado</th>
                            <th>Acción / Ver</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>
                                <strong>Convenio Principal</strong>
                                <small class="d-block text-muted">Documento inicial del convenio.</small>
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
                                        <i class="bi bi-eye-fill"></i> Ver Convenio Firmado
                                    </a>
                                <?php else: ?>
                                    <form action="index.php?c=convenios&m=subirConvenioFirmado" method="POST" enctype="multipart/form-data">
                                        <input type="hidden" name="convenio_id" value="<?php echo $c['convenio_id']; ?>">
                                        <input type="hidden" name="practicante_id" value="<?php echo $c['practicante_id']; ?>">
                                        <div class="input-group input-group-sm">
                                            <input type="file" name="documento_convenio" class="form-control" accept=".pdf" required>
                                            <button class="btn btn-outline-success" type="submit">Subir</button>
                                        </div>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                        
                        <?php if (empty($c['adendas'])): ?>
                            <tr>
                                <td colspan="3" class="text-center text-muted">No hay adendas registradas para este convenio.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($c['adendas'] as $a): ?>
                            <tr>
                                <td>
                                    <strong>Adenda: <?php echo htmlspecialchars($a['tipo_accion']); ?></strong>
                                    <small class="d-block text-muted">Fecha: <?php echo date("d/m/Y", strtotime($a['fecha_adenda'])); ?></small>
                                </td>
                                <td>
                                    <?php if (!empty($a['documento_adenda_url'])): ?>
                                        <span class="badge bg-success"><i class="bi bi-check-circle-fill"></i> Documento Registrado</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Sin documento</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($a['documento_adenda_url'])): ?>
                                        <a href="<?php echo htmlspecialchars($a['documento_adenda_url']); ?>" target="_blank" class="btn btn-sm btn-outline-secondary">
                                            <i class="bi bi-eye-fill"></i> Ver Adenda
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted fst-italic">N/A</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="tab-pane fade" id="acciones" role="tabpanel">
                
                <div class="mb-3 col-md-8 col-lg-6">
                    <label for="tipo_accion_selector" class="form-label fw-bold">Seleccione una Acción:</label>
                    <select id="tipo_accion_selector" class="form-select" <?php echo $esVigente ? '' : 'disabled'; ?>>
                        <option value="">Seleccione...</option>
                        <option value="ampliacion">1. Registrar Adenda de Ampliación</option>
                        <option value="reubicacion">2. Registrar Adenda de Reubicación</option>
                        <option value="corte">3. Registrar Adenda de Corte / Suspensión</option>
                        <option value="renuncia">4. Registrar Cese por Renuncia</option>
                        <option value="cancelar">5. Registrar Cese por Cancelación (Error)</option>
                    </select>
                </div>
                
                <div id="formularios-accion">

                    <div id="form-ampliacion" class="accion-form p-3 border rounded bg-light" style="display:none;">
                        <h6 class="text-primary mb-3"><i class="bi bi-calendar-plus"></i> Registrar Adenda de Ampliación</h6>
                        <form action="index.php?c=convenios&m=ampliar" method="POST" class="needs-validation" enctype="multipart/form-data" novalidate>
                            <input type="hidden" name="convenio_id" value="<?php echo $c['convenio_id']; ?>">
                            <input type="hidden" name="practicante_id" value="<?php echo $c['practicante_id']; ?>">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Fecha Fin Actual</label>
                                    <input type="date" class="form-control" id="ampl_fecha_fin_actual" value="<?php echo $fecha_fin_actual; ?>" readonly disabled>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Nueva Fecha de Fin <span class="text-danger">*</span></label>
                                    <input type="date" name="nueva_fecha_fin" class="form-control" id="ampl_fecha_fin_nueva" required>
                                    <div class="btn-group btn-group-sm mt-1" role="group">
                                        <button type="button" class="btn btn-outline-secondary btn-calc-fecha" data-meses="4" data-inicio="ampl_fecha_fin_actual" data-fin="ampl_fecha_fin_nueva">Extender 4 Meses</button>
                                        <button type="button" class="btn btn-outline-secondary btn-calc-fecha" data-meses="6" data-inicio="ampl_fecha_fin_actual" data-fin="ampl_fecha_fin_nueva">Extender 6 Meses</button>
                                        <button type="button" class="btn btn-outline-secondary btn-calc-fecha" data-meses="12" data-inicio="ampl_fecha_fin_actual" data-fin="ampl_fecha_fin_nueva">Extender 1 Año</button>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Fecha de Adenda <span class="text-danger">*</span></label>
                                    <input type="date" name="fecha_adenda" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                                </div>
                                 <div class="col-md-6 mb-3">
                                    <label class="form-label">Documento Adenda (PDF) <span class="text-danger">*</span></label>
                                    <input type="file" name="documento_adenda" class="form-control" accept=".pdf" required>
                                </div>
                                <div class="col-md-12 mb-3">
                                    <label class="form-label">Descripción / Justificación (Opcional)</label>
                                    <textarea name="descripcion" class="form-control" rows="2" placeholder="Ej: Ampliación por 1 mes adicional según documento..."></textarea>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary">Guardar Ampliación</button>
                        </form>
                    </div>

                    <div id="form-reubicacion" class="accion-form p-3 border rounded bg-light" style="display:none;">
                        <h6 class="text-info mb-3"><i class="bi bi-calendar-event"></i> Registrar Adenda de Reubicación</h6>
                        <p class="text-muted small">Esto finalizará el período actual y creará uno nuevo con la nueva área. Ambas acciones se registrarán en el historial.</p>
                        <form action="index.php?c=convenios&m=guardarPeriodo" method="POST" class="needs-validation" enctype="multipart/form-data" novalidate>
                            <input type="hidden" name="convenio_id" value="<?php echo $c['convenio_id']; ?>">
                            <input type="hidden" name="practicante_id" value="<?php echo $c['practicante_id']; ?>">
                            <input type="hidden" name="tipo_accion" value="REUBICACION">
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Fecha Inicio (Nuevo Período) <span class="text-danger">*</span></label>
                                    <input type="date" name="fecha_inicio" class="form-control" id="nuevo_fecha_inicio_reub" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Fecha Fin (Nuevo Período) <span class="text-danger">*</span></label>
                                    <input type="date" name="fecha_fin" class="form-control" id="nuevo_fecha_fin_reub" required>
                                </div>
                                <div class="col-12 mb-3">
                                     <label class="form-label">Calcular Fecha Fin (desde inicio):</label>
                                     <div class="btn-group btn-group-sm" role="group">
                                        <button type="button" class="btn btn-outline-secondary btn-calc-fecha" data-meses="4" data-inicio="nuevo_fecha_inicio_reub" data-fin="nuevo_fecha_fin_reub">4 Meses</button>
                                        <button type="button" class="btn btn-outline-secondary btn-calc-fecha" data-meses="6" data-inicio="nuevo_fecha_inicio_reub" data-fin="nuevo_fecha_fin_reub">6 Meses</button>
                                        <button type="button" class="btn btn-outline-secondary btn-calc-fecha" data-meses="12" data-inicio="nuevo_fecha_inicio_reub" data-fin="nuevo_fecha_fin_reub">1 Año</button>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Nuevo Local <span class="text-danger">*</span></label>
                                    <select name="local_id" class="form-select" required>
                                        <?php foreach($data['locales'] as $loc): ?>
                                        <option value="<?php echo $loc['local_id']; ?>"><?php echo htmlspecialchars($loc['nombre']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Nueva Área <span class="text-danger">*</span></label>
                                    <select name="area_id" class="form-select" required>
                                         <?php foreach($data['areas'] as $area): ?>
                                        <option value="<?php echo $area['area_id']; ?>"><?php echo htmlspecialchars($area['nombre']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Fecha de Adenda <span class="text-danger">*</span></label>
                                    <input type="date" name="fecha_adenda" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Documento Adenda (PDF) <span class="text-danger">*</span></label>
                                    <input type="file" name="documento_adenda" class="form-control" accept=".pdf" required>
                                </div>
                                <div class="col-md-12 mb-3">
                                    <label class="form-label">Descripción / Justificación (Opcional)</label>
                                    <textarea name="descripcion" class="form-control" rows="2" placeholder="Ej: Reubicado a Contabilidad según Memo..."></textarea>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-info text-dark">Guardar Reubicación</button>
                        </form>
                    </div>
                    
                    <div id="form-corte" class="accion-form p-3 border rounded bg-light" style="display:none;">
                        <h6 class="text-warning mb-3"><i class="bi bi-calendar-minus"></i> Registrar Adenda de Corte o Suspensión</h6>
                        <p class="text-muted small">Esto finalizará el período actual. Deberá crear otro período cuando el practicante regrese (usando esta misma opción).</p>
                        <form action="index.php?c=convenios&m=guardarPeriodo" method="POST" class="needs-validation" enctype="multipart/form-data" novalidate>
                            <input type="hidden" name="convenio_id" value="<?php echo $c['convenio_id']; ?>">
                            <input type="hidden" name="practicante_id" value="<?php echo $c['practicante_id']; ?>">
                            <input type="hidden" name="tipo_accion" value="CORTE">
                            
                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <p class="mb-1"><strong>Período a Iniciar (Nuevo):</strong></p>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Fecha Inicio (Nuevo Período) <span class="text-danger">*</span></label>
                                    <input type="date" name="fecha_inicio" class="form-control" id="nuevo_fecha_inicio_corte" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Fecha Fin (Nuevo Período) <span class="text-danger">*</span></label>
                                    <input type="date" name="fecha_fin" class="form-control" id="nuevo_fecha_fin_corte" required>
                                </div>
                                 <div class="col-12 mb-3">
                                     <label class="form-label">Calcular Fecha Fin (desde inicio):</label>
                                     <div class="btn-group btn-group-sm" role="group">
                                        <button type="button" class="btn btn-outline-secondary btn-calc-fecha" data-meses="4" data-inicio="nuevo_fecha_inicio_corte" data-fin="nuevo_fecha_fin_corte">4 Meses</button>
                                        <button type="button" class="btn btn-outline-secondary btn-calc-fecha" data-meses="6" data-inicio="nuevo_fecha_inicio_corte" data-fin="nuevo_fecha_fin_corte">6 Meses</button>
                                        <button type="button" class="btn btn-outline-secondary btn-calc-fecha" data-meses="12" data-inicio="nuevo_fecha_inicio_corte" data-fin="nuevo_fecha_fin_corte">1 Año</button>
                                    </div>
                                </div>
                                <input type="hidden" name="local_id" value="<?php echo $c['periodos'][0]['local_id'] ?? 1; ?>">
                                <input type="hidden" name="area_id" value="<?php echo $c['periodos'][0]['area_id'] ?? 1; ?>">
                                
                                <div class="col-md-12"><hr></div>
                                <p class="mb-1"><strong>Documento de Justificación (Adenda):</strong></p>
                                
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Fecha de Adenda <span class="text-danger">*</span></label>
                                    <input type="date" name="fecha_adenda" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Documento Adenda (PDF) <span class="text-danger">*</span></label>
                                    <input type="file" name="documento_adenda" class="form-control" accept=".pdf" required>
                                </div>
                                <div class="col-md-12 mb-3">
                                    <label class="form-label">Descripción / Justificación (Opcional)</label>
                                    <textarea name="descripcion" class="form-control" rows="2" placeholder="Ej: Suspensión por 1 mes, reinicia labores el..."></textarea>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-warning text-dark">Guardar Corte/Suspensión</button>
                        </form>
                    </div>

                    <div id="form-justificacion" class="accion-form p-3 border rounded bg-light" style="display:none;">
                        <h6 class="text-secondary"><i class="bi bi-journal-plus"></i> Registrar Adenda (Solo Justificación)</h6>
                        <p><small>Use esto si solo necesita registrar un documento de justificación sin alterar los períodos (ej. un permiso especial, fe de erratas, etc.).</small></p>
                        <form action="index.php?c=convenios&m=guardarAdenda" method="POST" class="needs-validation" enctype="multipart/form-data" novalidate>
                            <input type="hidden" name="convenio_id" value="<?php echo $c['convenio_id']; ?>">
                             <div class="row">
                                <div class="col-md-4 mb-2">
                                    <label class="form-label">Fecha Adenda <span class="text-danger">*</span></label>
                                    <input type="date" name="fecha_adenda" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                                </div>
                                 <div class="col-md-4 mb-2">
                                    <label class="form-label">Tipo de Acción <span class="text-danger">*</span></label>
                                    <select name="tipo_accion" class="form-select" required>
                                        <option value="">Seleccione...</option>
                                        <option value="OTRO">OTRO</option>
                                        <option value="FE DE ERRATAS">FE DE ERRATAS</option>
                                    </select>
                                </div>
                                 <div class="col-md-4 mb-2">
                                    <label class="form-label">Documento (PDF) <span class="text-danger">*</span></label>
                                    <input type="file" name="documento_adenda" class="form-control" accept=".pdf" required>
                                </div>
                                <div class="col-md-12 mb-3">
                                    <label class="form-label">Descripción / Justificación <span class="text-danger">*</span></label>
                                    <textarea name="descripcion" class="form-control" rows="2" required></textarea>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-sm btn-secondary">Guardar Solo Justificación</button>
                        </form>
                    </div>

                    <div id="form-finalizar" class="accion-form p-3 border rounded bg-light" style="display:none;">
                        <h6 class="text-danger"><i class="bi bi-sign-stop-fill"></i> Finalizar Convenio (Cese)</h6>
                        <p><small>Esto moverá al practicante a 'Cesado' y cerrará el convenio y el período activo actual.</small></p>
                        
                        <form action="index.php?c=convenios&m=finalizar" method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
                            <input type="hidden" name="convenio_id" value="<?php echo $c['convenio_id']; ?>">
                            <input type="hidden" name="practicante_id" value="<?php echo $c['practicante_id']; ?>">
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Motivo del Cese <span class="text-danger">*</span></label>
                                    <select name="estado" class="form-select" id="motivo_cese" required>
                                        <option value="">Seleccione...</option>
                                        <option value="Renuncia">1. Renuncia Voluntaria</option>
                                        <option value="Cancelado">2. Cancelación (Error Admin.)</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3" id="campo_doc_renuncia" style="display:none;">
                                    <label class="form-label">Documento Renuncia (PDF) <span class="text-danger">*</span></label>
                                    <input type="file" name="documento_renuncia" class="form-control" accept=".pdf">
                                </div>
                                <div class="col-md-12 mb-3">
                                    <label class="form-label">Descripción / Motivo <span class="text-danger">*</span></label>
                                    <textarea name="descripcion" class="form-control" rows="2" placeholder="Detallar el motivo de la renuncia o cancelación" required></textarea>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-danger">Confirmar Cese de Convenio</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {

    // --- 1. LÓGICA DEL SELECTOR DE ACCIONES ---
    const selector = document.getElementById('tipo_accion_selector');
    const formularios = document.querySelectorAll('.accion-form');
    const campoDocRenuncia = document.getElementById('campo_doc_renuncia');
    const inputDocRenuncia = campoDocRenuncia ? campoDocRenuncia.querySelector('input') : null;

    if (selector) {
        selector.addEventListener('change', function() {
            const valor = this.value;
            
            // Ocultar todos los formularios
            formularios.forEach(form => {
                form.style.display = 'none';
            });
            
            // Ocultar y des-requerir el campo de renuncia
            if(campoDocRenuncia) campoDocRenuncia.style.display = 'none';
            if(inputDocRenuncia) inputDocRenuncia.required = false;

            // Mostrar el formulario seleccionado
            if (valor) {
                const formSeleccionado = document.getElementById('form-' + valor);
                if (formSeleccionado) {
                    formSeleccionado.style.display = 'block';
                }
            }
        });
    }

    // Lógica para mostrar/ocultar el campo de archivo en "Finalizar"
    const selectMotivoCese = document.getElementById('motivo_cese');
    if (selectMotivoCese) {
        selectMotivoCese.addEventListener('change', function() {
            if (this.value === 'Renuncia') {
                if(campoDocRenuncia) campoDocRenuncia.style.display = 'block';
                if(inputDocRenuncia) inputDocRenuncia.required = true;
            } else {
                if(campoDocRenuncia) campoDocRenuncia.style.display = 'none';
                if(inputDocRenuncia) inputDocRenuncia.required = false;
            }
        });
    }


    // --- 2. LÓGICA DE CÁLCULO DE FECHA ---
    
    /**
     * Calcula la fecha de fin basada en una fecha de inicio y una duración en meses.
     * Resta un día al final (ej: 07/04/2025 + 12 meses = 06/04/2026).
     * Si la fecha de inicio es nula, usa la fecha actual.
     */
    function calcularFechaFin(fechaInicioStr, meses) {
        let fechaInicio;
        
        if (!fechaInicioStr) {
            fechaInicio = new Date(); // Usar hoy si no hay fecha de inicio
        } else {
            const partes = fechaInicioStr.split('-'); // YYYY-MM-DD
            const anio = parseInt(partes[0], 10);
            const mes = parseInt(partes[1], 10) - 1; // 0-11
            const dia = parseInt(partes[2], 10);
            fechaInicio = new Date(anio, mes, dia);
        }
        
        const fechaFin = new Date(fechaInicio.getTime());
        
        fechaFin.setMonth(fechaFin.getMonth() + parseInt(meses, 10));
        fechaFin.setDate(fechaFin.getDate() - 1);
        
        const anioFin = fechaFin.getFullYear();
        const mesFin = (fechaFin.getMonth() + 1).toString().padStart(2, '0');
        const diaFin = fechaFin.getDate().toString().padStart(2, '0');
        
        return `${anioFin}-${mesFin}-${diaFin}`;
    }
    
    const botonesCalc = document.querySelectorAll('.btn-calc-fecha');

    botonesCalc.forEach(boton => {
        boton.addEventListener('click', function() {
            const meses = this.getAttribute('data-meses');
            
            // IDs de los inputs de inicio y fin (vienen de data-attributes)
            const idInicio = this.getAttribute('data-inicio');
            const idFin = this.getAttribute('data-fin');
            
            const inputFechaInicio = document.getElementById(idInicio);
            const inputFechaFin = document.getElementById(idFin);

            if (inputFechaInicio && inputFechaFin) {
                let fechaInicio = inputFechaInicio.value;
                
                // Si el campo de inicio está deshabilitado (como en ampliación), usa su valor
                if (inputFechaInicio.disabled) {
                    fechaInicio = inputFechaInicio.value;
                }
                
                if (!fechaInicio) {
                    // Si el campo de inicio está vacío, rellenarlo con hoy
                    const hoy = new Date().toISOString().split('T')[0];
                    inputFechaInicio.value = hoy;
                    fechaInicio = hoy;
                }
                
                inputFechaFin.value = calcularFechaFin(fechaInicio, meses);

            }
        });
    });

    // --- 3. VALIDACIÓN BOOTSTRAP ---
    (function () {
      'use strict'
      var forms = document.querySelectorAll('.needs-validation')
      Array.prototype.slice.call(forms)
        .forEach(function (form) {
          form.addEventListener('submit', function (event) {
            if (!form.checkValidity()) {
              event.preventDefault()
              event.stopPropagation()
            }
            form.classList.add('was-validated')
          }, false)
        })
    })();
});
</script>