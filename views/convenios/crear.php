<?php
// views/convenios/crear.php
$p = $data['practicante'];
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><?php echo htmlspecialchars($data['titulo']); ?></h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="index.php?c=convenios" class="btn btn-sm btn-outline-secondary">
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

<div class="row">
    <div class="col-md-4">
        <div class="card">
            <div class="card-header"><i class="bi bi-person-badge"></i> Candidato Aceptado</div>
            <div class="card-body">
                <p><strong>DNI:</strong> <?php echo htmlspecialchars($p['dni']); ?></p>
                <p><strong>Nombres:</strong> <?php echo htmlspecialchars($p['apellidos'] . ', ' . $p['nombres']); ?></p>
                <p><strong>Escuela:</strong> <?php echo htmlspecialchars($p['escuela_nombre']); ?></p>
            </div>
        </div>
    </div>
    
    <div class="col-md-8">
        <div class="card">
            <div class="card-header"><i class="bi bi-file-earmark-plus"></i> Datos del Convenio</div>
            <div class="card-body">
                <form action="index.php?c=convenios&m=guardar" method="POST" class="needs-validation" novalidate>
                    <input type="hidden" name="practicante_id" value="<?php echo $p['practicante_id']; ?>">
                    <input type="hidden" name="proceso_id" value="<?php echo $data['proceso_id']; ?>">
                    
                    <h5 class="mb-3">1. Datos Generales del Convenio</h5>
                    <div class="row g-3">
                        <div class="col-12">
                            <label for="tipo_practica" class="form-label">Tipo de Práctica <span class="text-danger">*</span></label>
                            <select class="form-select" id="tipo_practica" name="tipo_practica" required>
                                <option value="">Seleccione...</option>
                                <option value="PREPROFESIONAL">PREPROFESIONAL</option>
                                <option value="PROFESIONAL">PROFESIONAL</option>
                            </select>
                            <div class="invalid-feedback">Debe seleccionar un tipo de práctica.</div>
                        </div>
                    </div>

                    <hr class="my-4">
                    
                    <h5 class="mb-3">2. Datos del Primer Período</h5>
                    <div class="row g-3">
                        <div class="col-sm-6">
                            <label for="fecha_inicio" class="form-label">Fecha de Inicio <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio" required>
                            <div class="invalid-feedback">Fecha de inicio es obligatoria.</div>
                        </div>
                        <div class="col-sm-6">
                            <label for="fecha_fin" class="form-label">Fecha de Fin <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="fecha_fin" name="fecha_fin" required>
                            <div class="invalid-feedback">Fecha de fin es obligatoria.</div>
                        </div>
                        <div class="col-sm-6">
                            <label for="local_id" class="form-label">Local <span class="text-danger">*</span></label>
                            <select class="form-select" id="local_id" name="local_id" required>
                                <option value="">Seleccione...</option>
                                <?php foreach($data['locales'] as $loc): ?>
                                <option value="<?php echo $loc['local_id']; ?>"><?php echo htmlspecialchars($loc['nombre']); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <div class="invalid-feedback">Seleccione un local.</div>
                        </div>
                        <div class="col-sm-6">
                            <label for="area_id" class="form-label">Área <span class="text-danger">*</span></label>
                            <select class="form-select" id="area_id" name="area_id" required>
                                <option value="">Seleccione...</option>
                                 <?php foreach($data['areas'] as $area): ?>
                                <option value="<?php echo $area['area_id']; ?>"><?php echo htmlspecialchars($area['nombre']); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <div class="invalid-feedback">Seleccione un área.</div>
                        </div>
                    </div>
                    
                    <hr class="my-4">
                    <button class="btn btn-primary btn-lg" type="submit">Guardar Convenio e Iniciar Prácticas</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
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