<div class="table-responsive">
    <p>Mostrando saldos (diferentes de cero) del período relevante de los empleados activos. Ordenado por deudas (negativos) primero.</p>
    <table class="table table-bordered table-striped" width="100%" cellspacing="0">
        <thead>
            <tr>
                <th>Nombre Completo</th>
                <th>Cargo</th>
                <th class="text-center">Saldo (Días)</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($resultados)): ?>
                <tr>
                    <td colspan="3" class="text-center">No se encontraron empleados con saldos positivos o negativos.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($resultados as $fila): ?>
                     <?php
                        $saldo = $fila['saldo_calculado'] ?? 0;
                        $saldo_class = $saldo < 0 ? 'text-danger' : 'text-success';
                    ?>
                    <tr>
                        <td><?php echo htmlspecialchars($fila['nombre_completo']); ?></td>
                        <td><?php echo htmlspecialchars($fila['cargo']); ?></td>
                        <td class="text-center font-weight-bold <?php echo $saldo_class; ?>">
                            <?php echo $saldo; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>