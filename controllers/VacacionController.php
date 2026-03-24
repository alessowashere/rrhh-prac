<?php
// controllers/VacacionController.php
require_once 'models/Vacacion.php';
require_once 'models/Periodo.php'; // Needed for period list in filter
require_once 'models/Persona.php';

class VacacionController {
    private $db;
    private $vacacionModel;
    private $periodoModel;
    private $personaModel;

    public function __construct() {
        if (!class_exists('Database')) require_once __DIR__ . '/../config/Database.php';
        $this->db = Database::getInstance()->getConnection();
        $this->vacacionModel = new Vacacion($this->db);
        $this->periodoModel = new Periodo($this->db);
        $this->personaModel = new Persona($this->db);
    }

    /**
     * --- NUEVA FUNCIÓN PRIVADA PARA MANEJAR SUBIDA DE ARCHIVOS ---
     * Procesa la subida de un archivo desde $_FILES.
     *
     * @param array $fileData El array $_FILES['nombre_del_campo']
     * @param string $existingFilePath Path del archivo existente (si se está actualizando)
     * @return string|null|false Devuelve el path del nuevo archivo, null si no se subió nada, o false si hubo un error.
     */
    private function _handleFileUpload($fileData, $existingFilePath = null) {
        // 1. Verificar si se subió un archivo
        if (!isset($fileData) || $fileData['error'] == UPLOAD_ERR_NO_FILE) {
            // No se subió archivo nuevo. 
            // Si había uno existente, lo mantenemos. Si no, queda null.
            return $existingFilePath; 
        }

        // 2. Verificar errores de subida
        if ($fileData['error'] !== UPLOAD_ERR_OK) {
            error_log("Error de subida de archivo: " . $fileData['error']);
            return false; // Indicar error
        }

        // 3. Definir y crear el directorio de destino
        // __DIR__ está en /controllers, así que retrocedemos uno
        $targetDir = __DIR__ . '/../uploads/vacaciones/';
        if (!is_dir($targetDir)) {
            if (!mkdir($targetDir, 0755, true)) {
                error_log("Error: No se pudo crear el directorio de subida: " . $targetDir);
                return false; // Indicar error
            }
        }
        if (!is_writable($targetDir)) {
             error_log("Error: El directorio de subida no tiene permisos de escritura: " . $targetDir);
             return false;
        }

        // 4. Crear un nombre de archivo único
        $fileName = uniqid() . '-' . basename($fileData['name']);
        $targetPath = $targetDir . $fileName;

        // 5. Mover el archivo
        if (move_uploaded_file($fileData['tmp_name'], $targetPath)) {
            // 6. Si se movió con éxito, borrar el archivo antiguo (si existía)
            if ($existingFilePath && file_exists($existingFilePath)) {
                 @unlink($existingFilePath);
            }
            // 7. Devolver la ruta relativa para guardar en la BD
            // (Ajusta esto si tu BASE_URL es necesaria)
            return 'uploads/vacaciones/' . $fileName; 
        } else {
            error_log("Error: No se pudo mover el archivo subido a " . $targetPath);
            return false; // Indicar error
        }
    }


    // --- ACCIÓN INDEX (Sin cambios) ---
    public function index() {
        // ... (código existente) ...
        $search_nombre = null; $search_area = null; $anio_inicio_filtro = null;
        $listaAnios = []; $listaVacaciones = []; $errorMessage = null;

        try {
            $search_nombre = strip_tags(filter_input(INPUT_GET, 'search_nombre') ?? '');
            $search_area = strip_tags(filter_input(INPUT_GET, 'search_area') ?? '');
            
            $anio_inicio_filtro = filter_input(INPUT_GET, 'anio_inicio', FILTER_VALIDATE_INT);
             if ($anio_inicio_filtro === false || $anio_inicio_filtro < 1900 || $anio_inicio_filtro > 2100) $anio_inicio_filtro = null;

            $listaAnios = $this->periodoModel->getPeriodoAnios();
            $listaVacaciones = $this->vacacionModel->listar(
                $search_nombre,
                $search_area,
                $anio_inicio_filtro
            );

        } catch (Exception $e) {
             error_log("Error in VacacionController::index - " . $e->getMessage());
             $errorMessage = "Error al cargar datos de vacaciones: " . $e.getMessage();
        }
        require 'views/layout/header.php';
        require 'views/vacaciones/index.php'; 
        require 'views/layout/footer.php';
    }


    // --- ACCIÓN CREATE (Sin cambios) ---
    public function create() {
        // ... (código existente) ...
        $listaPersonas = [];
        try {
            $listaPersonas = $this->personaModel->listar();
        } catch (Exception $e) { error_log("Error fetching personas for Vacacion create form: " . $e->getMessage()); }

        $esModal = isset($_GET['view']) && $_GET['view'] === 'modal';

        if ($esModal) {
            require 'views/layout/modal_header.php';
            require 'views/vacaciones/create.php'; 
            require 'views/layout/modal_footer.php';
        } else {
            require 'views/layout/header.php';
            require 'views/vacaciones/create.php'; 
            require 'views/layout/footer.php';
        }
    }

    // --- ACCIÓN STORE (Modificada) ---
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (empty($_POST['persona_id']) || empty($_POST['periodo_id']) || empty($_POST['fecha_inicio']) || empty($_POST['fecha_fin']) || !isset($_POST['dias_tomados'])) {
                 header('Location: index.php?controller=vacacion&action=create&status=error_datos'); exit; }

            // --- INICIO CAMBIO: Procesar subida de archivo ---
            $documento_path = $this->_handleFileUpload($_FILES['documento'] ?? null);
            if ($documento_path === false) {
                 header('Location: index.php?controller=vacacion&action=create&status=error_upload'); exit;
            }
            // --- FIN CAMBIO ---

            $persona_id = filter_input(INPUT_POST, 'persona_id', FILTER_VALIDATE_INT);
            $periodo_id = filter_input(INPUT_POST, 'periodo_id', FILTER_VALIDATE_INT);
            
            $fecha_inicio = strip_tags(filter_input(INPUT_POST, 'fecha_inicio') ?? '');
            $fecha_fin = strip_tags(filter_input(INPUT_POST, 'fecha_fin') ?? '');
            $dias_tomados = filter_input(INPUT_POST, 'dias_tomados', FILTER_VALIDATE_INT);
            $tipo = strip_tags(filter_input(INPUT_POST, 'tipo') ?? 'NORMAL');
            $estado = strip_tags(filter_input(INPUT_POST, 'estado') ?? 'PENDIENTE');

            // --- (Validación de Saldo - sin cambios) ---
            $saldo_disponible = -999;
            if ($periodo_id) {
                try {
                     $periodo_data_raw = $this->periodoModel->obtenerPorIdConSaldo($periodo_id);
                     if ($periodo_data_raw) {
                          $total_dias_periodo = $periodo_data_raw['total_dias'] ?? 0;
                          $dias_usados_periodo = $periodo_data_raw['dias_usados_calculados'] ?? 0;
                          $isCurrentEarning = (new DateTime() >= new DateTime($periodo_data_raw['periodo_inicio']) && $total_dias_periodo < 30);
                          $saldo_disponible = $total_dias_periodo - $dias_usados_periodo;
                     } else { $saldo_disponible = -998; }
                } catch (Exception $e) { error_log("Error fetching saldo for periodo {$periodo_id}: " . $e->getMessage()); $saldo_disponible = -997; }
            }
            if ($dias_tomados === false || $dias_tomados <= 0) {
                 header('Location: index.php?controller=vacacion&action=create&status=error_dias_invalidos'); exit;
            }
            if ($tipo != 'ADELANTO' && $dias_tomados > $saldo_disponible) {
                 $saldo_info = ($saldo_disponible >= -30) ? "&saldo={$saldo_disponible}" : "";
                 header('Location: index.php?controller=vacacion&action=create&status=error_saldo' . $saldo_info . "&req={$dias_tomados}"); exit;
            }
            // --- END Balance Validation ---

            // --- CAMBIO: Añadir $documento_path a $datos ---
            $datos = ['persona_id' => $persona_id, 'periodo_id' => $periodo_id, 'fecha_inicio' => $fecha_inicio,
                      'fecha_fin' => $fecha_fin, 'dias_tomados' => $dias_tomados, 'tipo' => $tipo, 'estado' => $estado,
                      'documento_adjunto' => $documento_path];

            try { if ($this->vacacionModel->crear($datos)) { header('Location: index.php?controller=vacacion&action=index&status=creado'); exit; }
                  else { header('Location: index.php?controller=vacacion&action=create&status=error_guardar'); exit; }
            } catch (Exception $e) { error_log("Error storing vacacion: " . $e->getMessage()); header('Location: index.php?controller=vacacion&action=create&status=error_excepcion'); exit; }
        }
        header('Location: index.php?controller=vacacion&action=index'); exit;
    }


    // --- ACCIÓN EDIT (Modificada para obtener doc) ---
    public function edit() {
        $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        if (!$id) die('ID de vacación no válido.');
        $vacacion = null; $listaPersonas = [];
        try { 
            // --- CAMBIO: obtenerPorId ahora trae el doc ---
            $vacacion = $this->vacacionModel->obtenerPorId($id); 
            $listaPersonas = $this->personaModel->listar(); 
        }
        catch (Exception $e) { error_log("Error fetching data for Vacacion edit form (ID: {$id}): " . $e->getMessage()); die("Error al cargar datos."); }
        if (!$vacacion) die('Vacación no encontrada.');

        $esModal = isset($_GET['view']) && $_GET['view'] === 'modal';

        if ($esModal) {
            require 'views/layout/modal_header.php'; 
            require 'views/vacaciones/edit.php'; 
            require 'views/layout/modal_footer.php';
        } else {
            require 'views/layout/header.php'; 
            require 'views/vacaciones/edit.php'; 
            require 'views/layout/footer.php';
        }
    }

    // --- ACCIÓN UPDATE (Modificada) ---
    public function update() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
            if (!$id || empty($_POST['persona_id']) || empty($_POST['periodo_id']) || empty($_POST['fecha_inicio']) || empty($_POST['fecha_fin']) || !isset($_POST['dias_tomados'])) {
                 header('Location: index.php?controller=vacacion&action=edit&id=' . ($id ?? '') . '&status=error_datos'); exit; }

            // --- INICIO CAMBIO: Procesar subida de archivo ---
            // 1. Obtener el path del documento antiguo
            $vacacion_actual = $this->vacacionModel->obtenerPorId($id);
            $doc_antiguo = $vacacion_actual['documento_adjunto'] ?? null;

            // 2. Procesar el nuevo archivo (la función se encargará de borrar el antiguo si se sube uno nuevo)
            $documento_path = $this->_handleFileUpload($_FILES['documento'] ?? null, $doc_antiguo);
            if ($documento_path === false) {
                 header('Location: index.php?controller=vacacion&action=edit&id=' . $id . '&status=error_upload'); exit;
            }
            // --- FIN CAMBIO ---

            $persona_id = filter_input(INPUT_POST, 'persona_id', FILTER_VALIDATE_INT);
            $periodo_id = filter_input(INPUT_POST, 'periodo_id', FILTER_VALIDATE_INT);
            
            $fecha_inicio = strip_tags(filter_input(INPUT_POST, 'fecha_inicio') ?? '');
            $fecha_fin = strip_tags(filter_input(INPUT_POST, 'fecha_fin') ?? '');
            $dias_tomados = filter_input(INPUT_POST, 'dias_tomados', FILTER_VALIDATE_INT);
            $tipo = strip_tags(filter_input(INPUT_POST, 'tipo') ?? 'NORMAL');
            $estado = strip_tags(filter_input(INPUT_POST, 'estado') ?? 'PENDIENTE');

            // --- (Validación de Saldo - sin cambios) ---
            $saldo_disponible = -999; $dias_actuales_registro = 0;
            if ($vacacion_actual) $dias_actuales_registro = $vacacion_actual['dias_tomados'];
            if ($periodo_id) { try { $periodo_data_raw = $this->periodoModel->obtenerPorIdConSaldo($periodo_id);
                     if ($periodo_data_raw) {
                          $total_dias_periodo = $periodo_data_raw['total_dias'] ?? 0;
                          $dias_usados_periodo = $periodo_data_raw['dias_usados_calculados'] ?? 0;
                          $saldo_periodo = $total_dias_periodo - $dias_usados_periodo;
                          $saldo_disponible = $saldo_periodo + $dias_actuales_registro;
                     } else { $saldo_disponible = -998; }
                } catch (Exception $e) { error_log("Error fetching saldo for edit (periodo {$periodo_id}): " . $e->getMessage()); $saldo_disponible = -997; } }
            if ($dias_tomados === false || $dias_tomados <= 0) {
                 header('Location: index.php?controller=vacacion&action=edit&id=' . $id . '&status=error_dias_invalidos'); exit;
            }
            if ($tipo != 'ADELANTO' && $dias_tomados > $saldo_disponible) {
                 $saldo_info = ($saldo_disponible >= -30) ? "&saldo={$saldo_disponible}" : "";
                 header('Location: index.php?controller=vacacion&action=edit&id=' . $id . '&status=error_saldo' . $saldo_info . "&req={$dias_tomados}"); exit;
            }
            // --- END Balance Validation ---

            // --- CAMBIO: Añadir $documento_path a $datos ---
            $datos = ['persona_id' => $persona_id, 'periodo_id' => $periodo_id, 'fecha_inicio' => $fecha_inicio,
                      'fecha_fin' => $fecha_fin, 'dias_tomados' => $dias_tomados, 'tipo' => $tipo, 'estado' => $estado,
                      'documento_adjunto' => $documento_path];

            try { if ($this->vacacionModel->actualizar($id, $datos)) { header('Location: index.php?controller=vacacion&action=index&status=actualizado'); exit; }
                  else { header('Location: index.php?controller=vacacion&action=edit&id=' . $id . '&status=error_guardar'); exit; }
            } catch (Exception $e) { error_log("Error updating vacacion (ID: {$id}): " . $e->getMessage()); header('Location: index.php?controller=vacacion&action=edit&id=' . $id . '&status=error_excepcion'); exit; }
        }
        header('Location: index.php?controller=vacacion&action=index'); exit;
    }


    // --- ACCIÓN DELETE (Sin cambios) ---
    // (La lógica de borrar el archivo se movió al Modelo)
    public function delete() {
         $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        if (!$id) { header('Location: index.php?controller=vacacion&action=index&status=error_id'); exit; }
        try { if ($this->vacacionModel->eliminar($id)) { header('Location: index.php?controller=vacacion&action=index&status=eliminado'); exit; }
              else { header('Location: index.php?controller=vacacion&action=index&status=error_eliminar'); exit; }
        } catch (Exception $e) { error_log("Error deleting vacacion (ID: {$id}): " . $e->getMessage()); header('Location: index.php?controller=vacacion&action=index&status=error_excepcion'); exit; }
    }
    
    // --- ACCIÓN APROBAR (Sin cambios) ---
    public function aprobar() {
         $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        if (!$id) { header('Location: index.php?controller=vacacion&action=index&status=error_id'); exit; }
        try { 
            if ($this->vacacionModel->actualizarEstado($id, 'APROBADO')) {
                 header('Location: index.php?controller=vacacion&action=index&status=aprobado'); exit;
            } else {
                 header('Location: index.php?controller=vacacion&action=index&status=error_estado'); exit; 
            }
        } catch (Exception $e) {
             error_log("Error aprobando vacacion (ID: {$id}): " . $e->getMessage());
             header('Location: index.php?controller=vacacion&action=index&status=error_excepcion'); exit;
        }
    }

    // --- ACCIÓN RECHAZAR (Sin cambios) ---
    public function rechazar() {
         $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        if (!$id) { header('Location: index.php?controller=vacacion&action=index&status=error_id'); exit; }
        try { 
            if ($this->vacacionModel->actualizarEstado($id, 'RECHAZADO')) {
                 header('Location: index.php?controller=vacacion&action=index&status=rechazado'); exit;
            } else {
                 header('Location: index.php?controller=vacacion&action=index&status=error_estado'); exit; 
            }
        } catch (Exception $e) {
             error_log("Error rechazando vacacion (ID: {$id}): " . $e->getMessage());
             header('Location: index.php?controller=vacacion&action=index&status=error_excepcion'); exit;
        }
    }

    // --- ACCIÓN INDEXMODAL (Sin cambios) ---
    public function indexModal() {
        // ... (código existente) ...
        $search_nombre = null; $search_area = null; $anio_inicio_filtro = null;
        $listaAnios = []; $listaVacaciones = []; $errorMessage = null;
        try {
            $search_nombre = strip_tags(filter_input(INPUT_GET, 'search_nombre') ?? '');
            $search_area = strip_tags(filter_input(INPUT_GET, 'search_area') ?? '');
            $anio_inicio_filtro = filter_input(INPUT_GET, 'anio_inicio', FILTER_VALIDATE_INT);
             if ($anio_inicio_filtro === false || $anio_inicio_filtro < 1900 || $anio_inicio_filtro > 2100) $anio_inicio_filtro = null;
            $listaAnios = $this->periodoModel->getPeriodoAnios();
            $listaVacaciones = $this->vacacionModel->listar(
                $search_nombre,
                $search_area,
                $anio_inicio_filtro
            );
        } catch (Exception $e) {
             error_log("Error in VacacionController::indexModal - " . $e->getMessage());
             $errorMessage = "Error al cargar datos: " . $e->getMessage();
        }
        require 'views/layout/modal_header.php';
        require 'views/vacaciones/index.php';
        require 'views/layout/modal_footer.php';
    }
    
    // --- ACCIÓN STOREMODAL (Modificada) ---
    public function storeModal() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (empty($_POST['persona_id']) || empty($_POST['periodo_id']) || empty($_POST['fecha_inicio']) || empty($_POST['fecha_fin']) || !isset($_POST['dias_tomados'])) {
                 header('Location: index.php?controller=vacacion&action=create&status=error_datos&view=modal'); exit; }

            // --- INICIO CAMBIO: Procesar subida de archivo ---
            $documento_path = $this->_handleFileUpload($_FILES['documento'] ?? null);
            if ($documento_path === false) {
                 header('Location: index.php?controller=vacacion&action=create&status=error_upload&view=modal'); exit;
            }
            // --- FIN CAMBIO ---

            $persona_id = filter_input(INPUT_POST, 'persona_id', FILTER_VALIDATE_INT);
            $periodo_id = filter_input(INPUT_POST, 'periodo_id', FILTER_VALIDATE_INT);
            $fecha_inicio = strip_tags(filter_input(INPUT_POST, 'fecha_inicio') ?? '');
            $fecha_fin = strip_tags(filter_input(INPUT_POST, 'fecha_fin') ?? '');
            $dias_tomados = filter_input(INPUT_POST, 'dias_tomados', FILTER_VALIDATE_INT);
            $tipo = strip_tags(filter_input(INPUT_POST, 'tipo') ?? 'NORMAL');
            $estado = strip_tags(filter_input(INPUT_POST, 'estado') ?? 'PENDIENTE');

            // ... (Validación de Saldo - sin cambios) ...
            $saldo_disponible = -999;
            if ($periodo_id) {
                try {
                     $periodo_data_raw = $this->periodoModel->obtenerPorIdConSaldo($periodo_id);
                     if ($periodo_data_raw) {
                          $total_dias_periodo = $periodo_data_raw['total_dias'] ?? 0;
                          $dias_usados_periodo = $periodo_data_raw['dias_usados_calculados'] ?? 0;
                          $isCurrentEarning = (new DateTime() >= new DateTime($periodo_data_raw['periodo_inicio']) && $total_dias_periodo < 30);
                          $saldo_disponible = $total_dias_periodo - $dias_usados_periodo;
                     } else { $saldo_disponible = -998; }
                } catch (Exception $e) { $saldo_disponible = -997; }
            }
            if ($dias_tomados === false || $dias_tomados <= 0) {
                 header('Location: index.php?controller=vacacion&action=create&status=error_dias_invalidos&view=modal'); exit;
            }
            if ($tipo != 'ADELANTO' && $dias_tomados > $saldo_disponible) {
                 $saldo_info = ($saldo_disponible >= -30) ? "&saldo={$saldo_disponible}" : "";
                 header('Location: index.php?controller=vacacion&action=create&status=error_saldo' . $saldo_info . "&req={$dias_tomados}&view=modal"); exit;
            }
            // --- Fin Validación Saldo ---

            // --- CAMBIO: Añadir $documento_path a $datos ---
            $datos = ['persona_id' => $persona_id, 'periodo_id' => $periodo_id, 'fecha_inicio' => $fecha_inicio,
                      'fecha_fin' => $fecha_fin, 'dias_tomados' => $dias_tomados, 'tipo' => $tipo, 'estado' => $estado,
                      'documento_adjunto' => $documento_path];

            try { 
                if ($this->vacacionModel->crear($datos)) { 
                    $persona = $this->personaModel->obtenerPorId($persona_id);
                    $periodo = $this->periodoModel->obtenerPorId($periodo_id);
                    $filtro_nombre = urlencode($persona['nombre_completo'] ?? '');
                    $filtro_anio = $periodo ? date('Y', strtotime($periodo['periodo_inicio'])) : '';
                    header("Location: index.php?controller=vacacion&action=indexModal&status=creado&search_nombre={$filtro_nombre}&anio_inicio={$filtro_anio}&persona_id_filtro={$persona_id}");
                    exit;
                } else { 
                    header('Location: index.php?controller=vacacion&action=create&status=error_guardar&view=modal'); exit; 
                }
            } catch (Exception $e) { header('Location: index.php?controller=vacacion&action=create&status=error_excepcion&view=modal'); exit; }
        }
    }

    // --- ACCIÓN UPDATEMODAL (Modificada) ---
    public function updateModal() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
            if (!$id || empty($_POST['persona_id']) || empty($_POST['periodo_id']) || empty($_POST['fecha_inicio']) || empty($_POST['fecha_fin']) || !isset($_POST['dias_tomados'])) {
                 header('Location: index.php?controller=vacacion&action=edit&id=' . ($id ?? '') . '&status=error_datos&view=modal'); exit; }

            // --- INICIO CAMBIO: Procesar subida de archivo ---
            $vacacion_actual = $this->vacacionModel->obtenerPorId($id);
            $doc_antiguo = $vacacion_actual['documento_adjunto'] ?? null;
            $documento_path = $this->_handleFileUpload($_FILES['documento'] ?? null, $doc_antiguo);
            if ($documento_path === false) {
                 header('Location: index.php?controller=vacacion&action=edit&id=' . $id . '&status=error_upload&view=modal'); exit;
            }
            // --- FIN CAMBIO ---

            $persona_id = filter_input(INPUT_POST, 'persona_id', FILTER_VALIDATE_INT);
            $periodo_id = filter_input(INPUT_POST, 'periodo_id', FILTER_VALIDATE_INT);
            $fecha_inicio = strip_tags(filter_input(INPUT_POST, 'fecha_inicio') ?? '');
            $fecha_fin = strip_tags(filter_input(INPUT_POST, 'fecha_fin') ?? '');
            $dias_tomados = filter_input(INPUT_POST, 'dias_tomados', FILTER_VALIDATE_INT);
            $tipo = strip_tags(filter_input(INPUT_POST, 'tipo') ?? 'NORMAL');
            $estado = strip_tags(filter_input(INPUT_POST, 'estado') ?? 'PENDIENTE');

            // ... (Validación de Saldo - sin cambios) ...
            $saldo_disponible = -999; $dias_actuales_registro = 0;
            if ($vacacion_actual) $dias_actuales_registro = $vacacion_actual['dias_tomados'];
            if ($periodo_id) { try { $periodo_data_raw = $this->periodoModel->obtenerPorIdConSaldo($periodo_id);
                     if ($periodo_data_raw) {
                          $total_dias_periodo = $periodo_data_raw['total_dias'] ?? 0;
                          $dias_usados_periodo = $periodo_data_raw['dias_usados_calculados'] ?? 0;
                          $saldo_periodo = $total_dias_periodo - $dias_usados_periodo;
                          $saldo_disponible = $saldo_periodo + $dias_actuales_registro;
                     } else { $saldo_disponible = -998; }
                } catch (Exception $e) { $saldo_disponible = -997; } }
            if ($dias_tomados === false || $dias_tomados <= 0) {
                 header('Location: index.php?controller=vacacion&action=edit&id=' . $id . '&status=error_dias_invalidos&view=modal'); exit;
            }
            if ($tipo != 'ADELANTO' && $dias_tomados > $saldo_disponible) {
                 $saldo_info = ($saldo_disponible >= -30) ? "&saldo={$saldo_disponible}" : "";
                 header('Location: index.php?controller=vacacion&action=edit&id=' . $id . '&status=error_saldo' . $saldo_info . "&req={$dias_tomados}&view=modal"); exit;
            }
            // --- Fin Validación Saldo ---

            // --- CAMBIO: Añadir $documento_path a $datos ---
            $datos = ['persona_id' => $persona_id, 'periodo_id' => $periodo_id, 'fecha_inicio' => $fecha_inicio,
                      'fecha_fin' => $fecha_fin, 'dias_tomados' => $dias_tomados, 'tipo' => $tipo, 'estado' => $estado,
                      'documento_adjunto' => $documento_path];

            try { 
                if ($this->vacacionModel->actualizar($id, $datos)) { 
                    $persona = $this->personaModel->obtenerPorId($persona_id);
                    $periodo = $this->periodoModel->obtenerPorId($periodo_id);
                    $filtro_nombre = urlencode($persona['nombre_completo'] ?? '');
                    $filtro_anio = $periodo ? date('Y', strtotime($periodo['periodo_inicio'])) : '';
                    header("Location: index.php?controller=vacacion&action=indexModal&status=actualizado&search_nombre={$filtro_nombre}&anio_inicio={$filtro_anio}&persona_id_filtro={$persona_id}");
                    exit;
                }
                  else { header('Location: index.php?controller=vacacion&action=edit&id=' . $id . '&status=error_guardar&view=modal'); exit; }
            } catch (Exception $e) { header('Location: index.php?controller=vacacion&action=edit&id=' . $id . '&status=error_excepcion&view=modal'); exit; }
        }
    }
} // --- Fin de la Clase ---
?>