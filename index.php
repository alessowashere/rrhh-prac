<?php
// index.php - Controlador Frontal

// --- AÑADE ESTAS LÍNEAS PARA VER ERRORES ---
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// ------------------------------------------

session_start();

// Cargar configuración y clases base
require_once 'config/config.php';
require_once 'core/Database.php';
require_once 'core/Controller.php';

// Gestión de rutas (Router simple)
$controllerName = 'DashboardController';
$methodName = 'index';
$params = [];

// Verificar si se pasa un controlador en la URL
if (isset($_GET['c'])) {
    $controllerName = ucwords($_GET['c']) . 'Controller';
}

// Verificar si el archivo del controlador existe
$controllerFile = 'controllers/' . $controllerName . '.php';
if (file_exists($controllerFile)) {
    require_once $controllerFile;
    
    // Verificar si se pasa un método en la URL
    if (isset($_GET['m'])) {
        $methodName = $_GET['m'];
    }

    // Verificar si el método existe en el controlador
    if (method_exists($controllerName, $methodName)) {
        // (Aquí se podrían obtener parámetros de la URL si es necesario)
        
        // Instanciar controlador y llamar al método
        $controller = new $controllerName();
        $controller->$methodName($params);

    } else {
        echo "Error: Método no encontrado.";
        // Opcional: Redirigir a una página de error 404
    }
} else {
    echo "Error: Controlador no encontrado.";
    // Opcional: Redirigir a una página de error 404
    // Por ahora, cargamos el dashboard por defecto
    require_once 'controllers/DashboardController.php';
    $controller = new DashboardController();
    $controller->index();
}

?>

