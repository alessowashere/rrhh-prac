<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-4 border-bottom">
    <h1 class="h2"><i class="bi bi-printer text-primary me-2"></i><?= htmlspecialchars($titulo) ?></h1>
</div>

<div class="row g-4">
    <div class="col-md-6 col-lg-4">
        <div class="card h-100 shadow-sm border-0">
            <div class="card-body text-center">
                <i class="bi bi-people text-success display-4 mb-3 d-block"></i>
                <h5 class="card-title">Directorio de Practicantes</h5>
                <p class="card-text text-muted">Genera un PDF con la lista completa de todos los practicantes actualmente activos en el sistema, ordenados alfabéticamente.</p>
            </div>
            <div class="card-footer bg-white border-0 pb-3 text-center">
                <a href="<?= BASE_URL ?>?c=reportes&m=generarDirectorio" target="_blank" class="btn btn-outline-success">
                    <i class="bi bi-file-earmark-pdf me-2"></i>Generar PDF
                </a>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-lg-4">
        <div class="card h-100 shadow-sm border-0">
            <div class="card-body text-center">
                <i class="bi bi-calendar-x text-danger display-4 mb-3 d-block"></i>
                <h5 class="card-title">Convenios Próximos a Vencer</h5>
                <p class="card-text text-muted">Obtén un listado de los practicantes cuyo contrato (Convenio o Adenda) expira en los próximos 30 días.</p>
            </div>
            <div class="card-footer bg-white border-0 pb-3 text-center">
                <button class="btn btn-outline-danger" disabled>
                    <i class="bi bi-cone-striped me-2"></i>En Desarrollo
                </button>
            </div>
        </div>
    </div>
</div>