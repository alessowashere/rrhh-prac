<?php
// views/reclutamiento/evaluar.php
$proceso = $data['proceso'];
$documentos = $data['documentos'] ?? [];

// --- Buscar Documentos ---
$url_pdf_principal = '';
$url_ficha_firmada = '';
foreach ($documentos as $doc) {
    if ($doc['tipo_documento'] == 'CONSOLIDADO') { $url_pdf_principal = $doc['url_archivo']; }
    if ($doc['tipo_documento'] == 'FICHA_CALIFICACION') { $url_ficha_firmada = $doc['url_archivo']; }
}
// Fallback a CV
if (empty($url_pdf_principal)) {
    foreach ($documentos as $doc) {
        if ($doc['tipo_documento'] == 'CV') { $url_pdf_principal = $doc['url_archivo']; break; }
    }
}

// --- Fecha de Entrevista ---
// Si ya tiene una fecha guardada, úsala. Si no, usa la fecha de hoy.
$fecha_entrevista = $proceso['fecha_entrevista'] ?? date('Y-m-d');

// --- Tipo de Práctica (para el JS) ---
$tipo_practica = $proceso['tipo_practica'] ?? 'PREPROFESIONAL';
?>

<style>
    /* Ocultar elementos en pantalla */
    .print-only {
        display: none;
    }

    @media print {
        @page {
            size: A4;
            margin: 20mm; 
        }
        body {
            font-size: 10pt;
            background-color: #fff;
        }
        
        /* Ocultar UI */
        .no-imprimir, .no-imprimir * { display: none !important; }
        .print-only { display: block; }
        .form-control { border: none !important; background-color: #f4f4f4 !important; }
        .form-control[readonly] { background-color: #eee !important; }

        /* Mostrar solo la ficha */
        body * { visibility: hidden; }
        #seccion-imprimible, #seccion-imprimible * { visibility: visible; }
        #seccion-imprimible {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            margin: 0;
            padding: 0;
        }

        /* Layout de la Ficha */
        h1.print-title {
            text-align: center;
            font-size: 16pt;
            margin-bottom: 20px;
        }
        
        .row.print-stack { display: block !important; }
        .row.print-stack > [class*="col-"] {
            width: 100% !important; flex: 0 0 100%; max-width: 100%;
        }

        /* Tabla de Datos del Candidato */
        .tabla-ficha {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        .tabla-ficha th, .tabla-ficha td {
            border: 1px solid #999;
            padding: 6px;
            text-align: left;
            vertical-align: top;
        }
        .tabla-ficha th {
            background-color: #f0f0f0;
            width: 25%;
            font-weight: bold;
        }
        .tabla-ficha td {
            width: 75%;
        }

        /* Tabla de Criterios */
        .tabla-criterios { width: 100%; border-collapse: collapse; }
        .tabla-criterios th, .tabla-criterios td {
            border: 1px solid #999;
            padding: 6px;
            text-align: left;
        }
        .tabla-criterios th { background-color: #f0f0f0; font-weight: bold; }
        .tabla-criterios td.criterio-nombre { width: 60%; }
        .tabla-criterios td.criterio-peso { width: 15%; }
        .tabla-criterios td.criterio-nota { width: 25%; }
        
        /* Ocultar campos de input, mostrar solo el valor */
        .criterio-row input[type="text"], .criterio-row input[type="number"] {
            display: none; /* Ocultar el input */
        }
        .criterio-row .print-value {
            display: inline !important; /* Mostrar el span */
            font-family: monospace;
            padding: 2px 4px;
            background-color: #f4f4f4;
        }
        .form-control.print-textarea {
             height: 100px; /* Asegurar altura para comentarios */
        }

        /* Firmas */
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
                                <tr>
                                    <th>Candidato</th>
                                    <td><?php echo htmlspecialchars($proceso['nombres'] . ' ' . $proceso['apellidos']); ?></td>
                                </tr>
                                <tr>
                                    <th>DNI</th>
                                    <td><?php echo htmlspecialchars($proceso['dni']); ?></td>
                                </tr>
                                 <tr>
                                    <th>Tipo Práctica</th>
                                    <td><?php echo htmlspecialchars($proceso['tipo_practica']); ?></td>
                                </tr>
                                <tr>
                                    <th>Universidad</th>
                                    <td><?php echo htmlspecialchars($proceso['universidad_nombre']); ?></td>
                                </tr>
                                <tr>
                                    <th>Escuela</th>
                                    <td><?php echo htmlspecialchars($proceso['escuela_nombre']); ?></td>
                                </tr>
                                <tr>
                                    <th>Fecha Entrevista</th>
                                    <td><span class="print-value"><?php echo date("d/m/Y", strtotime($fecha_entrevista)); ?></span></td>
                                </tr>
                                <tr>
                                    <th>Puntaje Ponderado</th>
                                    <td><h4 class="mb-0 text-primary"><span class="print-value"><?php echo htmlspecialchars($proceso['puntuacion_final_entrevista'] ?? '0.00'); ?></span></h4></td>
                                </tr>
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
                            <hr>
                            <strong>Puntaje Ponderado:</strong>
                            <h4 class="mb-0 text-primary" id="puntaje-calculado">
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
                                <a href="<?php echo htmlspecialchars($url_ficha_firmada); ?>" target="_blank" class="btn btn-sm btn-outline-success w-100 mt-2">Ver Ficha</a>
                            </div>
                        <?php else: ?>
                            <p><small>Subir la ficha firmada como constancia (PDF).</small></p>
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
            </div> <div class="col-lg-8 mb-4">
                <div class="card">
                    <div class="card-header">
                        <div class="row align-items-center">
                            <div class="col-6"><h5 class="mb-0">Criterios de Evaluación</h5></div>
                            <div class="col-6 text-end no-imprimir">
                                <div class="btn-group btn-group-sm" role="group">
                                    <button type="button" class="btn btn-outline-primary" id="btn-perfil-pre">Perfil PRE</button>
                                    <button type="button" class="btn btn-outline-primary" id="btn-perfil-pro">Perfil PRO</button>
                                    <button type="button" class="btn btn-outline-secondary" id="btn-perfil-custom">Personalizado</button>
                                </div>
                            </div>
                        </div>
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
                                    <?php for ($i = 1; $i <= 10; $i++): 
                                        $nombre_val = $proceso['campo_' . $i . '_nombre'] ?? '';
                                        $peso_val = $proceso['campo_' . $i . '_peso'] ?? '';
                                        $nota_val = $proceso['campo_' . $i . '_nota'] ?? '';
                                    ?>
                                    <tr class="criterio-row" id="criterio-<?php echo $i; ?>">
                                        <td><span class="print-value"><?php echo htmlspecialchars($nombre_val); ?></span></td>
                                        <td><span class="print-value"><?php echo htmlspecialchars($peso_val); ?></span></td>
                                        <td><span class="print-value"><?php echo htmlspecialchars($nota_val); ?></span></td>
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
                                <tbody>
                                <?php for ($i = 1; $i <= 10; $i++): 
                                    $nombre_key = 'campo_' . $i . '_nombre';
                                    $nota_key = 'campo_' . $i . '_nota';
                                    $peso_key = 'campo_' . $i . '_peso';
                                    
                                    $nombre_val = $proceso[$nombre_key] ?? '';
                                    $nota_val = $proceso[$nota_key] ?? '';
                                    $peso_val = $proceso[$peso_key] ?? '';
                                ?>
                                <tr class="criterio-row" id="criterio-row-<?php echo $i; ?>">
                                    <td>
                                        <input type="text" class="form-control form-control-sm criterio-nombre" 
                                               id="<?php echo $nombre_key; ?>" name="<?php echo $nombre_key; ?>" 
                                               value="<?php echo htmlspecialchars($nombre_val); ?>">
                                    </td>
                                    <td>
                                        <input type="number" class="form-control form-control-sm criterio-peso" 
                                               id="<?php echo $peso_key; ?>" name="<?php echo $peso_key; ?>" 
                                               placeholder="%" step="1" min="0" max="100"
                                               value="<?php echo htmlspecialchars($peso_val); ?>">
                                    </td>
                                    <td>
                                        <input type="number" class="form-control form-control-sm criterio-nota" 
                                               id="<?php echo $nota_key; ?>" name="<?php echo $nota_key; ?>" 
                                               placeholder="Nota" step="0.1" min="0" max="20"
                                               value="<?php echo htmlspecialchars($nota_val); ?>">
                                    </td>
                                </tr>
                                <?php endfor; ?>
                                </tbody>
                            </table>
                            <div class="text-end">
                                <strong>Total Peso: <span id="total-peso">0</span>%</strong>
                                <div id="peso-error" class="text-danger small d-none">El peso debe sumar 100%</div>
                            </div>
                        </div> <hr class="my-4">
                        
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

                    </div> </div> </div> </div> </form>
</div> <div class="modal fade no-imprimir" id="modalVerPDF" tabindex="-1" aria-labelledby="modalVerPDFLabel" aria-hidden="true">
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
    
    // --- Perfiles Definidos ---
    // (CONOCIMIENTO, PRESENCIA, COMUNICACION, PROACTIVIDAD, HABILIDAD)
    const perfil_base = [
        { nombre: "CONOCIMIENTO EN EL AREA", peso: 25 },
        { nombre: "PRESENCIA PERSONAL", peso: 15 },
        { nombre: "COMUNICACION ASERTIVA", peso: 15 },
        { nombre: "PROACTIVIDAD", peso: 15 },
        { nombre: "HABILIDAD DE RESOLUCION DE PROBLEMAS", peso: 30 }
    ];
    
    // (Puedes crear otro perfil si son diferentes)
    const perfil_pre = perfil_base; 
    const perfil_pro = perfil_base;

    const tipoPracticaPHP = "<?php echo $tipo_practica; ?>";
    
    const todasLasFilas = document.querySelectorAll('.criterio-row');
    const inputNombres = document.querySelectorAll('.criterio-nombre');
    const inputPesos = document.querySelectorAll('.criterio-peso');
    const inputNotas = document.querySelectorAll('.criterio-nota');
    
    /**
     * Llena el formulario con un perfil dado (array de criterios)
     */
    function aplicarPerfil(perfil) {
        // Llenar los campos del perfil
        perfil.forEach((criterio, index) => {
            if (inputNombres[index]) {
                inputNombres[index].value = criterio.nombre;
                inputNombres[index].readOnly = true; // Bloquear
            }
            if (inputPesos[index]) {
                inputPesos[index].value = criterio.peso;
                inputPesos[index].readOnly = true; // Bloquear
            }
            if (inputNotas[index]) {
                inputNotas[index].value = inputNotas[index].value || ''; // No borrar notas si ya existen
            }
            if (todasLasFilas[index]) {
                todasLasFilas[index].style.display = 'table-row';
            }
        });
        
        // Ocultar filas restantes
        for (let i = perfil.length; i < 10; i++) {
            if (todasLasFilas[i]) {
                todasLasFilas[i].style.display = 'none';
                if(inputNombres[i]) inputNombres[i].value = '';
                if(inputPesos[i]) inputPesos[i].value = '0'; // Poner peso 0
                if(inputNotas[i]) inputNotas[i].value = '';
            }
        }
        
        // Actualizar cálculos
        calcularPuntaje();
    }

    /**
     * Muestra todos los campos y los desbloquea
     */
    function aplicarPerfilPersonalizado() {
        for (let i = 0; i < 10; i++) {
            if (todasLasFilas[i]) {
                todasLasFilas[i].style.display = 'table-row';
            }
            if (inputNombres[i]) {
                inputNombres[i].value = inputNombres[i].value || `Criterio ${i+1}`;
                inputNombres[i].readOnly = false; // Desbloquear
            }
            if (inputPesos[i]) {
                inputPesos[i].value = inputPesos[i].value || '';
                inputPesos[i].readOnly = false; // Desbloquear
            }
        }
        calcularPuntaje();
    }

    /**
     * Calcula el puntaje ponderado en vivo
     */
    function calcularPuntaje() {
        let sumaPonderada = 0;
        let sumaPesosTotal = 0;
        
        for (let i = 0; i < 10; i++) {
            let nota = parseFloat(inputNotas[i].value) || 0;
            let peso = parseFloat(inputPesos[i].value) || 0;
            
            if (nota >= 0 && peso > 0) {
                sumaPonderada += nota * peso;
                sumaPesosTotal += peso;
            }
        }
        
        let promedio = (sumaPesosTotal > 0) ? (sumaPonderada / sumaPesosTotal) : 0;
        
        // Actualizar UI
        document.getElementById('puntaje-calculado').textContent = promedio.toFixed(2);
        
        const totalPesoSpan = document.getElementById('total-peso');
        const pesoErrorDiv = document.getElementById('peso-error');
        
        totalPesoSpan.textContent = sumaPesosTotal;
        if (sumaPesosTotal > 0 && sumaPesosTotal != 100) {
            totalPesoSpan.classList.add('text-danger');
            pesoErrorDiv.classList.remove('d-none');
        } else {
            totalPesoSpan.classList.remove('text-danger');
            pesoErrorDiv.classList.add('d-none');
        }
    }

    // --- Event Listeners ---
    
    // Botones de Perfil
    document.getElementById('btn-perfil-pre').addEventListener('click', () => aplicarPerfil(perfil_pre));
    document.getElementById('btn-perfil-pro').addEventListener('click', () => aplicarPerfil(perfil_pro));
    document.getElementById('btn-perfil-custom').addEventListener('click', aplicarPerfilPersonalizado);
    
    // Recalcular puntaje cada vez que se cambia una nota o peso
    inputNotas.forEach(input => input.addEventListener('input', calcularPuntaje));
    inputPesos.forEach(input => input.addEventListener('input', calcularPuntaje));

    // --- Carga Inicial ---
    
    // Si los campos de criterio están vacíos (primera vez que se abre)
    // Cargar el perfil automáticamente según el tipo de practicante.
    const primerCriterio = document.getElementById('campo_1_nombre').value;
    if (primerCriterio === '' || primerCriterio === 'Criterio 1') {
        if (tipoPracticaPHP === 'PREPROFESIONAL') {
            aplicarPerfil(perfil_pre);
            document.getElementById('btn-perfil-pre').classList.add('active');
        } else if (tipoPracticaPHP === 'PROFESIONAL') {
            aplicarPerfil(perfil_pro);
            document.getElementById('btn-perfil-pro').classList.add('active');
        } else {
            // Si no tiene tipo, cargar personalizado
            aplicarPerfilPersonalizado();
            document.getElementById('btn-perfil-custom').classList.add('active');
        }
    } else {
        // Si ya tiene datos guardados, solo calcular (no sobreescribir)
        calcularPuntaje();
    }
});
</script>