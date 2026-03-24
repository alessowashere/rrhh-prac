<?php
// models/Periodo.php

class Periodo {
    private $db;

    public function __construct($db) {
        // Basic check for valid DB connection
        if (!($db instanceof PDO)) {
            die("<p style='color:red; font-weight:bold;'>FATAL ERROR in PeriodoModel Constructor: Database connection is invalid!</p>");
        }
        $this->db = $db;
    }

    // --- Find the correct Period ID based on Person and Date ---
    /**
     * Finds the specific periodo.id for a given persona_id
     * where the provided date falls within the periodo_inicio and periodo_fin range.
     *
     * @param int $persona_id The ID of the employee.
     * @param string $date_str The date (YYYY-MM-DD) to check (usually vacation start date).
     * @return int|null The periodo.id if found, null otherwise.
     */
    public function findPeriodoIdByPersonaAndDate($persona_id, $date_str) {
        if (empty($persona_id) || empty($date_str)) {
            return null;
        }
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date_str)) {
             error_log("Invalid date format '{$date_str}' passed to findPeriodoIdByPersonaAndDate.");
             return null;
        }

        try {
            $sql = "SELECT id
                    FROM periodos
                    WHERE persona_id = :persona_id
                      AND :check_date BETWEEN periodo_inicio AND periodo_fin
                    LIMIT 1";

            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':persona_id', $persona_id, PDO::PARAM_INT);
            $stmt->bindParam(':check_date', $date_str, PDO::PARAM_STR);
            $stmt->execute();
            $result = $stmt->fetchColumn();
            return ($result === false) ? null : (int)$result;

        } catch (PDOException $e) {
            error_log("Error in findPeriodoIdByPersonaAndDate for persona {$persona_id}, date {$date_str}: " . $e->getMessage());
            return null;
        } catch (Exception $e) {
             error_log("General Error in findPeriodoIdByPersonaAndDate: " . $e->getMessage());
             return null;
        }
    }

    // --- Core Logic for Next Period (Check/Create/Update) ---
    public function verificarOCrearActualizarPeriodoSiguiente($persona_id, $fecha_ingreso_str) {
        if (empty($fecha_ingreso_str)) return false;
        try {
            $ingreso = new DateTime($fecha_ingreso_str); $hoy = new DateTime(); $hoy->setTime(0, 0, 0);
            $ultimo_aniversario = new DateTime($hoy->format('Y') . '-' . $ingreso->format('m-d'));
            if ($ultimo_aniversario > $hoy) $ultimo_aniversario->modify('-1 year');
            if ($ingreso->format('m-d') == '02-29' && !$ultimo_aniversario->format('L')) $ultimo_aniversario->setDate((int)$ultimo_aniversario->format('Y'), 2, 28);
            $periodo_siguiente_inicio = $ultimo_aniversario;
            $proximo_aniversario = clone $periodo_siguiente_inicio; $proximo_aniversario->modify('+1 year');
            if ($ingreso->format('m-d') == '02-29' && !$proximo_aniversario->format('L')) $proximo_aniversario->setDate((int)$proximo_aniversario->format('Y'), 2, 28);
            $periodo_siguiente_fin = clone $proximo_aniversario; $periodo_siguiente_fin->modify('-1 day');
            $intervalo = $hoy->diff($periodo_siguiente_inicio);
            $meses_pasados = ($intervalo->y * 12) + $intervalo->m + ($intervalo->d / 30.4375);
            $dias_devengados_hoy = floor($meses_pasados * (30 / 12)); $dias_devengados_hoy = max(0, min($dias_devengados_hoy, 30));
            $sql_check = "SELECT id, total_dias FROM periodos WHERE persona_id = ? AND periodo_inicio = ?";
            $stmt_check = $this->db->prepare($sql_check); $stmt_check->execute([$persona_id, $periodo_siguiente_inicio->format('Y-m-d')]);
            $periodo_existente = $stmt_check->fetch();
            if ($periodo_existente) {
                if ($periodo_existente['total_dias'] != $dias_devengados_hoy) {
                    $sql_update = "UPDATE periodos SET total_dias = ? WHERE id = ?"; $stmt_update = $this->db->prepare($sql_update);
                    $stmt_update->execute([$dias_devengados_hoy, $periodo_existente['id']]); }
            } else {
                $sql_insert = "INSERT INTO periodos (persona_id, periodo_inicio, periodo_fin, total_dias, dias_usados) VALUES (?, ?, ?, ?, 0)";
                $stmt_insert = $this->db->prepare($sql_insert); $stmt_insert->execute([$persona_id, $periodo_siguiente_inicio->format('Y-m-d'), $periodo_siguiente_fin->format('Y-m-d'), $dias_devengados_hoy]); }
            return true;
        } catch (Exception $e) { error_log("Error in Check/Create/Update Period for persona {$persona_id}: " . $e->getMessage()); return false; }
    }

    // --- Get distinct START YEARS + current earning year ---
    public function getPeriodoAnios() {
        try {
            $sql = "SELECT DISTINCT YEAR(periodo_inicio) as anio_inicio FROM periodos ORDER BY anio_inicio DESC";
            $stmt = $this->db->query($sql); $years_existentes = $stmt->fetchAll(PDO::FETCH_COLUMN);
            $options = []; $today = new DateTime(); $current_earning_year = null;
             try {
                 $sql_any_employee = "SELECT fecha_ingreso FROM personas WHERE estado = 'ACTIVO' AND fecha_ingreso IS NOT NULL LIMIT 1";
                 $stmt_any = $this->db->query($sql_any_employee); $any_ingreso_str = $stmt_any->fetchColumn();
                 if ($any_ingreso_str) {
                      $ingreso = new DateTime($any_ingreso_str); $ultimo_aniversario = new DateTime($today->format('Y') . '-' . $ingreso->format('m-d'));
                      if ($ultimo_aniversario > $today) $ultimo_aniversario->modify('-1 year'); $current_earning_year = (int)$ultimo_aniversario->format('Y'); }
             } catch (Exception $e) { /* ignore */ }
            foreach ($years_existentes as $year) { $year_int = (int)$year; $next_year = $year_int + 1; $is_current = ($year_int === $current_earning_year);
                 $options[$year_int] = ['filter_value' => $year_int, 'display_text' => $year_int . ' - ' . $next_year . ($is_current ? ' (En Progreso)' : '')]; }
            if ($current_earning_year !== null && !isset($options[$current_earning_year])) { $next_year = $current_earning_year + 1;
                $options[$current_earning_year] = ['filter_value' => $current_earning_year, 'display_text' => $current_earning_year . ' - ' . $next_year . ' (En Progreso)']; krsort($options); }
            return array_values($options);
        } catch (PDOException $e) { die("Error al obtener años de períodos: " . $e->getMessage()); } catch (Exception $e){ die("Date Error in getPeriodoAnios: " . $e->getMessage()); }
    }

    // --- List Function (Adds conceptual range) ---
    public function listar($periodo_filtro_anio_inicio = null) {
        try {
            $subQuery = "(SELECT periodo_id, SUM(dias_tomados) as dias_reales FROM vacaciones WHERE estado IN ('APROBADO', 'GOZADO') GROUP BY periodo_id)";
            $sql = "SELECT per.id, per.persona_id, p.nombre_completo, p.fecha_ingreso, per.periodo_inicio, per.periodo_fin, per.total_dias, COALESCE(v_calc.dias_reales, 0) AS dias_usados_calculados
                    FROM periodos AS per JOIN personas AS p ON per.persona_id = p.id LEFT JOIN " . $subQuery . " AS v_calc ON per.id = v_calc.periodo_id";
            $params = [];
            if ($periodo_filtro_anio_inicio && is_numeric($periodo_filtro_anio_inicio)) { $sql .= " WHERE YEAR(per.periodo_inicio) = ?"; $params[] = (int)$periodo_filtro_anio_inicio; }
            $sql .= " ORDER BY p.nombre_completo ASC, per.periodo_inicio DESC";
            $stmt = $this->db->prepare($sql); $stmt->execute($params); $results = $stmt->fetchAll();
            foreach ($results as $key => $row) { if (isset($row['periodo_inicio'])) { try { $start_year = (new DateTime($row['periodo_inicio']))->format('Y'); $end_year = (int)$start_year + 1; $results[$key]['conceptual_range'] = $start_year . ' - ' . $end_year; } catch (Exception $e) { $results[$key]['conceptual_range'] = 'Año Inválido'; } } else { $results[$key]['conceptual_range'] = 'Fecha Inválida'; } }
            return $results;
        } catch (PDOException $e) { die("Error al listar períodos: " . $e->getMessage()); } catch (Exception $e) { die("Error de Fecha al listar períodos: " . $e->getMessage()); }
    }

    // --- Get Period by ID with Calculated Saldo ---
    public function obtenerPorIdConSaldo($id) {
        if (!is_numeric($id) || $id <= 0) return false;
        try {
            $subQuery = "(SELECT periodo_id, SUM(dias_tomados) as dias_reales
                         FROM vacaciones WHERE estado IN ('APROBADO', 'GOZADO') AND periodo_id = :periodo_id_sub
                         GROUP BY periodo_id)";
            $sql = "SELECT per.*, COALESCE(v_calc.dias_reales, 0) AS dias_usados_calculados
                    FROM periodos AS per LEFT JOIN " . $subQuery . " AS v_calc ON per.id = v_calc.periodo_id
                    WHERE per.id = :periodo_id_main";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':periodo_id_sub', $id, PDO::PARAM_INT);
            $stmt->bindParam(':periodo_id_main', $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch();
        } catch (PDOException $e) { error_log("Error getting period with saldo (ID: {$id}): " . $e->getMessage()); return false; }
    }

    // --- Get Period by ID (Basic) ---
    public function obtenerPorId($id) {
         if (!is_numeric($id) || $id <= 0) return false;
        try {
            $sql = "SELECT * FROM periodos WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id]);
            return $stmt->fetch();
        } catch (PDOException $e) { error_log("Error getting period by ID {$id}: " . $e->getMessage()); return false; }
    }

    // --- Create Period ---
    public function crear($datos) {
        try {
            $inicio_date = $datos['periodo_inicio']; if (strlen($inicio_date) == 4) $inicio_date .= '-01-01';
            $fin_date = $datos['periodo_fin']; if (strlen($fin_date) == 4) $fin_date .= '-12-31';
            $sql = "INSERT INTO periodos (persona_id, periodo_inicio, periodo_fin, total_dias, dias_usados) VALUES (?, ?, ?, ?, 0)";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([ $datos['persona_id'], $inicio_date, $fin_date, $datos['total_dias'] ]);
        } catch (PDOException $e) { error_log("Error creating period: " . $e->getMessage()); return false; } // Return false on error
    }

    // --- Update Period ---
    public function actualizar($id, $datos) {
        if (!is_numeric($id) || $id <= 0) return false;
        try {
             $inicio_date = $datos['periodo_inicio']; if (strlen($inicio_date) == 4) $inicio_date .= '-01-01';
             $fin_date = $datos['periodo_fin']; if (strlen($fin_date) == 4) $fin_date .= '-12-31';
            $sql = "UPDATE periodos SET persona_id = ?, periodo_inicio = ?, periodo_fin = ?, total_dias = ? WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([ $datos['persona_id'], $inicio_date, $fin_date, $datos['total_dias'], $id ]);
        } catch (PDOException $e) { error_log("Error updating period ID {$id}: " . $e->getMessage()); return false; } // Return false on error
    }

    // --- Delete Period ---
    public function eliminar($id) {
        if (!is_numeric($id) || $id <= 0) return false;
        try {
            $sql = "DELETE FROM periodos WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$id]);
        } catch (PDOException $e) { error_log("Error deleting period ID {$id}: " . $e->getMessage()); return false; } // Return false on error
    }
} // End Class