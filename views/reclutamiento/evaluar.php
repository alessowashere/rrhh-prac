<?php
// views/reclutamiento/evaluar.php
$proceso = $data['proceso'];
?>

<style>
    @media print {
        body * {
            visibility: hidden;
        }
        #seccion-imprimible, #seccion-imprimible * {
            visibility: visible;
        }
        #seccion-imprimible {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
        }
        .no-imprimir, .no-imprimir * {
            display: none !important;
        }
        .form-control {
            border: 1px solid #ccc !important;
            background-color: #eee !important;
        }
        .card-header {
            border-bottom: 1px solid #000;
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

    <div class="row">
        <div class="col-lg-4 mb-4">
            <div class="card sticky-top" style="top: 80px;"> <div class="card-header">
                    <h5 class="mb-0">Datos del Candidato</h5>
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
                    <hr>
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
                    <p><small>Subir la ficha de evaluación firmada como constancia (PDF).</small></p>
                    <form action="index.php?c=reclutamiento&m=subirFicha" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="proceso_id" value="<?php echo $proceso['proceso_id']; ?>">
                        <input type="hidden" name="practicante_id" value="<?php echo $proceso['practicante_id']; ?>">
                        
                        <div class="mb-2">
                             <input type="file" class="form-control form-control-sm" name="ficha_firmada" accept=".pdf" required>
                        </div>
                        <button type="submit" class="btn btn-sm btn-outline-success w-100">Subir Ficha</button>
                    </form>
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
                        <p>Defina los criterios y asigne una nota (ej: 0-20). Los campos sin nota no se contarán en el promedio (el sistema ya hace esto).</p>
                        
                        <?php for ($i = 1; $i <= 10; $i++): 
                            $nombre_key = 'campo_' . $i . '_nombre';
                            $nota_key = 'campo_' . $i . '_nota';
                            
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

                        <div class="mt-5 d-none d-print-block">
                            <hr>
                            <h5 class="text-center mb-5">Firmas de Evaluadores</h5>
                            <div class="row text-center">
                                <div class="col-6">
                                    <p>_________________________</p>
                                    <p>Firma Evaluador 1</p>
                                </div>
                                <div class="col-6">
                                    <p>_________________________</p>
                                    <p>Firma Evaluador 2</p>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </form>
        </div>
    </div>

</div> <script>
document.getElementById('perfil_evaluacion').addEventListener('change', function() {
    const camposAMostrar = parseInt(this.value);
    const todasLasFilas = document.querySelectorAll('.criterio-row');
    
    todasLasFilas.forEach((fila, index) => {
        const inputNota = fila.querySelector('.criterio-nota');
        if (index < camposAMostrar) {
            // Mostrar fila
            fila.style.display = 'flex';
        } else {
            // Ocultar fila y limpiar la nota para que no cuente en el promedio
            fila.style.display = 'none';
            inputNota.value = '';
        }
    });
});

// Disparar el evento 'change' al cargar la página para aplicar el perfil por defecto (5 campos)
document.getElementById('perfil_evaluacion').dispatchEvent(new Event('change'));
</script>