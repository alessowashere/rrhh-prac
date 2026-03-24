<?php
// views/personas/index.php
// (La variable $listaPersonas viene del controlador)
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0 text-gray-800">Listado de Personal</h1>
    <a href="index.php?controller=persona&action=create" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i> Añadir Nuevo Empleado
    </a>
</div>

<?php if (isset($_GET['status'])): ?>
    <?php
    $status = $_GET['status'];
    $mensaje = '';
    $tipoAlerta = 'success'; // Default

    switch ($status) {
        case 'creado':
            $mensaje = 'Empleado registrado con éxito.';
            break;
        case 'actualizado':
            $mensaje = 'Datos del empleado actualizados con éxito.';
            break;
        case 'eliminado':
            $mensaje = 'Empleado eliminado correctamente.';
            break;
        case 'error':
            $mensaje = 'Ocurrió un error con la operación.';
            $tipoAlerta = 'danger';
            break;
    }
    ?>
    <div class="alert alert-<?php echo $tipoAlerta; ?> alert-dismissible fade show" role="alert">
        <?php echo $mensaje; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>
<div class="card shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover table-bordered align-middle">
                <thead class="table-light">
                    <tr>
                        <th>N° Empleado</th>
                        <th>Nombre Completo</th>
                        <th>Cargo</th>
                        <th>Área / Lugar</th>
                        <th>Estado</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($listaPersonas)): ?>
                        <tr>
                            <td colspan="6" class="text-center">No hay personas registradas.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($listaPersonas as $persona): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($persona['numero_empleado']); ?></td>
                                <td><?php echo htmlspecialchars($persona['nombre_completo']); ?></td>
                                <td><?php echo htmlspecialchars($persona['cargo']); ?></td>
                                <td><?php echo htmlspecialchars($persona['area']); ?></td>
                                <td>
                                    <span class="badge <?php echo $persona['estado'] == 'ACTIVO' ? 'text-bg-success' : 'text-bg-danger'; ?>">
                                        <?php echo htmlspecialchars($persona['estado']); ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <a href="index.php?controller=persona&action=edit&id=<?php echo $persona['id']; ?>" class="btn btn-warning btn-sm" title="Editar">
                                        <i class="bi bi-pencil-fill"></i>
                                    </a>
                                    <a href="index.php?controller=persona&action=delete&id=<?php echo $persona['id']; ?>"
                                       class="btn btn-danger btn-sm"
                                       title="Eliminar"
                                       onclick="return confirm('¿Estás seguro de que deseas eliminar a esta persona? Se borrarán también sus períodos y vacaciones asociadas.');">
                                        <i class="bi bi-trash-fill"></i>
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