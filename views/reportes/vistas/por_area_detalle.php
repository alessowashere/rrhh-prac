<?php
// views/reportes/vistas/por_area_detalle.php
// Muestra el detalle de vacaciones (programadas) para un área
?>
<div class="table-responsive">
    <p>Mostrando vacaciones (Aprobadas y Gozadas) para la unidad seleccionada.</p>
    <table class="table table-bordered table-striped" width="100%" cellspacing="0">
        <thead>
            <tr>
                <th>Nombre Completo</th>
                <th>Fecha Inicio</th>
                <th>Fecha Fin</th>
                <th class="text-center">Días Tomados</th>
                <th>Tipo</th>
                <th>Estado</th>
                <th>Período Afectado</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($resultados)): ?>
                <tr>
                    <td colspan="7" class="text-center">No se encontraron vacaciones en esta unidad (o para el período seleccionado).</td>
                </tr>
            <?php else: ?>
                <?php foreach ($resultados as $fila): ?>
                    <?php 
                    $periodo_afectado = (isset($fila['periodo_inicio']) && isset($fila['periodo_fin'])) ? 
                                        date('Y', strtotime($fila['periodo_inicio'])) . ' - ' . date('Y', strtotime($fila['periodo_fin'])) : 'N/A';
                    ?>
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