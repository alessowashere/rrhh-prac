<?php
// core/Controller.php - Controlador base optimizado

class Controller {
    
    // Cargar un modelo de forma segura
    public function model($model) {
        // Saneamiento para evitar inclusión de archivos maliciosos
        $model = preg_replace('/[^a-zA-Z0-9_]/', '', $model);
        $modelFile = BASE_PATH . 'models/' . $model . '.php';
        
        if (file_exists($modelFile)) {
            require_once BASE_PATH . 'core/Model.php'; // Aseguramos que el core exista
            require_once $modelFile;
            
            if (class_exists($model)) {
                return new $model();
            }
        }
        die("Error Crítico: No se pudo encontrar o instanciar el modelo '$model'.");
    }

    // Cargar una vista (Ahora soporta layouts dinámicos)
    // Por defecto usa 'dashboard', pero puedes pasar 'null' u otro layout
    public function view($view, $data = [], $layout = 'dashboard') {
        
        // Extrae el array asociativo a variables individuales ($data['nombre'] -> $nombre)
        if (!empty($data)) {
            extract($data);
        }

        $viewFile = BASE_PATH . 'views/' . $view . '.php';
        
        if (file_exists($viewFile)) {
            
            if ($layout === null) {
                // Si layout es null, carga SOLO la vista (ideal para AJAX, Modales o PDFs)
                require_once $viewFile;
            } else {
                // Carga la plantilla principal, asumiendo que dentro de ella se imprime $viewFile
                $templateFile = BASE_PATH . 'views/' . $layout . '.php'; 
                if (file_exists($templateFile)) {
                    require_once $templateFile;
                } else {
                    die("Error: Layout de diseño '$layout' no encontrado en '$templateFile'.");
                }
            }
            
        } else {
            die("Error: Vista de contenido '$view' no encontrada.");
        }
    }
}
?>