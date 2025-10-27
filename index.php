<?php
// index.php - Controlador Frontal

// --- ERRORES DESACTIVADOS (para producción) ---
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(0);
// ------------------------------------------

session_start();

// --- RUTAS ABSOLUTAS ---
// Usamos __DIR__ para asegurarnos que las rutas son correctas
$configPath = __DIR__ . '/config/config.php';
if(!file_exists($configPath)) die("Error: No se encuentra 'config/config.php'");
require_once $configPath;

$dbPath = __DIR__ . '/core/Database.php';
if(!file_exists($dbPath)) die("Error: No se encuentra 'core/Database.php'");
require_once $dbPath;

$controllerPath = __DIR__ . '/core/Controller.php';
if(!file_exists($controllerPath)) die("Error: No se encuentra 'core/Controller.php'");
require_once $controllerPath;
// --- FIN RUTAS ABSOLUTAS ---


// Gestión de rutas (Router simple)
$controllerName = 'DashboardController';
$methodName = 'index';
$params = [];

// Verificar si se pasa un controlador en la URL
if (isset($_GET['c'])) {
    $controllerName = ucwords($_GET['c']) . 'Controller';
}

// Verificar si el archivo del controlador existe
$controllerFile = __DIR__ . '/controllers/' . $controllerName . '.php'; // Usar __DIR__

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
        // En producción, es mejor redirigir a un error 404
        die("Error 404: Método no encontrado.");
    }
} else {
    // Fallback al Dashboard
    $defaultControllerFile = __DIR__ . '/controllers/DashboardController.php';
    
    if(!file_exists($defaultControllerFile)) {
            die("ERROR FATAL: No se encuentra 'DashboardController.php'.");
    }
    
    require_once $defaultControllerFile;
    $controller = new DashboardController();
    $controller->index();
}

?>

