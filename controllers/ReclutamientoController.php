<?php
// controllers/ReclutamientoController.php

class ReclutamientoController extends Controller {
    
    private $reclutamientoModel;
    private $practicanteModel; // Necesario para guardar documentos

    public function __construct() {
        $this->reclutamientoModel = $this->model('ReclutamientoModel');
        $this->practicanteModel = $this->model('PracticanteModel'); // Modelo existente
    }

    /**
     * Página principal. Muestra TODOS los procesos
     */
    public function index() {
        // Se llama al nuevo método que trae todos los procesos
        $procesos = $this->reclutamientoModel->getTodosLosProcesos();
        
        $data = [
            'titulo' => 'Gestión de Reclutamiento',
            'procesos' => $procesos
        ];

        $this->view('reclutamiento/index', $data);
    }

    /**
     * Muestra el formulario para registrar un nuevo candidato.
     */
    public function nuevo() {
        // (Sin cambios, usa el método existente del modelo)
        $catalogos = $this->reclutamientoModel->getCatalogosParaFormulario();
        
        $data = [
            'titulo' => 'Registrar Nuevo Candidato',
            'universidades' => $catalogos['universidades'],
            'escuelas' => $catalogos['escuelas']
        ];
        
        $data['escuelas_json'] = json_encode($catalogos['escuelas']);

        $this->view('reclutamiento/nuevo', $data);
    }

    /**
     * Procesa el formulario de 'nuevo' candidato.
     * Validaciones y subida de archivos.
     */
    public function guardar() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            
            // 1. Recolectar y sanitizar datos (igual que antes)
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
            
            // 2. Validaciones (Server-Side) (igual que antes)
            $errores = [];
            if (!preg_match('/^[0-9]{8}$/', $datosPost['dni'])) {
                $errores[] = 'DNI debe tener 8 dígitos numéricos.';
            }
            // ... (resto de validaciones) ...
            if (!isset($_FILES['file_cv']) || $_FILES['file_cv']['error'] != UPLOAD_ERR_OK) {
                $errores[] = 'El archivo CV es obligatorio.';
            }
             if (!isset($_FILES['file_dni']) || $_FILES['file_dni']['error'] != UPLOAD_ERR_OK) {
                $errores[] = 'El archivo DNI es obligatorio.';
            }
            if (!empty($errores)) {
                $_SESSION['mensaje_error'] = 'Error de validación: ' . implode(' ', $errores);
                header('Location: index.php?c=reclutamiento&m=nuevo');
                exit;
            }

            // 3. Llamar al modelo para crear el practicante y el proceso
            try {
                $nuevoProceso = $this->reclutamientoModel->crearNuevoProceso($datosPost);
                $practicante_id = $nuevoProceso['practicante_id'];
                $proceso_id = $nuevoProceso['proceso_id'];

                // 4. Manejar subida de archivos
                $ruta_base = __DIR__ . '/../uploads/documentos/';
                if (!is_dir($ruta_base)) mkdir($ruta_base, 0777, true);

                $archivos = [
                    'CARTA_PRESENTACION' => $_FILES['file_carta'] ?? null, // 1ro
                    'DNI' => $_FILES['file_dni'],                        // 2do
                    'CV' => $_FILES['file_cv'],                           // 3ro
                    'DECLARACIONES' => $_FILES['file_ddjj'] ?? null       // 4to
                ];

                $rutas_locales_subidas = []; // Guardaremos las rutas locales para el merge

                foreach ($archivos as $tipo_documento => $archivo) {
                    if ($archivo && $archivo['error'] == UPLOAD_ERR_OK) {
                        
                        $nombre_archivo = $practicante_id . '_' . $tipo_documento . '_' . $proceso_id . '.pdf';
                        $ruta_destino = $ruta_base . $nombre_archivo;
                        
                        if (move_uploaded_file($archivo['tmp_name'], $ruta_destino)) {
                            $url_relativa = 'uploads/documentos/' . $nombre_archivo;
                            
                            // Guardar el documento individual en la BD (usando el método modificado)
                            $this->reclutamientoModel->addDocumento($practicante_id, $proceso_id, $tipo_documento, $url_relativa);
                            
                            // Guardar ruta local para el merge
                            $rutas_locales_subidas[$tipo_documento] = $ruta_destino;
                        }
                    }
                }
                
                // 5. ¡NUEVO! LÓGICA DE UNIÓN (MERGE) DE PDFs
                try {
                    $pdfConsolidado = new \setasign\Fpdi\Fpdi();
                    
                    // Iteramos sobre las rutas locales EN EL ORDEN CORRECTO
                    // El array $archivos ya está en el orden deseado
                    foreach (array_keys($archivos) as $tipo_documento) {
                        
                        // Verificamos si el archivo se subió (existe en $rutas_locales_subidas)
                        if (isset($rutas_locales_subidas[$tipo_documento])) {
                            $ruta_pdf = $rutas_locales_subidas[$tipo_documento];
                            
                            $pageCount = $pdfConsolidado->setSourceFile($ruta_pdf);
                            
                            for ($i = 1; $i <= $pageCount; $i++) {
                                $tpl = $pdfConsolidado->importPage($i);
                                $size = $pdfConsolidado->getTemplateSize($tpl);
                                // Añade una página con la orientación y tamaño del PDF original
                                $pdfConsolidado->AddPage($size['orientation'], [$size['width'], $size['height']]);
                                $pdfConsolidado->useTemplate($tpl);
                            }
                        }
                    }

                    // 6. Guardar el PDF consolidado
                    $nombre_consolidado = $practicante_id . '_CONSOLIDADO_' . $proceso_id . '.pdf';
                    $ruta_consolidado_local = $ruta_base . $nombre_consolidado;
                    $url_consolidado_relativa = 'uploads/documentos/' . $nombre_consolidado;

                    $pdfConsolidado->Output($ruta_consolidado_local, 'F');
                    
                    // 7. Guardar el CONSOLIDADO en la BD
                    $this->reclutamientoModel->addDocumento($practicante_id, $proceso_id, 'CONSOLIDADO', $url_consolidado_relativa);

                    $_SESSION['mensaje_exito'] = 'Candidato registrado. Documentos subidos y CONSOLIDADOS exitosamente.';

                } catch (Exception $e) {
                    // Si el merge falla, no es crítico, los archivos individuales ya están subidos.
                    // (Ej: un PDF está corrupto)
                    $_SESSION['mensaje_exito'] = 'Candidato registrado. Documentos individuales subidos (PERO falló la unión automática: ' . $e->getMessage() . ')';
                }

            } catch (Exception $e) {
                // Error al crear el practicante (Ej: DNI duplicado)
                $_SESSION['mensaje_error'] = 'Error al guardar: ' . $e->getMessage();
            }

            header('Location: index.php?c=reclutamiento');
            exit;

        } else {
            header('Location: index.php?c=reclutamiento');
            exit;
        }
    }

    /**
     * Muestra el formulario para calificar una entrevista.
     */
    public function evaluar() {
        // El router simple no pasa params, los tomamos de $_GET
        $proceso_id = (int)($_GET['id'] ?? 0);

        if ($proceso_id === 0) {
            header('Location: index.php?c=reclutamiento');
            exit;
        }

        // 1. Obtener datos del proceso (como antes)
        $proceso = $this->reclutamientoModel->getProcesoCompleto($proceso_id);

        if (!$proceso) {
            $_SESSION['mensaje_error'] = 'Proceso no encontrado.';
            header('Location: index.php?c=reclutamiento');
            exit;
        }

        // 2. ¡NUEVO! Obtener los documentos
        $documentos = $this->reclutamientoModel->getDocumentosPorProceso($proceso_id);
        
        $data = [
            'titulo' => 'Evaluar Entrevista',
            'proceso' => $proceso,
            'documentos' => $documentos // <-- Pasamos los documentos a la vista
        ];

        $this->view('reclutamiento/evaluar', $data);
    }

    /**
     * Guarda las notas de la entrevista (ResultadosEntrevista)
     */
    public function guardarEvaluacion() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            
            $proceso_id = (int)($_POST['proceso_id'] ?? 0);
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
                // Importante: Convertir string vacío a null
                $nota_val = $_POST[$nota_key];
                $datosEntrevista[$nota_key] = ($nota_val !== '' && $nota_val !== null) ? (float)$nota_val : null;

                if ($datosEntrevista[$nota_key] !== null && $datosEntrevista[$nota_key] >= 0) {
                    $suma_notas += $datosEntrevista[$nota_key];
                    $cantidad_notas++;
                }
            }
            
            // Calcular promedio (Esto ya funciona como pediste: promedia solo las habilitadas)
            $promedio = ($cantidad_notas > 0) ? ($suma_notas / $cantidad_notas) : 0;
            $datosEntrevista['puntuacion_final'] = round($promedio, 2);

            try {
                $this->reclutamientoModel->actualizarEntrevista($datosEntrevista);
                $_SESSION['mensaje_exito'] = 'Evaluación guardada y promedio actualizado.';
            } catch (Exception $e) {
                $_SESSION['mensaje_error'] = 'Error al guardar evaluación: ' . $e->getMessage();
            }

            header('Location: index.php?c=reclutamiento&m=evaluar&id=' . $proceso_id);
            exit;
        } else {
            header('Location: index.php?c=reclutamiento');
            exit;
        }
    }

    /**
     * Cambia el estado de un proceso (Aceptado, Rechazado, Pendiente).
     */
    public function actualizarEstado() {
        $proceso_id = (int)($_GET['id'] ?? 0);
        $nuevo_estado = trim($_GET['estado'] ?? '');

        // Validar que el estado sea uno de los permitidos
        $estados_validos = ['Aceptado', 'Rechazado', 'En Evaluación', 'Pendiente']; // Añadido 'Pendiente'
        
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

    /**
     * NUEVO: Sube la ficha de evaluación firmada.
     */
    public function subirFicha() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $proceso_id = (int)$_POST['proceso_id'];
            $practicante_id = (int)$_POST['practicante_id'];
            $archivo = $_FILES['ficha_firmada'] ?? null;

            if ($proceso_id > 0 && $practicante_id > 0 && $archivo && $archivo['error'] == UPLOAD_ERR_OK) {
                
                $ruta_base = __DIR__ . '/../uploads/documentos/';
                if (!is_dir($ruta_base)) mkdir($ruta_base, 0777, true);

                $nombre_archivo = $practicante_id . '_FICHA_EVALUACION_' . $proceso_id . '.pdf';
                $ruta_destino = $ruta_base . $nombre_archivo;
                
                if (move_uploaded_file($archivo['tmp_name'], $ruta_destino)) {
                    $url_relativa = 'uploads/documentos/' . $nombre_archivo;
                    try {
                        // Guardar en la BD
                        $this->reclutamientoModel->addDocumento($practicante_id, $proceso_id, 'FICHA_CALIFICACION', $url_relativa);
                        $_SESSION['mensaje_exito'] = 'Ficha firmada subida exitosamente.';
                    } catch (Exception $e) {
                        $_SESSION['mensaje_error'] = 'Error al guardar ficha: ' . $e->getMessage();
                    }
                } else {
                    $_SESSION['mensaje_error'] = 'Error al mover el archivo subido.';
                }
            } else {
                $_SESSION['mensaje_error'] = 'Datos incompletos o error en la subida.';
            }

            header('Location: index.php?c=reclutamiento&m=evaluar&id=' . $proceso_id);
            exit;
        }
        header('Location: index.php?c=reclutamiento');
        exit;
    }
}