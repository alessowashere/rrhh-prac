<?php
// views/reclutamiento/index.php
// $data['procesos'] ahora contiene TODOS los procesos
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
// Mensajes de sesión
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
        <ul class="nav nav-tabs card-header-tabs" id="filtro-estados" role="tablist">
            <li class="nav-item">
                <button class="nav-link active" id="tab-evaluacion" data-bs-toggle="tab" data-filtro="En Evaluación" type="button">
                    En Evaluación <span class="badge bg-warning text-dark"></span>
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link" id="tab-aceptados" data-bs-toggle="tab" data-filtro="Aceptado" type="button">
                    Aceptados <span class="badge bg-success"></span>
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link" id="tab-rechazados" data-bs-toggle="tab" data-filtro="Rechazado" type="button">
                    Rechazados <span class="badge bg-danger"></span>
                </button>
            </li>
             <li class="nav-item">
                <button class="nav-link" id="tab-pendientes" data-bs-toggle="tab" data-filtro="Pendiente" type="button">
                    Pendientes <span class="badge bg-secondary"></span>
                </button>
            </li>
        </ul>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th scope="col">DNI</th>
                        <th scope="col">Nombre Completo</th>
                        <th scope="col">Escuela</th>
                        <th scope="col">F. Postulación</th>
                        <th scope="col">Nota Final</th>
                        <th scope="col" class="col-estado">Estado</th>
                        <th scope="col">Acciones</th>
                    </tr>
                </thead>
                <tbody id="tabla-procesos">
                    <?php if (empty($data['procesos'])): ?>
                        <tr>
                            <td colspan="7" class="text-center">No hay candidatos registrados.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($data['procesos'] as $proceso): 
                            $estado = htmlspecialchars($proceso['estado_proceso']);
                            $badge_class = '';
                            switch($estado) {
                                case 'Aceptado': $badge_class = 'bg-success'; break;
                                case 'Rechazado': $badge_class = 'bg-danger'; break;
                                case 'Pendiente': $badge_class = 'bg-secondary'; break;
                                default: $badge_class = 'bg-warning text-dark';
                            }
                        ?>
                        <tr data-estado="<?php echo $estado; ?>">
                            <td><?php echo htmlspecialchars($proceso['dni']); ?></td>
                            <td><?php echo htmlspecialchars($proceso['apellidos'] . ', ' . $proceso['nombres']); ?></td>
                            <td><?php echo htmlspecialchars($proceso['universidad_nombre'] . ' - ' . $proceso['escuela_nombre']); ?></td>
                            <td><?php echo date("d/m/Y", strtotime($proceso['fecha_postulacion'])); ?></td>
                            <td>
                                <strong><?php echo htmlspecialchars($proceso['puntuacion_final_entrevista'] ?? 'N/A'); ?></strong>
                            </td>
                            <td class="col-estado">
                                <span class="badge <?php echo $badge_class; ?>">
                                    <?php echo $estado; ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($estado == 'En Evaluación' || $estado == 'Pendiente'): ?>
                                <a href="index.php?c=reclutamiento&m=evaluar&id=<?php echo $proceso['proceso_id']; ?>" class="btn btn-sm btn-info" title="Evaluar">
                                    <i class="bi bi-pencil-square"></i>
                                </a>
                                <?php endif; ?>
                                
                                <?php if ($estado != 'Aceptado'): ?>
                                <a href="index.php?c=reclutamiento&m=actualizarEstado&id=<?php echo $proceso['proceso_id']; ?>&estado=Aceptado" class="btn btn-sm btn-success" title="Aceptar" onclick="return confirm('¿Está seguro de ACEPTAR este candidato?');">
                                    <i class="bi bi-check-lg"></i>
                                </a>
                                <?php endif; ?>
                                
                                <?php if ($estado != 'Rechazado'): ?>
                                <a href="index.php?c=reclutamiento&m=actualizarEstado&id=<?php echo $proceso['proceso_id']; ?>&estado=Rechazado" class="btn btn-sm btn-danger" title="Rechazar" onclick="return confirm('¿Está seguro de RECHAZAR este candidato?');">
                                    <i class="bi bi-x-lg"></i>
                                </a>
                                <?php endif; ?>

                                <?php if ($estado == 'Rechazado' || $estado == 'En Evaluación'): ?>
                                <a href="index.php?c=reclutamiento&m=actualizarEstado&id=<?php echo $proceso['proceso_id']; ?>&estado=Pendiente" class="btn btn-sm btn-secondary" title="Mover a Pendiente (Próximo Proceso)" onclick="return confirm('¿Mover a PENDIENTE para un próximo proceso?');">
                                    <i class="bi bi-clock-history"></i>
                                </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const botonesFiltro = document.querySelectorAll('#filtro-estados .nav-link');
    const tablaProcesos = document.getElementById('tabla-procesos');
    const filas = tablaProcesos.getElementsByTagName('tr');

    function filtrarTabla(estadoFiltro) {
        let count = 0;
        for (let i = 0; i < filas.length; i++) {
            const fila = filas[i];
            const estadoFila = fila.getAttribute('data-estado');
            
            if (estadoFila === estadoFiltro) {
                fila.style.display = '';
                count++;
            } else {
                fila.style.display = 'none';
            }
        }
        return count;
    }

    function actualizarContadores() {
        botonesFiltro.forEach(boton => {
            const filtro = boton.getAttribute('data-filtro');
            let count = 0;
            for (let i = 0; i < filas.length; i++) {
                if (filas[i].getAttribute('data-estado') === filtro) {
                    count++;
                }
            }
            boton.querySelector('.badge').textContent = count;
        });
    }

    botonesFiltro.forEach(boton => {
        boton.addEventListener('click', function(e) {
            e.preventDefault();
            const filtro = this.getAttribute('data-filtro');
            filtrarTabla(filtro);
        });
    });

    // Carga inicial
    actualizarContadores();
    // Mostrar el filtro por defecto ('En Evaluación')
    document.getElementById('tab-evaluacion').click();
});
</script>