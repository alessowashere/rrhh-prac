<?php
// views/reclutamiento/revisar.php - Vista de solo lectura
$proceso = $data['proceso'];
$documentos = $data['documentos'] ?? [];

// Buscar Documentos
$url_pdf_principal = '';
$url_ficha_firmada = ''; // <-- NECESARIO PARA EL NUEVO FORMULARIO
foreach ($documentos as $doc) {
    if ($doc['tipo_documento'] == 'CONSOLIDADO') { $url_pdf_principal = $doc['url_archivo']; }
    if ($doc['tipo_documento'] == 'FICHA_CALIFICACION') { $url_ficha_firmada = $doc['url_archivo']; } // <-- Buscamos la ficha
}
if (empty($url_pdf_principal)) {
    foreach ($documentos as $doc) {
        if ($doc['tipo_documento'] == 'CV') { $url_pdf_principal = $doc['url_archivo']; break; }
    }
}
$fecha_entrevista = $proceso['fecha_entrevista'] ?? date('Y-m-d');
?>

<style>
    /* Estilos para impresión (simplificados) */
    .print-only { display: none; }
    @media print {
        @page { size: A4; margin: 20mm; }
        body { font-size: 10pt; background-color: #fff; }
        
        /* Ocultar UI */
        .no-imprimir, .no-imprimir * { display: none !important; }
        .print-only { display: block; }

        body * { visibility: hidden; }
        #seccion-imprimible, #seccion-imprimible * { visibility: visible; }
        #seccion-imprimible {
            position: absolute; left: 0; top: 0; width: 100%; margin: 0; padding: 0;
        }
        
        h1.print-title { text-align: center; font-size: 16pt; margin-bottom: 20px; }
        .row.print-stack { display: block !important; }
        .row.print-stack > [class*="col-"] {
            width: 100% !important; flex: 0 0 100%; max-width: 100%;
        }

        /* Tablas de impresión */
        .tabla-ficha-print, .tabla-criterios-print {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        .tabla-ficha-print th, .tabla-ficha-print td,
        .tabla-criterios-print th, .tabla-criterios-print td {
            border: 1px solid #999;
            padding: 6px;
            text-align: left;
            vertical-align: top;
        }
        .tabla-ficha-print th { background-color: #f0f0f0; width: 25%; font-weight: bold; }
        .tabla-criterios-print th { background-color: #f0f0f0; font-weight: bold; }
        .tabla-criterios-print .criterio-nombre { width: 60%; }
        .tabla-criterios-print .criterio-peso { width: 15%; }
        .tabla-criterios-print .criterio-nota { width: 25%; }

        /* Comentarios y Firmas */
        .comentarios-print-box {
            border: 1px solid #999;
            padding: 10px;
            min-height: 80px;
            background-color: #f9f9f9;
            white-space: pre-wrap; /* Respetar saltos de línea */
        }
        .firmas-container { margin-top: 30px; }
        .firmas-container h5 { text-align: center; margin-bottom: 20px; }
        .firmas-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 50px 20px; }
        .firma-linea { text-align: center; padding-top: 30px; }
        .firma-linea p { margin: 0; padding-top: 5px; border-top: 1px solid #333; }
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
                    <ul class="list-group list-group-flush no-imprimir">
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <strong>Nombre:</strong>
                            <span class="text-end"><?php echo htmlspecialchars($proceso['nombres'] . ' ' . $proceso['apellidos']); ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <strong>DNI:</strong>
                            <span><?php echo htmlspecialchars($proceso['dni']); ?></span>
                        </li>
                         <li class="list-group-item d-flex justify-content-between align-items-center">
                            <strong>Tipo Práctica:</strong>
                            <span class="badge bg-info text-dark"><?php echo htmlspecialchars($proceso['tipo_practica']); ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <strong>Escuela:</strong>
                            <span class="text-end"><?php echo htmlspecialchars($proceso['escuela_nombre']); ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <strong>Promedio (Registro):</strong> 
                            <span class="fw-bold text-danger"><?php echo htmlspecialchars($proceso['promedio_general'] ?? 'N/A'); ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <strong>Puntaje Ponderado:</strong>
                            <h4 class="mb-0 text-primary">
                                <?php echo htmlspecialchars($proceso['puntuacion_final_entrevista'] ?? '0.00'); ?>
                            </h4>
                        </li>
                    </ul>

                    <div class="print-only">
                        <table class="tabla-ficha-print">
                            <tr>
                                <th>Candidato</th>
                                <td><?php echo htmlspecialchars($proceso['nombres'] . ' ' . $proceso['apellidos']); ?></td>
                            </tr>
                            <tr><th>DNI</th><td><?php echo htmlspecialchars($proceso['dni']); ?></td></tr>
                            <tr><th>Tipo Práctica</th><td><?php echo htmlspecialchars($proceso['tipo_practica']); ?></td></tr>
                            <tr><th>Universidad</th><td><?php echo htmlspecialchars($proceso['universidad_nombre']); ?></td></tr>
                            <tr><th>Escuela</th><td><?php echo htmlspecialchars($proceso['escuela_nombre']); ?></td></tr>
                            <tr><th>Fecha Entrevista</th><td><?php echo date("d/m/Y", strtotime($fecha_entrevista)); ?></td></tr>
                            <tr>
                                <th>Puntaje Ponderado</th>
                                <td><h4 class="mb-0 text-primary"><?php echo htmlspecialchars($proceso['puntuacion_final_entrevista'] ?? '0.00'); ?></h4></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>

            <div class="card mt-4 no-imprimir">
                <div class="card-header"><h5 class="mb-0">Ficha Firmada (Constancia)</h5></div>
                <div class="card-body">
                    <?php if (!empty($url_ficha_firmada)): ?>
                        <div class="alert alert-success text-center">
                            <i class="bi bi-check-circle-fill"></i> Ficha Subida
                            <a href="<?php echo htmlspecialchars($url_ficha_firmada); ?>" target="_blank" class="btn btn-sm btn-outline-success w-100 mt-2">
                                <i class="bi bi-box-arrow-up-right"></i> Ver Ficha Firmada
                            </a>
                        </div>
                    <?php else: ?>
                        <p><small>Subir la ficha firmada como constancia (PDF).</small></p>
                        <form action="index.php?c=reclutamiento&m=subirFicha" method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="proceso_id" value="<?php echo $proceso['proceso_id']; ?>">
                            <input type="hidden" name="practicante_id" value="<?php echo $proceso['practicante_id']; ?>">
                            <div class="mb-2">
                                 <input type="file" class="form-control form-control-sm" name="ficha_firmada" accept=".pdf" required>
                            </div>
                            <button type="submit" class="btn btn-sm btn-outline-success w-100">
                                <i class="bi bi-upload"></i> Subir Ficha
                            </button>
                        </form>
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
                        <input type="date" class="form-control" value="<?php echo htmlspecialchars($fecha_entrevista); ?>" readonly disabled>
                    </div>
                    
                    <div class="table-responsive no-imprimir">
                        <table class="table table-sm table-bordered table-striped">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 55%;">Criterio</th>
                                    <th>Peso (%)</th>
                                    <th>Nota (0-20)</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php 
                            $criterios_mostrados = 0;
                            for ($i = 1; $i <= 10; $i++): 
                                $nombre_key = 'campo_' . $i . '_nombre';
                                $nota_key = 'campo_' . $i . '_nota';
                                $peso_key = 'campo_' . $i . '_peso';
                                
                                $nombre_val = $proceso[$nombre_key] ?? '';
                                $nota_val = $proceso[$nota_key] ?? '';
                                $peso_val = $proceso[$peso_key] ?? '';

                                // Solo mostrar filas que tengan nombre
                                if (!empty($nombre_val)): 
                                    $criterios_mostrados++;
                            ?>
                            <tr>
                                <td><?php echo htmlspecialchars($nombre_val); ?></td>
                                <td><?php echo htmlspecialchars($peso_val); ?></td>
                                <td><?php echo htmlspecialchars($nota_val); ?></td>
                            </tr>
                            <?php 
                                endif; // Fin del if (!empty($nombre_val))
                            endfor; 

                            if ($criterios_mostrados === 0):
                            ?>
                                <tr>
                                    <td colspan="3" class="text-center text-muted">Aún no se han registrado criterios para esta evaluación.</td>
                                </tr>
                            <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="print-only">
                         <table class="tabla-criterios-print">
                            <thead>
                                <tr>
                                    <th class="criterio-nombre">Criterio</th>
                                    <th class="criterio-peso">Peso (%)</th>
                                    <th class="criterio-nota">Nota (0-20)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php for ($i = 1; $i <= 10; $i++): 
                                    $nombre_val = $proceso['campo_' . $i . '_nombre'] ?? '';
                                    $peso_val = $proceso['campo_' . $i . '_peso'] ?? '';
                                    $nota_val = $proceso['campo_' . $i . '_nota'] ?? '';
                                    if (!empty($nombre_val)):
                                ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($nombre_val); ?></span></td>
                                    <td><?php echo htmlspecialchars($peso_val); ?></span></td>
                                    <td><?php echo htmlspecialchars($nota_val); ?></span></td>
                                </tr>
                                <?php 
                                    endif;
                                endfor; 
                                ?>
                            </tbody>
                        </table>
                    </div>

                    <hr class="my-4">
                    
                    <div class="mb-3">
                        <label class="form-label"><strong>Comentarios Adicionales Registrados</strong></label>
                        <div class="card card-body bg-light p-3 no-imprimir" style="min-height: 100px;">
                            <p style="white-space: pre-wrap;"><?php echo htmlspecialchars($proceso['comentarios_adicionales'] ?? 'Sin comentarios registrados.'); ?></p>
                        </div>
                        <div class="print-only comentarios-print-box">
                            <?php echo htmlspecialchars($proceso['comentarios_adicionales'] ?? 'Sin comentarios registrados.'); ?>
                         </div>
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