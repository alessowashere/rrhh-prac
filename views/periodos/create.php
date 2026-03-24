<?php
// views/periodos/create.php
// (La variable $listaPersonas viene del controlador)
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0 text-gray-800">Crear Nuevo Período</h1>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        
        <form action="index.php?controller=periodo&action=store" method="POST">
            <div class="row g-3">

                <div class="col-md-12">
                    <label for="persona_id" class="form-label">Empleado *</label>
                    <select id="persona_id" name="persona_id" class="form-select" required>
                        <option value="">-- Seleccione un empleado --</option>
                        <?php foreach ($listaPersonas as $persona): ?>
                            <option value="<?php echo $persona['id']; ?>">
                                <?php echo htmlspecialchars($persona['nombre_completo']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-4">
                    <label for="periodo_inicio" class="form-label">Año Inicio (YYYY) *</label>
                    <input type="number" class="form-control" id="periodo_inicio" name="periodo_inicio" placeholder="Ej: 2024" required>
                </div>
                
                <div class="col-md-4">
                    <label for="periodo_fin" class="form-label">Año Fin (YYYY) *</label>
                    <input type="number" class="form-control" id="periodo_fin" name="periodo_fin" placeholder="Ej: 2025" required>
                </div>

                <div class="col-md-4">
                    <label for="total_dias" class="form-label">Total Días Derecho *</label>
                    <input type="number" class="form-control" id="total_dias" name="total_dias" value="30" required>
                </div>
                
            </div>

            <hr class="my-4">

            <div class="d-flex justify-content-end">
                <a href="index.php?controller=periodo&action=index" class="btn btn-secondary me-2">
                    Cancelar
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-floppy-fill me-1"></i> Guardar Período
                </button>
            </div>
        </form>

    </div>
</div>