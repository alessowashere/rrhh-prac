<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión RRHH - UAC</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />
    
    <style>
        body { background-color: #f8f9fa; }
        .sidebar {
            position: fixed; top: 0; bottom: 0; left: 0; z-index: 100; padding: 48px 0 0; 
            box-shadow: inset -1px 0 0 rgba(0, 0, 0, .1); background-color: #212529; color: white;
        }
        .sidebar-sticky { position: relative; top: 0; height: calc(100vh - 48px); padding-top: .5rem; overflow-x: hidden; overflow-y: auto; }
        .nav-link { color: #adb5bd; padding: 0.8rem 1rem; transition: all 0.3s; }
        .nav-link.active { color: #fff; font-weight: 500; background-color: #0d6efd; border-radius: 5px; margin: 0 10px; }
        .nav-link:hover:not(.active) { color: #fff; background-color: rgba(255,255,255,0.1); border-radius: 5px; margin: 0 10px; }
        .nav-link .bi { margin-right: 10px; font-size: 1.1rem; }
        .main-content { margin-left: 220px; padding-top: 1.5rem; }
        .navbar-brand { background-color: rgba(0, 0, 0, .25); box-shadow: inset -1px 0 0 rgba(0, 0, 0, .25); }
    </style>
</head>
<body>
<?php
// Obtener el controlador actual para marcar el menú activo
$pagina_actual = strtolower($_GET['c'] ?? 'dashboard');
?>
    <header class="navbar navbar-dark sticky-top bg-dark flex-md-nowrap p-0 shadow">
        <a class="navbar-brand col-md-3 col-lg-2 me-0 px-3 fs-6 py-3 text-center fw-bold" href="<?= BASE_URL ?>">RRHH - UAC</a>
        <button class="navbar-toggler position-absolute d-md-none collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#sidebarMenu">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="navbar-nav w-100 d-flex justify-content-end px-4 text-white">
            <span class="nav-item text-nowrap mt-2"><i class="bi bi-person-circle me-2"></i>Administrador</span>
        </div>
    </header>

    <div class="container-fluid">
        <div class="row">
            <nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block sidebar collapse">
                <div class="position-sticky pt-4 sidebar-sticky">
                    <ul class="nav flex-column gap-1">
                        <li class="nav-item">
                            <a class="nav-link <?= ($pagina_actual == 'dashboard') ? 'active' : '' ?>" href="<?= BASE_URL ?>?c=dashboard">
                                <i class="bi bi-grid-fill"></i> Panel Principal
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= ($pagina_actual == 'reclutamiento') ? 'active' : '' ?>" href="<?= BASE_URL ?>?c=reclutamiento">
                                <i class="bi bi-person-lines-fill"></i> Reclutamiento
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= ($pagina_actual == 'practicantes') ? 'active' : '' ?>" href="<?= BASE_URL ?>?c=practicantes">
                                <i class="bi bi-people-fill"></i> Personal / Practicantes
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= ($pagina_actual == 'convenios') ? 'active' : '' ?>" href="<?= BASE_URL ?>?c=convenios">
                                <i class="bi bi-file-earmark-text-fill"></i> Convenios y Adendas
                            </a>
                        </li>
                        <li class="nav-item mt-3">
                            <a class="nav-link <?= ($pagina_actual == 'reportes') ? 'active' : '' ?>" href="<?= BASE_URL ?>?c=reportes">
                                <i class="bi bi-printer-fill"></i> Reportes PDF
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content mb-5">
                
                <?php if (isset($_SESSION['mensaje_exito'])): ?>
                    <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                        <i class="bi bi-check-circle-fill me-2"></i><?= $_SESSION['mensaje_exito'] ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <?php unset($_SESSION['mensaje_exito']); ?>
                <?php endif; ?>

                <?php if (isset($_SESSION['mensaje_error'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i><?= $_SESSION['mensaje_error'] ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <?php unset($_SESSION['mensaje_error']); ?>
                <?php endif; ?>

                <?php if (isset($_SESSION['mensaje_info'])): ?>
                    <div class="alert alert-info alert-dismissible fade show shadow-sm" role="alert">
                        <i class="bi bi-info-circle-fill me-2"></i><?= $_SESSION['mensaje_info'] ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <?php unset($_SESSION['mensaje_info']); ?>
                <?php endif; ?>

                <?php
                if (isset($viewFile) && file_exists($viewFile)) {
                    require_once $viewFile;
                } else {
                    echo "<div class='alert alert-warning'>No se encontró el archivo de la vista solicitada.</div>";
                }
                ?>
            </main>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
</body>
</html>