<?php
// core/Controller.php - Controlador base del que heredarán todos

class Controller {
    
    // Cargar un modelo
    public function model($model) {
        $modelFile = 'models/' . $model . '.php';
        if (file_exists($modelFile)) {
            require_once $modelFile;
            return new $model();
        } else {
            die("Error: Modelo '$model' no encontrado.");
        }
    }

    // Cargar una vista (dentro de la plantilla)
    public function view($view, $data = []) {
        $viewFile = 'views/' . $view . '.php';
        if (file_exists($viewFile)) {
            // $data estará disponible dentro de la vista
            require_once 'views/template.php'; // Carga la plantilla principal
        } else {
            die("Error: Vista '$view' no encontrada.");
        }
    }
}
?>
