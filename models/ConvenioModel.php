<?php
// models/ConvenioModel.php

class ConvenioModel extends Model {

    // ==========================================================
    // 1. MÉTODOS CRUD PRINCIPALES (LOS QUE FALTABAN)
    // ==========================================================

    /**
     * Obtiene TODOS los convenios registrados (Histórico)
     */
    public function getTodosLosConvenios() {
        $sql = "SELECT c.*, p.nombres, p.apellidos, p.dni, a.nombre as area_nombre
                FROM Convenios c
                JOIN Practicantes p ON c.practicante_id = p.practicante_id
                LEFT JOIN PeriodosConvenio pc ON c.convenio_id = pc.convenio_id AND pc.estado_periodo = 'Activo'
                LEFT JOIN Areas a ON pc.area_id = a.area_id
                ORDER BY c.convenio_id DESC";
        return $this->db->query($sql)->fetchAll();
    }

    /**
     * Obtiene SOLO los convenios actualmente vigentes
     * Soluciona el Error Fatal de la línea 22
     */
    public function getConveniosVigentes() {
        // CORRECCIÓN: Se añadieron los alias exactos (fecha_inicio_actual, fecha_fin_actual)
        // que la vista necesita para imprimir las fechas correctamente.
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

    /**
     * Obtiene SOLO los convenios finalizados/cesados
     */
    public function getConveniosFinalizados() {
        $sql = "SELECT c.*, p.nombres, p.apellidos, p.dni
                FROM Convenios c
                JOIN Practicantes p ON c.practicante_id = p.practicante_id
                WHERE c.estado_convenio = 'Finalizado'
                ORDER BY c.convenio_id DESC";
        return $this->db->query($sql)->fetchAll();
    }

    public function getConvenioDetalle($convenio_id) {
        $sql = "SELECT c.*, p.nombres, p.apellidos, p.dni, p.escuela_profesional_id, pr.puntuacion_final_entrevista
                FROM Convenios c
                JOIN Practicantes p ON c.practicante_id = p.practicante_id
                LEFT JOIN ProcesosReclutamiento pr ON c.proceso_id = pr.proceso_id
                WHERE c.convenio_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$convenio_id]);
        return $stmt->fetch();
    }

    // ==========================================================
    // 2. CREACIÓN Y FORMALIZACIÓN
    // ==========================================================

    public function crearConvenio($data) {
        $this->db->beginTransaction();
        try {
            // 1. Insertar el Convenio
            $sql = "INSERT INTO Convenios (practicante_id, proceso_id, tipo_practica, estado_convenio, estado_firma) 
                    VALUES (?, ?, ?, 'Vigente', 'Pendiente')";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$data['practicante_id'], $data['proceso_id'], $data['tipo_practica']]);
            $convenio_id = $this->db->lastInsertId();

            // 2. Insertar el Periodo Inicial
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

    // ==========================================================
    // 3. CONSULTAS SECUNDARIAS (PERIODOS, ADENDAS, CATÁLOGOS)
    // ==========================================================

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

    // ==========================================================
    // 4. MÓDULO DE REPORTES
    // ==========================================================

    public function obtenerAreasConPracticantes() {
        $sql = "SELECT DISTINCT a.area_id, a.nombre 
                FROM Areas a
                INNER JOIN PeriodosConvenio pc ON a.area_id = pc.area_id
                WHERE pc.estado_periodo = 'Activo'
                ORDER BY a.nombre ASC";
        return $this->db->query($sql)->fetchAll();
    }

    // ==========================================================
    // 5. MÓDULO DE AUTOMATIZACIÓN Y DASHBOARD
    // ==========================================================

    public function ejecutarCeseAutomatico() {
        try {
            $this->db->beginTransaction();

            $sqlPeriodos = "UPDATE PeriodosConvenio 
                            SET estado_periodo = 'Finalizado' 
                            WHERE fecha_fin < CURDATE() AND estado_periodo = 'Activo'";
            $stmt = $this->db->query($sqlPeriodos);
            $periodosCaducados = $stmt->rowCount();

            if ($periodosCaducados > 0) {
                $sqlConvenios = "UPDATE Convenios c
                                SET c.estado_convenio = 'Finalizado'
                                WHERE c.estado_convenio = 'Vigente' 
                                AND NOT EXISTS (
                                    SELECT 1 FROM PeriodosConvenio pc 
                                    WHERE pc.convenio_id = c.convenio_id AND pc.estado_periodo = 'Activo'
                                )";
                $this->db->query($sqlConvenios);

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
            error_log("Error en Automatización de Ceses: " . $e->getMessage());
            return 0;
        }
    }

    public function contarConveniosPorVencer($dias) {
        $sql = "SELECT COUNT(*) as total FROM PeriodosConvenio 
                WHERE estado_periodo = 'Activo' 
                AND fecha_fin BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL ? DAY)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$dias]);
        return $stmt->fetch()['total'] ?? 0;
    }

    public function getConveniosPorVencer($dias) {
        // CORRECCIÓN: Se agregó "c.convenio_id" a la consulta para que el botón de Gestionar funcione.
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
        $sql = "SELECT pr.proceso_id, p.nombres, p.apellidos, pr.tipo_practica
                FROM ProcesosReclutamiento pr
                JOIN Practicantes p ON pr.practicante_id = p.practicante_id
                WHERE pr.estado_proceso = 'Aceptado'
                AND pr.proceso_id NOT IN (SELECT proceso_id FROM Convenios)";
        return $this->db->query($sql)->fetchAll();
    }

    public function getUltimosConveniosCreados($limite = 5) {
        // CORRECCIÓN: Se agregó el LEFT JOIN con PeriodosConvenio y Areas
        // para poder extraer "a.nombre as area_nombre" que necesita el Dashboard.
        $sql = "SELECT c.convenio_id, p.nombres, p.apellidos, c.tipo_practica, c.estado_convenio,
                       a.nombre as area_nombre
                FROM Convenios c
                JOIN Practicantes p ON c.practicante_id = p.practicante_id
                LEFT JOIN PeriodosConvenio pc ON c.convenio_id = pc.convenio_id AND pc.estado_periodo = 'Activo'
                LEFT JOIN Areas a ON pc.area_id = a.area_id
                ORDER BY c.convenio_id DESC LIMIT ?";
                
        $stmt = $this->db->prepare($sql);
        // Usamos bindValue para asegurarnos de que el límite pase como entero (necesario en PDO)
        $stmt->bindValue(1, (int)$limite, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>