<?php
// core/Controller.php - Controlador base del que heredarán todos

class Controller {
    
    // Cargar un modelo
    public function model($model) {
        // Usamos __DIR__ para una ruta más segura
        $modelFile = __DIR__ . '/../models/' . $model . '.php';
        if (file_exists($modelFile)) {
            require_once $modelFile;
            return new $model();
        } else {
            die("Error: Modelo '$model' no encontrado en '$modelFile'.");
        }
    }

    // Cargar una vista (dentro de la plantilla)
    public function view($view, $data = []) {
        // Esta es la ruta al *contenido* (ej: views/dashboard/index.php)
        $viewFile = __DIR__ . '/../views/' . $view . '.php';
        
        if (file_exists($viewFile)) {
            // $data estará disponible dentro de la vista
            
            // --- ¡CORRECCIÓN AQUÍ! ---
            // Esta es la ruta a la *plantilla* (el HTML principal con el menú)
            // Tu archivo de plantilla se llama 'dashboard.php', no 'template.php'
            $templateFile = __DIR__ . '/../views/dashboard.php'; 
            
            if (file_exists($templateFile)) {
                 require_once $templateFile; // Carga la plantilla principal
            } else {
                die("Error: El archivo de plantilla 'views/dashboard.php' no se encuentra en " . $templateFile);
            }
            // --- FIN DE CORRECCIÓN ---

        } else {
            die("Error: Vista de contenido '$view' no encontrada en '$viewFile'.");
        }
    }
}
?>
