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
            'vigentes' => $this->convenioModel->getConveniosVigentes() // Modelo modificado
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
     * El convenio nace con estado_firma = 'Pendiente'. Redirige a GESTIONAR.
     */
    public function guardar() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            
            $datosConvenio = [
                'practicante_id' => (int)$_POST['practicante_id'],
                'proceso_id' => (int)$_POST['proceso_id'],
                'tipo_practica' => trim($_POST['tipo_practica']),
                'estado_convenio' => 'Vigente', 
                'estado_firma' => 'Pendiente' // Nace pendiente
            ];
            
            $datosPeriodo = [
                'fecha_inicio' => trim($_POST['fecha_inicio']),
                'fecha_fin' => trim($_POST['fecha_fin']),
                'local_id' => (int)$_POST['local_id'],
                'area_id' => (int)$_POST['area_id'],
                'estado_periodo' => (strtotime($_POST['fecha_inicio']) <= time()) ? 'Activo' : 'Futuro'
            ];

            // Validaciones básicas
            if (empty($datosConvenio['practicante_id']) || empty($datosConvenio['tipo_practica']) || empty($datosPeriodo['fecha_inicio']) || empty($datosPeriodo['fecha_fin']) || empty($datosPeriodo['local_id']) || empty($datosPeriodo['area_id'])) {
                $_SESSION['mensaje_error'] = 'Todos los campos marcados con * son obligatorios.';
                 // Asegúrate de pasar los IDs de nuevo para recargar el form
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
                $_SESSION['mensaje_error'] = 'Error al guardar los datos del convenio: ' . $e->getMessage();
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
         // Si no hay activo, busca el futuro para calcular extensión
        if(empty($fecha_fin_actual)) {
             foreach ($convenio_detalle['periodos'] as $p) {
                if ($p['estado_periodo'] == 'Futuro') {
                    $fecha_fin_actual = $p['fecha_fin'];
                    break;
                }
            }
        }
        // Si sigue vacío, usa el fin del último período finalizado
        if(empty($fecha_fin_actual) && !empty($convenio_detalle['periodos'])) {
            $fecha_fin_actual = $convenio_detalle['periodos'][0]['fecha_fin']; // El primero es el más reciente
        }


        $data = [
            'titulo' => 'Gestionar Convenio',
            'convenio' => $convenio_detalle,
            'fecha_fin_actual_calculo' => $fecha_fin_actual, // Para usar en JS como base para calcular
            'locales' => $catalogos['locales'],
            'areas' => $catalogos['areas']
        ];
        
        $this->view('convenios/gestionar', $data);
    }

    /**
     * Sube el PDF del CONVENIO INICIAL firmado y actualiza el estado.
     */
    public function subirConvenioFirmado() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $convenio_id = (int)$_POST['convenio_id'];
            $practicante_id = (int)$_POST['practicante_id']; // Necesario para nombrar archivo
            $archivo = $_FILES['documento_convenio'] ?? null;

            if ($convenio_id > 0 && $practicante_id > 0 && $archivo && $archivo['error'] == UPLOAD_ERR_OK) {
                
                // Mover archivo
                $url_relativa = $this->moverDocumento($archivo, $practicante_id, $convenio_id, 'CONVENIO_FIRMADO');

                if ($url_relativa !== false) {
                    try {
                        $this->convenioModel->actualizarConvenioFirmado($convenio_id, $url_relativa);
                        $_SESSION['mensaje_exito'] = 'Convenio principal firmado y subido exitosamente.';
                    } catch (Exception $e) {
                        $_SESSION['mensaje_error'] = 'Error al actualizar la base de datos: ' . $e->getMessage();
                    }
                } else {
                    $_SESSION['mensaje_error'] = 'Error al mover el archivo subido. Verifique permisos de la carpeta uploads/documentos.';
                }
            } elseif ($archivo && $archivo['error'] !== UPLOAD_ERR_OK) {
                 $_SESSION['mensaje_error'] = 'Error al subir el archivo PDF: Código ' . $archivo['error'];
            }
             else {
                $_SESSION['mensaje_error'] = 'Datos incompletos o falta el archivo PDF.';
            }
            header('Location: index.php?c=convenios&m=gestionar&id=' . $convenio_id);
            exit;
        }
        header('Location: index.php?c=convenios');
        exit;
    }


    /**
     * Guarda una adenda de AMPLIACIÓN (extiende el período activo/futuro).
     * Incluye subida obligatoria de la adenda firmada.
     */
    public function ampliar() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $convenio_id = (int)$_POST['convenio_id'];
            $practicante_id = (int)$_POST['practicante_id']; // Necesario
            $archivo = $_FILES['documento_adenda'] ?? null;
            $nueva_fecha_fin = trim($_POST['nueva_fecha_fin']);
            $fecha_adenda = trim($_POST['fecha_adenda']);
            
            $datosAdenda = [
                'convenio_id' => $convenio_id,
                'tipo_accion' => 'AMPLIACION', // Fijo
                'fecha_adenda' => $fecha_adenda,
                'descripcion' => trim($_POST['descripcion']),
                'documento_adenda_url' => null // Se llenará después
            ];

            // Validaciones
            if (empty($nueva_fecha_fin) || empty($fecha_adenda)) {
                $_SESSION['mensaje_error'] = 'La Nueva Fecha Fin y la Fecha de Adenda son obligatorias.';
                header('Location: index.php?c=convenios&m=gestionar&id=' . $convenio_id);
                exit;
            }
             if (!$archivo || $archivo['error'] != UPLOAD_ERR_OK) {
                $_SESSION['mensaje_error'] = 'El Documento de Adenda (PDF) firmado es obligatorio.';
                header('Location: index.php?c=convenios&m=gestionar&id=' . $convenio_id);
                exit;
            }
            
            // 1. Mover el documento
            $url_relativa = $this->moverDocumento($archivo, $practicante_id, $convenio_id, 'ADENDA_AMPLIACION');
            if ($url_relativa === false) {
                 $_SESSION['mensaje_error'] = 'Error al guardar el documento de adenda. Verifique permisos.';
                 header('Location: index.php?c=convenios&m=gestionar&id=' . $convenio_id);
                 exit;
            }
            
            $datosAdenda['documento_adenda_url'] = $url_relativa;

            // 2. Ejecutar la transacción
            try {
                // El modelo actualiza el período activo O FUTURO y registra adenda
                $this->convenioModel->ampliarConvenio($datosAdenda, $nueva_fecha_fin);
                $_SESSION['mensaje_exito'] = 'Convenio ampliado exitosamente. Adenda registrada con documento.';
            } catch (Exception $e) {
                $_SESSION['mensaje_error'] = 'Error al procesar la ampliación: ' . $e->getMessage();
            }
            header('Location: index.php?c=convenios&m=gestionar&id=' . $convenio_id);
            exit;
        }
        header('Location: index.php?c=convenios');
        exit;
    }


    /**
     * Guarda un nuevo período (Reubicación o Corte/Suspensión).
     * Cierra el período anterior y registra la adenda con documento obligatorio.
     */
    public function guardarPeriodo() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $convenio_id = (int)$_POST['convenio_id'];
            $practicante_id = (int)$_POST['practicante_id']; // Necesario
            $archivo = $_FILES['documento_adenda'] ?? null;
            $tipo_accion = trim($_POST['tipo_accion']); // REUBICACION o CORTE

            // Validar tipo_accion
            if (!in_array($tipo_accion, ['REUBICACION', 'CORTE'])) {
                $_SESSION['mensaje_error'] = 'Tipo de acción inválido.';
                header('Location: index.php?c=convenios&m=gestionar&id=' . $convenio_id);
                exit;
            }

            $datosPeriodo = [
                'convenio_id' => $convenio_id,
                'fecha_inicio' => trim($_POST['fecha_inicio']),
                'fecha_fin' => trim($_POST['fecha_fin']),
                'local_id' => (int)$_POST['local_id'],
                'area_id' => (int)$_POST['area_id'],
                 // Determinar si el nuevo período es 'Activo' o 'Futuro'
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
            if (empty($datosPeriodo['fecha_inicio']) || empty($datosPeriodo['fecha_fin']) || empty($datosPeriodo['local_id']) || empty($datosPeriodo['area_id']) || empty($datosAdenda['fecha_adenda'])) {
                 $_SESSION['mensaje_error'] = 'Las fechas del nuevo período, local, área y fecha de adenda son obligatorios.';
                 header('Location: index.php?c=convenios&m=gestionar&id=' . $convenio_id);
                 exit;
            }
            if (!$archivo || $archivo['error'] != UPLOAD_ERR_OK) {
                $_SESSION['mensaje_error'] = 'El Documento de Adenda (PDF) firmado es obligatorio para esta acción.';
                header('Location: index.php?c=convenios&m=gestionar&id=' . $convenio_id);
                exit;
            }

            // 1. Mover el documento
            $nombre_doc = 'ADENDA_' . $tipo_accion;
            $url_relativa = $this->moverDocumento($archivo, $practicante_id, $convenio_id, $nombre_doc);
            if ($url_relativa === false) {
                 $_SESSION['mensaje_error'] = 'Error al guardar el documento de adenda. Verifique permisos.';
                 header('Location: index.php?c=convenios&m=gestionar&id=' . $convenio_id);
                 exit;
            }
            
            $datosAdenda['documento_adenda_url'] = $url_relativa;

            // 2. Ejecutar la transacción
            try {
                // El modelo cierra período anterior, inserta el nuevo Y registra la adenda.
                $this->convenioModel->agregarNuevoPeriodo($datosPeriodo, $datosAdenda);
                $_SESSION['mensaje_exito'] = 'Nuevo período registrado y adenda (' . $tipo_accion . ') guardada. El período anterior fue finalizado.';
            } catch (Exception $e) {
                $_SESSION['mensaje_error'] = 'Error al procesar el cambio de período: ' . $e->getMessage();
            }
            header('Location: index.php?c=convenios&m=gestionar&id=' . $convenio_id);
            exit;
        }
        header('Location: index.php?c=convenios');
        exit;
    }
    
    /**
     * Finaliza/Cancela un convenio (RENUNCIA o CANCELADO).
     * 'Renuncia' requiere documento PDF. 'Cancelado' no.
     * Registra la acción en la tabla Adendas.
     */
    public function finalizar() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $convenio_id = (int)($_POST['convenio_id'] ?? 0);
            $practicante_id = (int)($_POST['practicante_id'] ?? 0); // Necesario
            $nuevo_estado = trim($_POST['estado'] ?? ''); // 'Renuncia' o 'Cancelado'
            $archivo = $_FILES['documento_renuncia'] ?? null;
            $descripcion = trim($_POST['descripcion'] ?? '');

            if ($convenio_id === 0 || $practicante_id === 0 || !in_array($nuevo_estado, ['Renuncia', 'Cancelado'])) {
                 $_SESSION['mensaje_error'] = 'Datos inválidos para finalizar convenio.';
                 header('Location: index.php?c=convenios&m=gestionar&id=' . $convenio_id);
                 exit;
            }
            if (empty($descripcion)) {
                $_SESSION['mensaje_error'] = 'La descripción/motivo del cese es obligatoria.';
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
                if ($url_relativa === false) {
                     $_SESSION['mensaje_error'] = 'Error al guardar el documento de renuncia. Verifique permisos.';
                     header('Location: index.php?c=convenios&m=gestionar&id=' . $convenio_id);
                     exit;
                }
            }

            try {
                // El modelo actualiza Convenio, Practicante, Período y registra la Adenda.
                $this->convenioModel->finalizarConvenio($convenio_id, $practicante_id, $nuevo_estado, $descripcion, $url_relativa);
                 $_SESSION['mensaje_exito'] = "Convenio actualizado a '$nuevo_estado'. El practicante fue movido a 'Cesado'. Se registró la acción en el historial de adendas.";
            } catch (Exception $e) {
                $_SESSION['mensaje_error'] = 'Error al finalizar convenio: ' . $e->getMessage();
            }
            
            // Importante redirigir DESPUÉS del try-catch
            header('Location: index.php?c=convenios&m=gestionar&id=' . $convenio_id);
            exit;
        }
        // Si no es POST, simplemente redirigir a la gestión
        $convenio_id_get = (int)($_GET['convenio_id'] ?? 0);
         if ($convenio_id_get > 0) {
             header('Location: index.php?c=convenios&m=gestionar&id=' . $convenio_id_get);
         } else {
             header('Location: index.php?c=convenios');
         }
        exit;
    }

    /**
     * Función privada para mover archivos subidos.
     * Devuelve la URL relativa o false en caso de error.
     */
    private function moverDocumento($archivo, $practicante_id, $convenio_id, $tipo) {
        // Asegura que la ruta base sea correcta RELATIVA al index.php
        $ruta_base = 'uploads/documentos/'; 
        
        // Verifica si el directorio existe y tiene permisos, si no, intenta crearlo
        if (!is_dir($ruta_base)) {
            if (!mkdir($ruta_base, 0777, true)) {
                error_log("Error: No se pudo crear el directorio de subida: " . $ruta_base);
                return false; // No se pudo crear el directorio
            }
        } elseif (!is_writable($ruta_base)) {
             error_log("Error: El directorio de subida no tiene permisos de escritura: " . $ruta_base);
             return false; // El directorio no tiene permisos
        }

        // Limpia el tipo para usarlo en el nombre del archivo
        $tipo_limpio = preg_replace('/[^A-Za-z0-9_]/', '', $tipo);
        
        // Generar un nombre único (ej: 5_CONVENIO_FIRMADO_12_1678886400.pdf)
        $extension = pathinfo($archivo['name'], PATHINFO_EXTENSION);
        if (empty($extension)) $extension = 'pdf'; // Asumir pdf si no tiene extensión
        
        $nombre_archivo = $practicante_id . '_' . $tipo_limpio . '_' . $convenio_id . '_' . time() . '.' . $extension;
        $ruta_destino = $ruta_base . $nombre_archivo;
        
        if (move_uploaded_file($archivo['tmp_name'], $ruta_destino)) {
            return $ruta_base . $nombre_archivo; // Devuelve la URL relativa
        } else {
             error_log("Error: move_uploaded_file falló para " . $archivo['tmp_name'] . " a " . $ruta_destino);
             return false; // Error al mover el archivo
        }
    }
}