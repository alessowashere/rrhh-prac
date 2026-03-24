<?php
// views/personas/create.php
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0 text-gray-800">Añadir Nuevo Empleado</h1>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        
        <form action="index.php?controller=persona&action=store" method="POST">
            
            <div class="row g-3">
                <div class="col-md-6">
                    <label for="nombre_completo" class="form-label">Nombre Completo *</label>
                    <input type="text" class="form-control" id="nombre_completo" name="nombre_completo" required>
                </div>
                <div class="col-md-3">
                    <label for="dni" class="form-label">DNI</label>
                    <input type="text" class="form-control" id="dni" name="dni">
                </div>
                <div class="col-md-3">
                    <label for="numero_empleado" class="form-label">N° Empleado (Código)</label>
                    <input type="text" class="form-control" id="numero_empleado" name="numero_empleado">
                </div>

                <div class="col-md-6">
                    <label for="cargo" class="form-label">Cargo</label>
                    <input type="text" class="form-control" id="cargo" name="cargo">
                </div>
                <div class="col-md-6">
                    <label for="area" class="form-label">Área / Lugar</label>
                    <input type="text" class="form-control" id="area" name="area">
                </div>
                
                <div class="col-md-3">
                    <label for="fecha_ingreso" class="form-label">Fecha de Ingreso</label>
                    <input type="date" class="form-control" id="fecha_ingreso" name="fecha_ingreso">
                </div>

                <div class="col-md-3">
                    <label for="estado" class="form-label">Estado</label>
                    <select id="estado" name="estado" class="form-select">
                        <option value="ACTIVO" selected>ACTIVO</option>
                        <option value="CESADO">CESADO</option>
                    </select>
                </div>
            </div>

            <hr class="my-4">

            <div class="d-flex justify-content-end">
                <a href="index.php?controller=persona&action=index" class="btn btn-secondary me-2">
                    Cancelar
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-floppy-fill me-1"></i> Guardar Empleado
                </button>
            </div>
        </form>

    </div>
</div>