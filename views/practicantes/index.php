<?php
// views/practicantes/index.php
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><?php echo htmlspecialchars($data['titulo'] ?? 'Practicantes'); ?></h1>
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

<div class="card">
    <div class="card-header">
        <div class="row">
            <div class="col-md-6">
                Listado de Practicantes (Activos y Cesados)
            </div>
            <div class="col-md-6">
                <input type="text" id="buscador" class="form-control form-control-sm" placeholder="Buscar por DNI, Nombre o Escuela...">
            </div>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th scope="col">DNI</th>
                        <th scope="col">Apellidos</th>
                        <th scope="col">Nombres</th>
                        <th scope="col">Universidad</th>
                        <th scope="col">Escuela Profesional</th>
                        <th scope="col">Estado</th>
                        <th scope="col">Acciones</th>
                    </tr>
                </thead>
                <tbody id="tabla-practicantes">
                    <?php if (empty($data['practicantes'])): ?>
                        <tr>
                            <td colspan="7" class="text-center">No hay practicantes registrados.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($data['practicantes'] as $p): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($p['dni']); ?></td>
                            <td><?php echo htmlspecialchars($p['apellidos']); ?></td>
                            <td><?php echo htmlspecialchars($p['nombres']); ?></td>
                            <td><?php echo htmlspecialchars($p['universidad_nombre']); ?></td>
                            <td><?php echo htmlspecialchars($p['escuela_nombre']); ?></td>
                            <td>
                                <span class="badge <?php echo $p['estado_general'] == 'Activo' ? 'bg-success' : 'bg-danger'; ?>">
                                    <?php echo htmlspecialchars($p['estado_general']); ?>
                                </span>
                            </td>
                            <td>
                                <a href="index.php?c=practicantes&m=ver&id=<?php echo $p['practicante_id']; ?>" class="btn btn-sm btn-primary" title="Ver Perfil">
                                    <i class="bi bi-eye-fill"></i>
                                </a>
                                <a href="index.php?c=practicantes&m=editar&id=<?php echo $p['practicante_id']; ?>" class="btn btn-sm btn-warning" title="Editar Datos">
                                    <i class="bi bi-pencil-fill"></i>
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

<script>
document.getElementById('buscador').addEventListener('keyup', function() {
    let filtro = this.value.toLowerCase();
    let filas = document.getElementById('tabla-practicantes').getElementsByTagName('tr');
    
    for (let i = 0; i < filas.length; i++) {
        let textoFila = filas[i].innerText.toLowerCase();
        if (textoFila.includes(filtro)) {
            filas[i].style.display = "";
        } else {
            filas[i].style.display = "none";
        }
    }
});
</script>