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

<div class="card shadow-sm mb-4">
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
            <p class="text-muted small">Arrastra tus PDFs aquí. Usa la <i class="bi bi-zoom-in text-primary"></i> <b>lupa</b> para ampliar la página y leer los datos.</p>

            <div id="main-dropzone" class="border border-primary border-2 border-dashed rounded-3 p-3 text-center mb-3 bg-light" style="cursor: pointer; transition: 0.3s;">
                <i class="bi bi-cloud-arrow-up fs-2 text-primary"></i>
                <h6 class="mt-2 mb-1 fw-bold">Arrastra o haz clic para subir PDFs</h6>
                <input type="file" id="file-input-virtual" accept=".pdf" class="d-none" multiple>
            </div>

            <div class="row g-2">
                <div class="col-12 mb-2">
                    <div class="card border-secondary h-100 shadow-sm">
                        <div class="card-header bg-secondary text-white fw-bold py-2"><i class="bi bi-inbox"></i> Bandeja de Páginas Extraídas</div>
                        <div class="card-body bg-light p-2">
                            <div id="bandeja-sueltas" class="d-flex flex-row flex-wrap gap-2 sortable-box align-content-start" style="min-height: 140px; border: 1px dashed #ccc; border-radius: 5px; padding: 10px;">
                                <div class="w-100 text-center text-muted align-self-center fst-italic id-empty-msg">Las páginas aparecerán aquí...</div>
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

<div class="modal fade" id="modalVistaPreviaPDF" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-scrollable"> <div class="modal-content">
      <div class="modal-header bg-dark text-white py-2">
        <h5 class="modal-title fs-5"><i class="bi bi-file-earmark-pdf text-danger"></i> Vista Ampliada</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body text-center bg-secondary" style="min-height: 70vh;">
          <div class="spinner-border text-light mt-5" role="status" id="modal-spinner"></div>
          <canvas id="modal-canvas-hd" class="img-fluid shadow-lg rounded d-none" style="max-width: 100%; border: 1px solid #333;"></canvas>
      </div>
      <div class="modal-footer py-1">
        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cerrar y volver al formulario</button>
      </div>
    </div>
  </div>
</div>

<style>
    .pdf-page-item {
        cursor: grab; background: white; border: 1px solid #ced4da; border-radius: 6px; padding: 6px;
        width: 105px; display: flex; flex-direction: column; align-items: center; box-shadow: 0 2px 4px rgba(0,0,0,0.05); position: relative;
    }
    .pdf-page-item:hover { border-color: #0d6efd; transform: translateY(-2px); box-shadow: 0 4px 8px rgba(0,0,0,0.1); }
    .pdf-page-item:active { cursor: grabbing; }
    
    .pdf-thumb-canvas { width: 100%; height: 110px; object-fit: cover; background-color: #f8f9fa; border: 1px solid #dee2e6; border-radius: 4px; margin-top: 15px; margin-bottom: 5px; }
    .sortable-ghost { opacity: 0.5; background-color: #e9ecef; border: 2px dashed #6c757d; }
    
    /* Estilos para los botoncitos flotantes en la miniatura */
    .btn-float-left { position: absolute; top: 4px; left: 4px; z-index: 10; padding: 2px 6px; font-size: 0.75rem; }
    .btn-float-right { position: absolute; top: 4px; right: 4px; z-index: 10; padding: 2px 6px; font-size: 0.75rem; }
</style>

<script src="https://cdn.jsdelivr.net/npm/pdf-lib@1.17.1/dist/pdf-lib.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>

<script>
pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.worker.min.js';

document.addEventListener('DOMContentLoaded', function() {
    
    // UI Combos
    const tipoPracticaSelect = document.getElementById('tipo_practica');
    if (tipoPracticaSelect) {
        tipoPracticaSelect.addEventListener('change', function() {
            document.getElementById('contenedor-caja-carta').style.display = (this.value === 'PROFESIONAL') ? 'none' : 'block';
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
                const filtradas = todasLasEscuelas.filter(e => e.universidad_id == this.value);
                filtradas.forEach(e => selectEscuela.add(new Option(e.nombre, e.escuela_id)));
                selectEscuela.disabled = false;
            } else {
                selectEscuela.disabled = true;
            }
        });
    }

    // VARIABLES PARA PDF
    const boxes = {
        bandeja: document.getElementById('bandeja-sueltas'),
        file_dni: document.getElementById('caja-dni'),
        file_cv: document.getElementById('caja-cv'),
        file_carta: document.getElementById('caja-carta'),
        file_ddjj: document.getElementById('caja-ddjj')
    };

    let sourcePdfs = {};   // Guarda el doc para PDF-Lib (Ensamblado final)
    let pdfJsDocs = {};    // Guarda el doc para PDF.js (Vista previa HD y miniaturas)

    Object.values(boxes).forEach(box => {
        new Sortable(box, { group: 'shared-pages', animation: 150, ghostClass: 'sortable-ghost',
            onAdd: () => {
                const emptyMsg = boxes.bandeja.querySelector('.id-empty-msg');
                if (emptyMsg && boxes.bandeja.children.length > 1) emptyMsg.style.display = 'none';
            }
        });
    });

    const dropzone = document.getElementById('main-dropzone');
    const fileInputVirtual = document.getElementById('file-input-virtual');
    
    dropzone.addEventListener('click', () => fileInputVirtual.click());
    dropzone.addEventListener('dragover', (e) => { e.preventDefault(); dropzone.classList.replace('bg-light', 'bg-white'); });
    dropzone.addEventListener('dragleave', () => dropzone.classList.replace('bg-white', 'bg-light'));
    dropzone.addEventListener('drop', (e) => { e.preventDefault(); dropzone.classList.replace('bg-white', 'bg-light'); procesarPDFs(e.dataTransfer.files); });
    fileInputVirtual.addEventListener('change', (e) => procesarPDFs(e.target.files));

    async function procesarPDFs(files) {
        if (files.length === 0) return;
        dropzone.innerHTML = '<div class="spinner-border text-primary my-2"></div><h6 class="fw-bold">Leyendo y procesando Documentos...</h6>';
        const emptyMsg = boxes.bandeja.querySelector('.id-empty-msg');
        if (emptyMsg) emptyMsg.style.display = 'none';

        for (let file of files) {
            if (file.type !== 'application/pdf') continue;

            try {
                // SOLUCIÓN A LA PANTALLA BLANCA: Extraer bytes directos en vez de crear una URL
                const arrayBuffer = await file.arrayBuffer();
                const pdfData = new Uint8Array(arrayBuffer);
                const fileId = 'doc_' + Date.now() + '_' + Math.random().toString(36).substr(2, 5);

                // 1. Cargar en PDF-Lib (Para cuando hagamos Submit y se una todo)
                const pdfLibDoc = await PDFLib.PDFDocument.load(arrayBuffer);
                sourcePdfs[fileId] = pdfLibDoc;

                // 2. Cargar en PDF.js (Para generar Canvas)
                const pdfJsDoc = await pdfjsLib.getDocument({ data: pdfData }).promise;
                pdfJsDocs[fileId] = pdfJsDoc; // Guardamos para la vista de la lupa

                const numPages = pdfLibDoc.getPageCount();

                for (let i = 0; i < numPages; i++) {
                    const pageItem = document.createElement('div');
                    pageItem.className = 'pdf-page-item';
                    pageItem.dataset.fileId = fileId;
                    pageItem.dataset.pageIndex = i;
                    
                    // HTML Interno (Botón Zoom Izquierda, Botón Borrar Derecha)
                    pageItem.innerHTML = `
                        <button type="button" class="btn btn-primary btn-float-left rounded-circle shadow-sm btn-ampliar" title="Ampliar Vista"><i class="bi bi-zoom-in"></i></button>
                        <button type="button" class="btn btn-danger btn-float-right rounded-circle shadow-sm btn-quitar" title="Eliminar Hoja"><i class="bi bi-x-lg"></i></button>
                    `;
                    
                    // Crear el Canvas de miniatura
                    const canvas = document.createElement('canvas');
                    canvas.className = 'pdf-thumb-canvas';
                    
                    try {
                        const page = await pdfJsDoc.getPage(i + 1);
                        const viewport = page.getViewport({ scale: 0.4 }); // Escala bajita para miniatura rápida
                        canvas.width = viewport.width; 
                        canvas.height = viewport.height;
                        const ctx = canvas.getContext('2d');
                        await page.render({ canvasContext: ctx, viewport: viewport }).promise;
                    } catch (renderError) {
                        console.error("Error dibujando miniatura", renderError);
                    }

                    pageItem.appendChild(canvas);
                    pageItem.innerHTML += `<div class="w-100 text-center"><div class="fw-bold text-dark" style="font-size:0.75rem;">Pág. ${i + 1}</div></div>`;

                    // Lógica del botón Eliminar
                    pageItem.querySelector('.btn-quitar').addEventListener('click', (e) => {
                        e.stopPropagation(); pageItem.remove();
                    });

                    // Lógica del botón Ampliar (Pop-up)
                    pageItem.querySelector('.btn-ampliar').addEventListener('click', (e) => {
                        e.stopPropagation(); // Evitar que inicie el arrastre
                        abrirVistaPrevia(fileId, i + 1);
                    });

                    boxes.bandeja.appendChild(pageItem);
                }
            } catch (error) {
                console.error(error);
                alert(`Error al abrir "${file.name}". Puede estar corrupto o protegido con contraseña.`);
            }
        }
        
        dropzone.innerHTML = '<i class="bi bi-cloud-check-fill fs-2 text-success"></i><h6 class="mt-2 mb-1 fw-bold">¡Vistas previas generadas!</h6><small>Arrastra más si necesitas.</small>';
        fileInputVirtual.value = ''; 
    }

    // ==========================================
    // MAGIA DEL POP-UP (MODAL DE ALTA DEFINICIÓN)
    // ==========================================
    const modalPdf = new bootstrap.Modal(document.getElementById('modalVistaPreviaPDF'));
    
    async function abrirVistaPrevia(fileId, numeroPagina) {
        modalPdf.show();
        
        const canvas = document.getElementById('modal-canvas-hd');
        const spinner = document.getElementById('modal-spinner');
        const ctx = canvas.getContext('2d');
        
        // Resetear visualmente
        canvas.classList.add('d-none');
        spinner.classList.remove('d-none');
        ctx.clearRect(0, 0, canvas.width, canvas.height);

        try {
            const pdfJsDoc = pdfJsDocs[fileId];
            const page = await pdfJsDoc.getPage(numeroPagina);
            
            // Escala grande para que se pueda leer todo el texto
            const viewport = page.getViewport({ scale: 1.8 }); 
            canvas.width = viewport.width;
            canvas.height = viewport.height;
            
            await page.render({ canvasContext: ctx, viewport: viewport }).promise;
            
            // Ocultar spinner y mostrar imagen
            spinner.classList.add('d-none');
            canvas.classList.remove('d-none');
            
        } catch (error) {
            console.error("Fallo al cargar HD", error);
            spinner.classList.add('d-none');
            alert("No se pudo cargar la vista ampliada de esta página.");
        }
    }

    // ==========================================
    // ENSAMBLAJE FINAL Y ENVÍO DEL FORMULARIO
    // ==========================================
    const form = document.querySelector('form.needs-validation');
    const btnSubmit = document.getElementById('btn-submit-form');
    const globalError = document.getElementById('pdf_global_error');

    form.addEventListener('submit', async function(event) {
        event.preventDefault(); event.stopPropagation();
        globalError.classList.add('d-none'); globalError.innerHTML = '';

        if (!form.checkValidity()) { form.classList.add('was-validated'); return; }

        if (boxes.file_dni.querySelectorAll('.pdf-page-item').length === 0) {
            globalError.innerHTML = '<i class="bi bi-exclamation-triangle-fill"></i> Falta colocar hojas en la caja de <b>DNI</b>.';
            globalError.classList.remove('d-none'); return;
        }
        if (boxes.file_cv.querySelectorAll('.pdf-page-item').length === 0) {
            globalError.innerHTML = '<i class="bi bi-exclamation-triangle-fill"></i> Falta colocar hojas en la caja de <b>CV</b>.';
            globalError.classList.remove('d-none'); return;
        }

        btnSubmit.disabled = true;
        btnSubmit.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Ensamblando archivos finales...';

        try {
            const inputsDestino = ['file_dni', 'file_cv', 'file_carta', 'file_ddjj'];

            for (let inputName of inputsDestino) {
                const caja = boxes[inputName];
                const pageElements = caja.querySelectorAll('.pdf-page-item');

                if (pageElements.length > 0) {
                    const mergedPdf = await PDFLib.PDFDocument.create();

                    for (let el of pageElements) {
                        const fileId = el.dataset.fileId;
                        const pageIndex = parseInt(el.dataset.pageIndex);
                        
                        const srcDoc = sourcePdfs[fileId];
                        const [copiedPage] = await mergedPdf.copyPages(srcDoc, [pageIndex]);
                        mergedPdf.addPage(copiedPage);
                    }

                    const mergedBytes = await mergedPdf.save();
                    const mergedBlob = new Blob([mergedBytes], { type: 'application/pdf' });
                    const mergedFile = new File([mergedBlob], `${inputName}.pdf`, { type: 'application/pdf' });
                    
                    const dt = new DataTransfer();
                    dt.items.add(mergedFile);
                    document.getElementById(inputName).files = dt.files;
                }
            }

            HTMLFormElement.prototype.submit.call(form);

        } catch (error) {
            console.error('Error ensamblando:', error);
            globalError.innerHTML = '<i class="bi bi-x-circle-fill"></i> Error al unir PDFs.';
            globalError.classList.remove('d-none');
            btnSubmit.disabled = false;
            btnSubmit.innerHTML = '<i class="bi bi-save"></i> Guardar Registro y Procesar Documentos';
        }
    });
});
</script>