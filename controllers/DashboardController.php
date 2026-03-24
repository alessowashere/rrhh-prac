<?php
// controllers/DashboardController.php

class DashboardController extends Controller {
    
    public function index() {
        // 1. Instanciar modelos necesarios
        $practicanteModel = $this->model('PracticanteModel');
        $reclutamientoModel = $this->model('ReclutamientoModel');
        $convenioModel = $this->model('ConvenioModel');

        // --- AUTOMATIZACIÓN: Ejecutar ceses al entrar ---
        $cesados = $convenioModel->ejecutarCeseAutomatico();
        if ($cesados > 0) {
            $_SESSION['mensaje_info'] = "Se han detectado y cesado automáticamente $cesados practicante(s) con contrato vencido.";
        }

        // 2. Obtener datos actualizados para el Dashboard
        $data = [
            'titulo' => 'Panel de Control Activo',
            'kpis' => [
                'activos'    => $practicanteModel->contarActivos(),
                'en_proceso' => $reclutamientoModel->contarEnProceso(),
                'criticos'   => $convenioModel->contarConveniosPorVencer(7), // Próximos 7 días
                'por_vencer' => $convenioModel->contarConveniosPorVencer(30)
            ],
            'pendientes_convenio' => $convenioModel->getCandidatosAceptados(),
            'lista_por_vencer'    => $convenioModel->getConveniosPorVencer(30),
            'ultimos_registrados' => $convenioModel->getUltimosConveniosCreados(5)
        ];

        // 3. Cargar la vista rediseñada
        $this->view('dashboard/index', $data);
    }
}