<div class="table-responsive">
    <table class="table table-bordered table-striped" width="100%" cellspacing="0">
        <thead>
            <tr>
                <th>Nombre Completo</th>
                <th>Cargo</th>
                <th>Área</th>
                <th>Período Relevante</th>
                <th class="text-center">Días Total/Dev.</th>
                <th class="text-center">Días Usados</th>
                <th class="text-center">Saldo Actual</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($resultados)): ?>
                <tr>
                    <td colspan="7" class="text-center">No se encontraron empleados activos con saldos.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($resultados as $fila): ?>
                    <?php
                        $saldo = $fila['saldo_calculado'] ?? 0;
                        $saldo_class = $saldo > 5 ? 'text-success' : ($saldo <= 0 ? 'text-danger' : 'text-warning');
                        $periodo_txt = (isset($fila['periodo_inicio']) && isset($fila['periodo_fin'])) ? 
                                       date('d/m/Y', strtotime($fila['periodo_inicio'])) . ' al ' . date('d/m/Y', strtotime($fila['periodo_fin'])) : 'N/A';
                    ?>
                    <tr>
                        <td><?php echo htmlspecialchars($fila['nombre_completo']); ?></td>
                        <td><?php echo htmlspecialchars($fila['cargo']); ?></td>
                        <td><?php echo htmlspecialchars($fila['area']); ?></td>
                        <td><?php echo $periodo_txt; ?></td>
                        <td class="text-center"><?php echo htmlspecialchars($fila['total_dias']); ?></td>
                        <td class="text-center"><?php echo htmlspecialchars($fila['dias_usados_calculados']); ?></td>
                        <td class="text-center font-weight-bold <?php echo $saldo_class; ?>">
                            <?php echo $saldo; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
    <small class="text-muted">El "Período Relevante" es el período vigente o el último registrado. El "Saldo Actual" se calcula sobre ese período.</small>
</div>