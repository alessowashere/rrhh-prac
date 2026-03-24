<div class="table-responsive">
    <p>Mostrando vacaciones (Aprobadas y Gozadas) dentro del rango de fechas seleccionado.</p>
    <table class="table table-bordered table-striped" width="100%" cellspacing="0">
        <thead>
            <tr>
                <th>Nombre Completo</th>
                <th>Fecha Inicio</th>
                <th>Fecha Fin</th>
                <th class="text-center">Días Tomados</th>
                <th>Tipo</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($resultados)): ?>
                <tr>
                    <td colspan="6" class="text-center">No se encontraron vacaciones en el período seleccionado.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($resultados as $fila): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($fila['nombre_completo']); ?></td>
                        <td><?php echo date('d/m/Y', strtotime($fila['fecha_inicio'])); ?></td>
                        <td><?php echo date('d/m/Y', strtotime($fila['fecha_fin'])); ?></td>
                        <td class="text-center"><?php echo $fila['dias_tomados']; ?></td>
                        <td><?php echo htmlspecialchars($fila['tipo']); ?></td>
                        <td><?php echo htmlspecialchars($fila['estado']); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>