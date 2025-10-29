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
                    c.estado_firma, -- Para indicar si está firmado
                    p.practicante_id, p.dni, p.nombres, p.apellidos,
                    
                    pc_activo.fecha_inicio AS fecha_inicio_actual, -- Fecha inicio del periodo actual
                    pc_activo.fecha_fin AS fecha_fin_actual, -- Fecha fin del periodo actual
                    a.nombre AS area_actual,
                    
                    -- Conteo de Adendas (excluyendo Renuncia/Cancelado que son de cese)
                    (SELECT COUNT(ad.adenda_id) FROM Adendas ad 
                     WHERE ad.convenio_id = c.convenio_id 
                       AND ad.tipo_accion NOT IN ('Renuncia', 'Cancelado')) AS num_adendas

                FROM Convenios c
                JOIN Practicantes p ON c.practicante_id = p.practicante_id
                
                -- Unimos con el período ACTIVO (o el FUTURO si no hay activo) para obtener datos actuales
                LEFT JOIN PeriodosConvenio pc_activo ON pc_activo.convenio_id = c.convenio_id 
                                                     AND pc_activo.estado_periodo IN ('Activo', 'Futuro')
                LEFT JOIN Areas a ON pc_activo.area_id = a.area_id
                
                WHERE c.estado_convenio = 'Vigente'
                -- Agrupamos por convenio para evitar duplicados si hay Activo Y Futuro
                GROUP BY c.convenio_id 
                ORDER BY p.apellidos ASC, p.nombres ASC";
        
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
            // 1. Insertar el Convenio (con estado_firma)
            $sql_conv = "INSERT INTO Convenios (practicante_id, proceso_id, tipo_practica, estado_convenio, induccion_completada, estado_firma, documento_convenio_url)
                         VALUES (?, ?, ?, ?, FALSE, ?, NULL)"; // doc_url inicia NULL
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
            throw new Exception("Error en la transacción al crear convenio: " . $e->getMessage());
        }
    }
    
    /**
     * Obtiene todos los detalles de un convenio (incluyendo estado_firma y urls de documentos).
     */
    public function getDetalleConvenio(int $convenio_id) {
        // Trae también estado_firma y documento_convenio_url
        $sql = "SELECT c.*, p.dni, p.nombres, p.apellidos
                FROM Convenios c
                JOIN Practicantes p ON c.practicante_id = p.practicante_id
                WHERE c.convenio_id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$convenio_id]);
        $convenio = $stmt->fetch();
        
        if (!$convenio) return false;
        
        // Añadir períodos (ordenados del más reciente al más antiguo)
        $sql_per = "SELECT pc.*, a.nombre AS area_nombre, l.nombre AS local_nombre
                    FROM PeriodosConvenio pc
                    LEFT JOIN Areas a ON pc.area_id = a.area_id
                    LEFT JOIN Locales l ON pc.local_id = l.local_id
                    WHERE pc.convenio_id = ? ORDER BY pc.fecha_inicio DESC, pc.periodo_id DESC"; // Orden más robusto
        $stmt_per = $this->pdo->prepare($sql_per);
        $stmt_per->execute([$convenio_id]);
        $convenio['periodos'] = $stmt_per->fetchAll();
        
        // Añadir adendas (AHORA INCLUYE URL DEL DOCUMENTO)
        $sql_ad = "SELECT * FROM Adendas WHERE convenio_id = ? ORDER BY fecha_adenda DESC, adenda_id DESC";
        $stmt_ad = $this->pdo->prepare($sql_ad);
        $stmt_ad->execute([$convenio_id]);
        $convenio['adendas'] = $stmt_ad->fetchAll();
        
        return $convenio;
    }
    
    /**
     * Agrega una nueva adenda (incluye la URL del documento).
     * Usado internamente por las transacciones.
     */
    private function agregarAdendaRegistro(array $datos) {
        $sql = "INSERT INTO Adendas (convenio_id, tipo_accion, fecha_adenda, descripcion, documento_adenda_url)
                VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->pdo->prepare($sql);
        // Asegúrate de que todos los índices existen, aunque sean null
        return $stmt->execute([
            $datos['convenio_id'],
            $datos['tipo_accion'] ?? 'OTRO',
            $datos['fecha_adenda'] ?? date('Y-m-d'),
            $datos['descripcion'] ?? '',
            $datos['documento_adenda_url'] ?? null
        ]);
    }

    /**
     * Actualiza el convenio principal como "Firmado" y guarda la URL del PDF.
     */
    public function actualizarConvenioFirmado(int $convenio_id, string $url_documento) {
        $sql = "UPDATE Convenios 
                SET estado_firma = 'Firmado', documento_convenio_url = ?
                WHERE convenio_id = ?";
        $stmt = $this->pdo->prepare($sql);
        if (!$stmt->execute([$url_documento, $convenio_id])) {
             throw new Exception("Error al actualizar el estado de firma del convenio.");
        }
        return true;
    }

    /**
     * [TRANSACCIÓN] Amplía la fecha de fin del período activo O FUTURO y registra la adenda (con documento).
     */
    public function ampliarConvenio(array $datosAdenda, string $nueva_fecha_fin) {
        $this->pdo->beginTransaction();
        try {
            // 1. Actualizar el período 'Activo' o 'Futuro' (el que exista)
            $sql_update = "UPDATE PeriodosConvenio 
                           SET fecha_fin = ?
                           WHERE convenio_id = ? AND estado_periodo IN ('Activo', 'Futuro')
                           ORDER BY estado_periodo ASC -- Prioriza 'Activo' si ambos existen
                           LIMIT 1"; // Asegura actualizar solo uno
            $stmt_update = $this->pdo->prepare($sql_update);
            $stmt_update->execute([ $nueva_fecha_fin, $datosAdenda['convenio_id'] ]);
            
            // Verificar si se actualizó alguna fila
            if ($stmt_update->rowCount() == 0) {
                 throw new Exception("No se encontró un período 'Activo' o 'Futuro' para ampliar.");
            }

            // 2. Insertar el registro de la Adenda (que ya incluye la URL del doc)
            $this->agregarAdendaRegistro($datosAdenda);

            $this->pdo->commit();
            
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw new Exception("Error en transacción al ampliar convenio: " . $e->getMessage());
        }
    }
    
    /**
     * [TRANSACCIÓN] Agrega un nuevo período (Corte/Reubicación), finaliza el anterior
     * Y registra la adenda correspondiente con su documento.
     */
    public function agregarNuevoPeriodo(array $datosPeriodo, array $datosAdenda) {
        $this->pdo->beginTransaction();
        try {
            // 1. Finalizar el período 'Activo' o 'Futuro' actual
            $sql_update = "UPDATE PeriodosConvenio 
                           SET estado_periodo = 'Finalizado', fecha_fin = ?
                           WHERE convenio_id = ? AND estado_periodo IN ('Activo', 'Futuro')
                           ORDER BY estado_periodo ASC 
                           LIMIT 1";
            $stmt_update = $this->pdo->prepare($sql_update);
            // El período anterior termina un día antes de que empiece el nuevo
            $fecha_fin_anterior = date('Y-m-d', strtotime($datosPeriodo['fecha_inicio'] . ' -1 day'));
            $stmt_update->execute([ $fecha_fin_anterior, $datosPeriodo['convenio_id'] ]);
            
             // Es posible que no haya período activo/futuro si ya terminó, pero igual permitimos añadir uno nuevo
            // if ($stmt_update->rowCount() == 0) {
            //     throw new Exception("No se encontró un período 'Activo' o 'Futuro' para finalizar.");
            // }


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
                $datosPeriodo['estado_periodo'] // 'Activo' o 'Futuro'
            ]);
            
            // 3. Insertar el registro de la Adenda (que ya incluye la URL del doc)
            $this->agregarAdendaRegistro($datosAdenda);
            
            $this->pdo->commit();
            
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw new Exception("Error en transacción al agregar nuevo período: " . $e->getMessage());
        }
    }
    
    /**
     * [TRANSACCIÓN] Finaliza/Cancela un convenio (Renuncia o Cancelado).
     * Actualiza Convenio, Practicante, Período y registra la acción en Adendas.
     */
    public function finalizarConvenio(int $convenio_id, int $practicante_id, string $estado_convenio, string $descripcion, ?string $documento_url) {
        if (!in_array($estado_convenio, ['Renuncia', 'Cancelado'])) {
             throw new Exception("Estado de finalización inválido.");
        }
        
        $this->pdo->beginTransaction();
        try {
            // 1. Actualizar estado del Convenio
            $sql_conv = "UPDATE Convenios SET estado_convenio = ? WHERE convenio_id = ?";
            $this->pdo->prepare($sql_conv)->execute([$estado_convenio, $convenio_id]);
            
            // 2. Actualizar estado general del Practicante a 'Cesado'
            $sql_prac = "UPDATE Practicantes SET estado_general = 'Cesado' WHERE practicante_id = ?";
            $this->pdo->prepare($sql_prac)->execute([$practicante_id]);
            
            // 3. Finalizar el período 'Activo' o 'Futuro' (si existe) - Fecha fin es HOY
            $sql_per = "UPDATE PeriodosConvenio SET estado_periodo = 'Finalizado', fecha_fin = CURDATE()
                        WHERE convenio_id = ? AND estado_periodo IN ('Activo', 'Futuro')
                        ORDER BY estado_periodo ASC 
                        LIMIT 1";
            $this->pdo->prepare($sql_per)->execute([$convenio_id]);

            // 4. Registrar la acción como una Adenda para historial
            $datosAdendaCese = [
                 'convenio_id' => $convenio_id,
                 'tipo_accion' => $estado_convenio, // 'Renuncia' o 'Cancelado'
                 'fecha_adenda' => date('Y-m-d'), // Fecha de hoy
                 'descripcion' => $descripcion,
                 'documento_adenda_url' => $documento_url
             ];
            $this->agregarAdendaRegistro($datosAdendaCese);

            $this->pdo->commit();
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw new Exception("Error en transacción al finalizar el convenio: " . $e->getMessage());
        }
    }
    
    // --- Las funciones de conteo y búsqueda para Dashboard no cambian ---
    public function contarConveniosPorVencer(int $dias = 30) { /* ... código sin cambios ... */ }
    public function getConveniosPorVencer(int $dias = 30) { /* ... código sin cambios ... */ }
    public function getUltimosConveniosCreados(int $limite = 5) { /* ... código sin cambios ... */ }
}