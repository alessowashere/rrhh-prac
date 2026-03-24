<?php
require_once 'models/ReporteModel.php';
require_once 'models/Persona.php'; 
require_once 'models/Periodo.php'; 

class ReporteController {

    private $db;
    private $reporteModel;
    private $empleadoModel; 
    private $periodoModel;  
    
    // --- NUEVO: Propiedad para la lista de áreas ---
    private $personaModel; // Usaremos la clase Persona para la lista de áreas

    public function __construct() {
        if (!class_exists('Database')) require_once __DIR__ . '/../config/Database.php';
        $this->db = Database::getInstance()->getConnection();
        
        $this->reporteModel = new ReporteModel($this->db);
        $this->empleadoModel = new Persona($this->db); 
        $this->periodoModel = new Periodo($this->db);  
        
        // --- NUEVO: Instanciar PersonaModel ---
        $this->personaModel = new Persona($this->db);
    }

    /**
     * Muestra la página principal de selección de reportes.
     */
    public function index() {
        $empleados = [];
        $listaAnios = [];
        $listaAreas = []; // <-- NUEVA variable
        
        try {
            $empleados = $this->empleadoModel->listar(); 
            $listaAnios = $this->periodoModel->getPeriodoAnios(); 
            
            // --- NUEVO: Cargar la lista de áreas ---
            $listaAreas = $this->personaModel->listarAreasDistintas();
            
        } catch (Exception $e) {
             error_log("Error cargando datos para reportes: " . $e->getMessage());
        }
        
        require 'views/layout/header.php';
        // Las variables $empleados, $listaAnios y $listaAreas estarán disponibles
        require 'views/reportes/index.php'; 
        require 'views/layout/footer.php';
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
            'area' => $_POST['area'] ?? null, // <-- NUEVO filtro
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
                
                // --- NUEVO CASE PARA EL REPORTE ---
                // --- INICIO CAMBIO EN CASE 'por_area' ---
                case 'por_area':
                     if (empty($filtros['area'])) {
                        throw new Exception("Debe seleccionar una unidad/dependencia.");
                    }
                    
                    // Leer el nuevo filtro
                    $tipo_info = $_POST['tipo_info_area'] ?? 'saldos';
                    $filtros['tipo_info_area'] = $tipo_info;

                    if ($tipo_info == 'programados') {
                        // Reporte de Vacaciones (Detalle)
                        $tituloReporte = 'Reporte de Vacaciones Programadas por Unidad: ' . htmlspecialchars($filtros['area']);
                        $data['resultados'] = $this->reporteModel->getReporteVacacionesPorArea($filtros);
                        $vistaReporte = 'views/reportes/vistas/por_area_detalle.php'; // Nueva vista
                    } else {
                        // Reporte de Saldos (Resumen) - (Comportamiento anterior)
                        $tituloReporte = 'Reporte de Saldos por Unidad: ' . htmlspecialchars($filtros['area']);
                        // (La función getReportePorArea ya fue actualizada en Paso 2 para usar anio_inicio)
                        $data['resultados'] = $this->reporteModel->getReportePorArea($filtros); 
                        $vistaReporte = 'views/reportes/vistas/por_area.php'; // Vista existente
                    }
                    break;
                    case 'general_por_area':
                    // Leer el nuevo filtro
                    $tipo_info = $_POST['tipo_info_general_area'] ?? 'saldos';
                    $filtros['tipo_info_general_area'] = $tipo_info;

                    if ($tipo_info == 'programados') {
                        // Reporte de Vacaciones (Detalle)
                        $tituloReporte = 'Reporte General de Vacaciones por Unidad';
                        $data['resultados'] = $this->reporteModel->getReporteVacacionesGeneralPorArea($filtros);
                        $vistaReporte = 'views/reportes/vistas/general_detalle_por_area.php'; // Usaremos una NUEVA vista
                    } else {
                        // Reporte de Saldos (Resumen) - (Comportamiento anterior)
                        $tituloReporte = 'Reporte General de Saldos por Unidad';
                        $data['resultados'] = $this->reporteModel->getReporteGeneralPorArea($filtros); 
                        $vistaReporte = 'views/reportes/vistas/general.php'; // Vista existente
                    }
                    break;
                // --- FIN CAMBIO EN CASE 'por_area' ---

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
        require_once 'views/layout/report_preview.php';
    }
}