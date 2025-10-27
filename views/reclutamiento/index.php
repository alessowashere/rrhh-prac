<?php
// views/reclutamiento/index.php
// $data['procesos'] está disponible gracias al controlador
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><?php echo htmlspecialchars($data['titulo'] ?? 'Reclutamiento'); ?></h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="index.php?c=reclutamiento&m=nuevo" class="btn btn-sm btn-outline-primary">
            <i class="bi bi-person-plus-fill"></i>
            Registrar Nuevo Candidato
        </a>
    </div>
</div>

<?php 
// Mostrar mensajes de éxito o error (usando $_SESSION)
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


<div class="card">
    <div class="card-header">
        Candidatos en Proceso (<?php echo count($data['procesos']); ?>)
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th scope="col">DNI</th>
                        <th scope="col">Nombre Completo</th>
                        <th scope="col">Universidad</th>
                        <th scope="col">Escuela</th>
                        <th scope="col">F. Postulación</th>
                        <th scope="col">Estado</th>
                        <th scope="col">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($data['procesos'])): ?>
                        <tr>
                            <td colspan="7" class="text-center">No hay candidatos 'En Evaluación'.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($data['procesos'] as $proceso): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($proceso['dni']); ?></td>
                            <td><?php echo htmlspecialchars($proceso['apellidos'] . ', ' . $proceso['nombres']); ?></td>
                            <td><?php echo htmlspecialchars($proceso['universidad_nombre']); ?></td>
                            <td><?php echo htmlspecialchars($proceso['escuela_nombre']); ?></td>
                            <td><?php echo date("d/m/Y", strtotime($proceso['fecha_postulacion'])); ?></td>
                            <td>
                                <span class="badge bg-warning text-dark">
                                    <?php echo htmlspecialchars($proceso['estado_proceso']); ?>
                                </span>
                            </td>
                            <td>
                                <a href="index.php?c=reclutamiento&m=evaluar&id=<?php echo $proceso['proceso_id']; ?>" class="btn btn-sm btn-info" title="Evaluar">
                                    <i class="bi bi-pencil-square"></i>
                                </a>
                                
                                <a href="index.php?c=reclutamiento&m=actualizarEstado&id=<?php echo $proceso['proceso_id']; ?>&estado=Aceptado" class="btn btn-sm btn-success" title="Aceptar" onclick="return confirm('¿Está seguro de ACEPTAR este candidato?');">
                                    <i class="bi bi-check-lg"></i>
                                </a>
                                
                                <a href="index.php?c=reclutamiento&m=actualizarEstado&id=<?php echo $proceso['proceso_id']; ?>&estado=Rechazado" class="btn btn-sm btn-danger" title="Rechazar" onclick="return confirm('¿Está seguro de RECHAZAR este candidato?');">
                                    <i class="bi bi-x-lg"></i>
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