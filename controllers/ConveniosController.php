<?php
// controllers/ConveniosController.php

class ConveniosController extends Controller {

    private $convenioModel;
    private $reclutamientoModel; // Se cargará en el constructor

    public function __construct() {
        // Carga directa de modelos. El método model() maneja errores si no los encuentra.
        $this->convenioModel = $this->model('ConvenioModel');
        $this->reclutamientoModel = $this->model('ReclutamientoModel'); 
    }

    /**
     * Muestra el dashboard de Convenios.
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
     * Muestra el formulario para CREAR un nuevo convenio (solo datos).
     */
    public function crear() {
        $proceso_id = (int)($_GET['proceso_id'] ?? 0);
        $practicante_id = (int)($_GET['practicante_id'] ?? 0);

        if ($proceso_id === 0 || $practicante_id === 0) {
            $_SESSION['mensaje_error'] = 'IDs de Proceso y Practicante inválidos.';
            header('Location: index.php?c=convenios');
            exit;
        }

        // Ya no necesitamos la validación if (!$this->reclutamientoModel), 
        // porque si falló la carga en el constructor, la ejecución se habría detenido antes.

        // Usamos directamente el modelo cargado en el constructor
        $proceso_simple = $this->reclutamientoModel->getProcesoSimple($proceso_id);
        if (!$proceso_simple) {
            // Este error es si el modelo cargó, pero no encontró el ID específico
            $_SESSION['mensaje_error'] = 'El proceso de reclutamiento de origen (ID: '.$proceso_id.') no existe o no se pudo cargar.';
            header('Location: index.php?c=convenios');
            exit;
        }

        $catalogos = $this->convenioModel->getCatalogos();
        $practicante = $this->convenioModel->getPracticanteSimple($practicante_id);

        if(!$practicante){
            $_SESSION['mensaje_error'] = 'El practicante (ID: '.$practicante_id.') no existe o no se pudo cargar.';
            header('Location: index.php?c=convenios');
            exit;
        }
        
        $data = [
            'titulo' => 'Crear Nuevo Convenio',
            'proceso_id' => $proceso_id,
            'practicante' => $practicante,
            'tipo_practica' => $proceso_simple['tipo_practica'] ?? 'No especificado', // Asegurar valor por defecto
            'locales' => $catalogos['locales'],
            'areas' => $catalogos['areas']
        ];
        
        $this->view('convenios/crear', $data);
    }

    /**
     * Guarda el nuevo convenio (datos) y el primer período.
     */
    public function guardar() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            
            $datosConvenio = [
                'practicante_id' => (int)$_POST['practicante_id'],
                'proceso_id' => (int)$_POST['proceso_id'],
                'tipo_practica' => trim($_POST['tipo_practica']),
                'estado_convenio' => 'Vigente', 
                'estado_firma' => 'Pendiente'
            ];
            
            $datosPeriodo = [
                'fecha_inicio' => trim($_POST['fecha_inicio']),
                'fecha_fin' => trim($_POST['fecha_fin']),
                'local_id' => (int)$_POST['local_id'],
                'area_id' => (int)$_POST['area_id'],
                'estado_periodo' => (strtotime($_POST['fecha_inicio']) <= time()) ? 'Activo' : 'Futuro'
            ];

            if (empty($datosConvenio['practicante_id']) || empty($datosConvenio['tipo_practica']) || empty($datosPeriodo['fecha_inicio']) || empty($datosPeriodo['fecha_fin'])) {
                $_SESSION['mensaje_error'] = 'Todos los campos marcados con * son obligatorios.';
                header('Location: index.php?c=convenios&m=crear&proceso_id=' . ($datosConvenio['proceso_id'] ?? 0) . '&practicante_id=' . ($datosConvenio['practicante_id'] ?? 0));
                exit;
            }

            try {
                $convenio_id = $this->convenioModel->crearConvenioTransaccion($datosConvenio, $datosPeriodo);
                
                $_SESSION['mensaje_exito'] = 'Datos del convenio guardados. El practicante está "Activo". Ahora debe subir el convenio firmado.';
                header('Location: index.php?c=convenios&m=gestionar&id=' . $convenio_id); 
                exit;
            } catch (Exception $e) {
                $_SESSION['mensaje_error'] = 'Error al guardar convenio: ' . $e->getMessage();
                 // Log detallado para el servidor
                 error_log("Error en ConveniosController::guardar(): " . $e->getMessage());
                header('Location: index.php?c=convenios&m=crear&proceso_id=' . ($datosConvenio['proceso_id'] ?? 0) . '&practicante_id=' . ($datosConvenio['practicante_id'] ?? 0));
                exit;
            }
        }
        header('Location: index.php?c=convenios');
        exit;
    }

    /**
     * Muestra la página para GESTIONAR un convenio existente.
     */
    public function gestionar() {
        $convenio_id = (int)($_GET['id'] ?? 0);
        if ($convenio_id === 0) {
             $_SESSION['mensaje_error'] = 'ID de Convenio inválido.';
            header('Location: index.php?c=convenios');
            exit;
        }

        $catalogos = $this->convenioModel->getCatalogos();
        $convenio_detalle = $this->convenioModel->getDetalleConvenio($convenio_id);

        if (!$convenio_detalle) {
             $_SESSION['mensaje_error'] = 'Convenio no encontrado.';
             header('Location: index.php?c=convenios');
             exit;
        }
        
        $fecha_fin_actual = null;
        $periodo_activo = null; 
        if (isset($convenio_detalle['periodos']) && is_array($convenio_detalle['periodos'])) {
            foreach ($convenio_detalle['periodos'] as $p) {
                if ($p['estado_periodo'] == 'Activo') {
                    $fecha_fin_actual = $p['fecha_fin'];
                    $periodo_activo = $p; 
                    break;
                }
            }
        }
        if ($fecha_fin_actual === null) {
            $fecha_fin_actual = date('Y-m-d');
        }

        $data = [
            'titulo' => 'Gestionar Convenio #' . $convenio_id,
            'convenio' => $convenio_detalle,
            'fecha_fin_actual' => $fecha_fin_actual,
            'periodo_activo' => $periodo_activo,
            'locales' => $catalogos['locales'],
            'areas' => $catalogos['areas']
        ];
        
        $this->view('convenios/gestionar', $data);
    }

    /**
     * Sube el PDF del CONVENIO INICIAL firmado.
     */
    public function subirConvenioFirmado() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $convenio_id = (int)$_POST['convenio_id'];
            $practicante_id = (int)$_POST['practicante_id']; 
            $archivo = $_FILES['documento_convenio'] ?? null;

            if ($convenio_id > 0 && $practicante_id > 0 && $archivo && $archivo['error'] == UPLOAD_ERR_OK && $archivo['type'] == 'application/pdf') {
                
                $url_relativa = $this->moverDocumento($archivo, $practicante_id, $convenio_id, 'CONVENIO_FIRMADO');

                if ($url_relativa) {
                    try {
                        if($this->convenioModel->actualizarConvenioFirmado($convenio_id, $url_relativa)){
                            $_SESSION['mensaje_exito'] = 'Convenio principal firmado y subido exitosamente.';
                        } else {
                             $_SESSION['mensaje_error'] = 'Error al actualizar la base de datos (convenio no actualizado).';
                        }
                    } catch (Exception $e) {
                        error_log("Error BD al subir convenio firmado: ".$e->getMessage());
                        $_SESSION['mensaje_error'] = 'Error al actualizar BD: ' . $e->getMessage();
                    }
                } else {
                    $_SESSION['mensaje_error'] = 'Error al mover el archivo subido. Verifique permisos en uploads/documentos.';
                }
            } else {
                 $error_msg = 'Datos incompletos o error en la subida. ';
                 if(!$archivo || $archivo['error'] !== UPLOAD_ERR_OK) {
                     $upload_errors = [
                         UPLOAD_ERR_INI_SIZE   => "El archivo excede la directiva upload_max_filesize.",
                         UPLOAD_ERR_FORM_SIZE  => "El archivo excede la directiva MAX_FILE_SIZE.",
                         UPLOAD_ERR_PARTIAL    => "El archivo se subió parcialmente.",
                         UPLOAD_ERR_NO_FILE    => "No se subió ningún archivo.",
                         UPLOAD_ERR_NO_TMP_DIR => "Falta directorio temporal.",
                         UPLOAD_ERR_CANT_WRITE => "No se pudo escribir el archivo en disco.",
                         UPLOAD_ERR_EXTENSION  => "Una extensión de PHP detuvo la subida.",
                     ];
                     $error_code = $archivo['error'] ?? UPLOAD_ERR_NO_FILE;
                     $error_msg .= 'Error: ' . ($upload_errors[$error_code] ?? 'Desconocido');

                 } elseif ($archivo['type'] != 'application/pdf') {
                      $error_msg .= 'El archivo debe ser PDF (tipo detectado: '.$archivo['type'].').';
                 }
                $_SESSION['mensaje_error'] = $error_msg;
            }
            header('Location: index.php?c=convenios&m=gestionar&id=' . $convenio_id);
            exit;
        }
        header('Location: index.php?c=convenios');
        exit;
    }

    /**
     * Guarda una adenda de AMPLIACIÓN.
     */
    public function ampliar() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $convenio_id = (int)$_POST['convenio_id'];
            $practicante_id = (int)$_POST['practicante_id'];
            $archivo = $_FILES['documento_adenda_amp'] ?? null; 
            $nueva_fecha_fin = trim($_POST['nueva_fecha_fin']);
            
            $datosAdenda = [
                'convenio_id' => $convenio_id,
                'tipo_accion' => 'AMPLIACION', 
                'fecha_adenda' => trim($_POST['fecha_adenda']),
                'descripcion' => trim($_POST['descripcion_amp']), 
                'documento_adenda_url' => null 
            ];

            if (!$archivo || $archivo['error'] != UPLOAD_ERR_OK || $archivo['type'] != 'application/pdf') {
                $_SESSION['mensaje_error'] = 'El Documento de Adenda (PDF) es obligatorio para la ampliación.';
                header('Location: index.php?c=convenios&m=gestionar&id=' . $convenio_id);
                exit;
            }
             if (empty($nueva_fecha_fin) || empty($datosAdenda['fecha_adenda'])) {
                 $_SESSION['mensaje_error'] = 'Las fechas (Nueva Fin y Adenda) son obligatorias.';
                 header('Location: index.php?c=convenios&m=gestionar&id=' . $convenio_id);
                 exit;
             }
            
            $url_relativa = $this->moverDocumento($archivo, $practicante_id, $convenio_id, 'ADENDA_AMPLIACION');
            if (!$url_relativa) {
                 $_SESSION['mensaje_error'] = 'Error al guardar el documento de adenda de ampliación.';
                 header('Location: index.php?c=convenios&m=gestionar&id=' . $convenio_id);
                 exit;
            }
            $datosAdenda['documento_adenda_url'] = $url_relativa;

            try {
                if($this->convenioModel->ampliarConvenio($datosAdenda, $nueva_fecha_fin)){
                    $_SESSION['mensaje_exito'] = 'Convenio ampliado exitosamente. Adenda registrada.';
                } else {
                     $_SESSION['mensaje_error'] = 'Error al procesar la ampliación en la base de datos.';
                }
            } catch (Exception $e) {
                 error_log("Error en ConveniosController::ampliar(): " . $e->getMessage());
                $_SESSION['mensaje_error'] = 'Error al ampliar: ' . $e->getMessage();
            }
            header('Location: index.php?c=convenios&m=gestionar&id=' . $convenio_id);
            exit;
        }
        header('Location: index.php?c=convenios');
        exit;
    }

    /**
     * Guarda un nuevo período (REUBICACION o CORTE).
     */
    public function guardarPeriodo() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $convenio_id = (int)$_POST['convenio_id'];
            $practicante_id = (int)$_POST['practicante_id'];
            $tipo_accion = trim($_POST['tipo_accion']); 
            
            $nombre_campo_archivo = ($tipo_accion == 'REUBICACION') ? 'documento_adenda_reub' : 'documento_adenda_corte';
            $archivo = $_FILES[$nombre_campo_archivo] ?? null;
            
            $nombre_campo_desc = ($tipo_accion == 'REUBICACION') ? 'descripcion_reub' : 'descripcion_corte';

            $datosPeriodo = [
                'convenio_id' => $convenio_id,
                'fecha_inicio' => trim($_POST['fecha_inicio']),
                'fecha_fin' => trim($_POST['fecha_fin']),
                'local_id' => (int)$_POST['local_id'],
                'area_id' => (int)$_POST['area_id'],
                'estado_periodo' => (strtotime($_POST['fecha_inicio']) <= time()) ? 'Activo' : 'Futuro'
            ];
            
            $datosAdenda = [
                'convenio_id' => $convenio_id,
                'tipo_accion' => $tipo_accion,
                'fecha_adenda' => trim($_POST['fecha_adenda']),
                'descripcion' => trim($_POST[$nombre_campo_desc]),
                'documento_adenda_url' => null 
            ];

            if (!in_array($tipo_accion, ['REUBICACION', 'CORTE'])) {
                $_SESSION['mensaje_error'] = 'Tipo de acción inválida.';
                 header('Location: index.php?c=convenios&m=gestionar&id=' . $convenio_id);
                 exit;
            }
             if (!$archivo || $archivo['error'] != UPLOAD_ERR_OK || $archivo['type'] != 'application/pdf') {
                $_SESSION['mensaje_error'] = 'El Documento de Adenda (PDF) es obligatorio para ' . $tipo_accion . '.';
                header('Location: index.php?c=convenios&m=gestionar&id=' . $convenio_id);
                exit;
            }
            if (empty($datosPeriodo['fecha_inicio']) || empty($datosPeriodo['fecha_fin']) || empty($datosAdenda['fecha_adenda'])) {
                 $_SESSION['mensaje_error'] = 'Todas las fechas son obligatorias.';
                 header('Location: index.php?c=convenios&m=gestionar&id=' . $convenio_id);
                 exit;
             }

            $nombre_doc = 'ADENDA_' . $tipo_accion;
            $url_relativa = $this->moverDocumento($archivo, $practicante_id, $convenio_id, $nombre_doc);
            if (!$url_relativa) {
                 $_SESSION['mensaje_error'] = 'Error al guardar el documento de adenda de ' . $tipo_accion . '.';
                 header('Location: index.php?c=convenios&m=gestionar&id=' . $convenio_id);
                 exit;
            }
            $datosAdenda['documento_adenda_url'] = $url_relativa;

            try {
                if($this->convenioModel->agregarNuevoPeriodo($datosPeriodo, $datosAdenda)){
                    $_SESSION['mensaje_exito'] = 'Nuevo período registrado (' . $tipo_accion . ') y adenda guardada. El período anterior fue finalizado.';
                } else {
                     $_SESSION['mensaje_error'] = 'Error al procesar el registro del nuevo período en la base de datos.';
                }
            } catch (Exception $e) {
                 error_log("Error en ConveniosController::guardarPeriodo(): " . $e->getMessage());
                $_SESSION['mensaje_error'] = 'Error al guardar período (' . $tipo_accion . '): ' . $e->getMessage();
            }
            header('Location: index.php?c=convenios&m=gestionar&id=' . $convenio_id);
            exit;
        }
        header('Location: index.php?c=convenios');
        exit;
    }
    
    /**
     * Registra un CESE (RENUNCIA o CANCELADO).
     */
    public function registrarCese() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $convenio_id = (int)($_POST['convenio_id'] ?? 0);
            $practicante_id = (int)($_POST['practicante_id'] ?? 0);
            $nuevo_estado = trim($_POST['estado'] ?? ''); 
            $archivo = $_FILES['documento_renuncia'] ?? null; 
            $descripcion = trim($_POST['descripcion_cese'] ?? '');

            if ($convenio_id === 0 || $practicante_id === 0 || !in_array($nuevo_estado, ['Renuncia', 'Cancelado'])) {
                 $_SESSION['mensaje_error'] = 'Datos inválidos para registrar el cese.';
                 header('Location: index.php?c=convenios&m=gestionar&id=' . $convenio_id);
                 exit;
            }
            if (empty($descripcion)) {
                $_SESSION['mensaje_error'] = 'La descripción/motivo del cese es obligatoria.';
                header('Location: index.php?c=convenios&m=gestionar&id=' . $convenio_id);
                exit;
            }
            
            $url_relativa = null;
            
            if ($nuevo_estado == 'Renuncia') {
                if (!$archivo || $archivo['error'] != UPLOAD_ERR_OK || $archivo['type'] != 'application/pdf') {
                    $_SESSION['mensaje_error'] = 'El Documento de Renuncia (PDF) es obligatorio para registrar la renuncia.';
                    header('Location: index.php?c=convenios&m=gestionar&id=' . $convenio_id);
                    exit;
                }
                
                $url_relativa = $this->moverDocumento($archivo, $practicante_id, $convenio_id, 'RENUNCIA');
                if (!$url_relativa) {
                     $_SESSION['mensaje_error'] = 'Error al guardar el documento de renuncia.';
                     header('Location: index.php?c=convenios&m=gestionar&id=' . $convenio_id);
                     exit;
                }
            }

            try {
                if($this->convenioModel->finalizarConvenio($convenio_id, $practicante_id, $nuevo_estado, $descripcion, $url_relativa)){
                    $_SESSION['mensaje_exito'] = "Cese por '$nuevo_estado' registrado. El practicante fue movido a 'Cesado'.";
                } else {
                     $_SESSION['mensaje_error'] = 'Error al registrar el cese en la base de datos.';
                }
            } catch (Exception $e) {
                error_log("Error en ConveniosController::registrarCese(): " . $e->getMessage());
                $_SESSION['mensaje_error'] = 'Error al registrar cese: ' . $e->getMessage();
            }
            
            header('Location: index.php?c=convenios&m=gestionar&id=' . $convenio_id);
            exit;
        }
        header('Location: index.php?c=convenios');
        exit;
    }

    /**
     * Función privada para mover archivos subidos.
     */
    private function moverDocumento($archivo, $practicante_id, $convenio_id, $tipo) {
        $ruta_base_relativa = 'uploads/documentos/'; // Relativa al index.php
        $ruta_base_absoluta = realpath(__DIR__ . '/../') . '/' . $ruta_base_relativa;

        // Intentar crear directorio si no existe
        if (!is_dir($ruta_base_absoluta)) {
            if (!mkdir($ruta_base_absoluta, 0777, true)) {
                error_log("Error CRÍTICO: No se pudo crear el directorio de subida: " . $ruta_base_absoluta);
                return false; 
            }
        }
        // Verificar permisos de escritura si ya existe
        elseif (!is_writable($ruta_base_absoluta)) {
            error_log("Error CRÍTICO: El directorio de subida no tiene permisos de escritura: " . $ruta_base_absoluta);
            return false;
        }
        
        $tipo_limpio = preg_replace('/[^a-zA-Z0-9_]/', '_', strtoupper($tipo));
        $extension = pathinfo($archivo['name'], PATHINFO_EXTENSION);
        if (strtolower($extension) !== 'pdf') {
             error_log("Error: Se intentó subir un archivo no PDF ($extension) para $tipo.");
             return false; 
        }
        
        // Nombre: PID_TIPO_CID_timestamp.pdf
        $nombre_archivo = $practicante_id . '_' . $tipo_limpio . '_' . $convenio_id . '_' . time() . '.pdf';
        $ruta_destino_absoluta = $ruta_base_absoluta . $nombre_archivo;
        
        if (move_uploaded_file($archivo['tmp_name'], $ruta_destino_absoluta)) {
            return $ruta_base_relativa . $nombre_archivo; // Devuelve URL relativa
        } else {
             $last_error = error_get_last();
             error_log("Error al mover archivo subido: {$archivo['tmp_name']} a {$ruta_destino_absoluta}. Error PHP: " . ($last_error['message'] ?? 'No disponible'));
            return false;
        }
    }
} // Fin de la clase ConveniosController