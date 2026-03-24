<?php
// controllers/ImportarController.php

// --- ¡ASEGURAR VISIBILIDAD DE ERRORES AL INICIO! ---
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// --------------------------------------------------

require_once 'models/Persona.php';
require_once 'models/Periodo.php';
require_once 'models/Vacacion.php';

class ImportarController {

    private $db;

    public function __construct() {
        if (!class_exists('Database')) require_once __DIR__ . '/../config/Database.php';
        $this->db = Database::getInstance()->getConnection();
        if (session_status() == PHP_SESSION_NONE) { session_start(); }
    }

    public function index() {
        unset($_SESSION['import_preview_data']); unset($_SESSION['import_mode']); unset($_SESSION['import_errors']);
        require 'views/layout/header.php'; require 'views/importar/index.php'; require 'views/layout/footer.php';
    }

    public function previsualizar() {
        // ... (Validaciones iniciales - Sin cambios) ...
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_FILES['archivo_csv']) || $_FILES['archivo_csv']['error'] !== UPLOAD_ERR_OK) {
             $this->redirigirConError('index', 'Error al subir archivo. Código: ' . ($_FILES['archivo_csv']['error'] ?? 'N/A')); return; }
         $csvFilePath = $_FILES['archivo_csv']['tmp_name']; $fileMimeType = mime_content_type($csvFilePath);
         $allowedMimeTypes = ['text/plain', 'text/csv', 'application/csv'];
         if (!in_array($fileMimeType, $allowedMimeTypes)) { $this->redirigirConError('index', "Error: Archivo no CSV. (Tipo: $fileMimeType)"); return; }
         $file = fopen($csvFilePath, 'r'); if ($file === false) { $this->redirigirConError('index', 'Error al abrir archivo.'); return; }

        $import_mode = $_POST['import_mode'] ?? 'reemplazar'; $_SESSION['import_mode'] = $import_mode;
        $data_preview = []; $contadorPersonas = 0; $contadorVacaciones = 0; $errores_preview = [];

        try {
            fgetcsv($file, 1000, ";"); fgetcsv($file, 1000, ";"); fgetcsv($file, 1000, ";");
            $csv_headers = fgetcsv($file, 1000, ";"); $fila_actual = 4;

            while (($row = fgetcsv($file, 1000, ";")) !== FALSE) {
                $fila_actual++;
                 if (count($row) < 8 || empty($row[3]) || $row[0] === null) {
                     if(!(count($row) === 1 && $row[0] === null)) { $errores_preview[] = "Fila {$fila_actual}: Fila incompleta/vacía omitida."; } continue; }

                $persona_id_raw = $this->clean_value_php($row[0], true);
                 if ($persona_id_raw === null || (int)$persona_id_raw <= 0) { $errores_preview[] = "Fila {$fila_actual}: ID inválido ('{$row[0]}'). Omitida."; continue; }
                 $persona_id = (int)$persona_id_raw;

                $nombre = $this->clean_value_php($row[3]); $fecha_ingreso_raw = $this->clean_value_php($row[2]);
                $ingreso_dt = $this->parse_final_attempt_date_php($fecha_ingreso_raw);
                if ($ingreso_dt === null) { $errores_preview[] = "Emp. '{$nombre}' (ID: {$persona_id}, Fila: {$fila_actual}): Fecha ingreso inválida ('{$fecha_ingreso_raw}'). Omitido."; continue; }

                $persona_data = ['id' => $persona_id, 'dni' => $this->clean_value_php($row[1], true), 'nombre_completo' => $nombre, 'cargo' => $this->clean_value_php($row[4]), 'area' => $this->clean_value_php($row[5]), 'fecha_ingreso' => $ingreso_dt->format('Y-m-d'), 'numero_empleado' => "UAC-{$this->get_initials_php($nombre)}-{$persona_id}", 'periodo' => null, 'vacaciones' => [] ];
                $periodo_id = $persona_id; $periodo_inicio_sql = "2024-" . $ingreso_dt->format('m-d');
                try {
                    $periodo_fin_dt_base = new DateTime("2025-" . $ingreso_dt->format('m-d'));
                    if ($ingreso_dt->format('m-d') == '02-29' && !$periodo_fin_dt_base->format('L')) { $periodo_fin_dt_base->setDate(2025, 2, 28); }
                    $periodo_fin_dt = $periodo_fin_dt_base->modify('-1 day'); $periodo_fin_sql = $periodo_fin_dt->format('Y-m-d');
                } catch (Exception $e) { $errores_preview[] = "Emp. '{$nombre}' (ID: {$persona_id}): Error calc. fin período."; $periodo_fin_sql = "2025-12-31"; }
                $persona_data['periodo'] = ['id' => $periodo_id, 'persona_id' => $persona_id, 'periodo_inicio' => $periodo_inicio_sql, 'periodo_fin' => $periodo_fin_sql ];

                $vacas_raw = $this->clean_value_php($row[6]); $dias_raw = $this->clean_value_php($row[7]);
                preg_match_all('/(\d{1,2}\/\d{1,2}\/\d{4})\s*-\s*(\d{1,2}\/\d{1,2}\/\d{4})/', $vacas_raw, $date_matches);
                preg_match_all('/(\d+)/', $dias_raw, $dias_matches); $dias_list = $dias_matches[1];

                if (!empty($vacas_raw) && count($date_matches[1]) !== count($dias_list)) { $errores_preview[] = "Emp. '{$nombre}' (ID: {$persona_id}): Discrepancia fechas/días vacas. Omitidas.";
                } else {
                    foreach ($date_matches[1] as $index => $start_date_str) {
                        $start_sql = $this->format_final_attempt_date_php($start_date_str); $end_sql = $this->format_final_attempt_date_php($date_matches[2][$index]);
                        $dias_tomados = isset($dias_list[$index]) ? (int)$dias_list[$index] : 0;
                        if ($start_sql && $end_sql && $dias_tomados > 0) {
                            $persona_data['vacaciones'][] = ['fecha_inicio' => $start_sql, 'fecha_fin' => $end_sql, 'dias_tomados' => $dias_tomados ]; $contadorVacaciones++;
                        } else if (!empty($start_date_str) || !empty($date_matches[2][$index])) { $errores_preview[] = "Emp. '{$nombre}' (ID: {$persona_id}): Rango vacas inválido ('{$start_date_str}-{$date_matches[2][$index]}'). Omitido."; }
                    }
                }
                $data_preview[] = $persona_data; $contadorPersonas++;
            }

            fclose($file); $_SESSION['import_preview_data'] = $data_preview; $_SESSION['import_errors'] = $errores_preview;
            require 'views/layout/header.php'; require 'views/importar/preview.php'; require 'views/layout/footer.php';

        } catch (Exception $e) {
            if (isset($file) && is_resource($file)) fclose($file);
            $_SESSION['import_preview_data'] = $data_preview; $_SESSION['import_errors'] = $errores_preview;
            die("ERROR CRÍTICO EN PREVISUALIZAR: " . $e->getMessage() . "<br><pre>" . $e->getTraceAsString() . "</pre>");
        }
    }

    public function ejecutar() {
        ini_set('display_errors', 1); error_reporting(E_ALL);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['import_preview_data']) || !isset($_SESSION['import_mode'])) {
             $this->redirigirConError('index', 'Error: Sesión expirada o datos no encontrados.'); return; }

        $data_to_import = $_SESSION['import_preview_data']; $import_mode = $_SESSION['import_mode'];
        unset($_SESSION['import_preview_data']); unset($_SESSION['import_mode']); unset($_SESSION['import_errors']);

        // Mover la inicialización de $db y beginTransaction al inicio del try principal
        $this->db = Database::getInstance()->getConnection(); // Asegura conexión fresca

        try {
             // --- Intenta iniciar transacción ---
             if ($this->db->inTransaction()) { $this->db->rollBack(); } // Por si acaso
             $transactionStarted = $this->db->beginTransaction(); // Guarda el resultado
             if (!$transactionStarted) { // Verifica si realmente inició
                  throw new Exception("No se pudo iniciar la transacción de la base de datos.");
             }
             // --- Fin inicio transacción ---

             if ($import_mode === 'reemplazar') {
                 $this->db->exec("SET FOREIGN_KEY_CHECKS=0;");
                 $this->db->exec("DELETE FROM vacaciones;"); $this->db->exec("DELETE FROM periodos;"); $this->db->exec("DELETE FROM personas;");
                 $this->db->exec("ALTER TABLE personas AUTO_INCREMENT = 1;"); $this->db->exec("ALTER TABLE periodos AUTO_INCREMENT = 1;"); $this->db->exec("ALTER TABLE vacaciones AUTO_INCREMENT = 1;");
                 $this->db->exec("SET FOREIGN_KEY_CHECKS=1;");
             }

            $stmtPersona = $this->db->prepare("INSERT INTO personas (id, dni, numero_empleado, nombre_completo, cargo, area, fecha_ingreso, estado) VALUES (:id, :dni, :num_emp, :nombre, :cargo, :area, :ingreso, 'ACTIVO')");
            $stmtPeriodo = $this->db->prepare("INSERT INTO periodos (id, persona_id, periodo_inicio, periodo_fin, total_dias, dias_usados) VALUES (:id, :pid, :inicio, :fin, 30, 0)");
            $stmtVaca = $this->db->prepare("INSERT INTO vacaciones (persona_id, periodo_id, fecha_inicio, fecha_fin, dias_tomados, tipo, estado) VALUES (:pid, :perid, :inicio, :fin, :dias, 'NORMAL', 'GOZADO')");

            $contadorPersonas = 0; $contadorVacaciones = 0;

            foreach ($data_to_import as $persona_data) {
                // NO hay try/catch interno, dejamos que el externo capture el error original
                 $stmtPersona->execute([':id' => $persona_data['id'], ':dni' => $persona_data['dni'], ':num_emp' => $persona_data['numero_empleado'], ':nombre' => $persona_data['nombre_completo'], ':cargo' => $persona_data['cargo'], ':area' => $persona_data['area'], ':ingreso' => $persona_data['fecha_ingreso'] ]); $contadorPersonas++;
                 $periodo = $persona_data['periodo'];
                 $stmtPeriodo->execute([':id' => $periodo['id'], ':pid' => $periodo['persona_id'], ':inicio' => $periodo['periodo_inicio'], ':fin' => $periodo['periodo_fin'] ]);
                 foreach ($persona_data['vacaciones'] as $vaca) {
                      $stmtVaca->execute([':pid' => $persona_data['id'], ':perid' => $periodo['id'], ':inicio' => $vaca['fecha_inicio'], ':fin' => $vaca['fecha_fin'], ':dias' => $vaca['dias_tomados'] ]); $contadorVacaciones++; }
            } // Fin foreach

            // Si llegamos aquí sin errores, intentar commit
            if ($this->db->inTransaction()) { // Doble chequeo por si acaso
                 $commitSuccess = $this->db->commit(); // Guarda el resultado del commit
                 if (!$commitSuccess) {
                      throw new Exception("Falló la operación COMMIT de la base de datos.");
                 }
            } else {
                 // Si la transacción ya no estaba activa (error previo no capturado correctamente?)
                 throw new Exception("La transacción ya no estaba activa antes de intentar hacer commit final.");
            }

            header("Location: index.php?controller=importar&action=index&status=success&count_p=$contadorPersonas&count_v=$contadorVacaciones"); exit;

        } catch (Exception $e) { // Captura CUALQUIER excepción
            // Rollback SIEMPRE que haya error Y estemos en transacción
            if ($this->db && $this->db->inTransaction()) {
                 $this->db->rollBack();
            }

            // --- ¡CAMBIO AQUÍ! MOSTRAR EL ERROR ORIGINAL ---
            // $e->getMessage() ahora contendrá el error REAL de la base de datos o
            // la excepción "La transacción se perdió..." o "No se pudo iniciar..." etc.
            die("ERROR DETALLADO EN EJECUTAR (Error Original):<br><br>" . htmlspecialchars($e->getMessage()) .  // Usar htmlspecialchars para seguridad
                "<br><br>Código de Error PDO (si aplica): " . ($e instanceof PDOException ? htmlspecialchars($e->getCode()) : 'N/A') .
                "<br><br>Trace:<br><pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>"); // Usar htmlspecialchars
            // ---------------------------------------------------

            // $this->redirigirConError('index', 'Error en BD al ejecutar: ' . $e->getMessage());
        }
    }

    // --- Funciones de Ayuda (Sin cambios en lógica, solo llamadas) ---
    private function redirigirConError($action, $mensaje) {
        unset($_SESSION['import_preview_data']); unset($_SESSION['import_mode']); unset($_SESSION['import_errors']);
        // Limpia cualquier salida anterior antes de redirigir
        if (ob_get_level()) ob_end_clean(); 
        header("Location: index.php?controller=importar&action=$action&status=error&msg=" . urlencode($mensaje)); exit;
    }
    private function clean_value_php($value, $is_numeric = false) { /* ... */ }
    private function get_initials_php($full_name) { /* ... */ }
    private function parse_final_attempt_date_php($date_str) { /* ... */ }
    private function format_final_attempt_date_php($date_str_dmy) { /* ... */ }

} // Fin clase ImportarController