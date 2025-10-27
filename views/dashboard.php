<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Practicantes</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .sidebar {
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
            z-index: 100;
            padding: 48px 0 0; 
            box-shadow: inset -1px 0 0 rgba(0, 0, 0, .1);
        }
        .sidebar-sticky {
            position: relative;
            top: 0;
            height: calc(100vh - 48px);
            padding-top: .5rem;
            overflow-x: hidden;
            overflow-y: auto; 
        }
        .nav-link {
            color: #333;
        }
        .nav-link.active {
            color: #0d6efd;
            font-weight: 500;
        }
        .nav-link .bi {
            margin-right: 8px;
        }
        .main-content {
            margin-left: 220px; /* Ajustar al ancho del sidebar */
            padding-top: 1.5rem;
        }
    </style>
</head>
<body>

    <!-- Header (Navbar superior) -->
    <header class="navbar navbar-dark sticky-top bg-dark flex-md-nowrap p-0 shadow">
        <a class="navbar-brand col-md-3 col-lg-2 me-0 px-3 fs-6" href="#">Gestión Practicantes</a>
        <button class="navbar-toggler position-absolute d-md-none collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#sidebarMenu" aria-controls="sidebarMenu" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <!-- (Puedes añadir un buscador o un menú de usuario aquí) -->
    </header>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar (Menú Lateral) -->
            <nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse">
                <div class="position-sticky pt-3 sidebar-sticky">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <!-- (El 'active' debe ser dinámico) -->
                            <a class="nav-link active" aria-current="page" href="index.php?c=dashboard">
                                <i class="bi bi-house-door"></i>
                                Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="index.php?c=reclutamiento">
                                <i class="bi bi-person-plus"></i>
                                Reclutamiento
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="index.php?c=practicantes">
                                <i class="bi bi-people"></i>
                                Practicantes
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="index.php?c=convenios">
                                <i class="bi bi-file-earmark-text"></i>
                                Convenios
                            </a>
                        </li>
                    </ul>

                    <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted text-uppercase">
                        <span>Configuración</span>
                    </h6>
                    <ul class="nav flex-column mb-2">
                        <li class="nav-item">
                            <a class="nav-link" href="index.php?c=areas">
                                <i class="bi bi-diagram-3"></i>
                                Áreas y Locales
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="index.php?c=universidades">
                                <i class="bi bi-building"></i>
                                Universidades
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Contenido Principal -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
                <?php
                // Aquí es donde se cargará la vista específica (ej: dashboard.php)
                if (file_exists($viewFile)) {
                    include $viewFile;
                }
                ?>
            </main>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
