<div class="alert alert-info">
    <h4>Datos del Empleado</h4>
    <p><strong>Nombre:</strong> <?php echo htmlspecialchars($info_empleado['nombre_completo']); ?></p>
    <p><strong>Cargo:</strong> <?php echo htmlspecialchars($info_empleado['cargo']); ?></p> 
    <p><strong>Área:</strong> <?php echo htmlspecialchars($info_empleado['area']); ?></p>
    <p><strong>Fecha de Ingreso:</strong> <?php echo date('d/m/Y', strtotime($info_empleado['fecha_ingreso'])); ?></p>
</div>

<hr>

<h5>Detalle de Movimientos de Vacaciones</h5>
<div class="table-responsive">
    <table class="table table-bordered table-striped" width="100%" cellspacing="0">
        <thead>
            <tr>
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
                    <td colspan="6" class="text-center">No se encontraron movimientos para esta persona (o en este período).</td>
                </tr>
            <?php else: ?>
                <?php foreach ($resultados as $fila): ?>
                     <?php 
                        $periodo_afectado = (isset($fila['periodo_inicio']) && isset($fila['periodo_fin'])) ? 
                                            date('Y', strtotime($fila['periodo_inicio'])) . ' - ' . date('Y', strtotime($fila['periodo_fin'])) : 'N/A';
                    ?>
                    <tr>
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