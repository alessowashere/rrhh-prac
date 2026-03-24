<?php
// models/Persona.php

class Persona {
    private $db;

    public function __construct($db) {
        if (!($db instanceof PDO)) {
            die("<p style='color:red; font-weight:bold;'>FATAL ERROR in PersonaModel Constructor: DB connection invalid!</p>");
        }
        $this->db = $db;
    }

    // --- Get active employees with entry date ---
    public function listarActivosConIngreso() {
         try {
             $sql = "SELECT id, fecha_ingreso
                     FROM personas
                     WHERE estado = 'ACTIVO' AND fecha_ingreso IS NOT NULL";
             $stmt = $this->db->query($sql);
             return $stmt->fetchAll();
         } catch (PDOException $e) {
             error_log("Error fetching active employees with entry date: " . $e->getMessage());
             return [];
         }
     }

    // --- List all employees ---
    public function listar() {
        try {
            $sql = "SELECT id, numero_empleado, dni, nombre_completo, cargo, area, estado, fecha_ingreso
                    FROM personas
                    ORDER BY nombre_completo ASC";
            $stmt = $this->db->query($sql);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
             error_log("Error listing personas: " . $e->getMessage());
             return []; // Return empty on error
        }
    }

    // --- Get one employee by ID ---
    public function obtenerPorId($id) {
         if (!is_numeric($id) || $id <= 0) return false;
        try {
            $sql = "SELECT * FROM personas WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id]);
            return $stmt->fetch();
        } catch (PDOException $e) {
             error_log("Error getting persona by ID {$id}: " . $e->getMessage());
             return false;
        }
    }

    // --- Create a new employee ---
    public function crear($datos) {
        try {
            $sql = "INSERT INTO personas (dni, numero_empleado, nombre_completo, cargo, area, fecha_ingreso, estado)
                    VALUES (:dni, :num_emp, :nombre, :cargo, :area, :ingreso, :estado)";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                ':dni' => $datos['dni'] ?? null,
                ':num_emp' => $datos['numero_empleado'] ?? null,
                ':nombre' => $datos['nombre_completo'] ?? '',
                ':cargo' => $datos['cargo'] ?? null,
                ':area' => $datos['area'] ?? null,
                ':ingreso' => empty($datos['fecha_ingreso']) ? null : $datos['fecha_ingreso'],
                ':estado' => $datos['estado'] ?? 'ACTIVO'
            ]);
        } catch (PDOException $e) {
             error_log("Error creating persona: " . $e->getMessage());
             return false; // Return false on error
        }
    }

    // --- Update an employee ---
    public function actualizar($id, $datos) {
        if (!is_numeric($id) || $id <= 0) return false;
        try {
            $sql = "UPDATE personas SET
                        dni = :dni, numero_empleado = :num_emp, nombre_completo = :nombre,
                        cargo = :cargo, area = :area, fecha_ingreso = :ingreso, estado = :estado
                    WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                ':dni' => $datos['dni'] ?? null,
                ':num_emp' => $datos['numero_empleado'] ?? null,
                ':nombre' => $datos['nombre_completo'] ?? '',
                ':cargo' => $datos['cargo'] ?? null,
                ':area' => $datos['area'] ?? null,
                ':ingreso' => empty($datos['fecha_ingreso']) ? null : $datos['fecha_ingreso'],
                ':estado' => $datos['estado'] ?? 'ACTIVO',
                ':id' => $id
            ]);
        } catch (PDOException $e) {
             error_log("Error updating persona ID {$id}: " . $e->getMessage());
             return false; // Return false on error
        }
    }

    // --- Delete an employee ---
    public function eliminar($id) {
         if (!is_numeric($id) || $id <= 0) return false;
        try {
            $sql = "DELETE FROM personas WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
             error_log("Error deleting persona ID {$id}: " . $e->getMessage());
             return false; // Return false on error
        }
    }

    // --- Count Active Employees ---
    public function contarActivos() {
        try {
            $sql = "SELECT COUNT(*) FROM personas WHERE estado = 'ACTIVO'";
            $stmt = $this->db->query($sql);
            return $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Error counting activos: " . $e->getMessage());
            return 0;
        }
    }

    // --- List Employees with Extreme Balances ---
    public function listarSaldosExtremos($tipo = 'bajo', $umbral = 5, $limite = 5) {
         if ($tipo !== 'bajo' && $tipo !== 'alto') return [];

        try {
            $subQueryUsados = "(SELECT periodo_id, SUM(dias_tomados) as dias_reales FROM vacaciones WHERE estado IN ('APROBADO', 'GOZADO') GROUP BY periodo_id)";
            // Subquery to find the most relevant period (ending soonest but not past, or latest start)
             $subQueryPeriodoRelevante = "
                SELECT psub.id as persona_id_sub, COALESCE(
                    (SELECT per1.id FROM periodos per1 WHERE per1.persona_id = psub.id AND per1.periodo_fin >= CURDATE() ORDER BY per1.periodo_fin ASC LIMIT 1),
                    (SELECT per2.id FROM periodos per2 WHERE per2.persona_id = psub.id ORDER BY per2.periodo_inicio DESC LIMIT 1)
                ) as periodo_relevante_id
                FROM personas psub WHERE psub.estado = 'ACTIVO'
             ";

            $sql = "SELECT p.nombre_completo, per.periodo_inicio, per.periodo_fin, per.total_dias,
                           COALESCE(v_calc.dias_reales, 0) AS dias_usados_calculados,
                           (per.total_dias - COALESCE(v_calc.dias_reales, 0)) AS saldo_calculado
                    FROM personas p
                    JOIN ($subQueryPeriodoRelevante) AS pr ON p.id = pr.persona_id_sub
                    JOIN periodos per ON pr.periodo_relevante_id = per.id
                    LEFT JOIN $subQueryUsados AS v_calc ON per.id = v_calc.periodo_id
                    WHERE p.estado = 'ACTIVO'";

            if ($tipo === 'bajo') {
                $sql .= " HAVING saldo_calculado <= :umbral ORDER BY saldo_calculado ASC";
            } else { // 'alto'
                $sql .= " HAVING saldo_calculado >= :umbral ORDER BY saldo_calculado DESC";
            }
            $sql .= " LIMIT :limite";

            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':umbral', $umbral, PDO::PARAM_INT);
            $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error en listarSaldosExtremos ({$tipo}): " . $e->getMessage());
            return [];
        }
    }
    public function listarAreasDistintas() {
        try {
            // Selecciona solo las 'areas' distintas que no estén vacías
            $sql = "SELECT DISTINCT area
                    FROM personas
                    WHERE area IS NOT NULL AND area != ''
                    ORDER BY area ASC";
            $stmt = $this->db->query($sql);
            // Devuelve un array simple de strings (ej: ['Contabilidad', 'Sistemas', ...])
            return $stmt->fetchAll(PDO::FETCH_COLUMN); 
        } catch (PDOException $e) {
             error_log("Error listing distinct areas: " . $e->getMessage());
             return []; // Devuelve vacío en caso de error
        }
    }

} // End Class