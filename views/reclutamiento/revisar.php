<?php
// views/reclutamiento/revisar.php - Vista de solo lectura
$proceso = $data['proceso'];
$documentos = $data['documentos'] ?? [];

// Buscar Documentos (igual que en evaluar.php)
$url_pdf_principal = '';
$url_ficha_firmada = '';
foreach ($documentos as $doc) {
    if ($doc['tipo_documento'] == 'CONSOLIDADO') { $url_pdf_principal = $doc['url_archivo']; }
    if ($doc['tipo_documento'] == 'FICHA_CALIFICACION') { $url_ficha_firmada = $doc['url_archivo']; }
}
if (empty($url_pdf_principal)) {
    foreach ($documentos as $doc) {
        if ($doc['tipo_documento'] == 'CV') { $url_pdf_principal = $doc['url_archivo']; break; }
    }
}
$fecha_entrevista = $proceso['fecha_entrevista'] ?? date('Y-m-d');
?>

<style>
    /* Puedes mantener los estilos de impresión si quieres imprimir la revisión */
    .print-only { display: none; }
    @media print {
        /* ... (Copia los estilos @media print de evaluar.php si lo necesitas) ... */
         body * { visibility: hidden; }
        #seccion-imprimible, #seccion-imprimible * { visibility: visible; }
        #seccion-imprimible { position: absolute; left: 0; top: 0; width: 100%; }
        .no-imprimir { display: none !important; }
        .print-only { display: block; }
    }
    .form-control[readonly] {
        background-color: #e9ecef; /* Estilo visual para campos no editables */
    }
</style>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom no-imprimir">
    <h1 class="h2"><?php echo htmlspecialchars($data['titulo']); ?></h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <button type="button" class="btn btn-sm btn-outline-secondary me-2" onclick="window.print();">
            <i class="bi bi-printer"></i> Imprimir Ficha
        </button>
        <a href="index.php?c=reclutamiento" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Volver
        </a>
    </div>
</div>

<?php 
// Mensajes de sesión
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

    <h1 class="print-only print-title">FICHA DE EVALUACIÓN DE PRACTICANTE (REVISIÓN)</h1>
    
    <div class="row print-stack">
        
        <div class="col-lg-4 mb-4">
            <div class="card sticky-top" style="top: 80px;"> 
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Datos del Candidato</h5>
                    <?php if (!empty($url_pdf_principal)): ?>
                        <button type="button" class="btn btn-sm btn-primary no-imprimir" data-bs-toggle="modal" data-bs-target="#modalVerPDF">
                            <i class="bi bi-file-earmark-pdf-fill"></i> Ver PDF Postulación
                        </button>
                    <?php else: ?>
                        <span class="badge bg-warning text-dark no-imprimir">Sin PDF</span>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <div class="no-imprimir">
                        <strong>Nombre:</strong>
                        <p class="mb-2"><?php echo htmlspecialchars($proceso['nombres'] . ' ' . $proceso['apellidos']); ?></p>
                        <strong>DNI:</strong>
                        <p class="mb-2"><?php echo htmlspecialchars($proceso['dni']); ?></p>
                         <strong>Tipo Práctica:</strong>
                        <p class="mb-2"><span class="badge bg-info text-dark"><?php echo htmlspecialchars($proceso['tipo_practica']); ?></span></p>
                        <strong>Escuela:</strong>
                        <p class="mb-2"><?php echo htmlspecialchars($proceso['escuela_nombre']); ?></p>
                        <strong>Promedio (Registro):</strong> 
                        <p class="mb-2 fw-bold text-danger"><?php echo htmlspecialchars($proceso['promedio_general'] ?? 'N/A'); ?></p>
                        <hr>
                        <strong>Puntaje Ponderado Final:</strong>
                        <h4 class="mb-0 text-primary">
                            <?php echo htmlspecialchars($proceso['puntuacion_final_entrevista'] ?? '0.00'); ?>
                        </h4>
                    </div>
                </div>
            </div>

            <div class="card mt-4 no-imprimir">
                <div class="card-header"><h5 class="mb-0">Ficha Firmada</h5></div>
                <div class="card-body">
                    <?php if (!empty($url_ficha_firmada)): ?>
                        <div class="alert alert-success text-center">
                            <i class="bi bi-check-circle-fill"></i> Ficha Subida
                            <a href="<?php echo htmlspecialchars($url_ficha_firmada); ?>" target="_blank" class="btn btn-sm btn-outline-success w-100 mt-2">
                                <i class="bi bi-box-arrow-up-right"></i> Ver Ficha Firmada
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-warning text-center">
                            <i class="bi bi-exclamation-triangle-fill"></i> Aún no se ha subido la ficha firmada.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div> 

        <div class="col-lg-8 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Criterios de Evaluación Registrados</h5>
                </div>
                
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label"><strong>Fecha de Entrevista Registrada</strong></label>
                        <input type="date" class="form-control" value="<?php echo htmlspecialchars($fecha_entrevista); ?>" readonly>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 55%;">Criterio</th>
                                    <th>Peso (%)</th>
                                    <th>Nota (0-20)</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php for ($i = 1; $i <= 10; $i++): 
                                $nombre_key = 'campo_' . $i . '_nombre';
                                $nota_key = 'campo_' . $i . '_nota';
                                $peso_key = 'campo_' . $i . '_peso';
                                
                                $nombre_val = $proceso[$nombre_key] ?? '';
                                $nota_val = $proceso[$nota_key] ?? '';
                                $peso_val = $proceso[$peso_key] ?? '';

                                // Solo mostrar filas que tengan nombre (para ocultar 7-10 si están vacías)
                                if (!empty($nombre_val)): 
                            ?>
                            <tr>
                                <td>
                                    <input type="text" class="form-control form-control-sm" 
                                           value="<?php echo htmlspecialchars($nombre_val); ?>" readonly>
                                </td>
                                <td>
                                    <input type="number" class="form-control form-control-sm" 
                                           value="<?php echo htmlspecialchars($peso_val); ?>" readonly>
                                </td>
                                <td>
                                    <input type="number" class="form-control form-control-sm" 
                                           value="<?php echo htmlspecialchars($nota_val); ?>" readonly>
                                </td>
                            </tr>
                            <?php 
                                endif; // Fin del if (!empty($nombre_val))
                            endfor; 
                            ?>
                            </tbody>
                        </table>
                    </div> 
                    <hr class="my-4">
                    
                    <div class="mb-3">
                        <label class="form-label"><strong>Comentarios Adicionales Registrados</strong></label>
                        <textarea class="form-control" rows="4" readonly><?php echo htmlspecialchars($proceso['comentarios_adicionales'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="firmas-container print-only"> 
                        <h5 class="text-center">Firmas</h5>
                        <div class="firmas-grid">
                            <div class="firma-linea"><p>Firma</p></div>
                            <div class="firma-linea"><p>Firma</p></div>
                            <div class="firma-linea"><p>Firma</p></div>
                            <div class="firma-linea"><p>Firma</p></div>
                        </div>
                    </div>

                </div> 
            </div> 
        </div> 
    </div> 
</div>

<div class="modal fade no-imprimir" id="modalVerPDF" tabindex="-1" aria-labelledby="modalVerPDFLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-fullscreen-lg-down" style="height: 95vh;">
    <div class="modal-content" style="height: 100%;">
      <div class="modal-header">
        <h5 class="modal-title" id="modalVerPDFLabel">Documento Consolidado del Candidato</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-0">
        <?php if (!empty($url_pdf_principal)): ?>
            <iframe src="<?php echo htmlspecialchars($url_pdf_principal); ?>" width="100%" height="100%" frameborder="0"></iframe>
        <?php else: ?>
            <p class="p-3 text-center">No se encontró el archivo PDF (CONSOLIDADO o CV) para este candidato.</p>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>