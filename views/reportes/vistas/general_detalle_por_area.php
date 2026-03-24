<?php
// views/reportes/vistas/general_detalle_por_area.php
// Muestra el detalle de vacaciones (programadas) para TODOS, ordenado por AREA
?>
<div class="table-responsive">
    <p>Mostrando vacaciones (Aprobadas y Gozadas) para todos los empleados, ordenado por unidad.</p>
    <table class="table table-bordered table-striped" width="100%" cellspacing="0">
        <thead>
            <tr>
                <th>Nombre Completo</th>
                <th>Fecha Inicio</th>
                <th>Fecha Fin</th>
                <th class="text-center">Días</th>
                <th>Tipo</th>
                <th>Estado</th>
                <th>Período Afectado</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($resultados)): ?>
                <tr>
                    <td colspan="7" class="text-center">No se encontraron vacaciones (o para el período seleccionado).</td>
                </tr>
            <?php else: ?>
                <?php 
                $current_area = null; // Variable para agrupar
                ?>
                <?php foreach ($resultados as $fila): ?>
                    <?php 
                    $periodo_afectado = (isset($fila['periodo_inicio']) && isset($fila['periodo_fin'])) ? 
                                        date('Y', strtotime($fila['periodo_inicio'])) . ' - ' . date('Y', strtotime($fila['periodo_fin'])) : 'N/A';
                    $area = htmlspecialchars($fila['area'] ?? 'Sin Área');
                    ?>

                    <?php // --- Fila de Agrupación: Muestra el área cuando cambia ---
                    if ($area !== $current_area): 
                        $current_area = $area;
                    ?>
                        <tr class="table-light">
                            <td colspan="7" class="fw-bold" style="background-color: #f0f0f0;">
                                <i class="bi bi-diagram-3-fill me-2"></i> Unidad: <?php echo $area; ?>
                            </td>
                        </tr>
                    <?php endif; ?>

                    <tr>
                        <td><?php echo htmlspecialchars($fila['nombre_completo']); ?></td>
                        <td><?php echo date('d/m/Y', strtotime($fila['fecha_inicio'])); ?></td>
                        <td><?php echo date('d/m/Y', strtotime($fila['fecha_fin'])); ?></td>
                        <td class="text-center"><?php echo $fila['dias_tomados']; ?></td>
                        <td><?php echo htmlspecialchars($fila['tipo']); ?></td>
                        <td><?php echo htmlspecialchars($fila['estado']); ?></td>
                        <td><?php echo $periodo_afectado; ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>