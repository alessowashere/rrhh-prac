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
        
        // CAMBIO AQUÍ: Llamamos a getPracticantePorId en lugar de getInfoCompleta
        $practicante = $this->practicanteModel->getPracticantePorId($id);
        
        if (!$practicante) {
            header('Location: ' . BASE_URL . '?c=practicantes');
            exit;
        }

        // CAMBIO AQUÍ: Pasamos 'practicante' en el array, no 'info'
        $this->view('practicantes/ver', [
            'titulo' => 'Perfil del Practicante', 
            'practicante' => $practicante
        ]);
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
        // 1. Forzamos a que el ID sea un número entero desde el método GET
        $id = (int)($_GET['id'] ?? 0);

        if ($id === 0) {
            header('Location: ' . BASE_URL . '?c=practicantes');
            exit;
        }

        $practicante = $this->practicanteModel->getPracticantePorId($id);

        if (!$practicante) {
            header('Location: ' . BASE_URL . '?c=practicantes');
            exit;
        }

        // BLOQUEO SEGURIDAD: Si está cesado, no permitir edición
        if ($practicante['estado_general'] === 'Cesado') {
            $_SESSION['mensaje_error'] = 'No se pueden editar los datos de un practicante con estado CESADO.';
            header('Location: index.php?c=practicantes&m=ver&id=' . $id);
            exit;
        }

        // 2. Usamos el método correcto que sí existe en tu modelo
        $catalogos = $this->practicanteModel->getCatalogosParaFormulario();

        $data = [
            'titulo' => 'Editar Practicante',
            'practicante' => $practicante,
            'escuelas' => $catalogos['escuelas'], 
            'universidades' => $catalogos['universidades'] // Opcional, por si tu vista lo usa
        ];
        
        $this->view('practicantes/editar', $data);
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