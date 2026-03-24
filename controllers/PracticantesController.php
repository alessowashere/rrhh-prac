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
        $this->view('practicantes/index', $data);
    }

    public function ver() {
        $id = (int)($_GET['id'] ?? 0);
        if ($id === 0) header('Location: index.php?c=practicantes');
        $info = $this->practicanteModel->getInfoCompleta($id);
        if (!$info['detalle']) header('Location: index.php?c=practicantes');

        $this->view('practicantes/ver', ['titulo' => 'Perfil del Practicante', 'info' => $info]);
    }

    /**
     * [NUEVO] Procesa la carga masiva desde un archivo CSV
     */
    public function importar() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['archivo_csv'])) {
            $file = $_FILES['archivo_csv']['tmp_name'];
            $handle = fopen($file, "r");
            $contador = 0;

            fgetcsv($handle); // Saltar cabecera
            while (($row = fgetcsv($handle, 1000, ",")) !== FALSE) {
                $datos = [
                    'dni' => $row[0],
                    'nombres' => $row[1],
                    'apellidos' => $row[2],
                    'estado' => $row[3],
                    'escuela' => $row[4]
                ];
                if($this->practicanteModel->importarPracticanteSimple($datos)) $contador++;
            }
            fclose($handle);
            $_SESSION['mensaje_exito'] = "Se han importado $contador registros correctamente.";
            header('Location: index.php?c=practicantes');
            exit;
        }
        $this->view('practicantes/importar', ['titulo' => 'Importación Masiva']);
    }

    public function editar() {
        $id = (int)($_GET['id'] ?? 0);
        $practicante = $this->practicanteModel->getPracticanteDetalle($id);
        $catalogos = $this->practicanteModel->getCatalogosParaFormulario();
        $this->view('practicantes/editar', ['titulo' => 'Editar Practicante', 'practicante' => $practicante, 'universidades' => $catalogos['universidades'], 'escuelas' => $catalogos['escuelas'], 'escuelas_json' => json_encode($catalogos['escuelas'])]);
    }

    public function actualizar() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->practicanteModel->actualizarPracticante($_POST);
            $_SESSION['mensaje_exito'] = 'Datos actualizados.';
            header('Location: index.php?c=practicantes&m=ver&id=' . $_POST['practicante_id']);
            exit;
        }
    }
}