<?php
// controllers/ConveniosController.php

class ConveniosController extends Controller {

    private $convenioModel;

    public function __construct() {
        $this->convenioModel = $this->model('ConvenioModel');
    }

    /**
     * Muestra el dashboard de Convenios:
     * 1. Candidatos 'Aceptados' pendientes de convenio.
     * 2. Convenios 'Vigentes'.
     */
    public function index() {
        $data = [
            'titulo' => 'Gestión de Convenios',
            'pendientes' => $this->convenioModel->getCandidatosAceptados(),
            'vigentes' => $this->convenioModel->getConveniosVigentes()
        ];
        $this->view('convenios/index', $data);
    }

    /**
     * Muestra el formulario para CREAR un nuevo convenio.
     * Requiere el ID del proceso de reclutamiento.
     * se accede via ?c=convenios&m=crear&proceso_id=X&practicante_id=Y
     */
    public function crear() {
        $proceso_id = (int)($_GET['proceso_id'] ?? 0);
        $practicante_id = (int)($_GET['practicante_id'] ?? 0);

        if ($proceso_id === 0 || $practicante_id === 0) {
            $_SESSION['mensaje_error'] = 'IDs de Proceso y Practicante inválidos.';
            header('Location: index.php?c=convenios');
            exit;
        }

        // Cargamos los catálogos para los dropdowns
        $catalogos = $this->convenioModel->getCatalogos();
        
        $data = [
            'titulo' => 'Crear Nuevo Convenio',
            'proceso_id' => $proceso_id,
            'practicante' => $this->convenioModel->getPracticanteSimple($practicante_id),
            'locales' => $catalogos['locales'],
            'areas' => $catalogos['areas']
        ];
        
        $this->view('convenios/crear', $data);
    }

    /**
     * Guarda el nuevo convenio y el primer período.
     * Actualiza el estado del practicante a 'Activo'.
     * Es una transacción de BD.
     */
    public function guardar() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            
            $datosConvenio = [
                'practicante_id' => (int)$_POST['practicante_id'],
                'proceso_id' => (int)$_POST['proceso_id'],
                'tipo_practica' => trim($_POST['tipo_practica']),
                'estado_convenio' => 'Vigente' // Siempre inicia 'Vigente'
            ];
            
            $datosPeriodo = [
                'fecha_inicio' => trim($_POST['fecha_inicio']),
                'fecha_fin' => trim($_POST['fecha_fin']),
                'local_id' => (int)$_POST['local_id'],
                'area_id' => (int)$_POST['area_id'],
                // El estado del 1er período (Activo o Futuro)
                'estado_periodo' => (strtotime($_POST['fecha_inicio']) <= time()) ? 'Activo' : 'Futuro'
            ];

            // Validaciones
            if (empty($datosConvenio['practicante_id']) || empty($datosConvenio['tipo_practica']) || empty($datosPeriodo['fecha_inicio']) || empty($datosPeriodo['fecha_fin'])) {
                $_SESSION['mensaje_error'] = 'Todos los campos marcados con * son obligatorios.';
                header('Location: index.php?c=convenios&m=crear&proceso_id=' . $datosConvenio['proceso_id'] . '&practicante_id=' . $datosConvenio['practicante_id']);
                exit;
            }

            try {
                $this->convenioModel->crearConvenioTransaccion($datosConvenio, $datosPeriodo);
                $_SESSION['mensaje_exito'] = 'Convenio creado exitosamente. El practicante ha sido movido a "Activo".';
                header('Location: index.php?c=convenios');
                exit;
            } catch (Exception $e) {
                $_SESSION['mensaje_error'] = 'Error al guardar: ' . $e->getMessage();
                // Devolver al formulario de creación
                header('Location: index.php?c=convenios&m=crear&proceso_id=' . $datosConvenio['proceso_id'] . '&practicante_id=' . $datosConvenio['practicante_id']);
                exit;
            }

        } else {
            header('Location: index.php?c=convenios');
            exit;
        }
    }

    /**
     * Muestra la página para GESTIONAR un convenio existente.
     * Permite agregar adendas y nuevos períodos.
     * Se accede via ?c=convenios&m=gestionar&id=CONVENIO_ID
     */
    public function gestionar() {
        $convenio_id = (int)($_GET['id'] ?? 0);
        if ($convenio_id === 0) {
            header('Location: index.php?c=convenios');
            exit;
        }

        $catalogos = $this->convenioModel->getCatalogos();

        $data = [
            'titulo' => 'Gestionar Convenio',
            'convenio' => $this->convenioModel->getDetalleConvenio($convenio_id),
            'locales' => $catalogos['locales'],
            'areas' => $catalogos['areas']
        ];
        
        if (!$data['convenio']) {
             $_SESSION['mensaje_error'] = 'Convenio no encontrado.';
             header('Location: index.php?c=convenios');
             exit;
        }

        $this->view('convenios/gestionar', $data);
    }

    /**
     * Guarda una nueva adenda para un convenio.
     */
    public function guardarAdenda() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $convenio_id = (int)$_POST['convenio_id'];
            $datos = [
                'convenio_id' => $convenio_id,
                'tipo_accion' => trim($_POST['tipo_accion']),
                'fecha_adenda' => trim($_POST['fecha_adenda']),
                'descripcion' => trim($_POST['descripcion'])
            ];

            try {
                $this->convenioModel->agregarAdenda($datos);
                $_SESSION['mensaje_exito'] = 'Adenda registrada exitosamente.';
            } catch (Exception $e) {
                $_SESSION['mensaje_error'] = 'Error al guardar adenda: ' . $e->getMessage();
            }
            header('Location: index.php?c=convenios&m=gestionar&id=' . $convenio_id);
            exit;
        }
        header('Location: index.php?c=convenios');
        exit;
    }

    /**
     * Guarda un nuevo período (ej. reubicación, corte).
     * Cierra el período 'Activo' anterior.
     */
    public function guardarPeriodo() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $convenio_id = (int)$_POST['convenio_id'];
            $datosPeriodo = [
                'convenio_id' => $convenio_id,
                'fecha_inicio' => trim($_POST['fecha_inicio']),
                'fecha_fin' => trim($_POST['fecha_fin']),
                'local_id' => (int)$_POST['local_id'],
                'area_id' => (int)$_POST['area_id'],
                'estado_periodo' => (strtotime($_POST['fecha_inicio']) <= time()) ? 'Activo' : 'Futuro'
            ];

            try {
                // Esta transacción cierra el período anterior e inserta el nuevo
                $this->convenioModel->agregarNuevoPeriodo($datosPeriodo);
                $_SESSION['mensaje_exito'] = 'Nuevo período registrado. El período anterior fue finalizado.';
            } catch (Exception $e) {
                $_SESSION['mensaje_error'] = 'Error al guardar período: ' . $e->getMessage();
            }
            header('Location: index.php?c=convenios&m=gestionar&id=' . $convenio_id);
            exit;
        }
        header('Location: index.php?c=convenios');
        exit;
    }
    
    /**
     * Cambia el estado del convenio (Finalizado, Renuncia, Cancelado)
     * y el estado general del practicante (Cesado).
     */
    public function finalizar() {
        $convenio_id = (int)($_GET['convenio_id'] ?? 0);
        $practicante_id = (int)($_GET['practicante_id'] ?? 0);
        $nuevo_estado = trim($_GET['estado'] ?? ''); // (Finalizado, Renuncia, Cancelado)

        if ($convenio_id === 0 || $practicante_id === 0 || empty($nuevo_estado)) {
             $_SESSION['mensaje_error'] = 'Datos inválidos para finalizar convenio.';
             header('Location: index.php?c=convenios');
             exit;
        }

        try {
            // Esta transacción actualiza Convenio, Practicante y el último Período.
            $this->convenioModel->actualizarEstadoConvenio($convenio_id, $practicante_id, $nuevo_estado, 'Cesado');
             $_SESSION['mensaje_exito'] = "Convenio actualizado a '$nuevo_estado'. El practicante fue movido a 'Cesado'.";
        } catch (Exception $e) {
            $_SESSION['mensaje_error'] = 'Error al finalizar convenio: ' . $e->getMessage();
        }
        
        header('Location: index.php?c=convenios&m=gestionar&id=' . $convenio_id);
        exit;
    }
}