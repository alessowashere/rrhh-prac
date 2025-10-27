<?php
// controllers/PracticantesController.php

class PracticantesController extends Controller {

    private $practicanteModel;

    public function __construct() {
        $this->practicanteModel = $this->model('PracticanteModel');
    }

    /**
     * Muestra la página principal con la lista de practicantes
     * (Activos y Cesados).
     */
    public function index() {
        // Obtenemos la lista filtrada (solo Activos y Cesados)
        $practicantes = $this->practicanteModel->getPracticantesList();
        
        $data = [
            'titulo' => 'Gestión de Practicantes',
            'practicantes' => $practicantes
        ];

        $this->view('practicantes/index', $data);
    }

    /**
     * Muestra el perfil detallado de un practicante.
     * Esta es la vista más importante del módulo.
     * Se accede vía ?c=practicantes&m=ver&id=PRACTICANTE_ID
     */
    public function ver() {
        $id = (int)($_GET['id'] ?? 0);
        
        if ($id === 0) {
            header('Location: index.php?c=practicantes');
            exit;
        }

        // getInfoCompleta() trae al practicante, sus convenios,
        // periodos, adendas y documentos, todo en un array anidado.
        $info = $this->practicanteModel->getInfoCompleta($id);

        if (!$info['detalle']) {
            $_SESSION['mensaje_error'] = 'No se encontró al practicante.';
            header('Location: index.php?c=practicantes');
            exit;
        }

        $data = [
            'titulo' => 'Perfil del Practicante',
            'info' => $info
        ];

        $this->view('practicantes/ver', $data);
    }

    /**
     * Muestra el formulario para editar los datos personales
     * de un practicante.
     */
    public function editar() {
        $id = (int)($_GET['id'] ?? 0);
        if ($id === 0) {
            header('Location: index.php?c=practicantes');
            exit;
        }

        $practicante = $this->practicanteModel->getPracticanteDetalle($id);
        $catalogos = $this->practicanteModel->getCatalogosParaFormulario();

        if (!$practicante) {
            $_SESSION['mensaje_error'] = 'Practicante no encontrado.';
            header('Location: index.php?c=practicantes');
            exit;
        }

        $data = [
            'titulo' => 'Editar Practicante',
            'practicante' => $practicante,
            'universidades' => $catalogos['universidades'],
            'escuelas' => $catalogos['escuelas'],
            'escuelas_json' => json_encode($catalogos['escuelas'])
        ];

        $this->view('practicantes/editar', $data);
    }

    /**
     * Procesa el formulario de actualización de datos personales.
     */
    public function actualizar() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $id = (int)($_POST['practicante_id'] ?? 0);
            
            $datosPost = [
                'practicante_id' => $id,
                'dni' => trim($_POST['dni']) ?? '',
                'nombres' => trim($_POST['nombres']) ?? '',
                'apellidos' => trim($_POST['apellidos']) ?? '',
                'fecha_nacimiento' => trim($_POST['fecha_nacimiento']) ?? null,
                'email' => trim($_POST['email']) ?? null,
                'telefono' => trim($_POST['telefono']) ?? null,
                'promedio_general' => trim($_POST['promedio_general']) ?? null,
                'escuela_id' => (int)($_POST['escuela_id']) ?? null,
                'estado_general' => trim($_POST['estado_general']) ?? 'Activo'
            ];

            if (empty($datosPost['dni']) || empty($datosPost['nombres']) || empty($datosPost['apellidos']) || $id === 0) {
                $_SESSION['mensaje_error'] = 'Datos incompletos para actualizar.';
                header('Location: index.php?c=practicantes&m=editar&id=' . $id);
                exit;
            }

            try {
                $this->practicanteModel->actualizarPracticante($datosPost);
                $_SESSION['mensaje_exito'] = 'Datos del practicante actualizados.';
            } catch (Exception $e) {
                $_SESSION['mensaje_error'] = 'Error al actualizar: ' . $e->getMessage();
            }
            
            header('Location: index.php?c=practicantes&m=ver&id=' . $id);
            exit;

        } else {
            header('Location: index.php?c=practicantes');
            exit;
        }
    }
}