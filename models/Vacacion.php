<?php
// models/Vacacion.php

class Vacacion {
    private $db;

    public function __construct($db) {
         if (!($db instanceof PDO)) { die("FATAL ERROR in VacacionModel Constructor: DB connection invalid!"); }
        $this->db = $db;
    }

    // --- List Function (Handles Search/Filter & Auto-Update) ---
    public function listar($search_nombre = null, $search_area = null, $anio_inicio = null) {
        try {
            // --- CAMBIO: Añadido v.documento_adjunto ---
            $sql = "SELECT v.id, p.nombre_completo, p.area, v.fecha_inicio, v.fecha_fin, v.dias_tomados, v.estado, v.documento_adjunto
                    FROM vacaciones AS v JOIN personas AS p ON v.persona_id = p.id JOIN periodos AS per ON v.periodo_id = per.id
                    WHERE 1=1";
            $params = [];
            if (!empty($search_nombre)) { $sql .= " AND p.nombre_completo LIKE ?"; $params[] = "%" . $search_nombre . "%"; }
            if (!empty($search_area)) { $sql .= " AND p.area LIKE ?"; $params[] = "%" . $search_area . "%"; }
            if (!empty($anio_inicio) && is_numeric($anio_inicio)) { $sql .= " AND YEAR(per.periodo_inicio) = ?"; $params[] = (int)$anio_inicio; }
            
            // --- CAMBIO: Añadido p.nombre_completo ASC para agrupar ---
            $sql .= " ORDER BY p.nombre_completo ASC, v.fecha_inicio DESC";
            
            $stmt = $this->db->prepare($sql); $stmt->execute($params); $listaVacaciones = $stmt->fetchAll();

            // --- Auto State Update ---
            if (!empty($listaVacaciones)) {
                 try {
                     $hoy = new DateTime(); $hoy->setTime(0, 0, 0);
                     $updateStmt = $this->db->prepare("UPDATE vacaciones SET estado = 'GOZADO' WHERE id = ?");
                     $ids_to_update_in_list = [];
                     foreach ($listaVacaciones as $key => $vacacion) {
                         if (isset($vacacion['estado']) && $vacacion['estado'] == 'APROBADO' && isset($vacacion['fecha_fin'])) {
                             try {
                                 $fecha_fin_dt = new DateTime($vacacion['fecha_fin']);
                                 if ($fecha_fin_dt < $hoy) {
                                     if ($updateStmt->execute([ $vacacion['id'] ])) $ids_to_update_in_list[] = $key;
                                     else error_log("Failed status update ID: {$vacacion['id']}");
                                 }
                             } catch (Exception $dateEx) { error_log("Date error state update ID {$vacacion['id']}: " . $dateEx->getMessage()); }
                         }
                     }
                     foreach($ids_to_update_in_list as $index) $listaVacaciones[$index]['estado'] = 'GOZADO';
                 } catch (Exception $e) { error_log("Error during auto state update: " . $e->getMessage()); }
            }
            return $listaVacaciones;
        } catch (PDOException $e) { error_log("Error listing vacations: " . $e->getMessage()); return []; }
    }

    // --- Get one vacation by ID ---
    public function obtenerPorId($id) {
         if (!is_numeric($id) || $id <= 0) return false;
        try {
            // --- CAMBIO: Seleccionamos todo para incluir el documento ---
            $sql = "SELECT * FROM vacaciones WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id]);
            return $stmt->fetch();
        } catch (PDOException $e) { error_log("Error getting vacation ID {$id}: " . $e->getMessage()); return false; }
    }

    // --- Create a new vacation ---
    public function crear($datos) {
        try {
            // --- CAMBIO: Añadido documento_adjunto ---
            $sql = "INSERT INTO vacaciones (persona_id, periodo_id, fecha_inicio, fecha_fin, dias_tomados, tipo, estado, documento_adjunto)
                    VALUES (:pid, :perid, :inicio, :fin, :dias, :tipo, :estado, :doc)";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                ':pid' => $datos['persona_id'],
                ':perid' => $datos['periodo_id'],
                ':inicio' => $datos['fecha_inicio'],
                ':fin' => $datos['fecha_fin'],
                ':dias' => $datos['dias_tomados'],
                ':tipo' => $datos['tipo'],
                ':estado' => $datos['estado'],
                ':doc' => $datos['documento_adjunto'] ?? null // Añadido
            ]);
        } catch (PDOException $e) { error_log("Error creating vacation: " . $e->getMessage()); return false; }
    }

    // --- Update a vacation ---
    public function actualizar($id, $datos) {
        if (!is_numeric($id) || $id <= 0) return false;
        try {
            // --- CAMBIO: Añadido documento_adjunto ---
            $sql = "UPDATE vacaciones SET
                        persona_id = :pid, periodo_id = :perid, fecha_inicio = :inicio, fecha_fin = :fin,
                        dias_tomados = :dias, tipo = :tipo, estado = :estado, documento_adjunto = :doc
                    WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                ':pid' => $datos['persona_id'],
                ':perid' => $datos['periodo_id'],
                ':inicio' => $datos['fecha_inicio'],
                ':fin' => $datos['fecha_fin'],
                ':dias' => $datos['dias_tomados'],
                ':tipo' => $datos['tipo'],
                ':estado' => $datos['estado'],
                ':doc' => $datos['documento_adjunto'] ?? null, // Añadido
                ':id' => $id
            ]);
        } catch (PDOException $e) { error_log("Error updating vacation ID {$id}: " . $e->getMessage()); return false; }
    }

    // --- Delete a vacation ---
    public function eliminar($id) {
         if (!is_numeric($id) || $id <= 0) return false;
        try {
            // --- CAMBIO: Antes de borrar, obtenemos el path del doc para borrar el archivo físico ---
            $data_vac = $this->obtenerPorId($id);
            $doc_path = $data_vac['documento_adjunto'] ?? null;

            $sql = "DELETE FROM vacaciones WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $success = $stmt->execute([$id]);

            if ($success && $doc_path && file_exists($doc_path)) {
                @unlink($doc_path); // Intentamos borrar el archivo del servidor
            }
            return $success;

        } catch (PDOException $e) { error_log("Error deleting vacation ID {$id}: " . $e->getMessage()); return false; }
    }

    // ... (El resto de funciones: contarPorEstado, listarActuales, listarProximas, actualizarEstado) ...
    // ... (Copiar/pegar las funciones existentes que no se modificaron) ...

    public function contarPorEstado($estado = 'PENDIENTE') {
        try {
            $sql = "SELECT COUNT(*) FROM vacaciones WHERE estado = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$estado]);
            return $stmt->fetchColumn();
        } catch (PDOException $e) { error_log("Error counting vacations by state {$estado}: " . $e->getMessage()); return 0; }
    }
    public function listarActuales($limite = 5) {
        try {
            $sql = "SELECT p.nombre_completo, v.fecha_fin
                    FROM vacaciones v JOIN personas p ON v.persona_id = p.id
                    WHERE v.estado IN ('APROBADO', 'GOZADO') AND CURDATE() BETWEEN v.fecha_inicio AND v.fecha_fin
                    ORDER BY v.fecha_fin ASC LIMIT ?";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(1, $limite, PDO::PARAM_INT); $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) { error_log("Error listing current vacations: " . $e->getMessage()); return []; }
    }
    public function listarProximas($dias_anticipacion = 7, $limite = 5) {
        try {
            $sql = "SELECT p.nombre_completo, v.fecha_inicio, v.fecha_fin
                    FROM vacaciones v JOIN personas p ON v.persona_id = p.id
                    WHERE v.estado = 'APROBADO' AND v.fecha_inicio > CURDATE()
                      AND v.fecha_inicio <= DATE_ADD(CURDATE(), INTERVAL ? DAY)
                    ORDER BY v.fecha_inicio ASC LIMIT ?";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(1, $dias_anticipacion, PDO::PARAM_INT);
            $stmt->bindValue(2, $limite, PDO::PARAM_INT); $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) { error_log("Error listing upcoming vacations: " . $e->getMessage()); return []; }
    }
    public function actualizarEstado($id, $nuevoEstado) {
            if (!is_numeric($id) || $id <= 0) return false;
            $estadosValidos = ['APROBADO', 'RECHAZADO', 'PENDIENTE', 'GOZADO'];
            if (!in_array($nuevoEstado, $estadosValidos)) {
                error_log("Intento de actualizar a estado no válido '{$nuevoEstado}' para vacacion ID {$id}");
                return false;
            }
            try {
                $sql = "UPDATE vacaciones SET estado = :estado WHERE id = :id";
                $stmt = $this->db->prepare($sql);
                return $stmt->execute([
                    ':estado' => $nuevoEstado,
                    ':id' => $id
                ]);
            } catch (PDOException $e) {
                error_log("Error actualizando estado vacacion ID {$id}: " . $e->getMessage());
                return false;
            }
        }

} // End Class
?>