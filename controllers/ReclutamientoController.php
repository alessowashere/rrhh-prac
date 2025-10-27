<?php
// controllers/ReclutamientoController.php

class ReclutamientoController extends Controller {
    
    private $reclutamientoModel;

    public function __construct() {
        // Instanciamos el modelo para tener acceso a la base de datos
        // Usamos el método model() heredado de Controller.php
        $this->reclutamientoModel = $this->model('ReclutamientoModel');
    }

    /**
     * Página principal de Reclutamiento.
     * Muestra la lista de candidatos 'En Evaluación'.
     */
    public function index() {
        // Buscamos los procesos activos (estado 'En Evaluación')
        $procesos = $this->reclutamientoModel->getProcesosActivos();
        
        $data = [
            'titulo' => 'Gestión de Reclutamiento',
            'procesos' => $procesos
        ];

        // Cargamos la vista 'reclutamiento/index' dentro de la plantilla principal
        $this->view('reclutamiento/index', $data);
    }

    /**
     * Muestra el formulario para registrar un nuevo candidato.
     * Carga los catálogos (Universidades, Escuelas) para los dropdowns.
     */
    public function nuevo() {
        $catalogos = $this->reclutamientoModel->getCatalogosParaFormulario();
        
        $data = [
            'titulo' => 'Registrar Nuevo Candidato',
            'universidades' => $catalogos['universidades'],
            'escuelas' => $catalogos['escuelas']
        ];
        
        // Pasamos los datos de las escuelas como JSON para el Javascript
        $data['escuelas_json'] = json_encode($catalogos['escuelas']);

        $this->view('reclutamiento/nuevo', $data);
    }

    /**
     * Procesa el formulario de 'nuevo' candidato.
     * Es llamado por el método POST del formulario.
     */
    public function guardar() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // 1. Recolectar y sanitizar datos del POST
            $datosPost = [
                'dni' => trim($_POST['dni']) ?? '',
                'nombres' => trim($_POST['nombres']) ?? '',
                'apellidos' => trim($_POST['apellidos']) ?? '',
                'fecha_nacimiento' => trim($_POST['fecha_nacimiento']) ?? null,
                'email' => trim($_POST['email']) ?? null,
                'telefono' => trim($_POST['telefono']) ?? null,
                'promedio_general' => trim($_POST['promedio_general']) ?? null,
                'escuela_id' => (int)($_POST['escuela_id']) ?? null,
                'fecha_postulacion' => trim($_POST['fecha_postulacion']) ?? date('Y-m-d')
            ];
            
            // Validaciones básicas (puedes expandir esto)
            if (empty($datosPost['dni']) || empty($datosPost['nombres']) || empty($datosPost['apellidos']) || empty($datosPost['escuela_id'])) {
                $_SESSION['mensaje_error'] = 'DNI, Nombres, Apellidos y Escuela son obligatorios.';
                header('Location: index.php?c=reclutamiento&m=nuevo');
                exit;
            }

            // 2. Llamar al modelo para crear el practicante y el proceso
            try {
                $this->reclutamientoModel->crearNuevoProceso($datosPost);
                $_SESSION['mensaje_exito'] = 'Candidato y proceso registrados exitosamente.';
                
            } catch (Exception $e) {
                // Capturamos errores (ej: DNI duplicado)
                $_SESSION['mensaje_error'] = 'Error al guardar: ' . $e->getMessage();
            }

            // 3. Redirigir al listado
            header('Location: index.php?c=reclutamiento');
            exit;

        } else {
            // Si no es POST, redirigir
            header('Location: index.php?c=reclutamiento');
            exit;
        }
    }

    /**
     * Muestra el formulario para calificar una entrevista.
     * Se accede vía ?c=reclutamiento&m=evaluar&id=PROCESO_ID
     */
    public function evaluar() {
        // El router simple no pasa params, los tomamos de $_GET
        $proceso_id = (int)($_GET['id'] ?? 0);

        if ($proceso_id === 0) {
            header('Location: index.php?c=reclutamiento');
            exit;
        }

        $proceso = $this->reclutamientoModel->getProcesoCompleto($proceso_id);

        if (!$proceso) {
            $_SESSION['mensaje_error'] = 'Proceso no encontrado.';
            header('Location: index.php?c=reclutamiento');
            exit;
        }
        
        $data = [
            'titulo' => 'Evaluar Entrevista',
            'proceso' => $proceso
        ];

        $this->view('reclutamiento/evaluar', $data);
    }

    /**
     * Guarda las notas de la entrevista (ResultadosEntrevista)
     * y actualiza el puntaje final en ProcesosReclutamiento.
     */
    public function guardarEvaluacion() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            
            $proceso_id = (int)($_POST['proceso_id'] ?? 0);
            
            // Recolectamos las 10 notas y nombres
            $datosEntrevista = [
                'proceso_id' => $proceso_id,
                'comentarios' => trim($_POST['comentarios_adicionales']) ?? ''
            ];

            $suma_notas = 0;
            $cantidad_notas = 0;

            for ($i = 1; $i <= 10; $i++) {
                $nombre_key = 'campo_' . $i . '_nombre';
                $nota_key = 'campo_' . $i . '_nota';

                $datosEntrevista[$nombre_key] = trim($_POST[$nombre_key]) ?? 'Criterio ' . $i;
                $datosEntrevista[$nota_key] = (float)($_POST[$nota_key]) ?? null;

                if ($datosEntrevista[$nota_key] !== null && $datosEntrevista[$nota_key] > 0) {
                    $suma_notas += $datosEntrevista[$nota_key];
                    $cantidad_notas++;
                }
            }
            
            // Calcular promedio
            $promedio = ($cantidad_notas > 0) ? ($suma_notas / $cantidad_notas) : 0;
            $datosEntrevista['puntuacion_final'] = round($promedio, 2);

            // Llamar al modelo
            try {
                $this->reclutamientoModel->actualizarEntrevista($datosEntrevista);
                $_SESSION['mensaje_exito'] = 'Evaluación guardada y promedio actualizado.';
            } catch (Exception $e) {
                $_SESSION['mensaje_error'] = 'Error al guardar evaluación: ' . $e->getMessage();
            }

            // Redirigir de vuelta al formulario de evaluación
            header('Location: index.php?c=reclutamiento&m=evaluar&id=' . $proceso_id);
            exit;

        } else {
            header('Location: index.php?c=reclutamiento');
            exit;
        }
    }

    /**
     * Cambia el estado de un proceso (Aceptado, Rechazado).
     * Se accede vía ?c=reclutamiento&m=actualizarEstado&id=PROCESO_ID&estado=NUEVO_ESTADO
     */
    public function actualizarEstado() {
        $proceso_id = (int)($_GET['id'] ?? 0);
        $nuevo_estado = trim($_GET['estado'] ?? '');

        // Validar que el estado sea uno de los permitidos
        $estados_validos = ['Aceptado', 'Rechazado', 'En Evaluación'];
        
        if ($proceso_id > 0 && in_array($nuevo_estado, $estados_validos)) {
            try {
                $this->reclutamientoModel->cambiarEstadoProceso($proceso_id, $nuevo_estado);
                $_SESSION['mensaje_exito'] = "Proceso actualizado a '$nuevo_estado'.";
            } catch (Exception $e) {
                $_SESSION['mensaje_error'] = 'Error al actualizar estado: ' . $e->getMessage();
            }
        } else {
             $_SESSION['mensaje_error'] = 'Datos inválidos para actualizar estado.';
        }

        header('Location: index.php?c=reclutamiento');
        exit;
    }
}