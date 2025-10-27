<?php
// views/reclutamiento/nuevo.php
// $data['universidades'], $data['escuelas'], $data['escuelas_json']
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><?php echo htmlspecialchars($data['titulo'] ?? 'Nuevo Candidato'); ?></h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="index.php?c=reclutamiento" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left"></i>
            Cancelar y Volver
        </a>
    </div>
</div>

<?php 
// Mostrar mensajes de error si la validación falló
if (isset($_SESSION['mensaje_error'])) {
    echo '<div class="alert alert-danger" role="alert">' . $_SESSION['mensaje_error'] . '</div>';
    unset($_SESSION['mensaje_error']);
}
?>

<div class="card">
    <div class="card-body">
        <form action="index.php?c=reclutamiento&m=guardar" method="POST" class="needs-validation" novalidate>
            
            <h5 class="mb-3">Datos Personales del Candidato</h5>
            <div class="row g-3">
                <div class="col-sm-4">
                    <label for="dni" class="form-label">DNI <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="dni" name="dni" required maxlength="15">
                    <div class="invalid-feedback">El DNI es obligatorio.</div>
                </div>
                <div class="col-sm-4">
                    <label for="nombres" class="form-label">Nombres <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="nombres" name="nombres" required>
                    <div class="invalid-feedback">Los nombres son obligatorios.</div>
                </div>
                <div class="col-sm-4">
                    <label for="apellidos" class="form-label">Apellidos <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="apellidos" name="apellidos" required>
                    <div class="invalid-feedback">Los apellidos son obligatorios.</div>
                </div>

                <div class="col-sm-4">
                    <label for="fecha_nacimiento" class="form-label">Fecha de Nacimiento</label>
                    <input type="date" class="form-control" id="fecha_nacimiento" name="fecha_nacimiento">
                </div>
                <div class="col-sm-4">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email" placeholder="usuario@ejemplo.com">
                </div>
                <div class="col-sm-4">
                    <label for="telefono" class="form-label">Teléfono</label>
                    <input type="tel" class="form-control" id="telefono" name="telefono">
                </div>
            </div>

            <hr class="my-4">

            <h5 class="mb-3">Datos Académicos y Postulación</h5>
            <div class="row g-3">
                 <div class="col-sm-6">
                    <label for="universidad_id" class="form-label">Universidad <span class="text-danger">*</span></label>
                    <select class="form-select" id="universidad_id" name="universidad_id" required>
                        <option value="">Seleccione una universidad...</option>
                        <?php foreach ($data['universidades'] as $uni): ?>
                            <option value="<?php echo $uni['universidad_id']; ?>">
                                <?php echo htmlspecialchars($uni['nombre']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="invalid-feedback">Seleccione una universidad.</div>
                </div>
                
                <div class="col-sm-6">
                    <label for="escuela_id" class="form-label">Escuela Profesional <span class="text-danger">*</span></label>
                    <select class="form-select" id="escuela_id" name="escuela_id" required disabled>
                        <option value="">Primero seleccione una universidad...</option>
                        </select>
                    <div class="invalid-feedback">Seleccione una escuela.</div>
                </div>

                <div class="col-sm-4">
                    <label for="promedio_general" class="form-label">Promedio General</label>
                    <input type="number" class="form-control" id="promedio_general" name="promedio_general" step="0.01" min="0" max="20">
                </div>
                
                <div class="col-sm-4">
                    <label for="fecha_postulacion" class="form-label">Fecha Postulación <span class="text-danger">*</span></label>
                    <input type="date" class="form-control" id="fecha_postulacion" name="fecha_postulacion" value="<?php echo date('Y-m-d'); ?>" required>
                    <div class="invalid-feedback">La fecha es obligatoria.</div>
                </div>
            </div>

            <hr class="my-4">

            <button class="btn btn-primary btn-lg" type="submit">Guardar Registro</button>
        </form>
    </div>
</div>

<script>
    // Pasamos los datos de PHP a Javascript
    const todasLasEscuelas = <?php echo $data['escuelas_json']; ?>;

    document.getElementById('universidad_id').addEventListener('change', function() {
        const universidadId = this.value;
        const selectEscuela = document.getElementById('escuela_id');
        
        // Limpiar opciones anteriores
        selectEscuela.innerHTML = '<option value="">Cargando...</option>';
        
        if (universidadId) {
            // Filtrar escuelas
            const escuelasFiltradas = todasLasEscuelas.filter(escuela => escuela.universidad_id == universidadId);
            
            selectEscuela.innerHTML = '<option value="">Seleccione una escuela...</option>';
            
            if (escuelasFiltradas.length > 0) {
                escuelasFiltradas.forEach(escuela => {
                    const option = document.createElement('option');
                    option.value = escuela.escuela_id;
                    option.textContent = escuela.nombre;
                    selectEscuela.appendChild(option);
                });
                selectEscuela.disabled = false;
            } else {
                selectEscuela.innerHTML = '<option value="">No hay escuelas para esta universidad</option>';
                selectEscuela.disabled = true;
            }
        } else {
            selectEscuela.innerHTML = '<option value="">Primero seleccione una universidad...</option>';
            selectEscuela.disabled = true;
        }
    });

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