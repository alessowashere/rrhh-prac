<?php
// controllers/DashboardController.php

class DashboardController extends Controller {
    
    // 1. Declarar los modelos como propiedades privadas
    private $practicanteModel;
    private $reclutamientoModel;
    private $convenioModel;

    // 2. Instanciarlos todos en el constructor
    public function __construct() {
        $this->practicanteModel = $this->model('PracticanteModel');
        $this->reclutamientoModel = $this->model('ReclutamientoModel');
        $this->convenioModel = $this->model('ConvenioModel');
    }

    public function index() {
        // --- AUTOMATIZACIÓN: Ejecutar ceses al entrar ---
        // Ahora usamos $this->convenioModel
        $cesados = $this->convenioModel->ejecutarCeseAutomatico();
        if ($cesados > 0) {
            $_SESSION['mensaje_info'] = "Se han detectado y cesado automáticamente $cesados practicante(s) con contrato vencido.";
        }

        // 3. Obtener datos actualizados para el Dashboard
        $data = [
            'titulo' => 'Panel de Control Activo',
            'kpis' => [
                'activos'    => $this->practicanteModel->getPracticanteCounts()['activos'],
                'en_proceso' => $this->reclutamientoModel->contarEnProceso(),
                'criticos'   => $this->convenioModel->contarConveniosPorVencer(7), // Próximos 7 días
                'por_vencer' => $this->convenioModel->contarConveniosPorVencer(30)
            ],
            'pendientes_convenio' => $this->convenioModel->getCandidatosAceptados(),
            'lista_por_vencer'    => $this->convenioModel->getConveniosPorVencer(30),
            'ultimos_registrados' => $this->convenioModel->getUltimosConveniosCreados(5)
        ];

        // 4. Cargar la vista rediseñada
        $this->view('dashboard/index', $data);
    }
}
?>