<?php
// index.php - Controlador Frontal Seguro

// --- 1. GESTIÓN DE ERRORES ---
// En desarrollo mantenemos esto en 1. Cuando pases a producción en la universidad, ponlos en 0.
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

// --- 2. CARGAS PRINCIPALES ---
// Autoloader de Composer siempre va primero
require_once __DIR__ . '/vendor/autoload.php';

// Usamos __DIR__ aquí porque config.php es el que define BASE_PATH
$configPath = __DIR__ . '/config/config.php';
if (!file_exists($configPath)) die("Error crítico: No se encuentra 'config/config.php'");
require_once $configPath;

// A partir de aquí, usamos la constante BASE_PATH definida en el Hito 1
require_once BASE_PATH . 'core/Database.php';
require_once BASE_PATH . 'core/Controller.php';


// --- 3. ENRUTADOR SEGURO (Router) ---
$controllerName = 'DashboardController';
$methodName = 'index';
$params = [];

// Saneamiento estricto: Solo permitimos letras mayúsculas, minúsculas y números (Regex)
if (isset($_GET['c']) && preg_match('/^[a-zA-Z0-9]+$/', $_GET['c'])) {
    $controllerName = ucwords($_GET['c']) . 'Controller';
}

// Saneamiento estricto para los métodos (permitimos guiones bajos)
if (isset($_GET['m']) && preg_match('/^[a-zA-Z0-9_]+$/', $_GET['m'])) {
    $methodName = $_GET['m'];
}

$controllerFile = BASE_PATH . 'controllers/' . $controllerName . '.php';

// --- 4. EJECUCIÓN DEL CONTROLADOR ---
if (file_exists($controllerFile)) {
    require_once $controllerFile;
    
    // Validamos que el archivo realmente contenga la clase que esperamos
    if (class_exists($controllerName)) {
        $controller = new $controllerName();

        // Validamos que el método exista Y sea público (is_callable es más seguro que method_exists)
        if (is_callable([$controller, $methodName])) {
            $controller->$methodName($params);
        } else {
            http_response_code(404);
            die("Error 404: La acción solicitada no existe o no es accesible.");
        }
    } else {
        http_response_code(404);
        die("Error 404: El controlador está corrupto o mal nombrado.");
    }

} else {
    // Fallback seguro: Si ponen cualquier tontería en la URL, los mandamos al Dashboard
    $defaultControllerFile = BASE_PATH . 'controllers/DashboardController.php';
    
    if (file_exists($defaultControllerFile)) {
        require_once $defaultControllerFile;
        $controller = new DashboardController();
        $controller->index();
    } else {
        die("ERROR FATAL: El sistema no puede iniciar. Falta 'DashboardController.php'.");
    }
}
?>