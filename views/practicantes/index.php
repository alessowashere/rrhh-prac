<?php // views/practicantes/index.php ?>
<div class="d-flex justify-content-between align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="bi bi-people text-primary"></i> Directorio General</h1>
    <div class="btn-group">
        <a href="index.php?c=practicantes&m=importar" class="btn btn-outline-success">
            <i class="bi bi-file-earmark-excel"></i> Carga Masiva (CSV)
        </a>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-header bg-white py-3">
        <div class="row align-items-center">
            <div class="col-md-6">
                <ul class="nav nav-pills small" id="filtro-estados">
                    <li class="nav-item"><button class="nav-link active" data-filtro="Todos">Todos (<?php echo $data['counts']['total']; ?>)</button></li>
                    <li class="nav-item"><button class="nav-link" data-filtro="Activo">Activos</button></li>
                    <li class="nav-item"><button class="nav-link" data-filtro="Cesado">Cesados</button></li>
                </ul>
            </div>
            <div class="col-md-6 text-end">
                <input type="text" id="buscador" class="form-control form-control-sm d-inline-block w-75" placeholder="Buscar por DNI, Nombre o Escuela...">
            </div>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Practicante</th>
                    <th>Escuela Profesional</th>
                    <th class="text-center">Documentación</th>
                    <th>Estado</th>
                    <th class="text-end">Acciones</th>
                </tr>
            </thead>
            <tbody id="tabla-practicantes">
                <?php foreach ($data['practicantes'] as $p): 
                    $docs = (int)$p['total_docs'];
                    $color_semaforo = ($docs >= 4) ? 'success' : (($docs >= 2) ? 'warning' : 'danger');
                ?>
                <tr data-estado="<?php echo $p['estado_general']; ?>">
                    <td>
                        <div class="fw-bold text-dark"><?php echo $p['apellidos'] . ", " . $p['nombres']; ?></div>
                        <small class="text-muted">DNI: <?php echo $p['dni']; ?></small>
                    </td>
                    <td><small><?php echo $p['escuela_nombre']; ?></small></td>
                    <td class="text-center">
                        <span class="badge rounded-pill bg-<?php echo $color_semaforo; ?>-subtle text-<?php echo $color_semaforo; ?> border border-<?php echo $color_semaforo; ?>">
                            <i class="bi bi-file-earmark-check"></i> <?php echo $docs; ?> docs
                        </span>
                    </td>
                    <td>
                        <span class="badge bg-<?php echo $p['estado_general'] == 'Activo' ? 'success' : 'secondary'; ?>-subtle text-<?php echo $p['estado_general'] == 'Activo' ? 'success' : 'secondary'; ?> border">
                            <?php echo $p['estado_general']; ?>
                        </span>
                    </td>
                    <td class="text-end">
                        <a href="index.php?c=practicantes&m=ver&id=<?php echo $p['practicante_id']; ?>" class="btn btn-sm btn-light border" title="Ver Perfil"><i class="bi bi-eye"></i></a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
// Lógica de filtrado (Similar a la anterior pero optimizada para el nuevo diseño)
document.addEventListener('DOMContentLoaded', function() {
    const botones = document.querySelectorAll('#filtro-estados .nav-link');
    const buscador = document.getElementById('buscador');
    const filas = document.querySelectorAll('#tabla-practicantes tr');

    function filtrar() {
        const busqueda = buscador.value.toLowerCase();
        const filtro = document.querySelector('#filtro-estados .active').dataset.filtro;
        filas.forEach(f => {
            const texto = f.innerText.toLowerCase();
            const estado = f.dataset.estado;
            const matchBusqueda = texto.includes(busqueda);
            const matchEstado = filtro === 'Todos' || estado === filtro;
            f.style.display = (matchBusqueda && matchEstado) ? '' : 'none';
        });
    }

    botones.forEach(b => b.addEventListener('click', (e) => {
        botones.forEach(btn => btn.classList.remove('active'));
        e.target.classList.add('active');
        filtrar();
    }));
    buscador.addEventListener('input', filtrar);
});
</script>