<?php
// controllers/ReportesController.php

class ReportesController extends Controller {

    private $practicanteModel;
    private $convenioModel;

    public function __construct() {
        // Reutilizamos los modelos que ya tenemos, ¡PRINCIPIO DRY!
        $this->practicanteModel = $this->model('PracticanteModel');
        $this->convenioModel = $this->model('ConvenioModel');
    }

    public function index() {
        $data = [
            'titulo' => 'Centro de Reportes',
            'areas' => $this->convenioModel->obtenerAreasConPracticantes() // Necesitaremos agregar este método al modelo
        ];
        $this->view('reportes/index', $data);
    }

    // --- GENERADOR DE PDF: LISTA DE PRACTICANTES ACTIVOS ---
    public function generarDirectorio() {
        // 1. Cargar la librería FPDF usando nuestra constante segura
        require_once BASE_PATH . 'vendor/setasign/fpdf/fpdf.php';
        
        // 2. Obtener la data (Reutilizamos la consulta del Hito 3)
        $practicantes = $this->practicanteModel->getPracticantesList();

        // 3. Configurar el PDF (Orientación Horizontal 'L' para que quepan las columnas)
        $pdf = new FPDF('L', 'mm', 'A4');
        $pdf->AddPage();
        $pdf->SetFont('Arial', 'B', 16);

        // --- ENCABEZADO ---
        // Aquí podrías agregar el logo de la Universidad Andina si lo tienes en la carpeta assets
        // $pdf->Image(BASE_PATH . 'assets/img/logo_uac.png', 10, 8, 33);
        
        $pdf->Cell(0, 10, utf8_decode('Universidad Andina del Cusco'), 0, 1, 'C');
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(0, 10, utf8_decode('Directorio de Practicantes Activos'), 0, 1, 'C');
        $pdf->Ln(5);

        // --- CABECERAS DE TABLA ---
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->SetFillColor(200, 220, 255); // Color de fondo (Azul claro)
        $pdf->Cell(25, 10, 'DNI', 1, 0, 'C', true);
        $pdf->Cell(65, 10, 'Apellidos y Nombres', 1, 0, 'C', true);
        $pdf->Cell(80, 10, 'Escuela Profesional', 1, 0, 'C', true);
        $pdf->Cell(80, 10, 'Universidad Origen', 1, 0, 'C', true);
        $pdf->Cell(25, 10, 'Estado', 1, 1, 'C', true); // El '1' al final hace el salto de línea

        // --- CUERPO DE TABLA ---
        $pdf->SetFont('Arial', '', 9);
        $activos = 0;

        foreach ($practicantes as $p) {
            if ($p['estado_general'] === 'Activo') {
                $pdf->Cell(25, 8, $p['dni'], 1, 0, 'C');
                // Limitamos la longitud de las cadenas para que no desborden la celda
                $nombreCompleto = substr($p['apellidos'] . ', ' . $p['nombres'], 0, 35);
                $pdf->Cell(65, 8, utf8_decode($nombreCompleto), 1, 0, 'L');
                
                $escuela = substr($p['escuela_nombre'] ?? 'Sin Asignar', 0, 45);
                $pdf->Cell(80, 8, utf8_decode($escuela), 1, 0, 'L');
                
                $universidad = substr($p['universidad_nombre'] ?? 'Sin Asignar', 0, 45);
                $pdf->Cell(80, 8, utf8_decode($universidad), 1, 0, 'L');
                
                $pdf->Cell(25, 8, $p['estado_general'], 1, 1, 'C');
                $activos++;
            }
        }

        // --- PIE DE REPORTE ---
        $pdf->Ln(5);
        $pdf->SetFont('Arial', 'I', 10);
        $pdf->Cell(0, 10, "Total de Practicantes Activos: $activos", 0, 1, 'L');
        $pdf->Cell(0, 10, 'Fecha de Emision: ' . date('d/m/Y H:i'), 0, 1, 'L');

        // 4. Imprimir el PDF en el navegador
        $pdf->Output('I', 'Directorio_Practicantes_' . date('Ymd') . '.pdf');
    }
}
?>