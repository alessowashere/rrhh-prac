<?php
// models/ConvenioModel.php

class ConvenioModel {
    
    private $pdo;

    public function __construct() {
        $this->pdo = Database::getInstance()->getConnection();
    }

    /**
     * Obtiene los candidatos 'Aceptados' que AÚN NO tienen un convenio.
     */
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

    /**
     * Obtiene convenios 'Vigentes' con el área, fechas actuales y estado de firma.
     */
    public function getConveniosVigentes() {
        $sql = "SELECT 
                    c.convenio_id, c.tipo_practica, c.estado_convenio,
                    c.estado_firma, -- NUEVO: Para saber si está firmado
                    p.practicante_id, p.dni, p.nombres, p.apellidos,
                    
                    pc_activo.fecha_inicio AS fecha_inicio_actual,
                    pc_activo.fecha_fin AS fecha_fin_actual,
                    a.nombre AS area_actual,
                    
                    (SELECT COUNT(ad.adenda_id) FROM Adendas ad WHERE ad.convenio_id = c.convenio_id) AS num_adendas

                FROM Convenios c
                JOIN Practicantes p ON c.practicante_id = p.practicante_id
                
                LEFT JOIN PeriodosConvenio pc_activo ON pc_activo.convenio_id = c.convenio_id 
                                                     AND pc_activo.estado_periodo = 'Activo'
                LEFT JOIN Areas a ON pc_activo.area_id = a.area_id
                
                WHERE c.estado_convenio = 'Vigente'
                ORDER BY p.apellidos ASC";
        
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll();
    }
    
    /**
     * Obtiene los datos de un practicante para el formulario de 'crear'.
     */
    public function getPracticanteSimple(int $practicante_id) {
         $sql = "SELECT p.practicante_id, p.dni, p.nombres, p.apellidos, ep.nombre AS escuela_nombre
                 FROM Practicantes p
                 LEFT JOIN EscuelasProfesionales ep ON p.escuela_profesional_id = ep.escuela_id
                 WHERE p.practicante_id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$practicante_id]);
        return $stmt->fetch();
    }

    /**
     * Obtiene catálogos de Locales y Áreas para los dropdowns.
     */
    public function getCatalogos() {
        $sql_loc = "SELECT local_id, nombre FROM Locales ORDER BY nombre";
        $sql_are = "SELECT area_id, nombre FROM Areas ORDER BY nombre";
        
        return [
            'locales' => $this->pdo->query($sql_loc)->fetchAll(),
            'areas' => $this->pdo->query($sql_are)->fetchAll()
        ];
    }

    /**
     * [TRANSACCIÓN] Crea el Convenio (datos), su 1er Período y actualiza al Practicante.
     * El convenio nace con estado_firma = 'Pendiente'.
     */
    public function crearConvenioTransaccion(array $datosConvenio, array $datosPeriodo) {
        $this->pdo->beginTransaction();
        try {
            // 1. Insertar el Convenio
            // Se añade 'estado_firma'
            $sql_conv = "INSERT INTO Convenios (practicante_id, proceso_id, tipo_practica, estado_convenio, induccion_completada, estado_firma)
                         VALUES (?, ?, ?, ?, FALSE, ?)";
            $stmt_conv = $this->pdo->prepare($sql_conv);
            $stmt_conv->execute([
                $datosConvenio['practicante_id'],
                $datosConvenio['proceso_id'],
                $datosConvenio['tipo_practica'],
                $datosConvenio['estado_convenio'],
                $datosConvenio['estado_firma'] // 'Pendiente'
            ]);
            
            $convenio_id = $this->pdo->lastInsertId();

            // 2. Insertar el primer Período
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

            // 3. Actualizar al Practicante a 'Activo'
            $sql_prac = "UPDATE Practicantes SET estado_general = 'Activo' WHERE practicante_id = ?";
            $stmt_prac = $this->pdo->prepare($sql_prac);
            $stmt_prac->execute([$datosConvenio['practicante_id']]);

            $this->pdo->commit();
            return $convenio_id;

        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw new Exception("Error en la transacción: " . $e->getMessage());
        }
    }
    
    /**
     * Obtiene todos los detalles de un convenio (para la pág. 'gestionar').
     */
    public function getDetalleConvenio(int $convenio_id) {
        $sql = "SELECT c.*, p.dni, p.nombres, p.apellidos
                FROM Convenios c
                JOIN Practicantes p ON c.practicante_id = p.practicante_id
                WHERE c.convenio_id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$convenio_id]);
        $convenio = $stmt->fetch();
        
        if (!$convenio) return false;
        
        // Añadir períodos
        $sql_per = "SELECT pc.*, a.nombre AS area_nombre, l.nombre AS local_nombre
                    FROM PeriodosConvenio pc
                    LEFT JOIN Areas a ON pc.area_id = a.area_id
                    LEFT JOIN Locales l ON pc.local_id = l.local_id
                    WHERE pc.convenio_id = ? ORDER BY pc.fecha_inicio DESC";
        $stmt_per = $this->pdo->prepare($sql_per);
        $stmt_per->execute([$convenio_id]);
        $convenio['periodos'] = $stmt_per->fetchAll();
        
        // Añadir adendas (AHORA INCLUYE URL DEL DOCUMENTO)
        $sql_ad = "SELECT * FROM Adendas WHERE convenio_id = ? ORDER BY fecha_adenda DESC";
        $stmt_ad = $this->pdo->prepare($sql_ad);
        $stmt_ad->execute([$convenio_id]);
        $convenio['adendas'] = $stmt_ad->fetchAll();
        
        return $convenio;
    }
    
    /**
     * Agrega una nueva adenda (ahora incluye la URL del documento).
     */
    public function agregarAdenda(array $datos) {
        $sql = "INSERT INTO Adendas (convenio_id, tipo_accion, fecha_adenda, descripcion, documento_adenda_url)
                VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            $datos['convenio_id'],
            $datos['tipo_accion'],
            $datos['fecha_adenda'],
            $datos['descripcion'],
            $datos['documento_adenda_url'] // <-- NUEVO
        ]);
    }

    /**
     * [NUEVO] Actualiza el convenio principal como "Firmado" y guarda la URL del PDF.
     */
    public function actualizarConvenioFirmado(int $convenio_id, string $url_documento) {
        $sql = "UPDATE Convenios 
                SET estado_firma = 'Firmado', documento_convenio_url = ?
                WHERE convenio_id = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$url_documento, $convenio_id]);
    }

    /**
     * [TRANSACCIÓN] Amplía la fecha de fin del período activo y registra la adenda (con documento).
     */
    public function ampliarConvenio(array $datosAdenda, string $nueva_fecha_fin) {
        $this->pdo->beginTransaction();
        try {
            // 1. Actualizar el período 'Activo'
            $sql_update = "UPDATE PeriodosConvenio 
                           SET fecha_fin = ?
                           WHERE convenio_id = ? AND estado_periodo = 'Activo'";
            $stmt_update = $this->pdo->prepare($sql_update);
            $stmt_update->execute([ $nueva_fecha_fin, $datosAdenda['convenio_id'] ]);

            // 2. Insertar el registro de la Adenda (que ya incluye la URL del doc)
            $this->agregarAdenda($datosAdenda);

            $this->pdo->commit();
            
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw new Exception("Error al ampliar convenio: " . $e->getMessage());
        }
    }
    
    /**
     * [TRANSACCIÓN] Agrega un nuevo período (Corte/Reubicación) y finaliza el anterior.
     * AHORA TAMBIÉN REGISTRA LA ADENDA con su documento.
     */
    public function agregarNuevoPeriodo(array $datosPeriodo, array $datosAdenda) {
        $this->pdo->beginTransaction();
        try {
            // 1. Finalizar el período 'Activo' o 'Futuro' actual
            $sql_update = "UPDATE PeriodosConvenio 
                           SET estado_periodo = 'Finalizado', fecha_fin = ?
                           WHERE convenio_id = ? AND estado_periodo IN ('Activo', 'Futuro')";
            $stmt_update = $this->pdo->prepare($sql_update);
            $fecha_fin_anterior = date('Y-m-d', strtotime($datosPeriodo['fecha_inicio'] . ' -1 day'));
            $stmt_update->execute([ $fecha_fin_anterior, $datosPeriodo['convenio_id'] ]);

            // 2. Insertar el nuevo Período
            $sql_insert = "INSERT INTO PeriodosConvenio (convenio_id, fecha_inicio, fecha_fin, local_id, area_id, estado_periodo)
                           VALUES (?, ?, ?, ?, ?, ?)";
            $stmt_insert = $this->pdo->prepare($sql_insert);
            $stmt_insert->execute([
                $datosPeriodo['convenio_id'],
                $datosPeriodo['fecha_inicio'],
                $datosPeriodo['fecha_fin'],
                $datosPeriodo['local_id'],
                $datosPeriodo['area_id'],
                $datosPeriodo['estado_periodo']
            ]);
            
            // 3. Insertar el registro de la Adenda (que ya incluye la URL del doc)
            $this->agregarAdenda($datosAdenda);
            
            $this->pdo->commit();
            
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw new Exception("Error al agregar período: " . $e->getMessage());
        }
    }
    
    /**
     * [TRANSACCIÓN] Finaliza/Cancela un convenio (Renuncia o Cancelado).
     * Actualiza Convenio, Practicante, Período y registra el documento.
     */
    public function finalizarConvenio(int $convenio_id, int $practicante_id, string $estado_convenio, string $descripcion, ?string $documento_url) {
        $this->pdo->beginTransaction();
        try {
            // 1. Actualizar estado del Convenio
            $sql_conv = "UPDATE Convenios SET estado_convenio = ? WHERE convenio_id = ?";
            $this->pdo->prepare($sql_conv)->execute([$estado_convenio, $convenio_id]);
            
            // 2. Actualizar estado general del Practicante a 'Cesado'
            $sql_prac = "UPDATE Practicantes SET estado_general = 'Cesado' WHERE practicante_id = ?";
            $this->pdo->prepare($sql_prac)->execute([$practicante_id]);
            
            // 3. Finalizar el período 'Activo' (si existe)
            $sql_per = "UPDATE PeriodosConvenio SET estado_periodo = 'Finalizado', fecha_fin = CURDATE()
                        WHERE convenio_id = ? AND estado_periodo = 'Activo'";
            $this->pdo->prepare($sql_per)->execute([$convenio_id]);

            // 4. [NUEVO] Registrar la acción como una Adenda para historial
            // (Si es 'Renuncia' debe tener URL, si es 'Cancelado' puede ser solo descripción)
            $sql_ad = "INSERT INTO Adendas (convenio_id, tipo_accion, fecha_adenda, descripcion, documento_adenda_url)
                       VALUES (?, ?, CURDATE(), ?, ?)";
            $this->pdo->prepare($sql_ad)->execute([
                $convenio_id,
                $estado_convenio, // 'Renuncia' o 'Cancelado'
                $descripcion,
                $documento_url
            ]);

            $this->pdo->commit();
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw new Exception("Error al finalizar el convenio: " . $e->getMessage());
        }
    }
    
    /**
     * Cuenta convenios 'Activos' cuyo período 'Activo'
     * vence en los próximos X días.
     */
    public function contarConveniosPorVencer(int $dias = 30) {
        $sql = "SELECT COUNT(DISTINCT c.convenio_id) AS total 
                FROM Convenios c
                JOIN PeriodosConvenio pc ON c.convenio_id = pc.convenio_id
                WHERE c.estado_convenio = 'Vigente'
                  AND pc.estado_periodo = 'Activo'
                  AND pc.fecha_fin BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL ? DAY)";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$dias]);
        return $stmt->fetch()['total'] ?? 0;
    }
    
    /**
     * Obtiene la LISTA de convenios que vencen pronto.
     */
    public function getConveniosPorVencer(int $dias = 30) {
        $sql = "SELECT DISTINCT p.practicante_id, p.nombres, p.apellidos, pc.fecha_fin, c.convenio_id
                FROM Convenios c
                JOIN PeriodosConvenio pc ON c.convenio_id = pc.convenio_id
                JOIN Practicantes p ON c.practicante_id = p.practicante_id
                WHERE c.estado_convenio = 'Vigente'
                  AND pc.estado_periodo = 'Activo'
                  AND pc.fecha_fin BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL ? DAY)
                ORDER BY pc.fecha_fin ASC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$dias]);
        return $stmt->fetchAll();
    }
    
    /**
     * Obtiene los últimos convenios creados (para 'Actividad Reciente').
     */
    public function getUltimosConveniosCreados(int $limite = 5) {
        $sql = "SELECT c.convenio_id, c.tipo_practica, p.practicante_id, p.nombres, p.apellidos, a.nombre AS area_nombre
                FROM Convenios c
                JOIN Practicantes p ON c.practicante_id = p.practicante_id
                LEFT JOIN PeriodosConvenio pc ON pc.convenio_id = c.convenio_id
                                             AND pc.periodo_id = (SELECT MAX(periodo_id) 
                                                                  FROM PeriodosConvenio 
                                                                  WHERE convenio_id = c.convenio_id)
                LEFT JOIN Areas a ON pc.area_id = a.area_id
                ORDER BY c.convenio_id DESC
                LIMIT ?";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$limite]);
        return $stmt->fetchAll();
    }
}