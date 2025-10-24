<?php
// views/dashboard.php
// $data estará disponible aquí si se pasó desde el controlador

?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Dashboard</h1>
</div>

<div class="row">
    <div class="col-md-4">
        <div class="card text-white bg-primary mb-3">
            <div class="card-body">
                <h5 class="card-title"><i class="bi bi-people-fill"></i> Practicantes Activos</h5>
                <!-- (Dato dinámico) -->
                <p class="card-text fs-2">0</p> 
            </div>
            <div class="card-footer text-end">
                <a href="index.php?c=practicantes&m=activos" class="text-white">Ver detalle <i class="bi bi-arrow-right-circle"></i></a>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-white bg-warning mb-3">
            <div class="card-body">
                <h5 class="card-title"><i class="bi bi-person-plus-fill"></i> Candidatos en Proceso</h5>
                <!-- (Dato dinámico) -->
                <p class="card-text fs-2">0</p>
            </div>
             <div class="card-footer text-end">
                <a href="index.php?c=reclutamiento" class="text-white">Gestionar <i class="bi bi-arrow-right-circle"></i></a>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-white bg-danger mb-3">
            <div class="card-body">
                <h5 class="card-title"><i class="bi bi-calendar-x"></i> Convenios por Vencer</h5>
                 <!-- (Dato dinámico) -->
                <p class="card-text fs-2">0</p>
            </div>
             <div class="card-footer text-end">
                <a href="index.php?c=convenios&m=porVencer" class="text-white">Revisar <i class="bi bi-arrow-right-circle"></i></a>
            </div>
        </div>
    </div>
</div>

<h2 class="mt-4">Accesos Rápidos</h2>
<div class="row">
    <div class="col-md-6">
        <div class="list-group">
            <a href="index.php?c=reclutamiento&m=nuevo" class="list-group-item list-group-item-action d-flex align-items-center">
                <i class="bi bi-plus-circle fs-4 me-3"></i>
                <div>
                    <strong>Registrar Nuevo Candidato</strong>
                    <small class="d-block text-muted">Iniciar un nuevo proceso de selección.</small>
                </div>
            </a>
            <a href="index.php?c=practicantes" class="list-group-item list-group-item-action d-flex align-items-center">
                <i class="bi bi-search fs-4 me-3"></i>
                <div>
                    <strong>Buscar Practicante</strong>
                    <small class="d-block text-muted">Consultar el historial y documentos de un practicante.</small>
                </div>
            </a>
        </div>
    </div>
</div>
