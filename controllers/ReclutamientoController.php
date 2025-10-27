<?php
// controllers/ReclutamientoController.php
// (Ya no se necesita el 'require_once' de Composer aquí, 
//  porque ahora está en index.php)

class ReclutamientoController extends Controller {
    
    private $reclutamientoModel;
    private $practicanteModel; // Necesario para guardar documentos

    public function __construct() {
        $this->reclutamientoModel = $this->model('ReclutamientoModel');
        // Asegúrate de que PracticanteModel exista en tu carpeta models/
        // (En tus archivos se llama PracticanteModel.php, así que está bien)
        $this->practicanteModel = $this->model('PracticanteModel');
    }

    /**
     * Página principal. Muestra TODOS los procesos
     */
    public function index() {
        // Obtenemos todos los procesos (activos, aceptados, etc.)
        $procesos = $this->reclutamientoModel->getTodosLosProcesos();
        // OBTENER IDs con ficha
        $procesosConFicha = $this->reclutamientoModel->getProcesosConFicha(); 
        
        $data = [
            'titulo' => 'Gestión de Reclutamiento',
            'procesos' => $procesos,
            'procesos_con_ficha' => $procesosConFicha // Pasar a la vista
        ];

        $this->view('reclutamiento/index', $data);
    }

    /**
     * Muestra el formulario para registrar un nuevo candidato.
     */
    public function nuevo() {
        // Carga catálogos para los dropdowns
        $catalogos = $this->reclutamientoModel->getCatalogosParaFormulario();
        
        $data = [
            'titulo' => 'Registrar Nuevo Candidato',
            'universidades' => $catalogos['universidades'],
            'escuelas' => $catalogos['escuelas']
        ];
        
        // Pasa las escuelas como JSON para el Javascript
        $data['escuelas_json'] = json_encode($catalogos['escuelas']);

        $this->view('reclutamiento/nuevo', $data);
    }

    /**
     * Procesa el formulario de 'nuevo' candidato.
     * Incluye validaciones, subida de archivos y unión de PDFs.
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
                'fecha_postulacion' => trim($_POST['fecha_postulacion']) ?? date('Y-m-d'),
                'tipo_practica' => trim($_POST['tipo_practica']) ?? '' // Nuevo campo
            ];
            
            // 2. Validaciones (Server-Side)
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
            
            // Validar archivos obligatorios
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
                // Se pasa $datosPost (que incluye 'tipo_practica')
                $nuevoProceso = $this->reclutamientoModel->crearNuevoProceso($datosPost);
                $practicante_id = $nuevoProceso['practicante_id'];
                $proceso_id = $nuevoProceso['proceso_id'];

                // 4. Manejar subida de archivos
                $ruta_base = __DIR__ . '/../uploads/documentos/';
                if (!is_dir($ruta_base)) mkdir($ruta_base, 0777, true);

                // Orden de unión: CARTA -> DNI -> CV -> DDJJ
                $archivos = [
                    'CARTA_PRESENTACION' => $_FILES['file_carta'] ?? null, // 1ro
                    'DNI' => $_FILES['file_dni'],                        // 2do
                    'CV' => $_FILES['file_cv'],                           // 3ro
                    'DECLARACIONES' => $_FILES['file_ddjj'] ?? null       // 4to
                ];

                $rutas_locales_subidas = []; // Guardaremos las rutas locales para el merge

                foreach ($archivos as $tipo_documento => $archivo) {
                    if ($archivo && $archivo['error'] == UPLOAD_ERR_OK) {
                        
                        // Crear un nombre de archivo único
                        $nombre_archivo = $practicante_id . '_' . $tipo_documento . '_' . $proceso_id . '.pdf';
                        $ruta_destino = $ruta_base . $nombre_archivo;
                        
                        if (move_uploaded_file($archivo['tmp_name'], $ruta_destino)) {
                            // Guardar la URL relativa en la BD
                            $url_relativa = 'uploads/documentos/' . $nombre_archivo;
                            
                            // Usamos el modelo de reclutamiento para añadir el doc
                            $this->reclutamientoModel->addDocumento($practicante_id, $proceso_id, $tipo_documento, $url_relativa);

                            // Guardar ruta local para el merge
                            $rutas_locales_subidas[$tipo_documento] = $ruta_destino;
                        }
                    }
                }
                
                // 5. LÓGICA DE UNIÓN (MERGE) DE PDFs
                try {
                    // Usamos la clase (ahora disponible globalmente gracias a index.php)
                    $pdfConsolidado = new \setasign\Fpdi\Fpdi();
                    
                    // Iteramos sobre las rutas locales EN EL ORDEN CORRECTO
                    foreach (array_keys($archivos) as $tipo_documento) {
                        
                        // Verificamos si el archivo se subió
                        if (isset($rutas_locales_subidas[$tipo_documento])) {
                            $ruta_pdf = $rutas_locales_subidas[$tipo_documento];
                            
                            $pageCount = $pdfConsolidado->setSourceFile($ruta_pdf);
                            
                            for ($i = 1; $i <= $pageCount; $i++) {
                                $tpl = $pdfConsolidado->importPage($i);
                                $size = $pdfConsolidado->getTemplateSize($tpl);
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

                // Usamos \Throwable para atrapar Errores Fatales (ej: PDF corrupto)
                } catch (\Throwable $e) { 
                    $_SESSION['mensaje_error'] = 'Error FATAL al unir PDFs: ' . $e->getMessage() . ' en la línea ' . $e->getLine();
                    
                    // Redirigimos para ver el error, no nos quedamos en blanco.
                    header('Location: index.php?c=reclutamiento');
                    exit;
                }

            // Catch del 'try' principal (Paso 3)
            } catch (Exception $e) {
                $_SESSION['mensaje_error'] = 'Error al guardar en BD: ' . $e->getMessage();
            }

            // Redirección final si todo fue bien
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
     * Busca los documentos del proceso.
     * (Lógica de inyección de promedio ELIMINADA - ahora la hace el JS)
     */
    public function evaluar() {
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

        // ¡NUEVO! Obtener los documentos
        $documentos = $this->reclutamientoModel->getDocumentosPorProceso($proceso_id);
        
        $data = [
            'titulo' => 'Evaluar Entrevista',
            'proceso' => $proceso, // Se pasa el proceso limpio
            'documentos' => $documentos
        ];

        $this->view('reclutamiento/evaluar', $data);
    }

    /**
     * Guarda las notas de la entrevista (ResultadosEntrevista)
     * y actualiza el puntaje final en ProcesosReclutamiento.
     * --- ¡ESTA FUNCIÓN AHORA MARCA EL PROCESO COMO 'Evaluado'! ---
     */
    public function guardarEvaluacion() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            
            $proceso_id = (int)($_POST['proceso_id'] ?? 0);
            
            // Recolectamos datos adicionales
            $datosEntrevista = [
                'proceso_id' => $proceso_id,
                'comentarios' => trim($_POST['comentarios_adicionales']) ?? '',
                'fecha_entrevista' => trim($_POST['fecha_entrevista']) ?? date('Y-m-d')
            ];

            $suma_ponderada = 0;
            $suma_pesos_total = 0;

            for ($i = 1; $i <= 10; $i++) {
                $nombre_key = 'campo_' . $i . '_nombre';
                $nota_key = 'campo_' . $i . '_nota';
                $peso_key = 'campo_' . $i . '_peso'; // <-- Obtenemos el peso

                $datosEntrevista[$nombre_key] = trim($_POST[$nombre_key]) ?? 'Criterio ' . $i;
                
                $nota_val = $_POST[$nota_key];
                $peso_val = $_POST[$peso_key];
                
                $datosEntrevista[$nota_key] = ($nota_val !== '' && $nota_val !== null) ? (float)$nota_val : null;
                $datosEntrevista[$peso_key] = ($peso_val !== '' && $peso_val !== null) ? (float)$peso_val : null;


                // Solo calculamos si tenemos una nota Y un peso
                if ($datosEntrevista[$nota_key] !== null && $datosEntrevista[$nota_key] >= 0 && $datosEntrevista[$peso_key] !== null && $datosEntrevista[$peso_key] > 0) {
                    
                    $suma_ponderada += $datosEntrevista[$nota_key] * $datosEntrevista[$peso_key];
                    $suma_pesos_total += $datosEntrevista[$peso_key];
                }
            }
            
            // Calcular promedio ponderado
            $promedio = ($suma_pesos_total > 0) ? ($suma_ponderada / $suma_pesos_total) : 0;
            $datosEntrevista['puntuacion_final'] = round($promedio, 2);

            // Llamar al modelo
            try {
                // 1. Usamos el método existente para actualizar las notas
                $this->reclutamientoModel->actualizarEntrevista($datosEntrevista);
                
                // 2. Usamos un nuevo método para guardar la fecha
                $this->reclutamientoModel->actualizarFechaEntrevista($proceso_id, $datosEntrevista['fecha_entrevista']);
                
                // 3. *** ¡CAMBIO CLAVE! ***
                // Al guardar la evaluación, marcamos el proceso como 'Evaluado'
                $this->reclutamientoModel->cambiarEstadoProceso($proceso_id, 'Evaluado');
                
                $_SESSION['mensaje_exito'] = 'Evaluación guardada y proceso marcado como "Evaluado".';
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
     * Cambia el estado de un proceso (Aceptado, Rechazado, Pendiente).
     */
    public function actualizarEstado() {
        $proceso_id = (int)($_GET['id'] ?? 0);
        $nuevo_estado = trim($_GET['estado'] ?? '');

        // Validar que el estado sea uno de los permitidos
        // *** 'Evaluado' NO está aquí, porque solo se accede a él mediante guardarEvaluacion ***
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

        header('Location: index.php?c=reclutamiento');
        exit;
    }

    /**
     * Muestra la revisión de una evaluación ya completada (solo lectura).
     */
    public function revisar() {
        $proceso_id = (int)($_GET['id'] ?? 0);

        if ($proceso_id === 0) {
            header('Location: index.php?c=reclutamiento');
            exit;
        }

        // Usamos la misma función que evaluar para obtener los datos
        $proceso = $this->reclutamientoModel->getProcesoCompleto($proceso_id); 

        if (!$proceso) {
            $_SESSION['mensaje_error'] = 'Proceso no encontrado.';
            header('Location: index.php?c=reclutamiento');
            exit;
        }

        // Obtener documentos (incluida la ficha)
        $documentos = $this->reclutamientoModel->getDocumentosPorProceso($proceso_id);

        $data = [
            'titulo' => 'Revisar Evaluación',
            'proceso' => $proceso,
            'documentos' => $documentos,
            'es_revision' => true // Indicador para la vista
        ];

        // Carga la vista 'revisar.php'
        $this->view('reclutamiento/revisar', $data); 
    }
    
    /**
     * Sube la ficha de evaluación firmada.
     * --- ¡ESTA FUNCIÓN AHORA SOLO SUBE EL ARCHIVO! ---
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
                
                // === ¡Importante! ===
                // Si move_uploaded_file falla, es casi seguro que es un problema de permisos
                // en la carpeta /uploads/documentos/ de tu servidor.
                if (move_uploaded_file($archivo['tmp_name'], $ruta_destino)) {
                    $url_relativa = 'uploads/documentos/' . $nombre_archivo;
                    try {
                        // 1. Guardar en la BD
                        $this->reclutamientoModel->addDocumento($practicante_id, $proceso_id, 'FICHA_CALIFICACION', $url_relativa);

                        // 2. *** ¡LÍNEA ELIMINADA! ***
                        // Ya no cambia el estado aquí.
                        // $this->reclutamientoModel->cambiarEstadoProceso($proceso_id, 'Evaluado'); 

                        $_SESSION['mensaje_exito'] = 'Ficha firmada subida exitosamente.';
                    } catch (Exception $e) {
                        $_SESSION['mensaje_error'] = 'Error al guardar la ficha en la BD: ' . $e->getMessage();
                    }
                } else {
                    $_SESSION['mensaje_error'] = 'Error crítico: No se pudo mover el archivo al servidor. Verifica los permisos de la carpeta "uploads/documentos".';
                }
            } else {
                $_SESSION['mensaje_error'] = 'Datos incompletos o error en la subida del archivo.';
            }

            // Redirige de vuelta a la misma página de evaluación
            header('Location: index.php?c=reclutamiento&m=evaluar&id=' . $proceso_id);
            exit;
        }
        header('Location: index.php?c=reclutamiento');
        exit;
    }
}