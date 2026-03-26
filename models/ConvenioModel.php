<?php
// models/ConvenioModel.php

class ConvenioModel extends Model {

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
            return $periodosCaducados; 

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

    // ==========================================================
    // MÓDULO DE REPORTES
    // ==========================================================

    public function obtenerAreasConPracticantes() {
        $sql = "SELECT DISTINCT a.area_id, a.nombre 
                FROM Areas a
                INNER JOIN PeriodosConvenio pc ON a.area_id = pc.area_id
                WHERE pc.estado_periodo = 'Activo'
                ORDER BY a.nombre ASC";
        
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    // ==========================================================
    // MÉTODOS CRUD ESTÁNDAR PARA CONVENIOS
    // ==========================================================

    public function getTodosLosConvenios() {
        $sql = "SELECT c.*, p.nombres, p.apellidos, p.dni
                FROM Convenios c
                JOIN Practicantes p ON c.practicante_id = p.practicante_id
                ORDER BY c.convenio_id DESC";
        return $this->db->query($sql)->fetchAll();
    }

    public function crearConvenio($data) {
        $sql = "INSERT INTO Convenios (practicante_id, proceso_id, tipo_practica, estado_convenio, estado_firma) 
                VALUES (?, ?, ?, 'Vigente', 'Pendiente')";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$data['practicante_id'], $data['proceso_id'], $data['tipo_practica']]);
        return $this->db->lastInsertId();
    }

    public function crearPeriodo($data) {
        $sql = "INSERT INTO PeriodosConvenio (convenio_id, fecha_inicio, fecha_fin, local_id, area_id, estado_periodo) 
                VALUES (?, ?, ?, ?, ?, 'Activo')";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$data['convenio_id'], $data['fecha_inicio'], $data['fecha_fin'], $data['local_id'], $data['area_id']]);
    }

    public function getConvenioDetalle($convenio_id) {
        $sql = "SELECT c.*, p.nombres, p.apellidos, p.dni 
                FROM Convenios c
                JOIN Practicantes p ON c.practicante_id = p.practicante_id
                WHERE c.convenio_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$convenio_id]);
        return $stmt->fetch();
    }

    public function getPeriodosPorConvenio($convenio_id) {
        $sql = "SELECT pc.*, a.nombre AS area_nombre, l.nombre AS local_nombre
                FROM PeriodosConvenio pc
                LEFT JOIN Areas a ON pc.area_id = a.area_id
                LEFT JOIN Locales l ON pc.local_id = l.local_id
                WHERE pc.convenio_id = ?
                ORDER BY pc.fecha_inicio DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$convenio_id]);
        return $stmt->fetchAll();
    }

    public function getAdendasPorConvenio($convenio_id) {
        $sql = "SELECT * FROM Adendas WHERE convenio_id = ? ORDER BY fecha_adenda DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$convenio_id]);
        return $stmt->fetchAll();
    }

    public function getCatalogos() {
        return [
            'locales' => $this->db->query("SELECT * FROM Locales ORDER BY nombre")->fetchAll(),
            'areas'   => $this->db->query("SELECT * FROM Areas ORDER BY nombre")->fetchAll()
        ];
    }
    
    public function actualizarEstadoFirma($convenio_id, $estado_firma) {
        $sql = "UPDATE Convenios SET estado_firma = ? WHERE convenio_id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$estado_firma, $convenio_id]);
    }
}
?>