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
        <div class="card shadow-sm mb-3">
            <div class="card-header"><i class="bi bi-person-badge"></i> Candidato Aceptado</div>
            <div class="card-body">
                <p><strong>DNI:</strong> <?php echo htmlspecialchars($p['dni']); ?></p>
                <p><strong>Nombres:</strong> <?php echo htmlspecialchars($p['apellidos'] . ', ' . $p['nombres']); ?></p>
                <p><strong>Escuela:</strong> <?php echo htmlspecialchars($p['escuela_nombre']); ?></p>
            </div>
        </div>
    </div>
    
    <div class="col-md-8">
        <div class="card shadow-sm mb-3">
            <div class="card-header"><i class="bi bi-file-earmark-plus"></i> Datos del Convenio</div>
            <div class="card-body">
                <form action="index.php?c=convenios&m=guardar" method="POST" class="needs-validation" novalidate>
                    <input type="hidden" name="practicante_id" value="<?php echo $p['practicante_id']; ?>">
                    <input type="hidden" name="proceso_id" value="<?php echo $data['proceso_id']; ?>">
                    
                    <h5 class="mb-3">1. Datos Generales del Convenio</h5>
                    <div class="row g-3">
                        <div class="col-12">
                            <label for="tipo_practica_display" class="form-label">Tipo de Práctica (Automático)</label>
                            <input type="text" class="form-control bg-light" id="tipo_practica_display" 
                                   value="<?php echo htmlspecialchars($data['tipo_practica']); ?>" readonly>
                            <input type="hidden" name="tipo_practica" 
                                   value="<?php echo htmlspecialchars($data['tipo_practica']); ?>">
                        </div>
                    </div>

                    <hr class="my-4">
                    
                    <h5 class="mb-3">2. Datos del Primer Período</h5>
                    <div class="row g-3">
                        <div class="col-sm-6">
                            <label for="fecha_inicio" class="form-label">Fecha de Inicio <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio" value="<?php echo date('Y-m-d'); ?>" required>
                            <div class="invalid-feedback">Fecha de inicio es obligatoria.</div>
                        </div>
                        <div class="col-sm-6">
                            <label for="fecha_fin" class="form-label">Fecha de Fin <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="fecha_fin" name="fecha_fin" required>
                            <div class="invalid-feedback">Fecha de fin es obligatoria.</div>
                        </div>
                        
                        <div class="col-12">
                             <label class="form-label">Calcular Fecha Fin (desde inicio):</label>
                             <div class="btn-group btn-group-sm" role="group">
                                <button type="button" class="btn btn-outline-secondary btn-calc-fecha" data-meses="4">4 Meses</button>
                                <button type="button" class="btn btn-outline-secondary btn-calc-fecha" data-meses="6">6 Meses</button>
                                <button type="button" class="btn btn-outline-secondary btn-calc-fecha" data-meses="12">1 Año</button>
                            </div>
                        </div>

                        <div class="col-sm-6 mt-3">
                            <label for="local_id" class="form-label">Local <span class="text-danger">*</span></label>
                            <select class="form-select" id="local_id" name="local_id" required>
                                <option value="">Seleccione...</option>
                                <?php foreach($data['locales'] as $loc): ?>
                                <option value="<?php echo $loc['local_id']; ?>"><?php echo htmlspecialchars($loc['nombre']); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <div class="invalid-feedback">Seleccione un local.</div>
                        </div>
                        <div class="col-sm-6 mt-3">
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
                    <button class="btn btn-primary btn-lg" type="submit">
                       <i class="bi bi-save"></i> Guardar Datos e Ir a Subir Firma
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    
    function calcularFechaFin(fechaInicioStr, meses) {
        if (!fechaInicioStr) return '';
        const partes = fechaInicioStr.split('-'); 
        const anio = parseInt(partes[0], 10);
        const mes = parseInt(partes[1], 10) - 1; 
        const dia = parseInt(partes[2], 10);
        const fechaFin = new Date(anio, mes, dia);
        fechaFin.setMonth(fechaFin.getMonth() + parseInt(meses, 10));
        fechaFin.setDate(fechaFin.getDate() - 1);
        const anioFin = fechaFin.getFullYear();
        const mesFin = (fechaFin.getMonth() + 1).toString().padStart(2, '0');
        const diaFin = fechaFin.getDate().toString().padStart(2, '0');
        return `${anioFin}-${mesFin}-${diaFin}`;
    }

    const inputFechaInicio = document.getElementById('fecha_inicio');
    const inputFechaFin = document.getElementById('fecha_fin');
    const botonesCalc = document.querySelectorAll('.btn-calc-fecha');

    botonesCalc.forEach(boton => {
        boton.addEventListener('click', function() {
            const meses = this.getAttribute('data-meses');
            const fechaInicio = inputFechaInicio.value;
            if (fechaInicio) {
                inputFechaFin.value = calcularFechaFin(fechaInicio, meses);
                 // Trigger cambio para validación si es necesario
                 inputFechaFin.dispatchEvent(new Event('change'));
            } else {
                // alert('Por favor, seleccione una Fecha de Inicio primero.');
                inputFechaInicio.focus();
                // Opcional: Poner fecha de hoy si está vacío
                 if (!inputFechaInicio.value) {
                     const hoy = new Date().toISOString().split('T')[0];
                     inputFechaInicio.value = hoy;
                     inputFechaFin.value = calcularFechaFin(hoy, meses);
                     inputFechaFin.dispatchEvent(new Event('change'));
                 }
            }
        });
    });

    // Validación Bootstrap (existente)
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
});
</script>