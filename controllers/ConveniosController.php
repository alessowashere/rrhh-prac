<?php
// controllers/ConveniosController.php

class ConveniosController extends Controller {

    private $convenioModel;
    private $reclutamientoModel;

    public function __construct() {
        $this->convenioModel = $this->model('ConvenioModel');
        $this->reclutamientoModel = $this->model('ReclutamientoModel');
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

        $proceso_simple = $this->reclutamientoModel->getProcesoSimple($proceso_id);
        if (!$proceso_simple) {
            $_SESSION['mensaje_error'] = 'El proceso de reclutamiento de origen no existe.';
            header('Location: index.php?c=convenios');
            exit;
        }

        $catalogos = $this->convenioModel->getCatalogos();
        
        $data = [
            'titulo' => 'Crear Nuevo Convenio',
            'proceso_id' => $proceso_id,
            'practicante' => $this->convenioModel->getPracticanteSimple($practicante_id),
            'tipo_practica' => $proceso_simple['tipo_practica'],
            'locales' => $catalogos['locales'],
            'areas' => $catalogos['areas']
        ];
        
        $this->view('convenios/crear', $data);
    }

    /**
     * Guarda el nuevo convenio (solo datos) y el primer período.
     * El convenio nace con estado_firma = 'Pendiente'.
     */
    public function guardar() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            
            $datosConvenio = [
                'practicante_id' => (int)$_POST['practicante_id'],
                'proceso_id' => (int)$_POST['proceso_id'],
                'tipo_practica' => trim($_POST['tipo_practica']),
                'estado_convenio' => 'Vigente', // Siempre inicia 'Vigente'
                'estado_firma' => 'Pendiente' // NUEVO: Nace como pendiente de firma
            ];
            
            $datosPeriodo = [
                'fecha_inicio' => trim($_POST['fecha_inicio']),
                'fecha_fin' => trim($_POST['fecha_fin']),
                'local_id' => (int)$_POST['local_id'],
                'area_id' => (int)$_POST['area_id'],
                'estado_periodo' => (strtotime($_POST['fecha_inicio']) <= time()) ? 'Activo' : 'Futuro'
            ];

            // Validaciones...
            if (empty($datosConvenio['practicante_id']) || empty($datosConvenio['tipo_practica']) || empty($datosPeriodo['fecha_inicio']) || empty($datosPeriodo['fecha_fin'])) {
                $_SESSION['mensaje_error'] = 'Todos los campos marcados con * son obligatorios.';
                header('Location: index.php?c=convenios&m=crear&proceso_id=' . $datosConvenio['proceso_id'] . '&practicante_id=' . $datosConvenio['practicante_id']);
                exit;
            }

            try {
                // El modelo ahora solo pone 'Pendiente'
                $convenio_id = $this->convenioModel->crearConvenioTransaccion($datosConvenio, $datosPeriodo);
                
                $_SESSION['mensaje_exito'] = 'Datos del convenio guardados. El practicante está "Activo". Ahora debe subir el convenio firmado.';
                // Redirige a GESTIONAR para subir el documento
                header('Location: index.php?c=convenios&m=gestionar&id=' . $convenio_id); 
                exit;
            } catch (Exception $e) {
                $_SESSION['mensaje_error'] = 'Error al guardar: ' . $e->getMessage();
                header('Location: index.php?c=convenios&m=crear&proceso_id=' . $datosConvenio['proceso_id'] . '&practicante_id=' . $datosConvenio['practicante_id']);
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
        
        $fecha_fin_actual = '';
        foreach ($convenio_detalle['periodos'] as $p) {
            if ($p['estado_periodo'] == 'Activo') {
                $fecha_fin_actual = $p['fecha_fin'];
                break;
            }
        }

        $data = [
            'titulo' => 'Gestionar Convenio',
            'convenio' => $convenio_detalle,
            'fecha_fin_actual' => $fecha_fin_actual,
            'locales' => $catalogos['locales'],
            'areas' => $catalogos['areas']
        ];
        
        $this->view('convenios/gestionar', $data);
    }

    /**
     * [NUEVO] Sube el PDF del CONVENIO INICIAL firmado y actualiza el estado.
     */
    public function subirConvenioFirmado() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $convenio_id = (int)$_POST['convenio_id'];
            $practicante_id = (int)$_POST['practicante_id'];
            $archivo = $_FILES['documento_convenio'] ?? null;

            if ($convenio_id > 0 && $practicante_id > 0 && $archivo && $archivo['error'] == UPLOAD_ERR_OK) {
                
                // Mover archivo
                $url_relativa = $this->moverDocumento($archivo, $practicante_id, $convenio_id, 'CONVENIO_FIRMADO');

                if ($url_relativa) {
                    try {
                        $this->convenioModel->actualizarConvenioFirmado($convenio_id, $url_relativa);
                        $_SESSION['mensaje_exito'] = 'Convenio principal firmado y subido exitosamente.';
                    } catch (Exception $e) {
                        $_SESSION['mensaje_error'] = 'Error al actualizar BD: ' . $e->getMessage();
                    }
                } else {
                    $_SESSION['mensaje_error'] = 'Error al mover el archivo subido.';
                }
            } else {
                $_SESSION['mensaje_error'] = 'Datos incompletos o error en la subida del archivo.';
            }
            header('Location: index.php?c=convenios&m=gestionar&id=' . $convenio_id);
            exit;
        }
        header('Location: index.php?c=convenios');
        exit;
    }


    /**
     * Guarda una adenda de AMPLIACIÓN (extiende el período actual).
     * Ahora incluye la subida de la adenda firmada.
     */
    public function ampliar() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $convenio_id = (int)$_POST['convenio_id'];
            $practicante_id = (int)$_POST['practicante_id'];
            $archivo = $_FILES['documento_adenda'] ?? null;
            $nueva_fecha_fin = trim($_POST['nueva_fecha_fin']);
            
            $datosAdenda = [
                'convenio_id' => $convenio_id,
                'tipo_accion' => 'AMPLIACION', // Fijo
                'fecha_adenda' => trim($_POST['fecha_adenda']),
                'descripcion' => trim($_POST['descripcion']),
                'documento_adenda_url' => null // Se llenará después
            ];

            // Validaciones
            if (!$archivo || $archivo['error'] != UPLOAD_ERR_OK) {
                $_SESSION['mensaje_error'] = 'El Documento de Adenda (PDF) es obligatorio.';
                header('Location: index.php?c=convenios&m=gestionar&id=' . $convenio_id);
                exit;
            }
            
            // 1. Mover el documento
            $url_relativa = $this->moverDocumento($archivo, $practicante_id, $convenio_id, 'ADENDA_AMPLIACION');
            if (!$url_relativa) {
                 $_SESSION['mensaje_error'] = 'Error al guardar el documento de adenda.';
                 header('Location: index.php?c=convenios&m=gestionar&id=' . $convenio_id);
                 exit;
            }
            
            $datosAdenda['documento_adenda_url'] = $url_relativa;

            // 2. Ejecutar la transacción
            try {
                $this->convenioModel->ampliarConvenio($datosAdenda, $nueva_fecha_fin);
                $_SESSION['mensaje_exito'] = 'Convenio ampliado exitosamente. Adenda registrada.';
            } catch (Exception $e) {
                $_SESSION['mensaje_error'] = 'Error al ampliar: ' . $e->getMessage();
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
     * Ahora incluye la subida de la adenda firmada.
     */
    public function guardarPeriodo() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $convenio_id = (int)$_POST['convenio_id'];
            $practicante_id = (int)$_POST['practicante_id'];
            $archivo = $_FILES['documento_adenda'] ?? null;
            $tipo_accion = trim($_POST['tipo_accion']); // REUBICACION o CORTE

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
                'descripcion' => trim($_POST['descripcion']),
                'documento_adenda_url' => null // Se llenará después
            ];

            // Validaciones
            if (!$archivo || $archivo['error'] != UPLOAD_ERR_OK) {
                $_SESSION['mensaje_error'] = 'El Documento de Adenda (PDF) es obligatorio para esta acción.';
                header('Location: index.php?c=convenios&m=gestionar&id=' . $convenio_id);
                exit;
            }

            // 1. Mover el documento
            $nombre_doc = 'ADENDA_' . $tipo_accion;
            $url_relativa = $this->moverDocumento($archivo, $practicante_id, $convenio_id, $nombre_doc);
            if (!$url_relativa) {
                 $_SESSION['mensaje_error'] = 'Error al guardar el documento de adenda.';
                 header('Location: index.php?c=convenios&m=gestionar&id=' . $convenio_id);
                 exit;
            }
            
            $datosAdenda['documento_adenda_url'] = $url_relativa;

            // 2. Ejecutar la transacción
            try {
                // Esta transacción cierra el período anterior, inserta el nuevo Y registra la adenda.
                $this->convenioModel->agregarNuevoPeriodo($datosPeriodo, $datosAdenda);
                $_SESSION['mensaje_exito'] = 'Nuevo período registrado y adenda guardada. El período anterior fue finalizado.';
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
     * Finaliza/Cancela un convenio (RENUNCIA o CANCELADO).
     * 'Finalizado' ahora debe ser automático (lógica de cron job, no manual).
     */
    public function finalizar() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $convenio_id = (int)($_POST['convenio_id'] ?? 0);
            $practicante_id = (int)($_POST['practicante_id'] ?? 0);
            $nuevo_estado = trim($_POST['estado'] ?? ''); // 'Renuncia' o 'Cancelado'
            $archivo = $_FILES['documento_renuncia'] ?? null;
            $descripcion = trim($_POST['descripcion'] ?? '');

            if ($convenio_id === 0 || $practicante_id === 0 || !in_array($nuevo_estado, ['Renuncia', 'Cancelado'])) {
                 $_SESSION['mensaje_error'] = 'Datos inválidos para finalizar convenio.';
                 header('Location: index.php?c=convenios&m=gestionar&id=' . $convenio_id);
                 exit;
            }
            
            $url_relativa = null;
            
            // Si es Renuncia, el documento es OBLIGATORIO
            if ($nuevo_estado == 'Renuncia') {
                if (!$archivo || $archivo['error'] != UPLOAD_ERR_OK) {
                    $_SESSION['mensaje_error'] = 'El Documento de Renuncia (PDF) es obligatorio.';
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
                // Esta transacción actualiza Convenio, Practicante y el último Período.
                // Y ahora también registra la adenda/documento de renuncia.
                $this->convenioModel->finalizarConvenio($convenio_id, $practicante_id, $nuevo_estado, $descripcion, $url_relativa);
                 $_SESSION['mensaje_exito'] = "Convenio actualizado a '$nuevo_estado'. El practicante fue movido a 'Cesado'.";
            } catch (Exception $e) {
                $_SESSION['mensaje_error'] = 'Error al finalizar convenio: ' . $e->getMessage();
            }
            
            header('Location: index.php?c=convenios&m=gestionar&id=' . $convenio_id);
            exit;
        }
        header('Location: index.php?c=convenios');
        exit;
    }

    /**
     * Función privada para mover archivos y generar una URL relativa.
     */
    private function moverDocumento($archivo, $practicante_id, $convenio_id, $tipo) {
        $ruta_base = __DIR__ . '/../uploads/documentos/';
        if (!is_dir($ruta_base)) mkdir($ruta_base, 0777, true);
        
        // Generar un nombre único para evitar sobreescrituras (ej: 5_CONVENIO_FIRMADO_12_1678886400.pdf)
        $nombre_archivo = $practicante_id . '_' . $tipo . '_' . $convenio_id . '_' . time() . '.pdf';
        $ruta_destino = $ruta_base . $nombre_archivo;
        
        if (move_uploaded_file($archivo['tmp_name'], $ruta_destino)) {
            return 'uploads/documentos/' . $nombre_archivo;
        }
        return false;
    }
}