<?php
// views/convenios/index.php
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><?php echo htmlspecialchars($data['titulo'] ?? 'Convenios'); ?></h1>
</div>

<?php 
// Mostrar mensajes de éxito o error
if (isset($_SESSION['mensaje_exito'])) {
    echo '<div class="alert alert-success alert-dismissible fade show" role="alert">' . $_SESSION['mensaje_exito'] . '
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
    unset($_SESSION['mensaje_exito']);
}
if (isset($_SESSION['mensaje_error'])) {
    echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">' . $_SESSION['mensaje_error'] . '
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
    unset($_SESSION['mensaje_error']);
}
?>

<div class="card mb-4">
    <div class="card-header">
        <i class="bi bi-person-check-fill"></i> Candidatos Aceptados (Pendientes de Convenio)
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-sm table-hover">
                <thead>
                    <tr>
                        <th>DNI</th>
                        <th>Apellidos y Nombres</th>
                        <th>Escuela</th>
                        <th>Fecha Aceptado</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($data['pendientes'])): ?>
                        <tr><td colspan="5" class="text-center text-muted">No hay candidatos pendientes.</td></tr>
                    <?php else: ?>
                        <?php foreach ($data['pendientes'] as $p): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($p['dni']); ?></td>
                            <td><?php echo htmlspecialchars($p['apellidos'] . ', ' . $p['nombres']); ?></td>
                            <td><?php echo htmlspecialchars($p['escuela_nombre']); ?></td>
                            <td><?php echo date("d/m/Y", strtotime($p['fecha_postulacion'])); ?></td>
                            <td>
                                <a href="index.php?c=convenios&m=crear&proceso_id=<?php echo $p['proceso_id']; ?>&practicante_id=<?php echo $p['practicante_id']; ?>" class="btn btn-sm btn-primary">
                                    <i class="bi bi-plus-circle"></i> Crear Convenio
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
       <i class="bi bi-file-earmark-text-fill"></i> Convenios Vigentes
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-sm table-hover">
                 <thead>
                    <tr>
                        <th>DNI</th>
                        <th>Apellidos y Nombres</th>
                        <th>Tipo</th>
                        <th>Estado</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                     <?php if (empty($data['vigentes'])): ?>
                        <tr><td colspan="5" class="text-center text-muted">No hay convenios vigentes.</td></tr>
                    <?php else: ?>
                        <?php foreach ($data['vigentes'] as $c): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($c['dni']); ?></td>
                            <td><?php echo htmlspecialchars($c['apellidos'] . ', ' . $c['nombres']); ?></td>
                            <td><span class="badge bg-info text-dark"><?php echo htmlspecialchars($c['tipo_practica']); ?></span></td>
                            <td><span class="badge bg-success"><?php echo htmlspecialchars($c['estado_convenio']); ?></span></td>
                            <td>
                                <a href="index.php?c=convenios&m=gestionar&id=<?php echo $c['convenio_id']; ?>" class="btn btn-sm btn-success">
                                    <i class="bi bi-pencil-square"></i> Gestionar
                                </a>
                                <a href="index.php?c=practicantes&m=ver&id=<?php echo $c['practicante_id']; ?>" class="btn btn-sm btn-outline-secondary" title="Ver Perfil Completo">
                                    <i class="bi bi-person-fill"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>