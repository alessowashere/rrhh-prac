<?php
// models/ConvenioModel.php

class ConvenioModel {
    
    private $pdo;

    public function __construct() {
        $this->pdo = Database::getInstance()->getConnection();
    }

    /**
     * [NUEVO] Motor de Cese Automático
     * Busca periodos activos cuya fecha_fin ya pasó y los finaliza junto con el convenio y el practicante.
     */
    public function ejecutarCeseAutomatico() {
        $this->pdo->beginTransaction();
        try {
            // 1. Buscar periodos 'Activos' que ya vencieron
            $sql_find = "SELECT pc.convenio_id, c.practicante_id 
                         FROM PeriodosConvenio pc
                         JOIN Convenios c ON pc.convenio_id = c.convenio_id
                         WHERE pc.estado_periodo = 'Activo' 
                         AND pc.fecha_fin < CURDATE()";
            
            $stmt = $this->pdo->query($sql_find);
            $vencidos = $stmt->fetchAll();

            if (empty($vencidos)) {
                $this->pdo->commit();
                return 0;
            }

            foreach ($vencidos as $v) {
                // A. Finalizar el periodo
                $this->pdo->prepare("UPDATE PeriodosConvenio SET estado_periodo = 'Finalizado' WHERE convenio_id = ? AND estado_periodo = 'Activo'")
                          ->execute([$v['convenio_id']]);

                // B. Finalizar el convenio
                $this->pdo->prepare("UPDATE Convenios SET estado_convenio = 'Finalizado' WHERE convenio_id = ?")
                          ->execute([$v['convenio_id']]);

                // C. Cambiar estado del practicante a 'Cesado'
                $this->pdo->prepare("UPDATE Practicantes SET estado_general = 'Cesado' WHERE practicante_id = ?")
                          ->execute([$v['practicante_id']]);
            }

            $this->pdo->commit();
            return count($vencidos);

        } catch (Exception $e) {
            $this->pdo->rollBack();
            error_log("Error en cese automático: " . $e->getMessage());
            return false;
        }
    }
    public function obtenerAreasConPracticantes() {
        $sql = "SELECT DISTINCT a.area_id, a.nombre 
                FROM Areas a
                INNER JOIN PeriodosConvenio pc ON a.area_id = pc.area_id
                WHERE pc.estado_periodo = 'Activo'
                ORDER BY a.nombre ASC";
        
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }
    public function getCandidatosAceptados() {
        $sql = "SELECT p.practicante_id, p.dni, p.nombres, p.apellidos,
                       pr.proceso_id, pr.fecha_postulacion, ep.nombre AS escuela_nombre
                FROM ProcesosReclutamiento pr
                JOIN Practicantes p ON pr.practicante_id = p.practicante_id
                LEFT JOIN EscuelasProfesionales ep ON p.escuela_profesional_id = ep.escuela_id
                WHERE pr.estado_proceso = 'Aceptado'
                AND pr.proceso_id NOT IN (
                    SELECT c.proceso_id FROM Convenios c WHERE c.proceso_id IS NOT NULL
                )
                ORDER BY pr.fecha_postulacion ASC";
        
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll();
    }

    public function getConveniosVigentes() {
        $sql = "SELECT 
                    c.convenio_id, c.tipo_practica, c.estado_convenio, c.estado_firma,
                    p.practicante_id, p.dni, p.nombres, p.apellidos,
                    COALESCE(pc_activo.fecha_inicio, 'N/A') AS fecha_inicio_actual,
                    COALESCE(pc_activo.fecha_fin, 'N/A') AS fecha_fin_actual,
                    COALESCE(a.nombre, 'N/A') AS area_actual
                FROM Convenios c
                JOIN Practicantes p ON c.practicante_id = p.practicante_id
                LEFT JOIN PeriodosConvenio pc_activo ON pc_activo.convenio_id = c.convenio_id 
                                                     AND pc_activo.estado_periodo = 'Activo'
                LEFT JOIN Areas a ON pc_activo.area_id = a.area_id
                WHERE c.estado_convenio = 'Vigente'
                ORDER BY pc_activo.fecha_fin ASC";
        
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll();
    }
    
    public function getPracticanteSimple(int $practicante_id) {
         $sql = "SELECT p.practicante_id, p.dni, p.nombres, p.apellidos, ep.nombre AS escuela_nombre
                 FROM Practicantes p
                 LEFT JOIN EscuelasProfesionales ep ON p.escuela_profesional_id = ep.escuela_id
                 WHERE p.practicante_id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$practicante_id]);
        return $stmt->fetch();
    }

    public function getCatalogos() {
        $sql_loc = "SELECT local_id, nombre FROM Locales ORDER BY nombre";
        $sql_are = "SELECT area_id, nombre FROM Areas ORDER BY nombre";
        
        return [
            'locales' => $this->pdo->query($sql_loc)->fetchAll(),
            'areas' => $this->pdo->query($sql_are)->fetchAll()
        ];
    }

    public function crearConvenioTransaccion(array $datosConvenio, array $datosPeriodo) {
        $this->pdo->beginTransaction();
        try {
            $sql_conv = "INSERT INTO Convenios (practicante_id, proceso_id, tipo_practica, estado_convenio, induccion_completada, estado_firma)
                         VALUES (?, ?, ?, ?, FALSE, ?)";
            $stmt_conv = $this->pdo->prepare($sql_conv);
            $stmt_conv->execute([
                $datosConvenio['practicante_id'],
                $datosConvenio['proceso_id'],
                $datosConvenio['tipo_practica'],
                $datosConvenio['estado_convenio'],
                $datosConvenio['estado_firma']
            ]);
            $convenio_id = $this->pdo->lastInsertId();

            $sql_per = "INSERT INTO PeriodosConvenio (convenio_id, fecha_inicio, fecha_fin, local_id, area_id, estado_periodo)
                        VALUES (?, ?, ?, ?, ?, ?)";
            $stmt_per = $this->pdo->prepare($sql_per);
            $stmt_per->execute([
                $convenio_id,
                $datosPeriodo['fecha_inicio'],
                $datosPeriodo['fecha_fin'],
                $datosPeriodo['local_id'],
                $datosPeriodo['area_id'],
                $datosPeriodo['estado_periodo']
            ]);

            $sql_prac = "UPDATE Practicantes SET estado_general = 'Activo' WHERE practicante_id = ?";
            $stmt_prac = $this->pdo->prepare($sql_prac);
            $stmt_prac->execute([$datosConvenio['practicante_id']]);

            $this->pdo->commit();
            return $convenio_id;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw new Exception("Error en transacción crearConvenio: " . $e->getMessage());
        }
    }
    
    public function getDetalleConvenio(int $convenio_id) {
        $sql_conv = "SELECT c.*, p.dni, p.nombres, p.apellidos
                     FROM Convenios c
                     JOIN Practicantes p ON c.practicante_id = p.practicante_id
                     WHERE c.convenio_id = ?";
        $stmt_conv = $this->pdo->prepare($sql_conv);
        $stmt_conv->execute([$convenio_id]);
        $convenio = $stmt_conv->fetch(PDO::FETCH_ASSOC);
        
        if (!$convenio) return false;
        
        $sql_per = "SELECT pc.*, a.nombre AS area_nombre, l.nombre AS local_nombre
                    FROM PeriodosConvenio pc
                    LEFT JOIN Areas a ON pc.area_id = a.area_id
                    LEFT JOIN Locales l ON pc.local_id = l.local_id
                    WHERE pc.convenio_id = ? ORDER BY pc.fecha_inicio DESC";
        $stmt_per = $this->pdo->prepare($sql_per);
        $stmt_per->execute([$convenio_id]);
        $convenio['periodos'] = $stmt_per->fetchAll(PDO::FETCH_ASSOC);
        
        $sql_ad = "SELECT * FROM Adendas WHERE convenio_id = ? ORDER BY fecha_adenda DESC, adenda_id DESC";
        $stmt_ad = $this->pdo->prepare($sql_ad);
        $stmt_ad->execute([$convenio_id]);
        $convenio['adendas'] = $stmt_ad->fetchAll(PDO::FETCH_ASSOC);
        
        return $convenio;
    }

    public function contarConveniosPorVencer(int $dias = 30) {
        $sql = "SELECT COUNT(pc.periodo_id) as total
                FROM PeriodosConvenio pc
                WHERE pc.estado_periodo = 'Activo'
                AND pc.fecha_fin BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL ? DAY)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$dias]);
        $res = $stmt->fetch();
        return $res['total'] ?? 0;
    }

    public function getConveniosPorVencer(int $dias = 30) {
        $sql = "SELECT 
                    c.convenio_id, p.practicante_id, p.nombres, p.apellidos, pc.fecha_fin
                FROM PeriodosConvenio pc
                JOIN Convenios c ON pc.convenio_id = c.convenio_id
                JOIN Practicantes p ON c.practicante_id = p.practicante_id
                WHERE pc.estado_periodo = 'Activo'
                AND pc.fecha_fin BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL ? DAY)
                ORDER BY pc.fecha_fin ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$dias]);
        return $stmt->fetchAll();
    }

    public function getUltimosConveniosCreados(int $limite = 5) {
        $sql = "SELECT 
                    c.convenio_id, p.practicante_id, p.nombres, p.apellidos, 
                    c.tipo_practica, a.nombre as area_nombre
                FROM Convenios c
                JOIN Practicantes p ON c.practicante_id = p.practicante_id
                LEFT JOIN PeriodosConvenio pc ON c.convenio_id = pc.convenio_id AND pc.estado_periodo = 'Activo'
                LEFT JOIN Areas a ON pc.area_id = a.area_id
                ORDER BY c.convenio_id DESC 
                LIMIT ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$limite]);
        return $stmt->fetchAll();
    }
    // ==========================================================
    // MÓDULO DE AUTOMATIZACIÓN Y DASHBOARD
    // ==========================================================

    /**
     * Automatización Core: Cese en Cascada
     * 1. Vence periodos pasados.
     * 2. Finaliza convenios sin periodos activos.
     * 3. Cesa practicantes sin convenios vigentes.
     */
    public function ejecutarCeseAutomatico() {
        try {
            // Usamos transacciones para evitar inconsistencias si algo falla a la mitad
            $this->db->beginTransaction();

            // PASO 1: Caducar periodos que ya pasaron su fecha de fin
            $sqlPeriodos = "UPDATE PeriodosConvenio 
                            SET estado_periodo = 'Finalizado' 
                            WHERE fecha_fin < CURDATE() AND estado_periodo = 'Activo'";
            $stmt = $this->db->query($sqlPeriodos);
            $periodosCaducados = $stmt->rowCount();

            if ($periodosCaducados > 0) {
                // PASO 2: Caducar Convenios si TODOS sus periodos ya finalizaron
                $sqlConvenios = "UPDATE Convenios c
                                SET c.estado_convenio = 'Finalizado'
                                WHERE c.estado_convenio = 'Vigente' 
                                AND NOT EXISTS (
                                    SELECT 1 FROM PeriodosConvenio pc 
                                    WHERE pc.convenio_id = c.convenio_id AND pc.estado_periodo = 'Activo'
                                )";
                $this->db->query($sqlConvenios);

                // PASO 3: Cesar Practicantes si TODOS sus convenios ya finalizaron
                $sqlPracticantes = "UPDATE Practicantes p
                                    SET p.estado_general = 'Cesado'
                                    WHERE p.estado_general = 'Activo'
                                    AND NOT EXISTS (
                                        SELECT 1 FROM Convenios c 
                                        WHERE c.practicante_id = p.practicante_id AND c.estado_convenio = 'Vigente'
                                    )";
                $this->db->query($sqlPracticantes);
            }

            $this->db->commit();
            return $periodosCaducados; // Retorna cuántos contratos se vencieron hoy

        } catch (\PDOException $e) {
            $this->db->rollBack();
            error_log("Error Crítico en Automatización de Ceses: " . $e->getMessage());
            return 0;
        }
    }

    // --- Métodos de apoyo para los KPIs del Dashboard ---

    public function contarConveniosPorVencer($dias) {
        $sql = "SELECT COUNT(*) as total FROM PeriodosConvenio 
                WHERE estado_periodo = 'Activo' 
                AND fecha_fin BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL ? DAY)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$dias]);
        return $stmt->fetch()['total'] ?? 0;
    }

    public function getConveniosPorVencer($dias) {
        $sql = "SELECT p.nombres, p.apellidos, pc.fecha_fin, a.nombre as area 
                FROM PeriodosConvenio pc
                JOIN Convenios c ON pc.convenio_id = c.convenio_id
                JOIN Practicantes p ON c.practicante_id = p.practicante_id
                JOIN Areas a ON pc.area_id = a.area_id
                WHERE pc.estado_periodo = 'Activo' 
                AND pc.fecha_fin BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL ? DAY)
                ORDER BY pc.fecha_fin ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$dias]);
        return $stmt->fetchAll();
    }

    public function getCandidatosAceptados() {
        $sql = "SELECT pr.proceso_id, p.nombres, p.apellidos, pr.tipo_practica
                FROM ProcesosReclutamiento pr
                JOIN Practicantes p ON pr.practicante_id = p.practicante_id
                WHERE pr.estado_proceso = 'Aceptado'
                AND pr.proceso_id NOT IN (SELECT proceso_id FROM Convenios)";
        return $this->db->query($sql)->fetchAll();
    }

    public function getUltimosConveniosCreados($limite = 5) {
        $sql = "SELECT c.convenio_id, p.nombres, p.apellidos, c.tipo_practica, c.estado_convenio 
                FROM Convenios c
                JOIN Practicantes p ON c.practicante_id = p.practicante_id
                ORDER BY c.convenio_id DESC LIMIT ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$limite]);
        return $stmt->fetchAll();
    }
}