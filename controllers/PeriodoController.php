<?php
// controllers/PeriodoController.php
require_once 'models/Periodo.php';
require_once 'models/Persona.php'; // Needed for employee list and update logic

class PeriodoController {
    private $db;
    private $periodoModel;
    private $personaModel;

    public function __construct() {
        if (!class_exists('Database')) require_once __DIR__ . '/../config/Database.php';
        $this->db = Database::getInstance()->getConnection();
        $this->periodoModel = new Periodo($this->db);
        $this->personaModel = new Persona($this->db);
    }

    // --- ACCIÓN INDEX (READ) ---
    public function index() {
        $periodo_filtro_anio = null; $listaAnios = []; $listaPeriodos = []; $errorMessage = null;
        try {
            $periodo_filtro_anio = filter_input(INPUT_GET, 'anio_inicio', FILTER_VALIDATE_INT);
            if ($periodo_filtro_anio === false || $periodo_filtro_anio < 1900 || $periodo_filtro_anio > 2100) $periodo_filtro_anio = null;
            $listaAnios = $this->periodoModel->getPeriodoAnios();
            $listaPeriodos = $this->periodoModel->listar($periodo_filtro_anio);
        } catch (Exception $e) { error_log("Error in PeriodoController::index - " . $e->getMessage()); $errorMessage = "Error al cargar los datos: " . $e->getMessage(); }
        require 'views/layout/header.php';
        require 'views/periodos/index.php';
        require 'views/layout/footer.php';
    }

    // --- ACTION: Manual Update for Next Periods ---
    public function actualizarProximos() {
        $updatedCount = 0; $errorCount = 0; $listaActivos = [];
        try {
            $listaActivos = $this->personaModel->listarActivosConIngreso();
            if ($listaActivos) {
                foreach ($listaActivos as $persona) {
                     if (isset($persona['id']) && isset($persona['fecha_ingreso'])) {
                         if ($this->periodoModel->verificarOCrearActualizarPeriodoSiguiente($persona['id'], $persona['fecha_ingreso'])) $updatedCount++;
                         else $errorCount++;
                     } else { error_log("Skipping period update..."); $errorCount++; }
                }
            }
        } catch (Exception $e) { error_log("Error during actualizarProximos: " . $e->getMessage()); $errorCount++; }
        $status = ($errorCount > 0) ? 'error_actualizando' : 'proximos_actualizados';
        $redirectUrl = 'index.php?controller=periodo&action=index&status=' . $status;
        if ($status === 'proximos_actualizados' || $updatedCount > 0) $redirectUrl .= '&count=' . $updatedCount;
        header('Location: ' . $redirectUrl); exit;
    }

    // --- API ACTION: Get Periods for a Person (JSON) ---
    public function getPeriodosPorPersona() {
        header('Content-Type: application/json'); // Set header first
        $persona_id = filter_input(INPUT_GET, 'persona_id', FILTER_VALIDATE_INT);

        if (!$persona_id) { echo json_encode(['error' => 'ID de persona inválido.']); exit; }

        $periodos_json = [];
        try {
             // Reusing structure from obtenerPorIdConSaldo for consistency, but simplified
             $subQuery = "(SELECT periodo_id, SUM(dias_tomados) as dias_reales FROM vacaciones WHERE estado IN ('APROBADO', 'GOZADO') GROUP BY periodo_id)";
             $sql = "SELECT per.id, per.periodo_inicio, per.periodo_fin, per.total_dias, COALESCE(v_calc.dias_reales, 0) AS dias_usados_calculados
                     FROM periodos AS per LEFT JOIN " . $subQuery . " AS v_calc ON per.id = v_calc.periodo_id
                     WHERE per.persona_id = ? ORDER BY per.periodo_inicio DESC";
             $stmt = $this->db->prepare($sql);
             $stmt->execute([$persona_id]);
             $results = $stmt->fetchAll();

             $hoy_dt = new DateTime(); $hoy_dt->setTime(0,0,0); // Define today

             foreach ($results as $row) {
                 try {
                     $periodo_inicio_dt = new DateTime($row['periodo_inicio']);
                     $start_year = $periodo_inicio_dt->format('Y');
                     $end_year = (int)$start_year + 1;
                     $saldo = (int)$row['total_dias'] - (int)$row['dias_usados_calculados'];
                     // Check if it's the current earning period based on start date vs today
                     $isCurrentEarning = ($hoy_dt >= $periodo_inicio_dt && $row['total_dias'] < 30);
                     $days_info = $isCurrentEarning ? "{$row['total_dias']} Devengados" : "{$row['total_dias']} Total";

                     $periodos_json[] = [
                         'id' => (int)$row['id'],
                         'text' => $start_year . ' - ' . $end_year . " (Saldo: {$saldo}, {$days_info})"
                     ];
                 } catch (Exception $e) { /* Skip row if date is invalid */ }
             }
            echo json_encode($periodos_json);

        } catch (PDOException $e) {
            error_log("API Error getPeriodosPorPersona for persona {$persona_id}: " . $e->getMessage());
            // Ensure valid JSON is sent even on error
            echo json_encode(['error' => 'Error BD: ' . $e->getMessage()]);
        } catch (Exception $e) {
            error_log("API General Error getPeriodosPorPersona for persona {$persona_id}: " . $e->getMessage());
            echo json_encode(['error' => 'Error General: ' . $e->getMessage()]);
        }
        exit; // Stop script
    }


    // --- CRUD Actions ---
    public function create() {
        $listaPersonas = [];
        try { $listaPersonas = $this->personaModel->listar(); } catch (Exception $e) { /* ... */ }
        require 'views/layout/header.php';
        require 'views/periodos/create.php';
        require 'views/layout/footer.php';
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (empty($_POST['persona_id']) || empty($_POST['periodo_inicio']) || empty($_POST['periodo_fin']) || !isset($_POST['total_dias'])) {
                 header('Location: index.php?controller=periodo&action=create&status=error_datos'); exit; }
            $datos = [ 'persona_id' => filter_input(INPUT_POST, 'persona_id', FILTER_VALIDATE_INT),
                       'periodo_inicio' => filter_input(INPUT_POST, 'periodo_inicio', FILTER_SANITIZE_STRING), // Expects YYYY or YYYY-MM-DD
                       'periodo_fin' => filter_input(INPUT_POST, 'periodo_fin', FILTER_SANITIZE_STRING),       // Expects YYYY or YYYY-MM-DD
                       'total_dias' => filter_input(INPUT_POST, 'total_dias', FILTER_VALIDATE_INT, ["options" => ["default" => 30]]) ];
            try { if ($this->periodoModel->crear($datos)) { header('Location: index.php?controller=periodo&action=index&status=creado'); exit; }
                  else { header('Location: index.php?controller=periodo&action=create&status=error_guardar'); exit; }
            } catch (Exception $e) { error_log("Error storing period: " . $e->getMessage()); header('Location: index.php?controller=periodo&action=create&status=error_excepcion'); exit; }
        } header('Location: index.php?controller=periodo&action=index'); exit;
    }

    public function edit() {
        $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        // REMOVED DEBUGGING LINES FROM HERE
        if ($id === false || $id === null || $id <= 0) die('Error: ID de período no válido.');
        $periodo = null; $listaPersonas = [];
        try { $periodo = $this->periodoModel->obtenerPorId($id); $listaPersonas = $this->personaModel->listar(); }
        catch (Exception $e) { error_log("Error fetching data for edit form (ID: {$id}): " . $e->getMessage()); die("Error al cargar datos para editar."); }
        if ($periodo === false || $periodo === null) die('Error: Período no encontrado.');
        require 'views/layout/header.php'; require 'views/periodos/edit.php'; require 'views/layout/footer.php';
    }

    public function update() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
            if (!$id || empty($_POST['persona_id']) || empty($_POST['periodo_inicio']) || empty($_POST['periodo_fin']) || !isset($_POST['total_dias'])) {
                header('Location: index.php?controller=periodo&action=edit&id=' . ($id ?? '') . '&status=error_datos'); exit; }
            $datos = [ 'persona_id' => filter_input(INPUT_POST, 'persona_id', FILTER_VALIDATE_INT),
                       'periodo_inicio' => filter_input(INPUT_POST, 'periodo_inicio', FILTER_SANITIZE_STRING),
                       'periodo_fin' => filter_input(INPUT_POST, 'periodo_fin', FILTER_SANITIZE_STRING),
                       'total_dias' => filter_input(INPUT_POST, 'total_dias', FILTER_VALIDATE_INT, ["options" => ["default" => 30]]) ];
            try { if ($this->periodoModel->actualizar($id, $datos)) { header('Location: index.php?controller=periodo&action=index&status=actualizado'); exit; }
                  else { header('Location: index.php?controller=periodo&action=edit&id=' . $id . '&status=error_guardar'); exit; }
            } catch (Exception $e) { error_log("Error updating period (ID: {$id}): " . $e->getMessage()); header('Location: index.php?controller=periodo&action=edit&id=' . $id . '&status=error_excepcion'); exit; }
        } header('Location: index.php?controller=periodo&action=index'); exit;
    }

    public function delete() {
        $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        if (!$id) { header('Location: index.php?controller=periodo&action=index&status=error_id'); exit; }
        try { if ($this->periodoModel->eliminar($id)) { header('Location: index.php?controller=periodo&action=index&status=eliminado'); exit; }
              else { header('Location: index.php?controller=periodo&action=index&status=error_eliminar'); exit; }
        } catch (Exception $e) { error_log("Error deleting period (ID: {$id}): " . $e->getMessage()); header('Location: index.php?controller=periodo&action=index&status=error_excepcion'); exit; }
    }
} // End Class