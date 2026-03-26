<?php
// views/practicantes/ver.php
?>

<div class="d-flex justify-content-between align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="bi bi-person-badge"></i> Detalle del Practicante</h1>
    <div>
        <a href="index.php?c=practicantes" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Volver al listado
        </a>
        <?php if ($data['practicante']['estado_general'] !== 'Cesado'): ?>
            <a href="index.php?c=practicantes&m=editar&id=<?php echo $data['practicante']['practicante_id']; ?>" class="btn btn-primary">
                <i class="bi bi-pencil-square"></i> Editar Datos
            </a>
        <?php endif; ?>
    </div>
</div>

<div class="row">
    <div class="col-md-4">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-primary text-white text-center py-4">
                <i class="bi bi-person-circle display-1"></i>
                <h4 class="mt-2 mb-0"><?php echo htmlspecialchars($data['practicante']['apellidos'] . ', ' . $data['practicante']['nombres']); ?></h4>
                <span class="badge <?php echo ($data['practicante']['estado_general'] == 'Activo') ? 'bg-success' : 'bg-danger'; ?> mt-2">
                    <?php echo strtoupper($data['practicante']['estado_general']); ?>
                </span>
            </div>
            <div class="card-body">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item"><strong>DNI:</strong> <?php echo htmlspecialchars($data['practicante']['dni']); ?></li>
                    <li class="list-group-item"><strong>Email:</strong> <?php echo htmlspecialchars($data['practicante']['email'] ?? 'No registrado'); ?></li>
                    <li class="list-group-item"><strong>Teléfono:</strong> <?php echo htmlspecialchars($data['practicante']['telefono'] ?? 'No registrado'); ?></li>
                    <li class="list-group-item"><strong>Escuela:</strong> <?php echo htmlspecialchars($data['practicante']['escuela_nombre']); ?></li>
                    <li class="list-group-item">
                        <strong>Fecha Nac.:</strong> 
                        <?php echo $data['practicante']['fecha_nacimiento'] ? date('d/m/Y', strtotime($data['practicante']['fecha_nacimiento'])) : 'No registrada'; ?>
                    </li>
                </ul>
            </div>
        </div>

        <?php if ($data['practicante']['estado_general'] === 'Cesado' && isset($data['practicante']['info_cese'])): ?>
            <div class="alert alert-secondary border-start border-4 border-danger shadow-sm">
                <h5 class="text-danger fw-bold"><i class="bi bi-person-x-fill"></i> Información de Cese</h5>
                <p class="mb-1 small"><strong>Fecha de registro:</strong> <?php echo date('d/m/Y', strtotime($data['practicante']['info_cese']['fecha_adenda'])); ?></p>
                <p class="mb-1 small"><strong>Motivo:</strong> <?php echo htmlspecialchars($data['practicante']['info_cese']['descripcion']); ?></p>
                <?php if (!empty($data['practicante']['info_cese']['documento_adenda_url'])): ?>
                    <a href="<?php echo $data['practicante']['info_cese']['documento_adenda_url']; ?>" target="_blank" class="btn btn-sm btn-danger mt-2 w-100">
                        <i class="bi bi-file-pdf"></i> Ver Sustento de Cese
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="col-md-8">
        
        <ul class="nav nav-tabs mb-3" id="practicanteTab" role="tablist">
            <li class="nav-link active" id="eventos-tab" data-bs-toggle="tab" data-bs-target="#eventos" type="button" role="tab">
                <i class="bi bi-journal-text"></i> Línea de Tiempo y Archivos
            </li>
            <li class="nav-link" id="periodos-tab" data-bs-toggle="tab" data-bs-target="#periodos" type="button" role="tab">
                <i class="bi bi-calendar3"></i> Periodos y Áreas
            </li>
        </ul>

        <div class="tab-content border p-3 bg-white shadow-sm rounded">
            <div class="tab-pane fade show active" id="eventos" role="tabpanel">
                <h5 class="mb-3 fw-bold text-dark">Documentos y Acciones Registradas</h5>
                <div class="table-responsive">
                    <table class="table table-sm table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Fecha</th>
                                <th>Evento</th>
                                <th>Descripción / Motivo</th>
                                <th>Archivo</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($data['practicante']['convenio_principal']): ?>
                            <tr>
                                <td class="text-muted">-</td>
                                <td><span class="badge bg-primary">CONVENIO INICIAL</span></td>
                                <td>Registro de ingreso al sistema</td>
                                <td>
                                    <?php if (!empty($data['practicante']['convenio_principal']['documento_convenio_url'])): ?>
                                        <a href="<?php echo $data['practicante']['convenio_principal']['documento_convenio_url']; ?>" target="_blank" class="btn btn-sm btn-outline-danger">
                                            <i class="bi bi-file-pdf"></i> PDF
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted small italic">Pendiente</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endif; ?>

                            <?php if (empty($data['practicante']['adendas'])): ?>
                                <tr><td colspan="4" class="text-center py-3 text-muted">No hay eventos adicionales registrados.</td></tr>
                            <?php else: ?>
                                <?php foreach ($data['practicante']['adendas'] as $ad): ?>
                                <tr>
                                    <td><?php echo date('d/m/Y', strtotime($ad['fecha_adenda'])); ?></td>
                                    <td>
                                        <?php 
                                        $clase = (strpos($ad['tipo_accion'], 'CESE') !== false) ? 'danger' : 'warning';
                                        echo "<span class='badge bg-$clase text-dark'>{$ad['tipo_accion']}</span>"; 
                                        ?>
                                    </td>
                                    <td><small><?php echo htmlspecialchars($ad['descripcion']); ?></small></td>
                                    <td>
                                        <?php if (!empty($ad['documento_adenda_url'])): ?>
                                            <a href="<?php echo $ad['documento_adenda_url']; ?>" target="_blank" class="btn btn-sm btn-outline-secondary">
                                                <i class="bi bi-download"></i> PDF
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted small">-</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="tab-pane fade" id="periodos" role="tabpanel">
                <h5 class="mb-3 fw-bold text-dark">Historial de Áreas Asignadas</h5>
                <div class="table-responsive">
                    <table class="table table-sm table-hover border">
                        <thead class="table-light">
                            <tr>
                                <th>Área</th>
                                <th>Local</th>
                                <th>Desde</th>
                                <th>Hasta</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($data['practicante']['historial_periodos'] as $per): ?>
                            <tr class="<?php echo ($per['estado_periodo'] == 'Activo') ? 'table-success' : ''; ?>">
                                <td><strong><?php echo htmlspecialchars($per['area_nombre']); ?></strong></td>
                                <td><small><?php echo htmlspecialchars($per['local_nombre']); ?></small></td>
                                <td><?php echo date('d/m/Y', strtotime($per['fecha_inicio'])); ?></td>
                                <td><?php echo date('d/m/Y', strtotime($per['fecha_fin'])); ?></td>
                                <td>
                                    <span class="badge <?php echo ($per['estado_periodo'] == 'Activo') ? 'bg-success' : 'bg-secondary'; ?>">
                                        <?php echo $per['estado_periodo']; ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>