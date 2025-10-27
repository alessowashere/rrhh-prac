<?php
// views/reclutamiento/evaluar.php
// $data['proceso'] tiene todos los datos (practicante, proceso, resultados)
$proceso = $data['proceso'];
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><?php echo htmlspecialchars($data['titulo']); ?></h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="index.php?c=reclutamiento" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left"></i>
            Volver al Listado
        </a>
    </div>
</div>

<?php 
// Mensajes de sesión
if (isset($_SESSION['mensaje_exito'])) {
    echo '<div class="alert alert-success alert-dismissible fade show" role="alert">' . $_SESSION['mensaje_exito'] . '
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
    unset($_SESSION['mensaje_exito']);
}
if (isset($_SESSION['mensaje_error'])) {
    echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">' . $_SESSION['mensaje_error'] . '
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
    unset($_SESSION['mensaje_error']);
}
?>

<form action="index.php?c=reclutamiento&m=guardarEvaluacion" method="POST">
    <input type="hidden" name="proceso_id" value="<?php echo $proceso['proceso_id']; ?>">

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
                    
                    <strong>Promedio:</strong>
                    <p class="mb-2"><?php echo htmlspecialchars($proceso['promedio_general']); ?></p>
                    
                    <hr>
                    
                    <strong>Estado del Proceso:</strong>
                    <p class="mb-2">
                        <span class="badge 
                            <?php 
                                switch($proceso['estado_proceso']) {
                                    case 'Aceptado': echo 'bg-success'; break;
                                    case 'Rechazado': echo 'bg-danger'; break;
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
        </div>

        <div class="col-lg-8 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Criterios de Evaluación</h5>
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
                    <div class="row g-2 mb-2 align-items-center">
                        <div class="col-8">
                            <label for="<?php echo $nombre_key; ?>" class="visually-hidden">Nombre Criterio <?php echo $i; ?></label>
                            <input type="text" class="form-control" 
                                   id="<?php echo $nombre_key; ?>" 
                                   name="<?php echo $nombre_key; ?>" 
                                   value="<?php echo htmlspecialchars($nombre_val); ?>">
                        </div>
                        <div class="col-4">
                            <label for="<?php echo $nota_key; ?>" class="visually-hidden">Nota Criterio <?php echo $i; ?></label>
                            <input type="number" class="form-control" 
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
                    
                    <button type="submit" class="btn btn-primary btn-lg w-100">
                        <i class="bi bi-save"></i> Guardar Evaluación
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>