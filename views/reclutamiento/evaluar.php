<?php
// views/reclutamiento/evaluar.php
$proceso = $data['proceso'];
$documentos = $data['documentos'] ?? [];

// --- Buscar Documentos ---
$url_pdf_principal = '';
foreach ($documentos as $doc) {
    if ($doc['tipo_documento'] == 'CONSOLIDADO') { $url_pdf_principal = $doc['url_archivo']; }
}
if (empty($url_pdf_principal)) {
    foreach ($documentos as $doc) {
        if ($doc['tipo_documento'] == 'CV') { $url_pdf_principal = $doc['url_archivo']; break; }
    }
}

$fecha_entrevista = $proceso['fecha_entrevista'] ?? date('Y-m-d');
$promedio_general_val = (float)($proceso['promedio_general'] ?? 0); 

// --- DEFINICIÓN ESTRICTA DEL PERFIL ÚNICO ---
$criterios_fijos = [
    1 => ['nombre' => 'PROMEDIO (REGISTRO)', 'peso' => 50, 'nota_default' => $promedio_general_val, 'readonly_nota' => true],
    2 => ['nombre' => 'CONOCIMIENTO EN EL AREA', 'peso' => 12.5, 'nota_default' => '', 'readonly_nota' => false],
    3 => ['nombre' => 'PRESENCIA PERSONAL', 'peso' => 7.5, 'nota_default' => '', 'readonly_nota' => false],
    4 => ['nombre' => 'COMUNICACION ASERTIVA', 'peso' => 7.5, 'nota_default' => '', 'readonly_nota' => false],
    5 => ['nombre' => 'PROACTIVIDAD', 'peso' => 7.5, 'nota_default' => '', 'readonly_nota' => false],
    6 => ['nombre' => 'HABILIDAD DE RESOLUCION DE PROBLEMAS', 'peso' => 15, 'nota_default' => '', 'readonly_nota' => false]
];
?>

<style>
    .print-only { display: none; }
    @media print {
        @page { size: A4; margin: 20mm; }
        body { font-size: 10pt; background-color: #fff; }
        .no-imprimir, .no-imprimir * { display: none !important; }
        .print-only { display: block; }
        .form-control { border: none !important; background-color: #f4f4f4 !important; }
        .form-control[readonly] { background-color: #eee !important; }
        body * { visibility: hidden; }
        #seccion-imprimible, #seccion-imprimible * { visibility: visible; }
        #seccion-imprimible { position: absolute; left: 0; top: 0; width: 100%; margin: 0; padding: 0; }
        h1.print-title { text-align: center; font-size: 16pt; margin-bottom: 20px; }
        .row.print-stack { display: block !important; }
        .row.print-stack > [class*="col-"] { width: 100% !important; flex: 0 0 100%; max-width: 100%; }
        .tabla-ficha { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        .tabla-ficha th, .tabla-ficha td { border: 1px solid #999; padding: 6px; text-align: left; vertical-align: top; }
        .tabla-ficha th { background-color: #f0f0f0; width: 25%; font-weight: bold; }
        .tabla-criterios { width: 100%; border-collapse: collapse; }
        .tabla-criterios th, .tabla-criterios td { border: 1px solid #999; padding: 6px; text-align: left; }
        .tabla-criterios th { background-color: #f0f0f0; font-weight: bold; }
        .criterio-row input[type="text"], .criterio-row input[type="number"] { display: none; }
        .criterio-row .print-value { display: inline !important; font-family: monospace; padding: 2px 4px; background-color: #f4f4f4; }
        .form-control.print-textarea { height: 100px; }
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
    <h1 class="print-only print-title">FICHA DE EVALUACIÓN DE PRACTICANTE</h1>

    <form action="index.php?c=reclutamiento&m=guardarEvaluacion" method="POST">
        <input type="hidden" name="proceso_id" value="<?php echo $proceso['proceso_id']; ?>">
        
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
                        <div class="print-only">
                            <table class="tabla-ficha">
                                <tr><th>Candidato</th><td><?php echo htmlspecialchars($proceso['nombres'] . ' ' . $proceso['apellidos']); ?></td></tr>
                                <tr><th>DNI</th><td><?php echo htmlspecialchars($proceso['dni']); ?></td></tr>
                                <tr><th>Tipo Práctica</th><td><?php echo htmlspecialchars($proceso['tipo_practica']); ?></td></tr>
                                <tr><th>Universidad</th><td><?php echo htmlspecialchars($proceso['universidad_nombre']); ?></td></tr>
                                <tr><th>Escuela</th><td><?php echo htmlspecialchars($proceso['escuela_nombre']); ?></td></tr>
                                <tr><th>Fecha Entrevista</th><td><span class="print-value"><?php echo date("d/m/Y", strtotime($fecha_entrevista)); ?></span></td></tr>
                                <tr><th>Puntaje Ponderado</th><td><h4 class="mb-0 text-primary"><span class="print-value"><?php echo htmlspecialchars($proceso['puntuacion_final_entrevista'] ?? '0.00'); ?></span></h4></td></tr>
                            </table>
                        </div>
                        
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
                            <strong>Puntaje Ponderado:</strong>
                            <h4 class="mb-0 text-primary" id="puntaje-calculado">
                                <?php echo htmlspecialchars($proceso['puntuacion_final_entrevista'] ?? '0.00'); ?>
                            </h4>
                        </div>
                    </div>
                </div>
            </div> 
            
            <div class="col-lg-8 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Criterios de Evaluación</h5>
                    </div>
                    
                    <div class="card-body">
                        <div class="mb-3 no-imprimir">
                            <label for="fecha_entrevista" class="form-label"><strong>Fecha de Entrevista</strong></label>
                            <input type="date" class="form-control" id="fecha_entrevista" name="fecha_entrevista" value="<?php echo htmlspecialchars($fecha_entrevista); ?>">
                        </div>
                        
                        <div class="print-only">
                            <table class="tabla-criterios">
                                <thead>
                                    <tr>
                                        <th class="criterio-nombre">Criterio</th>
                                        <th class="criterio-peso">Peso (%)</th>
                                        <th class="criterio-nota">Nota (0-20)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php for ($i = 1; $i <= 6; $i++): ?>
                                    <tr class="criterio-row">
                                        <td><span class="print-value"><?php echo htmlspecialchars($criterios_fijos[$i]['nombre']); ?></span></td>
                                        <td><span class="print-value"><?php echo htmlspecialchars($criterios_fijos[$i]['peso']); ?></span></td>
                                        <td><span class="print-value"><?php echo htmlspecialchars($proceso['campo_' . $i . '_nota'] ?? ($i == 1 ? $criterios_fijos[1]['nota_default'] : '')); ?></span></td>
                                    </tr>
                                    <?php endfor; ?>
                                </tbody>
                            </table>
                        </div>

                        <div class="table-responsive no-imprimir">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th scope="col" style="width: 55%;">Criterio</th>
                                        <th scope="col">Peso (%)</th>
                                        <th scope="col">Nota (0-20)</th>
                                    </tr>
                                </thead>
                                <tbody id="tbody-criterios">
                                <?php 
                                // Renderizar solo los 6 campos fijos
                                for ($i = 1; $i <= 6; $i++): 
                                    $nombre_key = 'campo_' . $i . '_nombre';
                                    $nota_key = 'campo_' . $i . '_nota';
                                    $peso_key = 'campo_' . $i . '_peso';
                                    
                                    // Notas: Si está en BD lo usa, sino el default
                                    $nota_db = $proceso[$nota_key] ?? null;
                                    $nota_val = ($nota_db !== null && $nota_db !== '') ? $nota_db : $criterios_fijos[$i]['nota_default'];
                                ?>
                                <tr class="criterio-row">
                                    <td>
                                        <input type="text" class="form-control form-control-sm criterio-nombre bg-light" 
                                               id="<?php echo $nombre_key; ?>" name="<?php echo $nombre_key; ?>" 
                                               value="<?php echo htmlspecialchars($criterios_fijos[$i]['nombre']); ?>" readonly>
                                    </td>
                                    <td>
                                        <input type="number" class="form-control form-control-sm criterio-peso bg-light" 
                                               id="<?php echo $peso_key; ?>" name="<?php echo $peso_key; ?>" 
                                               value="<?php echo htmlspecialchars($criterios_fijos[$i]['peso']); ?>" readonly>
                                    </td>
                                    <td>
                                        <input type="number" class="form-control form-control-sm criterio-nota" 
                                               id="<?php echo $nota_key; ?>" name="<?php echo $nota_key; ?>" 
                                               placeholder="Nota" step="0.1" min="0" max="20"
                                               value="<?php echo htmlspecialchars($nota_val); ?>"
                                               <?php echo $criterios_fijos[$i]['readonly_nota'] ? 'readonly class="form-control form-control-sm bg-light"' : ''; ?>>
                                    </td>
                                </tr>
                                <?php endfor; ?>
                                </tbody>
                            </table>
                            
                            <div class="text-end">
                                <strong>Total Peso: <span id="total-peso">0</span>%</strong>
                                <div id="peso-error" class="text-danger small d-none">El peso debe sumar 100%</div>
                            </div>
                        </div> 
                        
                        <hr class="my-4">
                        
                        <div class="mb-3">
                            <label for="comentarios_adicionales" class="form-label"><strong>Comentarios Adicionales</strong></label>
                            <textarea class="form-control print-textarea" id="comentarios_adicionales" name="comentarios_adicionales" rows="4"><?php echo htmlspecialchars($proceso['comentarios_adicionales'] ?? ''); ?></textarea>
                        </div>
                        
                        <div class="d-flex justify-content-end no-imprimir">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="bi bi-save"></i> Guardar Evaluación
                            </button>
                        </div>

                        <div class="firmas-container d-none d-print-block">
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
    </form>
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    const inputPesos = document.querySelectorAll('.criterio-peso');
    const inputNotas = document.querySelectorAll('.criterio-nota');
    
    function calcularPuntaje() {
        let sumaPonderada = 0;
        let sumaPesosTotal = 0;
        
        for (let i = 0; i < inputNotas.length; i++) {
            let nota = parseFloat(inputNotas[i].value) || 0;
            let peso = parseFloat(inputPesos[i].value) || 0;
            
            if (nota >= 0 && peso > 0) {
                sumaPonderada += nota * peso;
                sumaPesosTotal += peso;
            }
        }
        
        let promedio = (sumaPesosTotal > 0) ? (sumaPonderada / sumaPesosTotal) : 0;
        document.getElementById('puntaje-calculado').textContent = promedio.toFixed(2);
        
        const totalPesoSpan = document.getElementById('total-peso');
        totalPesoSpan.textContent = sumaPesosTotal.toFixed(1); 
    }

    inputNotas.forEach(input => input.addEventListener('input', calcularPuntaje));

    // Calcular puntaje al cargar la página
    calcularPuntaje();
});
</script>