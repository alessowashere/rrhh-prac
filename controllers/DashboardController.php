<?php
// controllers/DashboardController.php

class DashboardController extends Controller {
    
    public function index() {
        // --- Lógica del Dashboard ---
        
        // 1. Instanciar modelos necesarios
        $practicanteModel = $this->model('PracticanteModel');
        $reclutamientoModel = $this->model('ReclutamientoModel');
        $convenioModel = $this->model('ConvenioModel');

        // 2. Obtener datos de los modelos
        
        // --- Contadores para los KPIs ---
        $activos = $practicanteModel->contarActivos();
        $candidatos = $reclutamientoModel->contarEnProceso();
        $porVencerCount = $convenioModel->contarConveniosPorVencer(30); // 30 días

        // --- Listas para los Paneles ---
        $pendientesConvenio = $convenioModel->getCandidatosAceptados();
        $listaPorVencer = $convenioModel->getConveniosPorVencer(30);
        $ultimosRegistrados = $convenioModel->getUltimosConveniosCreados(5);


        // 3. Preparar array $data para la vista
        $data = [
            'titulo' => 'Dashboard',
            
            // Datos para KPIs
            'practicantes_activos' => $activos,
            'candidatos_proceso' => $candidatos,
            'convenios_por_vencer' => $porVencerCount,
            
            // Datos para Paneles de Listas
            'pendientes_convenio' => $pendientesConvenio,
            'lista_por_vencer' => $listaPorVencer,
            'ultimos_registrados' => $ultimosRegistrados
        ];

        // 4. Cargar la vista
        $this->view('dashboard/index', $data);
    }
}