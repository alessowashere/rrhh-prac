<?php
// models/ConvenioModel.php

class ConvenioModel extends Model {

    // ==========================================================
    // 1. FUNCIONES ORIGINALES (BÁSICAS Y LECTURA)
    // ==========================================================

    public function getTodosLosConvenios() {
        $sql = "SELECT c.*, p.nombres, p.apellidos, p.dni, a.nombre as area_nombre
                FROM Convenios c
                JOIN Practicantes p ON c.practicante_id = p.practicante_id
                LEFT JOIN PeriodosConvenio pc ON c.convenio_id = pc.convenio_id AND pc.estado_periodo = 'Activo'
                LEFT JOIN Areas a ON pc.area_id = a.area_id
                ORDER BY c.convenio_id DESC";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getConveniosVigentes() {
        $sql = "SELECT c.*, p.nombres, p.apellidos, p.dni, 
                       a.nombre as area_actual,
                       pc.fecha_inicio as fecha_inicio_actual,
                       pc.fecha_fin as fecha_fin_actual,
                       (SELECT COUNT(*) FROM Adendas ad WHERE ad.convenio_id = c.convenio_id) as num_adendas
                FROM Convenios c
                JOIN Practicantes p ON c.practicante_id = p.practicante_id
                LEFT JOIN PeriodosConvenio pc ON c.convenio_id = pc.convenio_id AND pc.estado_periodo = 'Activo'
                LEFT JOIN Areas a ON pc.area_id = a.area_id
                WHERE c.estado_convenio = 'Vigente'
                ORDER BY c.convenio_id DESC";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getConveniosFinalizados() {
        $sql = "SELECT c.*, p.nombres, p.apellidos, p.dni
                FROM Convenios c
                JOIN Practicantes p ON c.practicante_id = p.practicante_id
                WHERE c.estado_convenio = 'Finalizado'
                ORDER BY c.convenio_id DESC";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getConvenioDetalle($convenio_id) {
        $sql = "SELECT c.*, p.nombres, p.apellidos, p.dni, p.escuela_profesional_id, pr.puntuacion_final_entrevista
                FROM Convenios c
                JOIN Practicantes p ON c.practicante_id = p.practicante_id
                LEFT JOIN ProcesosReclutamiento pr ON c.proceso_id = pr.proceso_id
                WHERE c.convenio_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$convenio_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function crearConvenio($data) {
        $this->db->beginTransaction();
        try {
            $sql = "INSERT INTO Convenios (practicante_id, proceso_id, tipo_practica, estado_convenio, estado_firma) 
                    VALUES (?, ?, ?, 'Vigente', 'Pendiente')";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$data['practicante_id'], $data['proceso_id'], $data['tipo_practica']]);
            $convenio_id = $this->db->lastInsertId();

            $this->crearPeriodo([
                'convenio_id' => $convenio_id,
                'fecha_inicio' => $data['fecha_inicio'],
                'fecha_fin' => $data['fecha_fin'],
                'local_id' => $data['local_id'],
                'area_id' => $data['area_id']
            ]);

            $this->db->commit();
            return $convenio_id;
        } catch (\PDOException $e) {
            $this->db->rollBack();
            throw new \Exception("Error al crear convenio: " . $e->getMessage());
        }
    }

    public function crearPeriodo($data) {
        $sql = "INSERT INTO PeriodosConvenio (convenio_id, fecha_inicio, fecha_fin, local_id, area_id, estado_periodo) 
                VALUES (?, ?, ?, ?, ?, 'Activo')";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$data['convenio_id'], $data['fecha_inicio'], $data['fecha_fin'], $data['local_id'], $data['area_id']]);
    }

    public function actualizarEstadoFirma($convenio_id, $estado_firma) {
        $sql = "UPDATE Convenios SET estado_firma = ? WHERE convenio_id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$estado_firma, $convenio_id]);
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
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAdendasPorConvenio($convenio_id) {
        $sql = "SELECT * FROM Adendas WHERE convenio_id = ? ORDER BY fecha_adenda DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$convenio_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getCatalogos() {
        return [
            'locales' => $this->db->query("SELECT * FROM Locales ORDER BY nombre")->fetchAll(PDO::FETCH_ASSOC),
            'areas'   => $this->db->query("SELECT * FROM Areas ORDER BY nombre")->fetchAll(PDO::FETCH_ASSOC)
        ];
    }

    public function obtenerAreasConPracticantes() {
        $sql = "SELECT DISTINCT a.area_id, a.nombre 
                FROM Areas a
                INNER JOIN PeriodosConvenio pc ON a.area_id = pc.area_id
                WHERE pc.estado_periodo = 'Activo'
                ORDER BY a.nombre ASC";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    // ==========================================================
    // 2. FUNCIONES DE AUTOMATIZACIÓN Y DASHBOARD
    // ==========================================================

    public function ejecutarCeseAutomatico() {
        try {
            $this->db->beginTransaction();
            $sqlPeriodos = "UPDATE PeriodosConvenio SET estado_periodo = 'Finalizado' WHERE fecha_fin < CURDATE() AND estado_periodo = 'Activo'";
            $stmt = $this->db->query($sqlPeriodos);
            $periodosCaducados = $stmt->rowCount();

            if ($periodosCaducados > 0) {
                $sqlConvenios = "UPDATE Convenios c SET c.estado_convenio = 'Finalizado' WHERE c.estado_convenio = 'Vigente' AND NOT EXISTS (SELECT 1 FROM PeriodosConvenio pc WHERE pc.convenio_id = c.convenio_id AND pc.estado_periodo = 'Activo')";
                $this->db->query($sqlConvenios);

                $sqlPracticantes = "UPDATE Practicantes p SET p.estado_general = 'Cesado' WHERE p.estado_general = 'Activo' AND NOT EXISTS (SELECT 1 FROM Convenios c WHERE c.practicante_id = p.practicante_id AND c.estado_convenio = 'Vigente')";
                $this->db->query($sqlPracticantes);
            }
            $this->db->commit();
            return $periodosCaducados; 
        } catch (\PDOException $e) {
            $this->db->rollBack();
            return 0;
        }
    }

    public function contarConveniosPorVencer($dias) {
        $sql = "SELECT COUNT(*) as total FROM PeriodosConvenio 
                WHERE estado_periodo = 'Activo' AND fecha_fin BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL ? DAY)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$dias]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'] ?? 0;
    }

    public function getConveniosPorVencer($dias) {
        $sql = "SELECT c.convenio_id, p.nombres, p.apellidos, pc.fecha_fin, a.nombre as area 
                FROM PeriodosConvenio pc
                JOIN Convenios c ON pc.convenio_id = c.convenio_id
                JOIN Practicantes p ON c.practicante_id = p.practicante_id
                JOIN Areas a ON pc.area_id = a.area_id
                WHERE pc.estado_periodo = 'Activo' 
                AND pc.fecha_fin BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL ? DAY)
                ORDER BY pc.fecha_fin ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$dias]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getCandidatosAceptados() {
        $sql = "SELECT pr.proceso_id, p.nombres, p.apellidos, p.dni, pr.fecha_postulacion, pr.tipo_practica, p.practicante_id, ep.nombre as escuela_nombre
                FROM ProcesosReclutamiento pr
                JOIN Practicantes p ON pr.practicante_id = p.practicante_id
                LEFT JOIN EscuelasProfesionales ep ON p.escuela_profesional_id = ep.escuela_id
                WHERE pr.estado_proceso = 'Aceptado'
                AND pr.proceso_id NOT IN (SELECT proceso_id FROM Convenios WHERE proceso_id IS NOT NULL)";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getUltimosConveniosCreados($limite = 5) {
        $sql = "SELECT c.convenio_id, p.nombres, p.apellidos, c.tipo_practica, c.estado_convenio, a.nombre as area_nombre
                FROM Convenios c
                JOIN Practicantes p ON c.practicante_id = p.practicante_id
                LEFT JOIN PeriodosConvenio pc ON c.convenio_id = pc.convenio_id AND pc.estado_periodo = 'Activo'
                LEFT JOIN Areas a ON pc.area_id = a.area_id
                ORDER BY c.convenio_id DESC LIMIT ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(1, (int)$limite, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ==========================================================
    // 3. FUNCIONES NUEVAS (GESTIÓN AVANZADA Y SUBIDA DE PDFs)
    // ==========================================================

    public function getPracticanteSimple($practicante_id) {
        $sql = "SELECT p.practicante_id, p.dni, p.nombres, p.apellidos, ep.nombre as escuela_nombre
                FROM Practicantes p
                LEFT JOIN EscuelasProfesionales ep ON p.escuela_profesional_id = ep.escuela_id
                WHERE p.practicante_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$practicante_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function crearConvenioTransaccion($datosConvenio, $datosPeriodo) {
        $this->db->beginTransaction();
        try {
            $sql = "INSERT INTO Convenios (practicante_id, proceso_id, tipo_practica, estado_convenio, estado_firma) VALUES (?, ?, ?, ?, ?)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$datosConvenio['practicante_id'], $datosConvenio['proceso_id'], $datosConvenio['tipo_practica'], $datosConvenio['estado_convenio'], $datosConvenio['estado_firma']]);
            $convenio_id = $this->db->lastInsertId();

            $sqlPeriodo = "INSERT INTO PeriodosConvenio (convenio_id, fecha_inicio, fecha_fin, local_id, area_id, estado_periodo) VALUES (?, ?, ?, ?, ?, ?)";
            $stmtPeriodo = $this->db->prepare($sqlPeriodo);
            $stmtPeriodo->execute([$convenio_id, $datosPeriodo['fecha_inicio'], $datosPeriodo['fecha_fin'], $datosPeriodo['local_id'], $datosPeriodo['area_id'], $datosPeriodo['estado_periodo']]);

            $sqlPract = "UPDATE Practicantes SET estado_general = 'Activo' WHERE practicante_id = ?";
            $this->db->prepare($sqlPract)->execute([$datosConvenio['practicante_id']]);

            $this->db->commit();
            return $convenio_id;
        } catch (\PDOException $e) {
            $this->db->rollBack();
            throw new \Exception("Error al crear convenio: " . $e->getMessage());
        }
    }

    public function getDetalleConvenio($convenio_id) {
        $sql = "SELECT c.*, p.nombres, p.apellidos, p.dni, ep.nombre as escuela_nombre, pr.puntuacion_final_entrevista
                FROM Convenios c
                JOIN Practicantes p ON c.practicante_id = p.practicante_id
                LEFT JOIN EscuelasProfesionales ep ON p.escuela_profesional_id = ep.escuela_id
                LEFT JOIN ProcesosReclutamiento pr ON c.proceso_id = pr.proceso_id
                WHERE c.convenio_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$convenio_id]);
        $convenio = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($convenio) {
            $sqlPeriodos = "SELECT pc.*, a.nombre as area_nombre, l.nombre as local_nombre FROM PeriodosConvenio pc LEFT JOIN Areas a ON pc.area_id = a.area_id LEFT JOIN Locales l ON pc.local_id = l.local_id WHERE pc.convenio_id = ? ORDER BY pc.fecha_inicio DESC";
            $stmtPeriodos = $this->db->prepare($sqlPeriodos);
            $stmtPeriodos->execute([$convenio_id]);
            $convenio['periodos'] = $stmtPeriodos->fetchAll(PDO::FETCH_ASSOC);
            
            $sqlAdendas = "SELECT * FROM Adendas WHERE convenio_id = ? ORDER BY fecha_adenda DESC";
            $stmtAdendas = $this->db->prepare($sqlAdendas);
            $stmtAdendas->execute([$convenio_id]);
            $convenio['adendas'] = $stmtAdendas->fetchAll(PDO::FETCH_ASSOC);
        }
        return $convenio;
    }

    // --- FUNCIONES QUE GUARDAN LAS URLS DE LOS DOCUMENTOS ---

    public function actualizarConvenioFirmado($convenio_id, $url_relativa) {
        try {
            $sql = "UPDATE Convenios SET estado_firma = 'Firmado', documento_convenio_url = ? WHERE convenio_id = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$url_relativa, $convenio_id]);
        } catch (\PDOException $e) {
            error_log("Error al actualizar firma: " . $e->getMessage());
            return false;
        }
    }

    public function ampliarConvenio($datosAdenda, $nueva_fecha_fin) {
        $this->db->beginTransaction();
        try {
            $sqlAdenda = "INSERT INTO Adendas (convenio_id, tipo_accion, fecha_adenda, descripcion, documento_adenda_url) VALUES (?, ?, ?, ?, ?)";
            $stmt = $this->db->prepare($sqlAdenda);
            $stmt->execute([$datosAdenda['convenio_id'], $datosAdenda['tipo_accion'], $datosAdenda['fecha_adenda'], $datosAdenda['descripcion'], $datosAdenda['documento_adenda_url'] ?? null]);
            
            $sqlUpdate = "UPDATE PeriodosConvenio SET fecha_fin = ? WHERE convenio_id = ? AND estado_periodo = 'Activo'";
            $stmtUpdate = $this->db->prepare($sqlUpdate);
            $stmtUpdate->execute([$nueva_fecha_fin, $datosAdenda['convenio_id']]);

            $this->db->commit();
            return true;
        } catch (\PDOException $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function agregarNuevoPeriodo($datosPeriodo, $datosAdenda) {
        $this->db->beginTransaction();
        try {
            $sqlClose = "UPDATE PeriodosConvenio SET estado_periodo = 'Finalizado' WHERE convenio_id = ? AND estado_periodo = 'Activo'";
            $this->db->prepare($sqlClose)->execute([$datosPeriodo['convenio_id']]);

            $sqlAdenda = "INSERT INTO Adendas (convenio_id, tipo_accion, fecha_adenda, descripcion, documento_adenda_url) VALUES (?, ?, ?, ?, ?)";
            $this->db->prepare($sqlAdenda)->execute([$datosAdenda['convenio_id'], $datosAdenda['tipo_accion'], $datosAdenda['fecha_adenda'], $datosAdenda['descripcion'], $datosAdenda['documento_adenda_url'] ?? null]);

            $sqlPeriodo = "INSERT INTO PeriodosConvenio (convenio_id, fecha_inicio, fecha_fin, local_id, area_id, estado_periodo) VALUES (?, ?, ?, ?, ?, ?)";
            $this->db->prepare($sqlPeriodo)->execute([$datosPeriodo['convenio_id'], $datosPeriodo['fecha_inicio'], $datosPeriodo['fecha_fin'], $datosPeriodo['local_id'], $datosPeriodo['area_id'], $datosPeriodo['estado_periodo']]);

            $this->db->commit();
            return true;
        } catch (\PDOException $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function finalizarConvenio($convenio_id, $practicante_id, $nuevo_estado, $descripcion, $url_relativa) {
        $this->db->beginTransaction();
        try {
            $sql = "UPDATE Convenios SET estado_convenio = 'Finalizado' WHERE convenio_id = ?";
            $this->db->prepare($sql)->execute([$convenio_id]);

            $sqlPer = "UPDATE PeriodosConvenio SET estado_periodo = 'Finalizado' WHERE convenio_id = ?";
            $this->db->prepare($sqlPer)->execute([$convenio_id]);

            $sqlPrac = "UPDATE Practicantes SET estado_general = 'Cesado' WHERE practicante_id = ?";
            $this->db->prepare($sqlPrac)->execute([$practicante_id]);

            $sqlAdenda = "INSERT INTO Adendas (convenio_id, tipo_accion, fecha_adenda, descripcion, documento_adenda_url) VALUES (?, ?, CURDATE(), ?, ?)";
            $this->db->prepare($sqlAdenda)->execute([$convenio_id, 'CESE_' . strtoupper($nuevo_estado), $descripcion, $url_relativa]);

            $this->db->commit();
            return true;
        } catch (\PDOException $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function registrarSuspension($datosNuevoPeriodo, $datosAdenda, $fecha_suspension) {
        $this->db->beginTransaction();
        try {
            $sqlClose = "UPDATE PeriodosConvenio SET fecha_fin = ?, estado_periodo = 'Finalizado' WHERE convenio_id = ? AND estado_periodo = 'Activo'";
            $this->db->prepare($sqlClose)->execute([$fecha_suspension, $datosNuevoPeriodo['convenio_id']]);

            $sqlAdenda = "INSERT INTO Adendas (convenio_id, tipo_accion, fecha_adenda, descripcion, documento_adenda_url) VALUES (?, ?, ?, ?, ?)";
            $this->db->prepare($sqlAdenda)->execute([$datosAdenda['convenio_id'], $datosAdenda['tipo_accion'], $datosAdenda['fecha_adenda'], $datosAdenda['descripcion'], $datosAdenda['documento_adenda_url'] ?? null]);

            $sqlPeriodo = "INSERT INTO PeriodosConvenio (convenio_id, fecha_inicio, fecha_fin, local_id, area_id, estado_periodo) VALUES (?, ?, ?, ?, ?, ?)";
            $this->db->prepare($sqlPeriodo)->execute([$datosNuevoPeriodo['convenio_id'], $datosNuevoPeriodo['fecha_inicio'], $datosNuevoPeriodo['fecha_fin'], $datosNuevoPeriodo['local_id'], $datosNuevoPeriodo['area_id'], $datosNuevoPeriodo['estado_periodo']]);

            $this->db->commit();
            return true;
        } catch (\PDOException $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
}