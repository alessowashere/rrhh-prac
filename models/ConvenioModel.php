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
     * Obtiene convenios 'Vigentes'. Incluye datos del período activo.
     */
    public function getConveniosVigentes() {
        // Nota: COALESCE se usa para mostrar 'N/A' si no hay período activo (caso raro)
        $sql = "SELECT 
                    c.convenio_id, c.tipo_practica, c.estado_convenio, c.estado_firma,
                    p.practicante_id, p.dni, p.nombres, p.apellidos,
                    
                    COALESCE(pc_activo.fecha_inicio, 'N/A') AS fecha_inicio_actual,
                    COALESCE(pc_activo.fecha_fin, 'N/A') AS fecha_fin_actual,
                    COALESCE(a.nombre, 'N/A') AS area_actual,
                    
                    (SELECT COUNT(ad.adenda_id) FROM Adendas ad WHERE ad.convenio_id = c.convenio_id) AS num_adendas

                FROM Convenios c
                JOIN Practicantes p ON c.practicante_id = p.practicante_id
                
                LEFT JOIN PeriodosConvenio pc_activo ON pc_activo.convenio_id = c.convenio_id 
                                                     AND pc_activo.estado_periodo = 'Activo'
                LEFT JOIN Areas a ON pc_activo.area_id = a.area_id
                
                WHERE c.estado_convenio = 'Vigente'
                ORDER BY c.estado_firma ASC, p.apellidos ASC"; // Prioriza pendientes de firma
        
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll();
    }
    
    /**
     * Obtiene los datos básicos de un practicante.
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
     * Obtiene catálogos de Locales y Áreas.
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
     */
    public function crearConvenioTransaccion(array $datosConvenio, array $datosPeriodo) {
        $this->pdo->beginTransaction();
        try {
            // 1. Insertar el Convenio (con estado_firma)
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
            throw new Exception("Error en transacción crearConvenio: " . $e->getMessage());
        }
    }
    
    /**
     * Obtiene todos los detalles de un convenio.
     */
    public function getDetalleConvenio(int $convenio_id) {
        // Obtener datos del convenio y practicante
        $sql_conv = "SELECT c.*, p.dni, p.nombres, p.apellidos
                     FROM Convenios c
                     JOIN Practicantes p ON c.practicante_id = p.practicante_id
                     WHERE c.convenio_id = ?";
        $stmt_conv = $this->pdo->prepare($sql_conv);
        $stmt_conv->execute([$convenio_id]);
        $convenio = $stmt_conv->fetch(PDO::FETCH_ASSOC); // Usar FETCH_ASSOC
        
        if (!$convenio) return false;
        
        // Obtener períodos (ordenados del más reciente al más antiguo)
        $sql_per = "SELECT pc.*, a.nombre AS area_nombre, l.nombre AS local_nombre
                    FROM PeriodosConvenio pc
                    LEFT JOIN Areas a ON pc.area_id = a.area_id
                    LEFT JOIN Locales l ON pc.local_id = l.local_id
                    WHERE pc.convenio_id = ? ORDER BY pc.fecha_inicio DESC";
        $stmt_per = $this->pdo->prepare($sql_per);
        $stmt_per->execute([$convenio_id]);
        $convenio['periodos'] = $stmt_per->fetchAll(PDO::FETCH_ASSOC); // Usar FETCH_ASSOC
        
        // Obtener adendas (ordenadas de la más reciente a la más antigua)
        $sql_ad = "SELECT * FROM Adendas WHERE convenio_id = ? ORDER BY fecha_adenda DESC, adenda_id DESC";
        $stmt_ad = $this->pdo->prepare($sql_ad);
        $stmt_ad->execute([$convenio_id]);
        $convenio['adendas'] = $stmt_ad->fetchAll(PDO::FETCH_ASSOC); // Usar FETCH_ASSOC
        
        return $convenio;
    }
    
    /**
     * Agrega una nueva adenda (incluye la URL del documento).
     * Devuelve true/false indicando éxito.
     */
    private function agregarAdenda(array $datos) {
        $sql = "INSERT INTO Adendas (convenio_id, tipo_accion, fecha_adenda, descripcion, documento_adenda_url)
                VALUES (:convenio_id, :tipo_accion, :fecha_adenda, :descripcion, :documento_url)";
        $stmt = $this->pdo->prepare($sql);
        try {
            return $stmt->execute([
                ':convenio_id' => $datos['convenio_id'],
                ':tipo_accion' => $datos['tipo_accion'],
                ':fecha_adenda' => $datos['fecha_adenda'],
                ':descripcion' => $datos['descripcion'],
                ':documento_url' => $datos['documento_adenda_url'] 
            ]);
        } catch (PDOException $e) {
             error_log("Error al agregar adenda: " . $e->getMessage());
             return false; // Devuelve false en caso de error
        }
    }

    /**
     * Actualiza el convenio principal como "Firmado" y guarda la URL del PDF.
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
            if (!$stmt_update->execute([ $nueva_fecha_fin, $datosAdenda['convenio_id'] ])) {
                throw new Exception("No se pudo actualizar el período activo.");
            }
             // Verificar si alguna fila fue afectada (si no existe período activo, fallará)
             if ($stmt_update->rowCount() === 0) {
                 throw new Exception("No se encontró un período activo para actualizar.");
             }

            // 2. Insertar el registro de la Adenda
            if (!$this->agregarAdenda($datosAdenda)) {
                 throw new Exception("No se pudo registrar la adenda.");
            }

            $this->pdo->commit();
            return true; // Éxito
            
        } catch (Exception $e) {
            $this->pdo->rollBack();
            // Propagar la excepción para que el controlador la capture
            throw new Exception("Error en transacción ampliarConvenio: " . $e->getMessage());
        }
    }
    
    /**
     * [TRANSACCIÓN] Agrega un nuevo período (Corte/Reubicación) y finaliza el anterior.
     * Registra la adenda con su documento.
     */
    public function agregarNuevoPeriodo(array $datosPeriodo, array $datosAdenda) {
        $this->pdo->beginTransaction();
        try {
            // 1. Finalizar el período 'Activo' o 'Futuro' actual
            $sql_update = "UPDATE PeriodosConvenio 
                           SET estado_periodo = 'Finalizado', fecha_fin = ?
                           WHERE convenio_id = ? AND estado_periodo IN ('Activo', 'Futuro')";
            $stmt_update = $this->pdo->prepare($sql_update);
            // El período anterior termina un día antes de que empiece el nuevo
            $fecha_fin_anterior = date('Y-m-d', strtotime($datosPeriodo['fecha_inicio'] . ' -1 day'));
            if (!$stmt_update->execute([ $fecha_fin_anterior, $datosPeriodo['convenio_id'] ])) {
                 throw new Exception("No se pudo finalizar el período anterior.");
            }
             // Es posible que no haya período activo/futuro si el convenio ya terminó, pero no debería llamarse a esta función entonces.
             // if ($stmt_update->rowCount() === 0) {
             //     throw new Exception("No se encontró período activo/futuro para finalizar.");
             // }

            // 2. Insertar el nuevo Período
            $sql_insert = "INSERT INTO PeriodosConvenio (convenio_id, fecha_inicio, fecha_fin, local_id, area_id, estado_periodo)
                           VALUES (?, ?, ?, ?, ?, ?)";
            $stmt_insert = $this->pdo->prepare($sql_insert);
            if (!$stmt_insert->execute([
                $datosPeriodo['convenio_id'],
                $datosPeriodo['fecha_inicio'],
                $datosPeriodo['fecha_fin'],
                $datosPeriodo['local_id'],
                $datosPeriodo['area_id'],
                $datosPeriodo['estado_periodo'] // 'Activo' o 'Futuro'
            ])) {
                 throw new Exception("No se pudo insertar el nuevo período.");
            }
            
            // 3. Insertar el registro de la Adenda
            if (!$this->agregarAdenda($datosAdenda)) {
                 throw new Exception("No se pudo registrar la adenda para el nuevo período.");
            }
            
            $this->pdo->commit();
            return true; // Éxito
            
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw new Exception("Error en transacción agregarNuevoPeriodo: " . $e->getMessage());
        }
    }
    
    /**
     * [TRANSACCIÓN] Finaliza/Cancela un convenio (Renuncia o Cancelado).
     * Actualiza Convenio, Practicante, Período y registra la adenda de cese.
     */
    public function finalizarConvenio(int $convenio_id, int $practicante_id, string $estado_convenio, string $descripcion, ?string $documento_url) {
        // Validar estado
        if (!in_array($estado_convenio, ['Renuncia', 'Cancelado'])) {
            throw new Exception("Estado de finalización inválido: $estado_convenio");
        }

        $this->pdo->beginTransaction();
        try {
            // 1. Actualizar estado del Convenio
            $sql_conv = "UPDATE Convenios SET estado_convenio = ? WHERE convenio_id = ?";
            $stmt_conv = $this->pdo->prepare($sql_conv);
            if (!$stmt_conv->execute([$estado_convenio, $convenio_id])) {
                throw new Exception("No se pudo actualizar el estado del convenio.");
            }

            // 2. Actualizar estado general del Practicante a 'Cesado'
            $sql_prac = "UPDATE Practicantes SET estado_general = 'Cesado' WHERE practicante_id = ?";
            $stmt_prac = $this->pdo->prepare($sql_prac);
             if (!$stmt_prac->execute([$practicante_id])) {
                 // Podríamos continuar si esto falla? Quizás sí, loguear el error.
                 error_log("Advertencia: No se pudo actualizar el estado del practicante $practicante_id a Cesado.");
             }
            
            // 3. Finalizar el período 'Activo' (si existe), poniéndole fecha fin de hoy
            $sql_per = "UPDATE PeriodosConvenio SET estado_periodo = 'Finalizado', fecha_fin = CURDATE()
                        WHERE convenio_id = ? AND estado_periodo = 'Activo'";
            $stmt_per = $this->pdo->prepare($sql_per);
            // No lanzamos error si no afecta filas, puede que ya no hubiera período activo
            $stmt_per->execute([$convenio_id]);

            // 4. Registrar la acción como una Adenda para historial
            $datosAdendaCese = [
                 'convenio_id' => $convenio_id,
                 'tipo_accion' => $estado_convenio, // 'Renuncia' o 'Cancelado'
                 'fecha_adenda' => date('Y-m-d'), // Hoy
                 'descripcion' => $descripcion,
                 'documento_adenda_url' => $documento_url // Será null si es 'Cancelado'
            ];
            if (!$this->agregarAdenda($datosAdendaCese)) {
                throw new Exception("No se pudo registrar la adenda de cese.");
            }

            $this->pdo->commit();
            return true; // Éxito
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw new Exception("Error en transacción finalizarConvenio: " . $e->getMessage());
        }
    }
    /**
 * [TRANSACCIÓN] Registra una suspensión.
 * 1. Finaliza el período activo en la fecha de suspensión.
 * 2. Crea un nuevo período con las fechas de retorno y nueva fecha fin.
 * 3. Registra la adenda.
 */
public function registrarSuspension(array $datosNuevoPeriodo, array $datosAdenda, string $fecha_suspension) {
    $this->pdo->beginTransaction();
    try {
        // 1. Finalizar el período 'Activo' o 'Futuro' actual en la fecha de suspensión
        $sql_update = "UPDATE PeriodosConvenio 
                       SET estado_periodo = 'Finalizado', fecha_fin = ?
                       WHERE convenio_id = ? AND estado_periodo IN ('Activo', 'Futuro')";
        $stmt_update = $this->pdo->prepare($sql_update);

        if (!$stmt_update->execute([ $fecha_suspension, $datosNuevoPeriodo['convenio_id'] ])) {
             throw new Exception("No se pudo finalizar el período anterior.");
        }
         if ($stmt_update->rowCount() === 0) {
             throw new Exception("No se encontró un período activo/futuro para finalizar (suspender).");
         }

        // 2. Insertar el nuevo Período (el de retorno)
        $sql_insert = "INSERT INTO PeriodosConvenio (convenio_id, fecha_inicio, fecha_fin, local_id, area_id, estado_periodo)
                       VALUES (?, ?, ?, ?, ?, ?)";
        $stmt_insert = $this->pdo->prepare($sql_insert);
        if (!$stmt_insert->execute([
            $datosNuevoPeriodo['convenio_id'],
            $datosNuevoPeriodo['fecha_inicio'], // fecha_retorno
            $datosNuevoPeriodo['fecha_fin'],    // nueva_fecha_fin
            $datosNuevoPeriodo['local_id'],
            $datosNuevoPeriodo['area_id'],
            $datosNuevoPeriodo['estado_periodo'] // 'Activo' o 'Futuro'
        ])) {
             throw new Exception("No se pudo insertar el nuevo período de retorno.");
        }

        // 3. Insertar el registro de la Adenda
        if (!$this->agregarAdenda($datosAdenda)) {
             throw new Exception("No se pudo registrar la adenda para la suspensión.");
        }

        $this->pdo->commit();
        return true; // Éxito

    } catch (Exception $e) {
        $this->pdo->rollBack();
        throw new Exception("Error en transacción registrarSuspension: " . $e->getMessage());
    }
}
    
    // --- Funciones de Reporte (sin cambios) ---
    public function contarConveniosPorVencer(int $dias = 30) { /* ... */ }
    public function getConveniosPorVencer(int $dias = 30) { /* ... */ }
    public function getUltimosConveniosCreados(int $limite = 5) { /* ... */ }
}