<?php
// views/reportes/preview_general.php
// Variables: $reportData, $errorMessage
?>

<h2 class="h4 mb-3">Reporte General de Saldos de Empleados Activos</h2>
<p class="text-muted">Fecha de Generación: <?php echo date('d/m/Y H:i'); ?></p>

<?php if (isset($errorMessage)): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($errorMessage); ?></div>
<?php elseif (!isset($reportData) || empty($reportData)): ?>
    <div class="alert alert-warning">No se encontraron datos para generar el reporte.</div>
<?php else: ?>
    <div class="table-responsive">
        <table class="table table-bordered table-striped table-hover">
            <thead class="table-light">
                <tr>
                    <th>DNI</th>
                    <th>Nombre Completo</th>
                    <th>Cargo</th>
                    <th>Área / Lugar</th>
                    <th>Fecha Ingreso</th>
                    <th>Período Relevante</th>
                    <th class="text-center">Días Total/Dev.</th>
                    <th class="text-center">Días Usados</th>
                    <th class="text-center">Saldo</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($reportData as $row): ?>
                     <?php
                         // Determine if the relevant period is "in progress"
                         $total_dias = $row['total_dias'] ?? 0;
                         $isCurrentEarning = false; // Default
                         try {
                              if (isset($row['periodo_fin'])) {
                                   $isCurrentEarning = (new DateTime() <= new DateTime($row['periodo_fin']) && $total_dias < 30);
                              }
                         } catch (Exception $e) {/* ignore date error */}
                         $saldo = $row['saldo_calculado'] ?? 0;
                         $saldo_class = $saldo >= 0 ? '' : 'text-danger';
                     ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['dni'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($row['nombre_completo'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($row['cargo'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($row['area'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($row['fecha_ingreso'] ?? 'N/A'); ?></td>
                        <td><?php echo (isset($row['periodo_inicio']) && isset($row['periodo_fin'])) ? htmlspecialchars($row['periodo_inicio'] . ' al ' . $row['periodo_fin']) : 'N/A'; ?></td>
                        <td class="text-center">
                             <?php echo htmlspecialchars($total_dias); ?>
                             <?php if($isCurrentEarning): ?> <span class="badge bg-info text-dark ms-1">Prog.</span><?php endif; ?>
                        </td>
                        <td class="text-center"><?php echo htmlspecialchars($row['dias_usados_calculados'] ?? 0); ?></td>
                        <td class="text-center fw-bold <?php echo $saldo_class; ?>"><?php echo htmlspecialchars($saldo); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
         <small class="text-muted">"Período Relevante" es el período vigente o el último registrado. "Días Total/Dev." muestra días devengados si está "En Progreso (Prog.)".</small>
    </div>
<?php endif; ?>