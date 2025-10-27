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
    echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">' . $_SESSION['mensaje_error'] . '
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
    unset($_SESSION['mensaje_error']);
}
?>

<div class="card">
    <div class="card-body">
        <form action="index.php?c=reclutamiento&m=guardar" method="POST" class="needs-validation" novalidate enctype="multipart/form-data">
            
            <h5 class="mb-3">Datos Personales del Candidato</h5>
            <div class="row g-3">
                <div class="col-sm-4">
                    <label for="dni" class="form-label">DNI <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="dni" name="dni" required 
                           pattern="[0-9]{8}" maxlength="8" title="El DNI debe tener 8 dígitos numéricos.">
                    <div class="invalid-feedback">El DNI debe tener 8 dígitos numéricos.</div>
                </div>
                <div class="col-sm-4">
                    <label for="nombres" class="form-label">Nombres <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="nombres" name="nombres" required
                           pattern="[A-Za-zÀ-ÿ\s]+" title="Solo se permiten letras y espacios.">
                    <div class="invalid-feedback">Los nombres son obligatorios (solo letras).</div>
                </div>
                <div class="col-sm-4">
                    <label for="apellidos" class="form-label">Apellidos <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="apellidos" name="apellidos" required
                           pattern="[A-Za-zÀ-ÿ\s]+" title="Solo se permiten letras y espacios.">
                    <div class="invalid-feedback">Los apellidos son obligatorios (solo letras).</div>
                </div>

                <div class="col-sm-4">
                    <label for="fecha_nacimiento" class="form-label">Fecha de Nacimiento <span class="text-danger">*</span></label>
                    <input type="date" class="form-control" id="fecha_nacimiento" name="fecha_nacimiento" required>
                    <div class="invalid-feedback">La fecha de nacimiento es obligatoria.</div>
                </div>
                <div class="col-sm-4">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email" placeholder="usuario@ejemplo.com">
                    <div class="invalid-feedback">Ingrese un correo válido.</div>
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
                    <label for="tipo_practica" class="form-label">Tipo de Práctica <span class="text-danger">*</span></label>
                    <select class="form-select" id="tipo_practica" name="tipo_practica" required>
                        <option value="">Seleccione un tipo...</option>
                        <option value="PREPROFESIONAL">PREPROFESIONAL</option>
                        <option value="PROFESIONAL">PROFESIONAL</option>
                    </select>
                    <div class="invalid-feedback">Seleccione el tipo de práctica.</div>
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
            
            <h5 class="mb-3">Carga de Documentos (CV, DNI, DDJJ)</h5>
            <p>Para la unión automática de PDFs (drag-and-drop y merge), se necesita una librería externa (como Dropzone.js para el front-end y FPDI/TCPDF para el back-end). Por ahora, he implementado la subida de archivos individuales.</p>

            <div class="row g-3">
                <div class="col-md-6">
                    <label for="file_cv" class="form-label">1. Curriculum Vitae (CV) <span class="text-danger">*</span></label>
                    <input class="form-control" type="file" id="file_cv" name="file_cv" accept=".pdf" required>
                    <div class="invalid-feedback">El CV es obligatorio (PDF).</div>
                </div>
                <div class="col-md-6">
                    <label for="file_dni" class="form-label">2. Documento de Identidad (DNI) <span class="text-danger">*</span></label>
                    <input class="form-control" type="file" id="file_dni" name="file_dni" accept=".pdf" required>
                    <div class="invalid-feedback">El DNI es obligatorio (PDF).</div>
                </div>
                <div class="col-md-6" id="campo_carta">
                    <label for="file_carta" class="form-label">3. Carta de Presentación (Pre-profesional)</label>
                    <input class="form-control" type="file" id="file_carta" name="file_carta" accept=".pdf">
                </div>
                 <div class="col-md-6">
                    <label for="file_ddjj" class="form-label">4. Declaraciones Juradas</label>
                    <input class="form-control" type="file" id="file_ddjj" name="file_ddjj" accept=".pdf">
                </div>
            </div>

            <hr class="my-4">

            <button class="btn btn-primary btn-lg" type="submit">Guardar Registro</button>
        </form>
    </div>
</div>

<script>
    // --- SCRIPT PARA OCULTAR CARTA DE PRESENTACIÓN ---
    document.getElementById('tipo_practica').addEventListener('change', function() {
        const tipo = this.value;
        const campoCarta = document.getElementById('campo_carta');
        const inputCarta = document.getElementById('file_carta');
        
        if (tipo === 'PROFESIONAL') {
            campoCarta.style.display = 'none';
            inputCarta.required = false; // No es requerido si está oculto
        } else {
            campoCarta.style.display = 'block';
            // Puedes decidir si la carta es obligatoria para PRE
            // inputCarta.required = true; 
        }
    });

    // --- SCRIPT PARA UNIVERSIDADES/ESCUELAS (EXISTENTE) ---
    const todasLasEscuelas = <?php echo $data['escuelas_json']; ?>;

    document.getElementById('universidad_id').addEventListener('change', function() {
        const universidadId = this.value;
        const selectEscuela = document.getElementById('escuela_id');
        
        selectEscuela.innerHTML = '<option value="">Cargando...</option>';
        
        if (universidadId) {
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

    // --- SCRIPT VALIDACIÓN BOOTSTRAP (EXISTENTE) ---
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