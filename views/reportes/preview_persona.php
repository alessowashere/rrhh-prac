<?php
// views/reportes/preview_persona.php
// Variables: $reportData['persona', 'periodos', 'vacaciones'], $errorMessage

$persona = $reportData['persona'] ?? null;
$periodos = $reportData['periodos'] ?? [];
$vacaciones = $reportData['vacaciones'] ?? [];
?>

<?php if (isset($errorMessage)): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($errorMessage); ?></div>
<?php elseif (!$persona): ?>
    <div class="alert alert-warning">No se encontraron datos para el empleado seleccionado.</div>
<?php else: ?>
    <h2 class="h4 mb-1">Reporte Individual de Vacaciones</h2>
    <p class="text-muted mb-3">Fecha de Generación: <?php echo date('d/m/Y H:i'); ?></p>

    <div class="card mb-4">
        <div class="card-header">Datos del Empleado</div>
        <div class="card-body">
            <p><strong>Nombre:</strong> <?php echo htmlspecialchars($persona['nombre_completo'] ?? 'N/A'); ?></p>
            <p><strong>DNI:</strong> <?php echo htmlspecialchars($persona['dni'] ?? 'N/A'); ?></p>
            <p><strong>Cargo:</strong> <?php echo htmlspecialchars($persona['cargo'] ?? 'N/A'); ?></p>
            <p><strong>Área / Lugar:</strong> <?php echo htmlspecialchars($persona['area'] ?? 'N/A'); ?></p>
            <p><strong>Fecha Ingreso:</strong> <?php echo htmlspecialchars($persona['fecha_ingreso'] ?? 'N/A'); ?></p>
        </div>
    </div>

    <h3 class="h5 mb-3">Resumen de Períodos Vacacionales</h3>
    <?php if (empty($periodos)): ?>
        <p>No hay períodos registrados para este empleado.</p>
    <?php else: ?>
        <div class="table-responsive mb-4">
            <table class="table table-sm table-bordered">
                <thead class="table-light">
                    <tr>
                        <th>Período</th>
                        <th class="text-center">Días Total/Dev.</th>
                        <th class="text-center">Días Usados</th>
                        <th class="text-center">Saldo</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($periodos as $p): ?>
                         <?php
                             $total_dias_p = $p['total_dias'] ?? 0;
                             $dias_usados_p = $p['dias_usados_calculados'] ?? 0;
                             $saldo_p = $total_dias_p - $dias_usados_p;
                             $saldo_p_class = $saldo_p >= 0 ? '' : 'text-danger';
                             $isCurrentEarningP = false;
                             try { if (isset($p['periodo_fin'])) $isCurrentEarningP = (new DateTime() <= new DateTime($p['periodo_fin']) && $total_dias_p < 30); } catch(Exception $e){}
                         ?>
                        <tr>
                            <td><?php echo (isset($p['periodo_inicio']) && isset($p['periodo_fin'])) ? htmlspecialchars($p['periodo_inicio'] . ' al ' . $p['periodo_fin']) : 'N/A'; ?></td>
                            <td class="text-center">
                                 <?php echo htmlspecialchars($total_dias_p); ?>
                                 <?php if($isCurrentEarningP): ?> <span class="badge bg-info text-dark ms-1">Prog.</span><?php endif; ?>
                            </td>
                            <td class="text-center"><?php echo htmlspecialchars($dias_usados_p); ?></td>
                            <td class="text-center fw-bold <?php echo $saldo_p_class; ?>">
                                 <?php echo ($isCurrentEarningP && $total_dias_p < 30) ? '-' : htmlspecialchars($saldo_p); ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

    <h3 class="h5 mb-3">Historial de Vacaciones Tomadas</h3>
    <?php if (empty($vacaciones)): ?>
        <p>No hay registros de vacaciones para este empleado.</p>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-sm table-bordered table-striped">
                <thead class="table-light">
                    <tr>
                        <th>Fecha Inicio</th>
                        <th>Fecha Fin</th>
                        <th class="text-center">Días</th>
                        <th>Tipo</th>
                        <th>Estado</th>
                         <th>Período Afectado</th> </tr>
                </thead>
                <tbody>
                    <?php
                       // Create a quick lookup for period dates by ID
                       $periodo_lookup = [];
                       foreach($periodos as $p_lookup) {
                           $periodo_lookup[$p_lookup['id']] = ($p_lookup['periodo_inicio'] ?? '??') . ' al ' . ($p_lookup['periodo_fin'] ?? '??');
                       }
                    ?>
                    <?php foreach ($vacaciones as $v): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($v['fecha_inicio'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($v['fecha_fin'] ?? 'N/A'); ?></td>
                            <td class="text-center"><?php echo htmlspecialchars($v['dias_tomados'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($v['tipo'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($v['estado'] ?? 'N/A'); ?></td>
                             <td><?php echo htmlspecialchars($periodo_lookup[$v['periodo_id']] ?? 'ID: '.$v['periodo_id']); ?></td> </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

<?php endif; ?>