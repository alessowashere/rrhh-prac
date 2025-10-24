<?php
// controllers/DashboardController.php

class DashboardController extends Controller {
    
    public function index() {
        // --- Lógica del Dashboard ---
        // 1. Instanciar modelos necesarios
        // $practicanteModel = $this->model('PracticanteModel');
        // $convenioModel = $this->model('ConvenioModel');

        // 2. Obtener datos de los modelos
        // $activos = $practicanteModel->contarActivos();
        // $candidatos = $practicanteModel->contarCandidatos();
        // $porVencer = $convenioModel->contarPorVencer(30); // 30 días

        // 3. Preparar array $data para la vista
        $data = [
            'titulo' => 'Dashboard',
            'practicantes_activos' => 0, // Reemplazar con $activos
            'candidatos_proceso' => 0, // Reemplazar con $candidatos
            'convenios_por_vencer' => 0 // Reemplazar con $porVencer
        ];

        // 4. Cargar la vista (pasando los datos)
        
        // --- ¡CORRECCIÓN AQUÍ! ---
        // La vista de contenido que quieres cargar es 'dashboard/index'
        // que corresponde al archivo 'views/dashboard/index.php'
        $this->view('dashboard/index', $data);
        // --- FIN DE CORRECCIÓN ---
    }
}
?>
