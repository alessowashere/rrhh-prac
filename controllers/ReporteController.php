<?php
// controllers/ReporteController.php

// 1. Heredamos de Controller (Ya no necesitas hacer require de los modelos aquí arriba)
class ReporteController extends Controller {

    private $reporteModel;
    private $empleadoModel; 
    private $periodoModel;  
    private $personaModel; 

    public function __construct() {
        // 2. Instanciamos los modelos usando la función segura del framework
        $this->reporteModel = $this->model('ReporteModel');
        $this->empleadoModel = $this->model('Persona'); 
        $this->periodoModel = $this->model('Periodo');  
        $this->personaModel = $this->model('Persona');
    }

    /**
     * Muestra la página principal de selección de reportes.
     */
    public function index() {
        $empleados = [];
        $listaAnios = [];
        $listaAreas = []; 
        
        try {
            $empleados = $this->empleadoModel->listar(); 
            $listaAnios = $this->periodoModel->getPeriodoAnios(); 
            $listaAreas = $this->personaModel->listarAreasDistintas();
            
        } catch (Exception $e) {
             error_log("Error cargando datos para reportes: " . $e->getMessage());
        }
        
        // 3. Adaptación a la vista maestra
        $data = [
            'empleados' => $empleados,
            'listaAnios' => $listaAnios,
            'listaAreas' => $listaAreas
        ];
        
        // Asumiendo que ahora usamos la vista maestra unificada
        $this->view('reportes/index', $data); 
    }

    /**
     * Punto de entrada central para CUALQUIER reporte.
     */
    public function generar() {
        if (!isset($_POST['tipo_reporte'])) {
            die("Error: Tipo de reporte no especificado.");
        }

        $tipoReporte = $_POST['tipo_reporte'];
        $data = []; 
        $vistaReporte = ''; 
        $tituloReporte = ''; 

        $filtros = [
            'empleado_id' => $_POST['empleado_id'] ?? null,
            'fecha_inicio' => $_POST['fecha_inicio'] ?? null,
            'fecha_fin' => $_POST['fecha_fin'] ?? null,
            'anio_inicio' => $_POST['anio_inicio'] ?? null,
            'area' => $_POST['area'] ?? null,
        ];

        try {
            switch ($tipoReporte) {
                case 'general':
                    $tituloReporte = 'Reporte General de Saldos';
                    $data['resultados'] = $this->reporteModel->getReporteGeneral($filtros);
                    $vistaReporte = 'views/reportes/vistas/general.php';
                    break;

                case 'por_persona':
                    if (empty($filtros['empleado_id'])) {
                        throw new Exception("Debe seleccionar un empleado.");
                    }
                    $tituloReporte = 'Reporte Individual de Vacaciones';
                    $data['info_empleado'] = $this->empleadoModel->obtenerPorId($filtros['empleado_id']); 
                    $data['resultados'] = $this->reporteModel->getReportePorPersona($filtros);
                    $vistaReporte = 'views/reportes/vistas/por_persona.php';
                    break;

                case 'por_periodo':
                     if (empty($filtros['anio_inicio'])) {
                        throw new Exception("Debe seleccionar un período.");
                    }
                    $tituloReporte = 'Reporte de Vacaciones por Período';
                    $data['resultados'] = $this->reporteModel->getReportePorPeriodo($filtros);
                    $vistaReporte = 'views/reportes/vistas/por_periodo.php';
                    break;

                case 'saldos':
                    $tituloReporte = 'Reporte de Saldos (Positivos y Negativos)';
                    $data['resultados'] = $this->reporteModel->getReporteSaldos($filtros);
                    $vistaReporte = 'views/reportes/vistas/saldos.php';
                    break;
                
                case 'por_area':
                     if (empty($filtros['area'])) {
                        throw new Exception("Debe seleccionar una unidad/dependencia.");
                    }
                    
                    $tipo_info = $_POST['tipo_info_area'] ?? 'saldos';
                    $filtros['tipo_info_area'] = $tipo_info;

                    if ($tipo_info == 'programados') {
                        $tituloReporte = 'Reporte de Vacaciones Programadas por Unidad: ' . htmlspecialchars($filtros['area']);
                        $data['resultados'] = $this->reporteModel->getReporteVacacionesPorArea($filtros);
                        $vistaReporte = 'views/reportes/vistas/por_area_detalle.php'; 
                    } else {
                        $tituloReporte = 'Reporte de Saldos por Unidad: ' . htmlspecialchars($filtros['area']);
                        $data['resultados'] = $this->reporteModel->getReportePorArea($filtros); 
                        $vistaReporte = 'views/reportes/vistas/por_area.php'; 
                    }
                    break;
                    
                case 'general_por_area':
                    $tipo_info = $_POST['tipo_info_general_area'] ?? 'saldos';
                    $filtros['tipo_info_general_area'] = $tipo_info;

                    if ($tipo_info == 'programados') {
                        $tituloReporte = 'Reporte General de Vacaciones por Unidad';
                        $data['resultados'] = $this->reporteModel->getReporteVacacionesGeneralPorArea($filtros);
                        $vistaReporte = 'views/reportes/vistas/general_detalle_por_area.php'; 
                    } else {
                        $tituloReporte = 'Reporte General de Saldos por Unidad';
                        $data['resultados'] = $this->reporteModel->getReporteGeneralPorArea($filtros); 
                        $vistaReporte = 'views/reportes/vistas/general.php'; 
                    }
                    break;

                default:
                    throw new Exception("Tipo de reporte no válido.");
            }

            $this->mostrarVistaPrevia($tituloReporte, $vistaReporte, $data, $filtros);

        } catch (Exception $e) {
            echo "Error al generar el reporte: " . $e->getMessage();
        }
    }

    private function mostrarVistaPrevia($tituloReporte, $vistaReporte, $data, $filtros) {
        extract($data);
        extract(['filtros' => $filtros]);
        // 4. Utilizamos la ruta segura BASE_PATH para evitar problemas con require
        require_once BASE_PATH . 'views/layout/report_preview.php';
    }
}
?>