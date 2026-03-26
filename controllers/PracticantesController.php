<?php
// controllers/PracticantesController.php

class PracticantesController extends Controller {

    private $practicanteModel;

    public function __construct() {
        $this->practicanteModel = $this->model('PracticanteModel');
    }

    public function index() {
        $data = [
            'titulo' => 'Directorio de Practicantes',
            'practicantes' => $this->practicanteModel->getPracticantesList(),
            'counts' => $this->practicanteModel->getPracticanteCounts()
        ];
        // Gracias al extract() del Hito 3, estas variables pasarán limpias a la vista
        $this->view('practicantes/index', $data);
    }

    public function ver() {
        $id = (int)($_GET['id'] ?? 0);
        
        // Uso de BASE_URL y exit obligatorio tras una redirección
        if ($id === 0) {
            header('Location: ' . BASE_URL . '?c=practicantes');
            exit; 
        }
        
        $info = $this->practicanteModel->getInfoCompleta($id);
        if (!$info['detalle']) {
            header('Location: ' . BASE_URL . '?c=practicantes');
            exit;
        }

        $this->view('practicantes/ver', ['titulo' => 'Perfil del Practicante', 'info' => $info]);
    }

    /**
     * Procesa la carga masiva desde un archivo CSV con seguridad estricta
     */
    public function importar() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['archivo_csv'])) {
            
            // 1. Validar errores a nivel de servidor
            if ($_FILES['archivo_csv']['error'] !== UPLOAD_ERR_OK) {
                $_SESSION['mensaje_error'] = "Error del servidor al recibir el archivo.";
                header('Location: ' . BASE_URL . '?c=practicantes&m=importar');
                exit;
            }

            $fileTmpPath = $_FILES['archivo_csv']['tmp_name'];
            
            // 2. Validar que sea un archivo legítimo subido por HTTP POST (Previene ataques LFI)
            if (is_uploaded_file($fileTmpPath)) {
                
                // 3. Validar la extensión para evitar ejecución de código
                $fileExtension = strtolower(pathinfo($_FILES['archivo_csv']['name'], PATHINFO_EXTENSION));
                if ($fileExtension !== 'csv') {
                    $_SESSION['mensaje_error'] = "El formato es inválido. Solo se admiten archivos .csv";
                    header('Location: ' . BASE_URL . '?c=practicantes&m=importar');
                    exit;
                }

                $handle = fopen($fileTmpPath, "r");
                $contador = 0;

                fgetcsv($handle); // Saltar cabecera

                while (($row = fgetcsv($handle, 1000, ",")) !== FALSE) {
                    // 4. Evitar líneas en blanco y limpiar (trim) las cadenas
                    if (isset($row[0]) && !empty(trim($row[0]))) {
                        $datos = [
                            'dni' => trim($row[0]),
                            'nombres' => trim($row[1]),
                            'apellidos' => trim($row[2]),
                            'estado' => trim($row[3] ?? 'Activo'),
                            'escuela' => trim($row[4] ?? '')
                        ];
                        if ($this->practicanteModel->importarPracticanteSimple($datos)) {
                            $contador++;
                        }
                    }
                }
                fclose($handle);
                
                $_SESSION['mensaje_exito'] = "Se han importado $contador registros correctamente.";
                header('Location: ' . BASE_URL . '?c=practicantes');
                exit;
            } else {
                // Si entra aquí, alguien está intentando inyectar un archivo local del servidor
                die("Error Crítico: Intento de manipulación de archivos detectado.");
            }
        }
        
        $this->view('practicantes/importar', ['titulo' => 'Importación Masiva']);
    }

    public function editar() {
        $id = (int)($_GET['id'] ?? 0);
        $practicante = $this->practicanteModel->getPracticanteDetalle($id);
        $catalogos = $this->practicanteModel->getCatalogosParaFormulario();
        
        $this->view('practicantes/editar', [
            'titulo' => 'Editar Practicante', 
            'practicante' => $practicante, 
            'universidades' => $catalogos['universidades'], 
            'escuelas' => $catalogos['escuelas'], 
            'escuelas_json' => json_encode($catalogos['escuelas'])
        ]);
    }

    public function actualizar() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->practicanteModel->actualizarPracticante($_POST);
            $_SESSION['mensaje_exito'] = 'Datos actualizados.';
            header('Location: ' . BASE_URL . '?c=practicantes&m=ver&id=' . $_POST['practicante_id']);
            exit;
        }
    }
}
?>