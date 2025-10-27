<?php
// views/reclutamiento/evaluar.php
// $data['proceso'] tiene todos los datos (practicante, proceso, resultados)
// $data['documentos'] tiene los archivos (CV, DNI, CONSOLIDADO, etc.)
$proceso = $data['proceso'];
$documentos = $data['documentos'] ?? []; // Asegurarnos que sea un array

// Buscamos el PDF consolidado (o el CV si no hay consolidado)
$url_pdf_principal = '';
$url_ficha_firmada = '';

foreach ($documentos as $doc) {
    if ($doc['tipo_documento'] == 'CONSOLIDADO') {
        $url_pdf_principal = $doc['url_archivo'];
    }
    if ($doc['tipo_documento'] == 'FICHA_CALIFICACION') {
        $url_ficha_firmada = $doc['url_archivo'];
    }
}

// Fallback: Si no hay CONSOLIDADO, buscar el CV
if (empty($url_pdf_principal)) {
    foreach ($documentos as $doc) {
        if ($doc['tipo_documento'] == 'CV') {
            $url_pdf_principal = $doc['url_archivo'];
            break;
        }
    }
}
?>

<style>
    @media print {
        /* Define el tamaño de la página y márgenes */
        @page {
            size: A4;
            margin: 20mm; 
        }

        body {
            font-size: 10pt; /* Reduce el tamaño de letra general */
            margin: 0;
            padding: 0;
        }

        /* Oculta todo por defecto */
        body * {
            visibility: hidden;
        }

        /* Muestra solo la sección imprimible y sus hijos */
        #seccion-imprimible, #seccion-imprimible * {
            visibility: visible;
        }

        /* Asegura que la sección ocupe el espacio */
        #seccion-imprimible {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            margin: 0;
            padding: 0;
        }

        /* Oculta botones, alertas, etc. */
        .no-imprimir, .no-imprimir * {
            display: none !important;
        }

        /* Evita saltos de página dentro de estos elementos */
        .card, .card-body, form {
            page-break-inside: avoid;
        }

        /* Estilos para los campos de formulario */
        .form-control {
            border: 1px solid #ccc !important;
            background-color: #f8f8f8 !important; /* Fondo ligero para ver el campo */
            padding: 2px 4px;
        }
        textarea.form-control {
            min-height: 80px; /* Altura de comentarios */
        }
        
        .card {
            border: 1px solid #aaa;
            margin-bottom: 10px !important;
        }
        .card-header {
            border-bottom: 1px solid #aaa;
            padding: 5px 10px;
        }
        .card-body {
            padding: 10px;
        }

        /* Fuerza que las columnas (col-lg-4, col-lg-8) se apilen */
        .row.print-stack {
            display: block !important;
        }
        .row.print-stack > [class*="col-"] {
            width: 100% !important;
            flex: 0 0 100%;
            max-width: 100%;
        }

        /* Estilos para las firmas */
        .firmas-container {
            page-break-before: auto; /* Intenta ponerlo en la misma pág. si cabe */
            margin-top: 25px;
        }
        .firmas-container p {
            margin-bottom: 0;
            font-size: 10pt;
        }
    }
</style>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom no-imprimir">
    <h1 class="h2"><?php echo htmlspecialchars($data['titulo']); ?></h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="index.php?c=reclutamiento" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left"></i>
            Volver al Listado
        </a>
    </div>
</div>

<?php 
// Mensajes de sesión (No imprimir)
if (isset($_SESSION['mensaje_exito'])) {
    echo '<div class="alert alert-success alert-dismissible fade show no-imprimir" role="alert">' . $_SESSION['mensaje_exito'] . '
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
    unset($_SESSION['mensaje_exito']);
}
if (isset($_SESSION['mensaje_error'])) {
    echo '<div class="alert alert-danger alert-dismissible fade show no-imprimir" role="alert">' . $_SESSION['mensaje_error'] . '
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
    unset($_SESSION['mensaje_error']);
}
?>

<div id="seccion-imprimible">

    <div class="row print-stack">
        <div class="col-lg-4 mb-4">
            <div class="card sticky-top" style="top: 80px;"> 
                
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Datos del Candidato</h5>
                    
                    <?php if (!empty($url_pdf_principal)): ?>
                        <button type="button" class="btn btn-sm btn-primary no-imprimir" data-bs-toggle="modal" data-bs-target="#modalVerPDF">
                            <i class="bi bi-file-earmark-pdf-fill"></i> Ver PDF
                        </button>
                    <?php else: ?>
                        <span class="badge bg-warning text-dark no-imprimir">Sin PDF</span>
                    <?php endif; ?>
                </div>

                <div class="card-body">
                    <strong>Nombre:</strong>
                    <p class="mb-2"><?php echo htmlspecialchars($proceso['nombres'] . ' ' . $proceso['apellidos']); ?></p>
                    
                    <strong>DNI:</strong>
                    <p class="mb-2"><?php echo htmlspecialchars($proceso['dni']); ?></p>
                    
                    <strong>Universidad:</strong>
                    <p class="mb-2"><?php echo htmlspecialchars($proceso['universidad_nombre']); ?></p>
                    
                    <strong>Escuela:</strong>
                    <p class="mb-2"><?php echo htmlspecialchars($proceso['escuela_nombre']); ?></p>
                    
                    <strong>Promedio:</strong>
                    <p class="mb-2"><?php echo htmlspecialchars($proceso['promedio_general']); ?></p>
                    
                    <hr class="no-imprimir">
                    
                    <strong>Estado del Proceso:</strong>
                    <p class="mb-2">
                        <span class="badge 
                            <?php 
                                switch($proceso['estado_proceso']) {
                                    case 'Aceptado': echo 'bg-success'; break;
                                    case 'Rechazado': echo 'bg-danger'; break;
                                    case 'Pendiente': echo 'bg-secondary'; break;
                                    default: echo 'bg-warning text-dark';
                                }
                            ?>">
                            <?php echo htmlspecialchars($proceso['estado_proceso']); ?>
                        </span>
                    </p>
                    
                    <strong>Puntaje Final Entrevista:</strong>
                    <h4 class="mb-0 text-primary">
                        <?php echo htmlspecialchars($proceso['puntuacion_final_entrevista'] ?? '0.00'); ?>
                    </h4>
                </div>
            </div>

            <div class="card mt-4 no-imprimir">
                <div class="card-header">
                    <h5 class="mb-0">Ficha Firmada</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($url_ficha_firmada)): ?>
                        <div class="alert alert-success text-center">
                            <i class="bi bi-check-circle-fill"></i> Ficha Subida
                            <a href="<?php echo htmlspecialchars($url_ficha_firmada); ?>" target="_blank" class="btn btn-sm btn-outline-success w-100 mt-2">Ver Ficha</a>
                        </div>
                    <?php else: ?>
                        <p><small>Subir la ficha de evaluación firmada como constancia (PDF).</small></p>
                        <form action="index.php?c=reclutamiento&m=subirFicha" method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="proceso_id" value="<?php echo $proceso['proceso_id']; ?>">
                            <input type="hidden" name="practicante_id" value="<?php echo $proceso['practicante_id']; ?>">
                            
                            <div class="mb-2">
                                 <input type="file" class="form-control form-control-sm" name="ficha_firmada" accept=".pdf" required>
                            </div>
                            <button type="submit" class="btn btn-sm btn-outline-success w-100">Subir Ficha</button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
            
        </div>

        <div class="col-lg-8 mb-4">
            <form action="index.php?c=reclutamiento&m=guardarEvaluacion" method="POST">
                <input type="hidden" name="proceso_id" value="<?php echo $proceso['proceso_id']; ?>">
                
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Criterios de Evaluación</h5>
                        <div class="col-5 no-imprimir">
                            <label for="perfil_evaluacion" class="visually-hidden">Cargar Perfil</label>
                            <select id="perfil_evaluacion" class="form-select form-select-sm">
                                <option value="10">Personalizado (10 campos)</option>
                                <option value="5" selected>Perfil Pre-Profesional (5 campos)</option>
                                <option value="7">Perfil Profesional (7 campos)</option>
                            </select>
                        </div>
                    </div>
                    <div class="card-body">
                        <p>Defina los criterios y asigne una nota (ej: 0-20). Los campos sin nota no se contarán en el promedio.</p>
                        
                        <?php for ($i = 1; $i <= 10; $i++): 
                            $nombre_key = 'campo_' . $i . '_nombre';
                            $nota_key = 'campo_' . $i . '_nota';
                            
                            // Usamos los valores por defecto de la BD
                            $nombre_val = $proceso[$nombre_key] ?? 'Criterio ' . $i;
                            $nota_val = $proceso[$nota_key];
                        ?>
                        <div class="row g-2 mb-2 align-items-center criterio-row" id="criterio-<?php echo $i; ?>">
                            <div class="col-8">
                                <label for="<?php echo $nombre_key; ?>" class="visually-hidden">Nombre Criterio <?php echo $i; ?></label>
                                <input type="text" class="form-control" 
                                       id="<?php echo $nombre_key; ?>" 
                                       name="<?php echo $nombre_key; ?>" 
                                       value="<?php echo htmlspecialchars($nombre_val); ?>">
                            </div>
                            <div class="col-4">
                                <label for="<?php echo $nota_key; ?>" class="visually-hidden">Nota Criterio <?php echo $i; ?></label>
                                <input type="number" class="form-control criterio-nota" 
                                       id="<?php echo $nota_key; ?>" 
                                       name="<?php echo $nota_key; ?>" 
                                       placeholder="Nota (0-20)" 
                                       step="0.1" min="0" max="20"
                                       value="<?php echo htmlspecialchars($nota_val); ?>">
                            </div>
                        </div>
                        <?php endfor; ?>
                        
                        <hr class="my-4">
                        
                        <div class="mb-3">
                            <label for="comentarios_adicionales" class="form-label">Comentarios Adicionales</label>
                            <textarea class="form-control" id="comentarios_adicionales" name="comentarios_adicionales" rows="4"><?php echo htmlspecialchars($proceso['comentarios_adicionales']); ?></textarea>
                        </div>
                        
                        <div class="d-flex justify-content-between no-imprimir">
                            <button type="button" class="btn btn-secondary" onclick="window.print();">
                                <i class="bi bi-printer"></i> Imprimir Ficha
                            </button>
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="bi bi-save"></i> Guardar Evaluación
                            </button>
                        </div>

                        <div class="firmas-container d-none d-print-block">
                            <h5 class="text-center" style="margin-top: 30px; margin-bottom: 20px;">Firmas</h5>
                            <div class="row text-center" style="padding-top: 30px;">
                                <div class="col-6" style="margin-bottom: 50px;">
                                    <p>_________________________</p>
                                </div>
                                <div class="col-6" style="margin-bottom: 50px;">
                                    <p>_________________________</p>
                                </div>
                                <div class="col-6">
                                    <p>_________________________</p>
                                </div>
                                <div class="col-6">
                                    <p>_________________________</p>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </form>
        </div>
    </div>

</div> <div class="modal fade" id="modalVerPDF" tabindex="-1" aria-labelledby="modalVerPDFLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-fullscreen-lg-down" style="height: 95vh;">
    <div class="modal-content" style="height: 100%;">
      <div class="modal-header">
        <h5 class="modal-title" id="modalVerPDFLabel">Documento Consolidado del Candidato</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-0">
        <?php if (!empty($url_pdf_principal)): ?>
            <iframe src="<?php echo htmlspecialchars($url_pdf_principal); ?>" width="100%" height="100%" frameborder="0">
                Tu navegador no soporta PDFs. <a href="<?php echo htmlspecialchars($url_pdf_principal); ?>">Descarga el PDF aquí</a>.
            </iframe>
        <?php else: ?>
            <p class="p-3 text-center">No se encontró el archivo PDF (CONSOLIDADO o CV) para este candidato.</p>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    
    const perfilSelect = document.getElementById('perfil_evaluacion');
    
    function aplicarPerfil() {
        // Asegurarse de que el select exista (no es null)
        if (!perfilSelect) return; 

        const camposAMostrar = parseInt(perfilSelect.value);
        const todasLasFilas = document.querySelectorAll('.criterio-row');
        
        todasLasFilas.forEach((fila, index) => {
            const inputNota = fila.querySelector('.criterio-nota');
            if (index < camposAMostrar) {
                // Mostrar fila
                fila.style.display = 'flex';
            } else {
                // Ocultar fila y limpiar la nota para que no cuente en el promedio
                fila.style.display = 'none';
                if(inputNota) {
                    inputNota.value = '';
                }
            }
        });
    }

    // Evento change
    if (perfilSelect) {
        perfilSelect.addEventListener('change', aplicarPerfil);
        
        // Carga inicial al abrir el formulario
        aplicarPerfil();
    }

});
</script>