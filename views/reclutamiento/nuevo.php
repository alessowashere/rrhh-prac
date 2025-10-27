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
                    <div class="invalid-feedback" id="dni_feedback">El DNI debe tener 8 dígitos numéricos.</div>
                </div>
                <div class="col-sm-4">
                    <label for="nombres" class="form-label">Nombres <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="nombres" name="nombres" required
                           pattern="[A-Za-zÀ-ÿ\s]+" title="Solo se permiten letras y espacios.">
                    <div class="invalid-feedback" id="nombres_feedback">Los nombres son obligatorios (solo letras).</div>
                </div>
                <div class="col-sm-4">
                    <label for="apellidos" class="form-label">Apellidos <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="apellidos" name="apellidos" required
                           pattern="[A-Za-zÀ-ÿ\s]+" title="Solo se permiten letras y espacios.">
                    <div class="invalid-feedback" id="apellidos_feedback">Los apellidos son obligatorios (solo letras).</div>
                </div>

                <div class="col-sm-4">
                    <label for="fecha_nacimiento" class="form-label">Fecha de Nacimiento <span class="text-danger">*</span></label>
                    <input type="date" class="form-control" id="fecha_nacimiento" name="fecha_nacimiento" required>
                    <div class="invalid-feedback">La fecha de nacimiento es obligatoria.</div>
                </div>
                <div class="col-sm-4">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email" placeholder="usuario@ejemplo.com">
                    <div class="invalid-feedback" id="email_feedback">Debe ser un correo válido (ej: usuario@dominio.com).</div>
                </div>
                <div class="col-sm-4">
                    <label for="telefono" class="form-label">Teléfono / Celular</label>
                    <input type="tel" class="form-control" id="telefono" name="telefono"
                           pattern="[0-9]{9}" maxlength="9">
                    <div class="invalid-feedback" id="telefono_feedback">El celular debe tener 9 dígitos numéricos.</div>
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
            <p>Se validará que los archivos sean PDF y no superen los 10MB c/u.</p>

            <div class="row g-3">
                <div class="col-md-6">
                    <label for="file_cv" class="form-label">1. Curriculum Vitae (CV) <span class="text-danger">*</span></label>
                    <input class="form-control" type="file" id="file_cv" name="file_cv" accept=".pdf" required>
                    <div class="invalid-feedback">El CV es obligatorio (PDF).</div>
                    <div id="file_cv_error" class="form-text text-danger"></div>
                </div>
                <div class="col-md-6">
                    <label for="file_dni" class="form-label">2. Documento de Identidad (DNI) <span class="text-danger">*</span></label>
                    <input class="form-control" type="file" id="file_dni" name="file_dni" accept=".pdf" required>
                    <div class="invalid-feedback">El DNI es obligatorio (PDF).</div>
                    <div id="file_dni_error" class="form-text text-danger"></div>
                </div>
                <div class="col-md-6" id="campo_carta">
                    <label for="file_carta" class="form-label">3. Carta de Presentación (Pre-profesional)</label>
                    <input class="form-control" type="file" id="file_carta" name="file_carta" accept=".pdf">
                    <div id="file_carta_error" class="form-text text-danger"></div>
                </div>
                 <div class="col-md-6">
                    <label for="file_ddjj" class="form-label">4. Declaraciones Juradas</label>
                    <input class="form-control" type="file" id="file_ddjj" name="file_ddjj" accept=".pdf">
                    <div id="file_ddjj_error" class="form-text text-danger"></div>
                </div>
            </div>

            <hr class="my-4">

            <button class="btn btn-primary btn-lg" type="submit">Guardar Registro</button>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    
    // --- SCRIPT PARA OCULTAR CARTA DE PRESENTACIÓN ---
    const tipoPracticaSelect = document.getElementById('tipo_practica');
    if (tipoPracticaSelect) {
        tipoPracticaSelect.addEventListener('change', function() {
            const tipo = this.value;
            const campoCarta = document.getElementById('campo_carta');
            const inputCarta = document.getElementById('file_carta');
            
            if (tipo === 'PROFESIONAL') {
                campoCarta.style.display = 'none';
                inputCarta.required = false; // No es requerido si está oculto
                inputCarta.value = ''; // Limpia el valor si se cambia
            } else {
                campoCarta.style.display = 'block';
                // inputCarta.required = true; // Descomenta si la carta es obligatoria para PRE
            }
        });
        // Ejecutar al cargar por si se recarga la página con un valor
        tipoPracticaSelect.dispatchEvent(new Event('change'));
    }

    // --- SCRIPT PARA UNIVERSIDADES/ESCUELAS (EXISTENTE) ---
    const todasLasEscuelas = <?php echo $data['escuelas_json']; ?>;
    const uniSelect = document.getElementById('universidad_id');
    
    if (uniSelect) {
        uniSelect.addEventListener('change', function() {
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
    }

    // --- SCRIPT VALIDACIÓN BOOTSTRAP (EXISTENTE) ---
    // (Esto activa los pop-ups de validación al ENVIAR)
    (function () {
      'use strict'
      var forms = document.querySelectorAll('.needs-validation')
      Array.prototype.slice.call(forms)
        .forEach(function (form) {
          form.addEventListener('submit', function (event) {
            // Validar campos personalizados antes de enviar
            if (!form.checkValidity()) {
              event.preventDefault()
              event.stopPropagation()
            }
            form.classList.add('was-validated')
          }, false)
        })
    })();


    // ==========================================================
    // NUEVO: VALIDACIONES EN VIVO (MIENTRAS ESCRIBES)
    // ==========================================================

    /**
     * Función reutilizable para validar campos de NÚMEROS
     * @param {string} inputId - El ID del <input>
     * @param {number} longitudExacta - El número de dígitos requeridos
     */
    function validarNumeros(inputId, longitudExacta) {
        const input = document.getElementById(inputId);
        const feedback = document.getElementById(inputId + '_feedback'); // (ej: 'dni_feedback')
        if (!input || !feedback) return;

        input.addEventListener('input', function(e) {
            // 1. Forzar solo números
            this.value = this.value.replace(/[^0-9]/g, '');

            // 2. Validar longitud (usando setCustomValidity for Bootstrap)
            if (this.value.length > 0 && this.value.length < longitudExacta) {
                this.setCustomValidity(`Debe tener ${longitudExacta} dígitos.`);
                feedback.textContent = `Faltan ${longitudExacta - this.value.length} dígitos.`;
            } else if (this.value.length == longitudExacta) {
                this.setCustomValidity(''); // Válido
                feedback.textContent = `Debe tener ${longitudExacta} dígitos.`; // Mensaje por defecto
            } else {
                // Si está vacío (y no es 'required', como el teléfono)
                if (this.value.length == 0 && !this.required) {
                    this.setCustomValidity('');
                    feedback.textContent = `Debe tener ${longitudExacta} dígitos.`;
                } else {
                     this.setCustomValidity(`Debe tener ${longitudExacta} dígitos.`);
                     feedback.textContent = `Debe tener ${longitudExacta} dígitos.`;
                }
            }
        });
    }

    /**
     * Función reutilizable para validar campos de LETRAS
     * @param {string} inputId - El ID del <input>
     */
    function validarSoloLetras(inputId) {
        const input = document.getElementById(inputId);
        if (!input) return;
        
        input.addEventListener('input', function(e) {
            // Reemplaza cualquier cosa que no sea letra (incluidas tildes) o espacio
            this.value = this.value.replace(/[^A-Za-zÀ-ÿ\s]/g, '');
        });
    }

    /**
     * Función reutilizable para validar EMAIL
     * @param {string} inputId - El ID del <input>
     */
    function validarEmail(inputId) {
        const input = document.getElementById(inputId);
        const feedback = document.getElementById(inputId + '_feedback');
        if (!input || !feedback) return;
        
        // Regex simple para email
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

        input.addEventListener('input', function(e) {
            // Si el campo no está vacío y no pasa el regex
            if (this.value.length > 0 && !emailRegex.test(this.value)) {
                this.setCustomValidity('Formato de correo inválido.'); // Marca como inválido
                feedback.textContent = 'Formato incorrecto (ej: usuario@dominio.com)';
            } else {
                this.setCustomValidity(''); // Válido
                feedback.textContent = 'Debe ser un correo válido (ej: usuario@dominio.com).';
            }
        });
    }

    /**
     * Función reutilizable para validar TAMAÑO DE ARCHIVOS
     * @param {string} inputId - El ID del <input>
     */
    function validarArchivo(inputId) {
        const fileInput = document.getElementById(inputId);
        const errorDiv = document.getElementById(inputId + '_error');
        if (!fileInput || !errorDiv) return;
        
        // Define el tamaño máximo (ej: 10 MB)
        const MAX_FILE_SIZE = 10 * 1024 * 1024; // 10 Megabytes en bytes

        fileInput.addEventListener('change', function(e) {
            errorDiv.textContent = ''; // Limpiar error previo
            this.setCustomValidity(''); // Limpiar validez
            
            if (this.files && this.files.length > 0) {
                const file = this.files[0];

                // a. Validar tipo (aunque 'accept' ya ayuda)
                if (file.type !== 'application/pdf') {
                    errorDiv.textContent = 'Error: El archivo debe ser un PDF.';
                    this.value = ''; // Limpiar el input
                    this.setCustomValidity('El archivo debe ser PDF.');
                    return;
                }

                // b. Validar tamaño
                if (file.size > MAX_FILE_SIZE) {
                    const tamanoEnMB = (file.size / (1024 * 1024)).toFixed(2);
                    errorDiv.textContent = `Error: El archivo es muy grande (${tamanoEnMB} MB). El límite es 10 MB.`;
                    this.value = ''; // Limpiar el input
                    this.setCustomValidity('El archivo supera las 10MB.');
                    return;
                }
            }
        });
    }
    
    // --- CONECTAR LOS EVENTOS ---
    validarNumeros('dni', 8);
    validarNumeros('telefono', 9);
    validarSoloLetras('nombres');
    validarSoloLetras('apellidos');
    validarEmail('email');
    
    validarArchivo('file_cv');
    validarArchivo('file_dni');
    validarArchivo('file_carta');
    validarArchivo('file_ddjj');

});
</script>