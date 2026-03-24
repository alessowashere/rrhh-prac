<?php
class ReporteModel {

    private $db; 

    // 1. Constructor para recibir la conexión BD
    public function __construct($db) {
        if (!($db instanceof PDO)) {
            die("FATAL ERROR in ReporteModel Constructor: DB connection invalid!");
        }
        $this->db = $db;
    }

    // 2. SQL REAL para Reporte General
    public function getReporteGeneral($filtros) {
        try {
            $sql = "SELECT p.id, p.nombre_completo, p.cargo, p.area, p.fecha_ingreso,
                           per.periodo_inicio, per.periodo_fin, per.total_dias,
                           COALESCE(v_calc.dias_reales, 0) AS dias_usados_calculados,
                           (per.total_dias - COALESCE(v_calc.dias_reales, 0)) AS saldo_calculado
                    FROM personas p ";

                    // --- INICIO CAMBIO LÓGICA DE PERÍODO ---
                    if (!empty($filtros['anio_inicio'])) {
                        $sql .= " LEFT JOIN periodos per ON p.id = per.persona_id AND YEAR(per.periodo_inicio) = :anio_inicio ";
                    } else {
                        $sql .= " JOIN (
                                    SELECT psub.id as persona_id_sub, COALESCE(
                                        (SELECT per1.id FROM periodos per1 WHERE per1.persona_id = psub.id AND per1.periodo_fin >= CURDATE() ORDER BY per1.periodo_fin ASC LIMIT 1),
                                        (SELECT per2.id FROM periodos per2 WHERE per2.persona_id = psub.id ORDER BY per2.periodo_inicio DESC LIMIT 1)
                                    ) as periodo_relevante_id
                                    FROM personas psub WHERE psub.estado = 'ACTIVO'
                                ) AS pr ON p.id = pr.persona_id_sub
                                LEFT JOIN periodos per ON pr.periodo_relevante_id = per.id ";
                    }
                    // --- FIN CAMBIO LÓGICA DE PERÍODO ---

                    $sql .= " LEFT JOIN (SELECT periodo_id, ... ) AS v_calc ON per.id = v_calc.periodo_id
                            WHERE p.estado = 'ACTIVO'
                            ORDER BY p.nombre_completo ASC";

                    $stmt = $this->db->prepare($sql);

                    // --- INICIO CÓDIGO AÑADIDO ---
                    if (!empty($filtros['anio_inicio'])) {
                        $stmt->bindValue(':anio_inicio', $filtros['anio_inicio'], PDO::PARAM_INT);
                    }
                    // --- FIN CÓDIGO AÑADIDO ---

                    $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en ReporteModel::getReporteGeneral: " . $e->getMessage());
            return [];
        }
    }

    // 3. SQL REAL para Reporte por Persona
    public function getReportePorPersona($filtros) {
        try {
            $sql = "SELECT v.fecha_inicio, v.fecha_fin, v.dias_tomados, v.estado, v.tipo, 
                           per.periodo_inicio, per.periodo_fin
                    FROM vacaciones v
                    JOIN periodos per ON v.periodo_id = per.id
                    WHERE v.persona_id = :persona_id";
            
            $params = [':persona_id' => $filtros['empleado_id']];
            
            if (!empty($filtros['fecha_inicio'])) {
                $sql .= " AND v.fecha_inicio >= :fecha_inicio";
                $params[':fecha_inicio'] = $filtros['fecha_inicio'];
            }
            if (!empty($filtros['fecha_fin'])) {
                $sql .= " AND v.fecha_fin <= :fecha_fin";
                $params[':fecha_fin'] = $filtros['fecha_fin'];
            }
            // ... (después de los if de fecha_inicio y fecha_fin) ...

            // --- INICIO CÓDIGO AÑADIDO ---
            if (!empty($filtros['anio_inicio'])) {
                $sql .= " AND YEAR(per.periodo_inicio) = :anio_inicio";
                $params[':anio_inicio'] = $filtros['anio_inicio'];
            }
            // --- FIN CÓDIGO AÑADIDO ---

            $sql .= " ORDER BY v.fecha_inicio DESC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en ReporteModel::getReportePorPersona: " . $e->getMessage());
            return [];
        }
    }

    // 4. CAMBIO AQUÍ: SQL para Reporte por Período (usa anio_inicio)
    public function getReportePorPeriodo($filtros) {
        try {
            // La consulta ahora une 'periodos' para filtrar por AÑO
            $sql = "SELECT p.nombre_completo, v.fecha_inicio, v.fecha_fin, v.dias_tomados, v.estado, v.tipo
                    FROM vacaciones v
                    JOIN personas p ON v.persona_id = p.id
                    JOIN periodos per ON v.periodo_id = per.id
                    WHERE YEAR(per.periodo_inicio) = :anio_inicio
                    AND v.estado IN ('APROBADO', 'GOZADO')
                    ORDER BY p.nombre_completo ASC, v.fecha_inicio ASC";
            
            // Los parámetros ahora solo usan 'anio_inicio'
            $params = [
                ':anio_inicio' => $filtros['anio_inicio']
            ];
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en ReporteModel::getReportePorPeriodo: " . $e->getMessage());
            return [];
        }
    }

    // 5. SQL REAL para Reporte de Saldos
    public function getReporteSaldos($filtros) {
         try {
            // Reutiliza la consulta general, pero filtra por saldos no-cero
            $sql = "SELECT p.nombre_completo, p.cargo,
                           (per.total_dias - COALESCE(v_calc.dias_reales, 0)) AS saldo_calculado
                    FROM personas p ";

                    // --- INICIO CAMBIO LÓGICA DE PERÍODO ---
                    if (!empty($filtros['anio_inicio'])) {
                        $sql .= " LEFT JOIN periodos per ON p.id = per.persona_id AND YEAR(per.periodo_inicio) = :anio_inicio ";
                    } else {
                        $sql .= " JOIN (
                                    SELECT psub.id as persona_id_sub, COALESCE(
                                        (SELECT per1.id FROM periodos per1 WHERE per1.persona_id = psub.id AND per1.periodo_fin >= CURDATE() ORDER BY per1.periodo_fin ASC LIMIT 1),
                                        (SELECT per2.id FROM periodos per2 WHERE per2.persona_id = psub.id ORDER BY per2.periodo_inicio DESC LIMIT 1)
                                    ) as periodo_relevante_id
                                    FROM personas psub WHERE psub.estado = 'ACTIVO'
                                ) AS pr ON p.id = pr.persona_id_sub
                                LEFT JOIN periodos per ON pr.periodo_relevante_id = per.id ";
                    }
                    // --- FIN CAMBIO LÓGICA DE PERÍODO ---

                    $sql .= " LEFT JOIN (SELECT periodo_id, ... ) AS v_calc ON per.id = v_calc.periodo_id
                    WHERE p.estado = 'ACTIVO'
                    HAVING saldo_calculado != 0
                    ORDER BY saldo_calculado ASC";

            $stmt = $this->db->prepare($sql);

            // --- INICIO CÓDIGO AÑADIDO ---
            if (!empty($filtros['anio_inicio'])) {
                $stmt->bindValue(':anio_inicio', $filtros['anio_inicio'], PDO::PARAM_INT);
            }
            // --- FIN CÓDIGO AÑADIDO ---

            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en ReporteModel::getReporteSaldos: " . $e->getMessage());
            return [];
        }
    }
    public function getReportePorArea($filtros) {
        // Esta consulta es idéntica a getReporteGeneral, 
        // pero con un filtro WHERE adicional para el área.
        try {
            $sql = "SELECT p.id, p.nombre_completo, p.cargo, p.fecha_ingreso,
                        per.periodo_inicio, per.periodo_fin, per.total_dias,
                        COALESCE(v_calc.dias_reales, 0) AS dias_usados_calculados,
                        (per.total_dias - COALESCE(v_calc.dias_reales, 0)) AS saldo_calculado
                    FROM personas p ";

            // --- INICIO CAMBIO LÓGICA DE PERÍODO ---
            if (!empty($filtros['anio_inicio'])) {
                $sql .= " LEFT JOIN periodos per ON p.id = per.persona_id AND YEAR(per.periodo_inicio) = :anio_inicio ";
            } else {
                $sql .= " JOIN (
                            SELECT psub.id as persona_id_sub, COALESCE(
                                (SELECT per1.id FROM periodos per1 WHERE per1.persona_id = psub.id AND per1.periodo_fin >= CURDATE() ORDER BY per1.periodo_fin ASC LIMIT 1),
                                (SELECT per2.id FROM periodos per2 WHERE per2.persona_id = psub.id ORDER BY per2.periodo_inicio DESC LIMIT 1)
                            ) as periodo_relevante_id
                            FROM personas psub WHERE psub.estado = 'ACTIVO'
                        ) AS pr ON p.id = pr.persona_id_sub
                        LEFT JOIN periodos per ON pr.periodo_relevante_id = per.id ";
            }
            // --- FIN CAMBIO LÓGICA DE PERÍODO ---

            $sql .= " LEFT JOIN (SELECT periodo_id, SUM(dias_tomados) as dias_reales FROM vacaciones WHERE estado IN ('APROBADO', 'GOZADO') GROUP BY periodo_id) AS v_calc ON per.id = v_calc.periodo_id
                    WHERE p.estado = 'ACTIVO' 
                    AND p.area = :area  -- <-- El filtro de área
                    ORDER BY p.nombre_completo ASC";

            // --- INICIO CAMBIO PARÁMETROS ---
            $params = [':area' => $filtros['area']];
            if (!empty($filtros['anio_inicio'])) {
                $params[':anio_inicio'] = $filtros['anio_inicio'];
            }
            // --- FIN CAMBIO PARÁMETROS ---

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params); // Pasamos los parámetros
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en ReporteModel::getReportePorArea: " . $e->getMessage());
            return [];
        }
    }
// ... (después de getReportePorArea()) ...

    // --- NUEVA FUNCIÓN (PARA REQUEST 3) ---
    public function getReporteVacacionesPorArea($filtros) {
        try {
            // Esta consulta es similar a 'getReportePorPeriodo' pero filtra por área
            $sql = "SELECT p.nombre_completo, v.fecha_inicio, v.fecha_fin, v.dias_tomados, v.estado, v.tipo,
                           per.periodo_inicio, per.periodo_fin
                    FROM vacaciones v
                    JOIN personas p ON v.persona_id = p.id
                    JOIN periodos per ON v.periodo_id = per.id
                    WHERE p.area = :area
                    AND v.estado IN ('APROBADO', 'GOZADO')";
            
            $params = [
                ':area' => $filtros['area']
            ];
            
            // Añadir filtro de período si existe
            if (!empty($filtros['anio_inicio'])) {
                $sql .= " AND YEAR(per.periodo_inicio) = :anio_inicio";
                $params[':anio_inicio'] = $filtros['anio_inicio'];
            }

            $sql .= " ORDER BY p.nombre_completo ASC, v.fecha_inicio ASC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en ReporteModel::getReporteVacacionesPorArea: " . $e->getMessage());
            return [];
        }
    }
    public function getReporteGeneralPorArea($filtros) {
        try {
            $sql = "SELECT p.id, p.nombre_completo, p.cargo, p.area, p.fecha_ingreso,
                           per.periodo_inicio, per.periodo_fin, per.total_dias,
                           COALESCE(v_calc.dias_reales, 0) AS dias_usados_calculados,
                           (per.total_dias - COALESCE(v_calc.dias_reales, 0)) AS saldo_calculado
                    FROM personas p ";
            
            if (!empty($filtros['anio_inicio'])) {
                $sql .= " LEFT JOIN periodos per ON p.id = per.persona_id AND YEAR(per.periodo_inicio) = :anio_inicio ";
            } else {
                $sql .= " JOIN (
                            SELECT psub.id as persona_id_sub, COALESCE(
                                (SELECT per1.id FROM periodos per1 WHERE per1.persona_id = psub.id AND per1.periodo_fin >= CURDATE() ORDER BY per1.periodo_fin ASC LIMIT 1),
                                (SELECT per2.id FROM periodos per2 WHERE per2.persona_id = psub.id ORDER BY per2.periodo_inicio DESC LIMIT 1)
                            ) as periodo_relevante_id
                            FROM personas psub WHERE psub.estado = 'ACTIVO'
                        ) AS pr ON p.id = pr.persona_id_sub
                        LEFT JOIN periodos per ON pr.periodo_relevante_id = per.id ";
            }
            
            $sql .= " LEFT JOIN (SELECT periodo_id, SUM(dias_tomados) as dias_reales FROM vacaciones WHERE estado IN ('APROBADO', 'GOZADO') GROUP BY periodo_id) AS v_calc ON per.id = v_calc.periodo_id
                    WHERE p.estado = 'ACTIVO'
                    ORDER BY p.area ASC, p.nombre_completo ASC"; // <-- CAMBIO CLAVE
            
            $stmt = $this->db->prepare($sql);
            if (!empty($filtros['anio_inicio'])) {
                $stmt->bindValue(':anio_inicio', $filtros['anio_inicio'], PDO::PARAM_INT);
            }
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en ReporteModel::getReporteGeneralPorArea: " . $e->getMessage());
            return [];
        }
    }
    public function getReporteVacacionesGeneralPorArea($filtros) {
        try {
            $sql = "SELECT p.area, p.nombre_completo, v.fecha_inicio, v.fecha_fin, v.dias_tomados, v.estado, v.tipo,
                           per.periodo_inicio, per.periodo_fin
                    FROM vacaciones v
                    JOIN personas p ON v.persona_id = p.id
                    JOIN periodos per ON v.periodo_id = per.id
                    WHERE v.estado IN ('APROBADO', 'GOZADO')";
            
            $params = []; // Sin parámetros por defecto
            
            // Añadir filtro de período si existe
            if (!empty($filtros['anio_inicio'])) {
                $sql .= " AND YEAR(per.periodo_inicio) = :anio_inicio";
                $params[':anio_inicio'] = $filtros['anio_inicio'];
            }

            // --- CAMBIO CLAVE: ORDENAR POR AREA ---
            $sql .= " ORDER BY p.area ASC, p.nombre_completo ASC, v.fecha_inicio ASC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en ReporteModel::getReporteVacacionesGeneralPorArea: " . $e->getMessage());
            return [];
        }
    }
    // --- FIN NUEVA FUNCIÓN ---
} // Fin de la clase