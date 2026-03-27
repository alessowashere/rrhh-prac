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
if (isset($_SESSION['mensaje_error'])) {
    echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">' . $_SESSION['mensaje_error'] . '
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
    unset($_SESSION['mensaje_error']);
}
?>

<div class="card shadow-sm">
    <div class="card-body">
        <form action="index.php?c=reclutamiento&m=guardar" method="POST" class="needs-validation" novalidate enctype="multipart/form-data">
            
            <h5 class="mb-3 text-primary border-bottom pb-2">1. Datos Personales del Candidato</h5>
            <div class="row g-3 mb-4">
                <div class="col-sm-4">
                    <label for="dni" class="form-label">DNI <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="dni" name="dni" required pattern="[0-9]{8}" maxlength="8">
                    <div class="invalid-feedback" id="dni_feedback">El DNI debe tener 8 dígitos.</div>
                </div>
                <div class="col-sm-4">
                    <label for="nombres" class="form-label">Nombres <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="nombres" name="nombres" required>
                    <div class="invalid-feedback" id="nombres_feedback">Obligatorio (solo letras).</div>
                </div>
                <div class="col-sm-4">
                    <label for="apellidos" class="form-label">Apellidos <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="apellidos" name="apellidos" required>
                    <div class="invalid-feedback" id="apellidos_feedback">Obligatorio (solo letras).</div>
                </div>

                <div class="col-sm-4">
                    <label for="fecha_nacimiento" class="form-label">Fecha de Nacimiento <span class="text-danger">*</span></label>
                    <input type="date" class="form-control" id="fecha_nacimiento" name="fecha_nacimiento" required>
                    <div class="invalid-feedback">Obligatorio.</div>
                </div>
                <div class="col-sm-4">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email" placeholder="usuario@ejemplo.com">
                    <div class="invalid-feedback" id="email_feedback">Correo válido requerido.</div>
                </div>
                <div class="col-sm-4">
                    <label for="telefono" class="form-label">Teléfono / Celular</label>
                    <input type="tel" class="form-control" id="telefono" name="telefono" maxlength="9">
                    <div class="invalid-feedback" id="telefono_feedback">Debe tener 9 dígitos.</div>
                </div>
            </div>

            <h5 class="mb-3 text-primary border-bottom pb-2">2. Datos Académicos y Postulación</h5>
            <div class="row g-3 mb-4">
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
                    <div class="invalid-feedback">Obligatorio.</div>
                </div>

                <div class="col-sm-4">
                    <label for="promedio_general" class="form-label">Promedio General</label>
                    <input type="number" class="form-control" id="promedio_general" name="promedio_general" step="0.01" min="0" max="20">
                </div>
                
                <div class="col-sm-4">
                    <label for="fecha_postulacion" class="form-label">Fecha Postulación <span class="text-danger">*</span></label>
                    <input type="date" class="form-control" id="fecha_postulacion" name="fecha_postulacion" value="<?php echo date('Y-m-d'); ?>" required>
                    <div class="invalid-feedback">Obligatorio.</div>
                </div>
            </div>
            
            <h5 class="mb-3 text-primary border-bottom pb-2">3. Clasificador de Documentos</h5>
            <p class="text-muted small">Arrastra tus PDFs aquí. Podrás ver una <b>vista previa</b> de cada hoja y arrastrarla a la caja correspondiente de forma horizontal.</p>

            <div id="main-dropzone" class="border border-primary border-2 border-dashed rounded-3 p-3 text-center mb-3 bg-light" style="cursor: pointer; transition: 0.3s;">
                <i class="bi bi-cloud-arrow-up fs-2 text-primary"></i>
                <h6 class="mt-2 mb-1 fw-bold">Arrastra o haz clic para subir PDFs</h6>
                <input type="file" id="file-input-virtual" accept=".pdf" class="d-none" multiple>
            </div>

            <div class="row g-2">
                <div class="col-12 mb-2">
                    <div class="card border-secondary h-100 shadow-sm">
                        <div class="card-header bg-secondary text-white fw-bold py-2">
                            <i class="bi bi-inbox"></i> Bandeja (Páginas sin asignar)
                        </div>
                        <div class="card-body bg-light p-2">
                            <div id="bandeja-sueltas" class="d-flex flex-row flex-wrap gap-2 sortable-box align-content-start" style="min-height: 140px; border: 1px dashed #ccc; border-radius: 5px; padding: 10px;">
                                <div class="w-100 text-center text-muted align-self-center fst-italic id-empty-msg">Sube un PDF y las páginas aparecerán aquí...</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card border-primary h-100 shadow-sm">
                        <div class="card-header bg-primary text-white fw-bold py-2"><i class="bi bi-person-vcard"></i> 1. DNI <span class="text-danger">*</span></div>
                        <div class="card-body p-2 bg-light">
                            <div id="caja-dni" class="d-flex flex-row flex-wrap gap-2 sortable-box h-100 align-content-start" style="min-height: 140px; border: 1px dashed #0d6efd; border-radius: 5px; padding: 8px;"></div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="card border-success h-100 shadow-sm">
                        <div class="card-header bg-success text-white fw-bold py-2"><i class="bi bi-file-earmark-person"></i> 2. CV <span class="text-danger">*</span></div>
                        <div class="card-body p-2 bg-light">
                            <div id="caja-cv" class="d-flex flex-row flex-wrap gap-2 sortable-box h-100 align-content-start" style="min-height: 140px; border: 1px dashed #198754; border-radius: 5px; padding: 8px;"></div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6" id="contenedor-caja-carta">
                    <div class="card border-info h-100 shadow-sm">
                        <div class="card-header bg-info text-dark fw-bold py-2"><i class="bi bi-envelope-paper"></i> 3. Carta</div>
                        <div class="card-body p-2 bg-light">
                            <div id="caja-carta" class="d-flex flex-row flex-wrap gap-2 sortable-box h-100 align-content-start" style="min-height: 140px; border: 1px dashed #0dcaf0; border-radius: 5px; padding: 8px;"></div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="card border-warning h-100 shadow-sm">
                        <div class="card-header bg-warning text-dark fw-bold py-2"><i class="bi bi-files"></i> 4. Otros / DDJJ</div>
                        <div class="card-body p-2 bg-light">
                            <div id="caja-ddjj" class="d-flex flex-row flex-wrap gap-2 sortable-box h-100 align-content-start" style="min-height: 140px; border: 1px dashed #ffc107; border-radius: 5px; padding: 8px;"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-none">
                <input type="file" id="file_cv" name="file_cv" accept=".pdf">
                <input type="file" id="file_dni" name="file_dni" accept=".pdf">
                <input type="file" id="file_carta" name="file_carta" accept=".pdf">
                <input type="file" id="file_ddjj" name="file_ddjj" accept=".pdf">
            </div>
            
            <div id="pdf_global_error" class="alert alert-danger d-none mt-3 fw-bold fs-6"></div>

            <hr class="my-4">

            <button class="btn btn-primary btn-lg w-100 shadow" type="submit" id="btn-submit-form">
                <i class="bi bi-save"></i> Guardar Registro y Procesar Documentos
            </button>
        </form>
    </div>
</div>

<style>
    /* Diseño compacto y horizontal para las páginas */
    .pdf-page-item {
        cursor: grab;
        background: white;
        border: 1px solid #ced4da;
        border-radius: 6px;
        padding: 6px;
        width: 105px; /* Ancho fijo para mantener la cuadrícula */
        display: flex;
        flex-direction: column;
        align-items: center;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        position: relative;
        transition: transform 0.2s, border-color 0.2s;
    }
    .pdf-page-item:hover {
        border-color: #0d6efd;
        transform: translateY(-2px);
    }
    .pdf-page-item:active { cursor: grabbing; }
    
    /* Estilo para el Canvas (Vista Previa) */
    .pdf-thumb-canvas {
        width: 100%;
        height: 110px;
        object-fit: contain;
        background-color: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 4px;
        margin-bottom: 5px;
    }

    .sortable-ghost { 
        opacity: 0.5; 
        background-color: #e9ecef; 
        border: 2px dashed #6c757d;
    }
</style>

<script src="https://cdn.jsdelivr.net/npm/pdf-lib@1.17.1/dist/pdf-lib.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>

<script>
// Configurar el worker de PDF.js (Requisito de la librería)
pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.worker.min.js';

document.addEventListener('DOMContentLoaded', function() {
    
    // --- LÓGICA DE UI EXISTENTE (Validaciones y Combos) ---
    const tipoPracticaSelect = document.getElementById('tipo_practica');
    if (tipoPracticaSelect) {
        tipoPracticaSelect.addEventListener('change', function() {
            if (this.value === 'PROFESIONAL') {
                document.getElementById('contenedor-caja-carta').style.display = 'none';
            } else {
                document.getElementById('contenedor-caja-carta').style.display = 'block';
            }
        });
        tipoPracticaSelect.dispatchEvent(new Event('change'));
    }

    const todasLasEscuelas = <?php echo $data['escuelas_json'] ?? '[]'; ?>;
    const uniSelect = document.getElementById('universidad_id');
    if (uniSelect) {
        uniSelect.addEventListener('change', function() {
            const selectEscuela = document.getElementById('escuela_id');
            selectEscuela.innerHTML = '<option value="">Seleccione una escuela...</option>';
            if (this.value) {
                const escuelasFiltradas = todasLasEscuelas.filter(e => e.universidad_id == this.value);
                escuelasFiltradas.forEach(e => selectEscuela.add(new Option(e.nombre, e.escuela_id)));
                selectEscuela.disabled = false;
            } else {
                selectEscuela.disabled = true;
            }
        });
    }

    // --- MAGIA DE PDF: DIVIDIR, PREVISUALIZAR Y ORDENAR ---
    const dropzone = document.getElementById('main-dropzone');
    const fileInputVirtual = document.getElementById('file-input-virtual');
    const globalError = document.getElementById('pdf_global_error');
    
    const boxes = {
        bandeja: document.getElementById('bandeja-sueltas'),
        file_dni: document.getElementById('caja-dni'),
        file_cv: document.getElementById('caja-cv'),
        file_carta: document.getElementById('caja-carta'),
        file_ddjj: document.getElementById('caja-ddjj')
    };

    let pagesData = {}; // Guarda los bytes reales del PDF

    // 1. Inicializar Sortable
    Object.values(boxes).forEach(box => {
        new Sortable(box, {
            group: 'shared-pages',
            animation: 150,
            ghostClass: 'sortable-ghost',
            onAdd: () => {
                const emptyMsg = boxes.bandeja.querySelector('.id-empty-msg');
                if (emptyMsg && boxes.bandeja.children.length > 1) emptyMsg.style.display = 'none';
            }
        });
    });

    // 2. Eventos Dropzone
    dropzone.addEventListener('click', () => fileInputVirtual.click());
    dropzone.addEventListener('dragover', (e) => { e.preventDefault(); dropzone.classList.replace('bg-light', 'bg-white'); });
    dropzone.addEventListener('dragleave', () => dropzone.classList.replace('bg-white', 'bg-light'));
    dropzone.addEventListener('drop', (e) => {
        e.preventDefault();
        dropzone.classList.replace('bg-white', 'bg-light');
        procesarPDFs(e.dataTransfer.files);
    });
    fileInputVirtual.addEventListener('change', (e) => procesarPDFs(e.target.files));

    // 3. Procesar, Separar y Dibujar Previsualizaciones
    async function procesarPDFs(files) {
        if (files.length === 0) return;
        
        dropzone.innerHTML = '<div class="spinner-border text-primary my-2"></div><h6 class="fw-bold">Generando Vistas Previas...</h6>';
        const emptyMsg = boxes.bandeja.querySelector('.id-empty-msg');
        if (emptyMsg) emptyMsg.style.display = 'none';

        for (let file of files) {
            if (file.type !== 'application/pdf') continue;

            try {
                // A. Leer para PDF.js (Vista previa visual)
                const fileUrl = URL.createObjectURL(file);
                const pdfJsDoc = await pdfjsLib.getDocument(fileUrl).promise;

                // B. Leer para PDF-Lib (Bytes reales para guardar luego)
                const arrayBuffer = await file.arrayBuffer();
                const pdfLibDoc = await PDFLib.PDFDocument.load(arrayBuffer);
                const numPages = pdfLibDoc.getPageCount();

                for (let i = 0; i < numPages; i++) {
                    // Extraer la página física
                    const newPdf = await PDFLib.PDFDocument.create();
                    const [copiedPage] = await newPdf.copyPages(pdfLibDoc, [i]);
                    newPdf.addPage(copiedPage);
                    const pdfBytes = await newPdf.save();
                    
                    const pageId = 'page_' + Date.now() + '_' + i;
                    pagesData[pageId] = pdfBytes; // Guardamos en memoria

                    // Crear el contenedor HTML de la tarjetita
                    const pageItem = document.createElement('div');
                    pageItem.className = 'pdf-page-item';
                    pageItem.dataset.id = pageId;
                    
                    // Botón para eliminar
                    pageItem.innerHTML = `<button type="button" class="btn-close btn-sm position-absolute top-0 end-0 m-1 bg-white rounded-circle border" style="font-size:0.5rem; z-index:10;" title="Quitar"></button>`;
                    
                    // Dibujar el Canvas (Miniatura)
                    const canvas = document.createElement('canvas');
                    canvas.className = 'pdf-thumb-canvas';
                    
                    // Renderizar página con PDF.js
                    const page = await pdfJsDoc.getPage(i + 1);
                    // Calculamos una escala pequeña para no consumir mucha memoria
                    const viewport = page.getViewport({ scale: 0.5 }); 
                    canvas.width = viewport.width;
                    canvas.height = viewport.height;
                    const ctx = canvas.getContext('2d');
                    await page.render({ canvasContext: ctx, viewport: viewport }).promise;

                    // Textos descriptivos
                    const textDiv = document.createElement('div');
                    textDiv.className = 'w-100 text-center';
                    textDiv.innerHTML = `<div class="fw-bold" style="font-size:0.75rem;">Pág. ${i + 1}</div>`;

                    pageItem.appendChild(canvas);
                    pageItem.appendChild(textDiv);

                    // Evento Eliminar
                    pageItem.querySelector('.btn-close').addEventListener('click', (e) => {
                        e.stopPropagation();
                        delete pagesData[pageId];
                        pageItem.remove();
                    });

                    // Añadir a la bandeja horizontal
                    boxes.bandeja.appendChild(pageItem);
                }
            } catch (error) {
                console.error("Error PDF:", error);
                alert(`Error al previsualizar "${file.name}". Puede tener contraseña.`);
            }
        }
        
        dropzone.innerHTML = '<i class="bi bi-cloud-check-fill fs-2 text-success"></i><h6 class="mt-2 mb-1 fw-bold">¡Vistas previas generadas!</h6><small>Arrastra más si necesitas.</small>';
        fileInputVirtual.value = ''; 
    }

    // 4. Interceptar el Envío (Validación y Ensamblaje)
    const form = document.querySelector('form.needs-validation');
    const btnSubmit = document.getElementById('btn-submit-form');

    form.addEventListener('submit', async function(event) {
        event.preventDefault();
        event.stopPropagation();
        
        globalError.classList.add('d-none');
        globalError.innerHTML = '';

        if (!form.checkValidity()) {
            form.classList.add('was-validated');
            return;
        }

        // --- VALIDACIÓN ESTRICTA DE CAJAS ---
        const totalDni = boxes.file_dni.querySelectorAll('.pdf-page-item').length;
        const totalCv = boxes.file_cv.querySelectorAll('.pdf-page-item').length;

        if (totalDni === 0) {
            globalError.innerHTML = '<i class="bi bi-exclamation-triangle-fill"></i> Debes arrastrar al menos una hoja a la caja del <b>1. DNI</b>.';
            globalError.classList.remove('d-none');
            return;
        }
        if (totalCv === 0) {
            globalError.innerHTML = '<i class="bi bi-exclamation-triangle-fill"></i> Debes arrastrar al menos una hoja a la caja del <b>2. CV</b>.';
            globalError.classList.remove('d-none');
            return;
        }

        btnSubmit.disabled = true;
        btnSubmit.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Ensamblando y Guardando...';

        try {
            const inputsDestino = ['file_dni', 'file_cv', 'file_carta', 'file_ddjj'];

            for (let inputName of inputsDestino) {
                const caja = boxes[inputName];
                const pageElements = caja.querySelectorAll('.pdf-page-item');

                if (pageElements.length > 0) {
                    const mergedPdf = await PDFLib.PDFDocument.create();

                    for (let el of pageElements) {
                        const id = el.dataset.id;
                        const pageBytes = pagesData[id];
                        const tempPdf = await PDFLib.PDFDocument.load(pageBytes);
                        const [copiedPage] = await mergedPdf.copyPages(tempPdf, [0]);
                        mergedPdf.addPage(copiedPage);
                    }

                    const mergedBytes = await mergedPdf.save();
                    const mergedBlob = new Blob([mergedBytes], { type: 'application/pdf' });
                    const mergedFile = new File([mergedBlob], `${inputName}.pdf`, { type: 'application/pdf' });

                    const dt = new DataTransfer();
                    dt.items.add(mergedFile);
                    
                    // Asignación directa al input asegurando que el ID es idéntico a inputName
                    document.getElementById(inputName).files = dt.files;
                }
            }

            form.submit();

        } catch (error) {
            console.error('Error ensamblando:', error);
            globalError.innerHTML = '<i class="bi bi-x-circle-fill"></i> Error al unir PDFs. Revisa la consola.';
            globalError.classList.remove('d-none');
            btnSubmit.disabled = false;
            btnSubmit.innerHTML = '<i class="bi bi-save"></i> Guardar Registro y Procesar Documentos';
        }
    });
});
</script>