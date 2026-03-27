<?php
// controllers/ReclutamientoController.php

class ReclutamientoController extends Controller {
    
    private $reclutamientoModel;
    private $practicanteModel; 

    public function __construct() {
        $this->reclutamientoModel = $this->model('ReclutamientoModel');
        $this->practicanteModel = $this->model('PracticanteModel');
    }

    public function index() {
        $procesos = $this->reclutamientoModel->getTodosLosProcesos();
        $procesosConFicha = $this->reclutamientoModel->getProcesosConFicha(); 
        
        $contadores = [
            'en_evaluacion' => 0,
            'evaluado' => 0,
            'pendiente' => 0,
            'rechazado' => 0,
            'aceptado' => 0 
        ];
        
        foreach ($procesos as $proceso) {
            switch ($proceso['estado_proceso']) {
                case 'En Evaluación': $contadores['en_evaluacion']++; break;
                case 'Evaluado': $contadores['evaluado']++; break;
                case 'Pendiente': $contadores['pendiente']++; break;
                case 'Rechazado': $contadores['rechazado']++; break;
                case 'Aceptado': $contadores['aceptado']++; break;
            }
        }
        
        $data = [
            'titulo' => 'Dashboard de Reclutamiento', 
            'procesos' => $procesos,
            'procesos_con_ficha' => $procesosConFicha,
            'contadores' => $contadores 
        ];

        $this->view('reclutamiento/index', $data);
    }

    public function nuevo() {
        $catalogos = $this->reclutamientoModel->getCatalogosParaFormulario();
        
        $data = [
            'titulo' => 'Registrar Nuevo Candidato',
            'universidades' => $catalogos['universidades'],
            'escuelas' => $catalogos['escuelas'],
            'escuelas_json' => json_encode($catalogos['escuelas'])
        ];

        $this->view('reclutamiento/nuevo', $data);
    }

    public function guardar() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            
            $datosPost = [
                'dni' => trim($_POST['dni']) ?? '',
                'nombres' => trim($_POST['nombres']) ?? '',
                'apellidos' => trim($_POST['apellidos']) ?? '',
                'fecha_nacimiento' => trim($_POST['fecha_nacimiento']) ?? null,
                'email' => trim($_POST['email']) ?? null,
                'telefono' => trim($_POST['telefono']) ?? null,
                'promedio_general' => trim($_POST['promedio_general']) ?? null,
                'escuela_id' => (int)($_POST['escuela_id']) ?? null,
                'fecha_postulacion' => trim($_POST['fecha_postulacion']) ?? date('Y-m-d'),
                'tipo_practica' => trim($_POST['tipo_practica']) ?? '' 
            ];
            
            $errores = [];
            if (!preg_match('/^[0-9]{8}$/', $datosPost['dni'])) {
                $errores[] = 'DNI debe tener 8 dígitos numéricos.';
            }
            if (!preg_match('/^[A-Za-zÀ-ÿ\s]+$/', $datosPost['nombres']) || !preg_match('/^[A-Za-zÀ-ÿ\s]+$/', $datosPost['apellidos'])) {
                $errores[] = 'Nombres y Apellidos solo deben contener letras y espacios.';
            }
            if (empty($datosPost['fecha_nacimiento'])) {
                $errores[] = 'Fecha de nacimiento es obligatoria.';
            }
            if (!empty($datosPost['email']) && !filter_var($datosPost['email'], FILTER_VALIDATE_EMAIL)) {
                $errores[] = 'El formato del Email no es válido.';
            }
            if (empty($datosPost['escuela_id'])) {
                $errores[] = 'Escuela es obligatoria.';
            }
            if (empty($datosPost['tipo_practica'])) {
                $errores[] = 'Tipo de Práctica es obligatorio.';
            }
            
            $errores_upload = [
                UPLOAD_ERR_INI_SIZE   => 'El archivo supera el tamaño máximo permitido.',
                UPLOAD_ERR_FORM_SIZE  => 'El archivo supera el tamaño máximo del formulario.',
                UPLOAD_ERR_PARTIAL    => 'El archivo se subió de forma incompleta.',
                UPLOAD_ERR_NO_FILE    => 'No se detectó ningún archivo.',
                UPLOAD_ERR_NO_TMP_DIR => 'El servidor no tiene carpeta temporal.',
                UPLOAD_ERR_CANT_WRITE => 'Fallo al escribir el archivo en el disco.',
                UPLOAD_ERR_EXTENSION  => 'Una extensión de PHP bloqueó la subida.'
            ];

            if (!isset($_FILES['file_cv']) || $_FILES['file_cv']['error'] != UPLOAD_ERR_OK) {
                $codigo = $_FILES['file_cv']['error'] ?? UPLOAD_ERR_NO_FILE;
                $errores[] = 'Error en CV: ' . ($errores_upload[$codigo] ?? "Error ($codigo)");
            }
            if (!isset($_FILES['file_dni']) || $_FILES['file_dni']['error'] != UPLOAD_ERR_OK) {
                $codigo = $_FILES['file_dni']['error'] ?? UPLOAD_ERR_NO_FILE;
                $errores[] = 'Error en DNI: ' . ($errores_upload[$codigo] ?? "Error ($codigo)");
            }
            // Validar que el JS haya enviado el consolidado
            if (!isset($_FILES['file_consolidado']) || $_FILES['file_consolidado']['error'] != UPLOAD_ERR_OK) {
                $errores[] = 'Error: No se recibió el archivo CONSOLIDADO generado.';
            }

            if (!empty($errores)) {
                $_SESSION['mensaje_error'] = 'Error de validación: <br>• ' . implode('<br>• ', $errores);
                header('Location: ' . BASE_URL . '?c=reclutamiento&m=nuevo');
                exit;
            }

            try {
                $nuevoProceso = $this->reclutamientoModel->crearNuevoProceso($datosPost);
                $practicante_id = $nuevoProceso['practicante_id'];
                $proceso_id = $nuevoProceso['proceso_id'];

                $ruta_base = BASE_PATH . 'uploads/documentos/';
                if (!is_dir($ruta_base)) mkdir($ruta_base, 0777, true);

                // Recibimos todos los archivos, incluyendo el CONSOLIDADO creado en JS
                $archivos = [
                    'CARTA_PRESENTACION' => $_FILES['file_carta'] ?? null, 
                    'DNI' => $_FILES['file_dni'],                        
                    'CV' => $_FILES['file_cv'],                           
                    'DECLARACIONES' => $_FILES['file_ddjj'] ?? null,
                    'CONSOLIDADO' => $_FILES['file_consolidado'] // Mágicamente procesado por JS
                ];

                foreach ($archivos as $tipo_documento => $archivo) {
                    if ($archivo && isset($archivo['error']) && $archivo['error'] == UPLOAD_ERR_OK) {
                        
                        $nombre_archivo = $practicante_id . '_' . $tipo_documento . '_' . $proceso_id . '.pdf';
                        $ruta_destino = $ruta_base . $nombre_archivo;
                        
                        if (move_uploaded_file($archivo['tmp_name'], $ruta_destino)) {
                            $url_relativa = 'uploads/documentos/' . $nombre_archivo;
                            $this->reclutamientoModel->addDocumento($practicante_id, $proceso_id, $tipo_documento, $url_relativa);
                        }
                    }
                }
                
                $_SESSION['mensaje_exito'] = 'Candidato registrado. Documentos procesados exitosamente.';

            } catch (Exception $e) {
                $_SESSION['mensaje_error'] = 'Error al guardar en BD: ' . $e->getMessage();
            }

            header('Location: ' . BASE_URL . '?c=reclutamiento');
            exit;

        } else {
            header('Location: ' . BASE_URL . '?c=reclutamiento');
            exit;
        }
    }

    public function evaluar() {
        $proceso_id = (int)($_GET['id'] ?? 0);

        if ($proceso_id === 0) {
            header('Location: ' . BASE_URL . '?c=reclutamiento');
            exit;
        }

        $proceso = $this->reclutamientoModel->getProcesoCompleto($proceso_id);

        if (!$proceso) {
            $_SESSION['mensaje_error'] = 'Proceso no encontrado.';
            header('Location: ' . BASE_URL . '?c=reclutamiento');
            exit;
        }

        $documentos = $this->reclutamientoModel->getDocumentosPorProceso($proceso_id);
        
        $data = [
            'titulo' => 'Evaluar Entrevista',
            'proceso' => $proceso, 
            'documentos' => $documentos
        ];

        $this->view('reclutamiento/evaluar', $data);
    }

    public function guardarEvaluacion() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            
            $proceso_id = (int)($_POST['proceso_id'] ?? 0);
            
            $datosEntrevista = [
                'proceso_id' => $proceso_id,
                'comentarios' => trim($_POST['comentarios_adicionales'] ?? ''), // Cambiado para que el modelo lo reconozca
                'fecha_entrevista' => trim($_POST['fecha_entrevista'] ?? date('Y-m-d'))
            ];

            $suma_ponderada = 0;
            $suma_pesos_total = 0;

            for ($i = 1; $i <= 10; $i++) {
                $nombre_key = 'campo_' . $i . '_nombre';
                $nota_key = 'campo_' . $i . '_nota';
                $peso_key = 'campo_' . $i . '_peso'; 

                // Si el campo viene vacío o no viene (porque está disabled), le asignamos null
                $datosEntrevista[$nombre_key] = trim($_POST[$nombre_key] ?? '') ?: null;
                
                $nota_val = $_POST[$nota_key] ?? null;
                $peso_val = $_POST[$peso_key] ?? null;
                
                $datosEntrevista[$nota_key] = ($nota_val !== '' && $nota_val !== null) ? (float)$nota_val : null;
                $datosEntrevista[$peso_key] = ($peso_val !== '' && $peso_val !== null) ? (float)$peso_val : null;

                // Solo sumamos si hay nota y peso válidos
                if ($datosEntrevista[$nota_key] !== null && $datosEntrevista[$nota_key] >= 0 && $datosEntrevista[$peso_key] !== null && $datosEntrevista[$peso_key] > 0) {
                    $suma_ponderada += $datosEntrevista[$nota_key] * $datosEntrevista[$peso_key];
                    $suma_pesos_total += $datosEntrevista[$peso_key];
                }
            }
            
            $promedio = ($suma_pesos_total > 0) ? ($suma_ponderada / $suma_pesos_total) : 0;
            $datosEntrevista['puntuacion_final'] = round($promedio, 2);

            try {
                // Ahora sí se ejecutarán todas las actualizaciones sin detenerse
                $this->reclutamientoModel->actualizarEntrevista($datosEntrevista);
                $this->reclutamientoModel->actualizarFechaEntrevista($proceso_id, $datosEntrevista['fecha_entrevista']);
                $this->reclutamientoModel->cambiarEstadoProceso($proceso_id, 'Evaluado');
                
                $_SESSION['mensaje_exito'] = 'Evaluación guardada y proceso marcado como "Evaluado".';
            } catch (Exception $e) {
                $_SESSION['mensaje_error'] = 'Error al guardar evaluación: ' . $e->getMessage();
            }

            header('Location: ' . BASE_URL . '?c=reclutamiento');
            exit;

        } else {
            header('Location: ' . BASE_URL . '?c=reclutamiento');
            exit;
        }
    }

    public function actualizarEstado() {
        $proceso_id = (int)($_GET['id'] ?? 0);
        $nuevo_estado = trim($_GET['estado'] ?? '');

        $estados_validos = ['Aceptado', 'Rechazado', 'En Evaluación', 'Pendiente']; 
        
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

        header('Location: ' . BASE_URL . '?c=reclutamiento');
        exit;
    }

    public function revisar() {
        $proceso_id = (int)($_GET['id'] ?? 0);

        if ($proceso_id === 0) {
            header('Location: ' . BASE_URL . '?c=reclutamiento');
            exit;
        }

        $proceso = $this->reclutamientoModel->getProcesoCompleto($proceso_id); 

        if (!$proceso) {
            $_SESSION['mensaje_error'] = 'Proceso no encontrado.';
            header('Location: ' . BASE_URL . '?c=reclutamiento');
            exit;
        }

        $documentos = $this->reclutamientoModel->getDocumentosPorProceso($proceso_id);

        $data = [
            'titulo' => 'Revisar Evaluación',
            'proceso' => $proceso,
            'documentos' => $documentos,
            'es_revision' => true 
        ];

        $this->view('reclutamiento/revisar', $data); 
    }
    
    public function subirFicha() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $proceso_id = (int)$_POST['proceso_id'];
            $practicante_id = (int)$_POST['practicante_id'];
            $archivo = $_FILES['ficha_firmada'] ?? null;

            if ($proceso_id > 0 && $practicante_id > 0 && $archivo && $archivo['error'] == UPLOAD_ERR_OK) {
                
                $ruta_base = BASE_PATH . 'uploads/documentos/';
                if (!is_dir($ruta_base)) mkdir($ruta_base, 0777, true);

                $nombre_archivo = $practicante_id . '_FICHA_EVALUACION_' . $proceso_id . '.pdf';
                $ruta_destino = $ruta_base . $nombre_archivo;
                
                if (move_uploaded_file($archivo['tmp_name'], $ruta_destino)) {
                    $url_relativa = 'uploads/documentos/' . $nombre_archivo;
                    try {
                        $this->reclutamientoModel->addDocumento($practicante_id, $proceso_id, 'FICHA_CALIFICACION', $url_relativa);
                        
                        $_SESSION['mensaje_exito'] = 'Ficha firmada subida exitosamente.';
                    } catch (Exception $e) {
                        $_SESSION['mensaje_error'] = 'Error al guardar la ficha en la BD: ' . $e->getMessage();
                    }
                } else {
                    $_SESSION['mensaje_error'] = 'Error crítico: No se pudo mover el archivo al servidor.';
                }
            } else {
                $_SESSION['mensaje_error'] = 'Datos incompletos o error en la subida del archivo.';
            }

            header('Location: ' . BASE_URL . '?c=reclutamiento');
            exit;
        }
        header('Location: ' . BASE_URL . '?c=reclutamiento');
        exit;
    }
}
?>