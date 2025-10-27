<?php
// views/practicantes/index.php
$counts = $data['counts'];
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

<div class="card shadow-sm">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <ul class="nav nav-tabs card-header-tabs" id="filtro-estados" role="tablist">
                <li class="nav-item">
                    <button class="nav-link active" id="tab-todos" data-bs-toggle="tab" data-filtro="Todos" type="button">
                        Todos <span class="badge bg-secondary"><?php echo $counts['total']; ?></span>
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" id="tab-activos" data-bs-toggle="tab" data-filtro="Activo" type="button">
                        Activos <span class="badge bg-success"><?php echo $counts['activos']; ?></span>
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" id="tab-cesados" data-bs-toggle="tab" data-filtro="Cesado" type="button">
                        Cesados <span class="badge bg-danger"><?php echo $counts['cesados']; ?></span>
                    </button>
                </li>
            </ul>
            <div class="ms-3" style="width: 300px;">
                <input type="text" id="buscador" class="form-control form-control-sm" placeholder="Buscar por DNI, Nombre, Escuela...">
            </div>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover table-sm">
                <thead class="table-light">
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
                        <?php foreach ($data['practicantes'] as $p): 
                            $estado = htmlspecialchars($p['estado_general']);
                        ?>
                        <tr data-estado="<?php echo $estado; ?>">
                            <td><?php echo htmlspecialchars($p['dni']); ?></td>
                            <td><?php echo htmlspecialchars($p['apellidos']); ?></td>
                            <td><?php echo htmlspecialchars($p['nombres']); ?></td>
                            <td><?php echo htmlspecialchars($p['universidad_nombre']); ?></td>
                            <td><?php echo htmlspecialchars($p['escuela_nombre']); ?></td>
                            <td>
                                <span class="badge <?php echo $estado == 'Activo' ? 'bg-success' : 'bg-danger'; ?>">
                                    <?php echo $estado; ?>
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
document.addEventListener('DOMContentLoaded', function() {
    
    const botonesFiltro = document.querySelectorAll('#filtro-estados .nav-link');
    const buscador = document.getElementById('buscador');
    const tablaPracticantes = document.getElementById('tabla-practicantes');
    const filas = tablaPracticantes.getElementsByTagName('tr');
    let filtroActivo = 'Todos'; // Estado inicial

    function filtrarTabla() {
        let textoBusqueda = buscador.value.toLowerCase();

        for (let fila of filas) {
            // Ignorar la fila de "no hay practicantes"
            if (fila.getElementsByTagName('td').length < 7) continue;

            let estadoFila = fila.getAttribute('data-estado');
            let textoFila = fila.innerText.toLowerCase();

            // 1. Comprobar filtro de ESTADO (pestaña)
            const PasaFiltroEstado = (filtroActivo === 'Todos' || filtroActivo === estadoFila);

            // 2. Comprobar filtro de BÚSQUEDA
            const PasaFiltroBusqueda = (textoBusqueda === '' || textoFila.includes(textoBusqueda));

            // 3. Mostrar/Ocultar
            if (PasaFiltroEstado && PasaFiltroBusqueda) {
                fila.style.display = ""; // (reset a 'table-row')
            } else {
                fila.style.display = "none";
            }
        }
    }

    // Evento para las pestañas
    botonesFiltro.forEach(boton => {
        boton.addEventListener('click', function(e) {
            e.preventDefault();
            // Quitar 'active' de todos (lo gestiona Bootstrap, pero por si acaso)
            // botonesFiltro.forEach(b => b.classList.remove('active'));
            // this.classList.add('active');
            
            filtroActivo = this.getAttribute('data-filtro');
            filtrarTabla();
        });
    });

    // Evento para el buscador
    buscador.addEventListener('keyup', filtrarTabla);

    // Carga inicial (para asegurar que 'Todos' esté activo)
    filtrarTabla();

});
</script>