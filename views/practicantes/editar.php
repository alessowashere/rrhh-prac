<?php
// views/practicantes/editar.php
$p = $data['practicante'];
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><?php echo htmlspecialchars($data['titulo']); ?></h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="index.php?c=practicantes&m=ver&id=<?php echo $p['practicante_id']; ?>" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Cancelar y Volver
        </a>
    </div>
</div>

<?php 
if (isset($_SESSION['mensaje_error'])) {
    echo '<div class="alert alert-danger" role="alert">' . $_SESSION['mensaje_error'] . '</div>';
    unset($_SESSION['mensaje_error']);
}
?>

<div class="card">
    <div class="card-body">
        <form action="index.php?c=practicantes&m=actualizar" method="POST" class="needs-validation" novalidate>
            <input type="hidden" name="practicante_id" value="<?php echo $p['practicante_id']; ?>">
            
            <h5 class="mb-3">Datos Personales</h5>
            <div class="row g-3">
                <div class="col-sm-4">
                    <label for="dni" class="form-label">DNI <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="dni" name="dni" required value="<?php echo htmlspecialchars($p['dni']); ?>">
                </div>
                <div class="col-sm-4">
                    <label for="nombres" class="form-label">Nombres <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="nombres" name="nombres" required value="<?php echo htmlspecialchars($p['nombres']); ?>">
                </div>
                <div class="col-sm-4">
                    <label for="apellidos" class="form-label">Apellidos <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="apellidos" name="apellidos" required value="<?php echo htmlspecialchars($p['apellidos']); ?>">
                </div>

                <div class="col-sm-4">
                    <label for="fecha_nacimiento" class="form-label">Fecha de Nacimiento</label>
                    <input type="date" class="form-control" id="fecha_nacimiento" name="fecha_nacimiento" value="<?php echo htmlspecialchars($p['fecha_nacimiento']); ?>">
                </div>
                <div class="col-sm-4">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($p['email']); ?>">
                </div>
                <div class="col-sm-4">
                    <label for="telefono" class="form-label">Teléfono</label>
                    <input type="tel" class="form-control" id="telefono" name="telefono" value="<?php echo htmlspecialchars($p['telefono']); ?>">
                </div>
            </div>

            <hr class="my-4">

            <h5 class="mb-3">Datos Académicos y Estado</h5>
            <div class="row g-3">
                 <div class="col-sm-6">
                    <label for="universidad_id" class="form-label">Universidad <span class="text-danger">*</span></label>
                    <select class="form-select" id="universidad_id" name="universidad_id" required>
                        <option value="">Seleccione...</option>
                        <?php foreach ($data['universidades'] as $uni): ?>
                            <option value="<?php echo $uni['universidad_id']; ?>" <?php echo ($uni['universidad_id'] == $p['universidad_id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($uni['nombre']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="col-sm-6">
                    <label for="escuela_id" class="form-label">Escuela Profesional <span class="text-danger">*</span></label>
                    <select class="form-select" id="escuela_id" name="escuela_id" required>
                        <option value="<?php echo htmlspecialchars($p['escuela_profesional_id']); ?>">Cargando...</option>
                    </select>
                </div>

                <div class="col-sm-4">
                    <label for="promedio_general" class="form-label">Promedio General</label>
                    <input type="number" class="form-control" id="promedio_general" name="promedio_general" step="0.01" min="0" max="20" value="<?php echo htmlspecialchars($p['promedio_general']); ?>">
                </div>
                
                <div class="col-sm-4">
                    <label for="estado_general" class="form-label">Estado General <span class="text-danger">*</span></label>
                    <select class="form-select" id="estado_general" name="estado_general" required>
                        <option value="Activo" <?php echo ($p['estado_general'] == 'Activo') ? 'selected' : ''; ?>>Activo</option>
                        <option value="Cesado" <?php echo ($p['estado_general'] == 'Cesado') ? 'selected' : ''; ?>>Cesado</option>
                    </select>
                </div>
            </div>

            <hr class="my-4">

            <button class="btn btn-primary btn-lg" type="submit">Actualizar Practicante</button>
        </form>
    </div>
</div>

<script>
    const todasLasEscuelas = <?php echo $data['escuelas_json']; ?>;
    const idUniversidadSeleccionada = "<?php echo $p['universidad_id']; ?>";
    const idEscuelaSeleccionada = "<?php echo $p['escuela_profesional_id']; ?>";

    const selectUniversidad = document.getElementById('universidad_id');
    const selectEscuela = document.getElementById('escuela_id');

    function cargarEscuelas(universidadId) {
        selectEscuela.innerHTML = '<option value="">Cargando...</option>';
        
        if (universidadId) {
            const escuelasFiltradas = todasLasEscuelas.filter(escuela => escuela.universidad_id == universidadId);
            
            selectEscuela.innerHTML = '<option value="">Seleccione una escuela...</option>';
            
            if (escuelasFiltradas.length > 0) {
                escuelasFiltradas.forEach(escuela => {
                    const option = document.createElement('option');
                    option.value = escuela.escuela_id;
                    option.textContent = escuela.nombre;
                    // Seleccionar la escuela guardada
                    if (escuela.escuela_id == idEscuelaSeleccionada) {
                        option.selected = true;
                    }
                    selectEscuela.appendChild(option);
                });
                selectEscuela.disabled = false;
            } else {
                selectEscuela.innerHTML = '<option value="">No hay escuelas</option>';
                selectEscuela.disabled = true;
            }
        } else {
            selectEscuela.innerHTML = '<option value="">Seleccione universidad...</option>';
            selectEscuela.disabled = true;
        }
    }

    // Evento change
    selectUniversidad.addEventListener('change', function() {
        cargarEscuelas(this.value);
    });

    // Carga inicial al abrir el formulario
    cargarEscuelas(idUniversidadSeleccionada);

    // Validación Bootstrap
    (function () {
      'use strict'
      var forms = document.querySelectorAll('.needs-validation')
      Array.prototype.slice.call(forms)
        .forEach(function (form) {
          form.addEventListener('submit', function (event) {
            if (!form.checkValidity()) {
              event.preventDefault()
              event.stopPropagation()
            }
            form.classList.add('was-validated')
          }, false)
        })
    })()
</script>