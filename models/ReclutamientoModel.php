<?php
// models/ReclutamientoModel.php

class ReclutamientoModel extends Model {
    
    // Ya no necesitamos el constructor ni $this->pdo porque hereda $this->db del core/Model.php

    /**
     * Obtiene TODOS los procesos (para el nuevo listado)
     * con los datos del practicante y su escuela.
     */
    public function getTodosLosProcesos() {
        $sql = "SELECT 
                    pr.proceso_id, pr.fecha_postulacion, pr.estado_proceso,
                    pr.puntuacion_final_entrevista,
                    p.practicante_id, p.dni, p.nombres, p.apellidos,
                    ep.nombre AS escuela_nombre,
                    u.nombre AS universidad_nombre
                FROM ProcesosReclutamiento pr
                JOIN Practicantes p ON pr.practicante_id = p.practicante_id
                LEFT JOIN EscuelasProfesionales ep ON p.escuela_profesional_id = ep.escuela_id
                LEFT JOIN Universidades u ON ep.universidad_id = u.universidad_id
                ORDER BY pr.fecha_postulacion DESC";
        
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    /**
     * Obtiene los catálogos de Universidades y Escuelas.
     */
    public function getCatalogosParaFormulario() {
        $sql_uni = "SELECT universidad_id, nombre FROM Universidades ORDER BY nombre";
        $stmt_uni = $this->db->query($sql_uni);
        
        $sql_esc = "SELECT escuela_id, universidad_id, nombre FROM EscuelasProfesionales ORDER BY nombre";
        $stmt_esc = $this->db->query($sql_esc);

        return [
            'universidades' => $stmt_uni->fetchAll(),
            'escuelas' => $stmt_esc->fetchAll()
        ];
    }

    /**
     * Crea un nuevo Practicante y ProcesoReclutamiento.
     * Modificado para incluir 'tipo_practica' y devolver IDs.
     */
    public function crearNuevoProceso(array $data) {
        $this->db->beginTransaction();
        
        try {
            // 1. Verificar si el practicante (por DNI) ya existe
            $sql_find_prac = "SELECT practicante_id FROM Practicantes WHERE dni = ?";
            $stmt_find = $this->db->prepare($sql_find_prac);
            $stmt_find->execute([$data['dni']]);
            $practicante = $stmt_find->fetch();

            $practicante_id = null;

            if ($practicante) {
                // Si existe, usamos su ID
                $practicante_id = $practicante['practicante_id'];
                // Opcional: Actualizar datos del practicante si ya existe
                $sql_update_prac = "UPDATE Practicantes SET 
                                        nombres = ?, apellidos = ?, fecha_nacimiento = ?, email = ?, 
                                        telefono = ?, promedio_general = ?, escuela_profesional_id = ?
                                    WHERE practicante_id = ?";
                $stmt_update_prac = $this->db->prepare($sql_update_prac);
                $stmt_update_prac->execute([
                    $data['nombres'], $data['apellidos'], $data['fecha_nacimiento'], $data['email'],
                    $data['telefono'], $data['promedio_general'], $data['escuela_id'],
                    $practicante_id
                ]);

            } else {
                // Si no existe, lo creamos
                $sql_new_prac = "INSERT INTO Practicantes 
                                    (dni, nombres, apellidos, fecha_nacimiento, email, telefono, promedio_general, escuela_profesional_id, estado_general) 
                                 VALUES 
                                    (?, ?, ?, ?, ?, ?, ?, ?, 'Candidato')";
                
                $stmt_new_prac = $this->db->prepare($sql_new_prac);
                $stmt_new_prac->execute([
                    $data['dni'], $data['nombres'], $data['apellidos'],
                    $data['fecha_nacimiento'] ?: null,
                    $data['email'] ?: null,
                    $data['telefono'] ?: null,
                    $data['promedio_general'] ?: null,
                    $data['escuela_id']
                ]);
                
                $practicante_id = $this->db->lastInsertId();
            }

            // 2. Crear el Proceso de Reclutamiento
            $sql_new_proc = "INSERT INTO ProcesosReclutamiento 
                                (practicante_id, fecha_postulacion, estado_proceso, tipo_practica) 
                             VALUES 
                                (?, ?, 'En Evaluación', ?)";
            
            $stmt_new_proc = $this->db->prepare($sql_new_proc);
            $stmt_new_proc->execute([
                $practicante_id,
                $data['fecha_postulacion'],
                $data['tipo_practica'] 
            ]);
            
            $proceso_id = $this->db->lastInsertId();

            // 3. Crear el registro 'ResultadosEntrevista' vacío
            $sql_new_res = "INSERT INTO ResultadosEntrevista (proceso_id) VALUES (?)";
            $stmt_new_res = $this->db->prepare($sql_new_res);
            $stmt_new_res->execute([$proceso_id]);

            $this->db->commit();
            
            return ['practicante_id' => $practicante_id, 'proceso_id' => $proceso_id];

        } catch (Exception $e) {
            $this->db->rollBack();
            throw new Exception("Error en la base de datos: " . $e->getMessage());
        }
    }

    public function addDocumento(int $practicante_id, int $proceso_id, string $tipo_documento, string $url_archivo) {
        $sql = "INSERT INTO Documentos (practicante_id, proceso_id, tipo_documento, url_archivo) 
                VALUES (?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$practicante_id, $proceso_id, $tipo_documento, $url_archivo]);
    }

    public function getProcesoCompleto(int $proceso_id) {
        $sql = "SELECT 
                    p.*, 
                    pr.*, 
                    pr.tipo_practica,
                    re.*,
                    ep.nombre AS escuela_nombre,
                    u.nombre AS universidad_nombre
                FROM ProcesosReclutamiento pr
                JOIN Practicantes p ON pr.practicante_id = p.practicante_id
                LEFT JOIN ResultadosEntrevista re ON pr.proceso_id = re.proceso_id
                LEFT JOIN EscuelasProfesionales ep ON p.escuela_profesional_id = ep.escuela_id
                LEFT JOIN Universidades u ON ep.universidad_id = u.universidad_id
                WHERE pr.proceso_id = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$proceso_id]);
        return $stmt->fetch();
    }

    public function actualizarEntrevista(array $data) {
        $this->db->beginTransaction();
        
        try {
            $sql_res = "UPDATE ResultadosEntrevista SET
                            campo_1_nombre = ?, campo_1_nota = ?, campo_1_peso = ?,
                            campo_2_nombre = ?, campo_2_nota = ?, campo_2_peso = ?,
                            campo_3_nombre = ?, campo_3_nota = ?, campo_3_peso = ?,
                            campo_4_nombre = ?, campo_4_nota = ?, campo_4_peso = ?,
                            campo_5_nombre = ?, campo_5_nota = ?, campo_5_peso = ?,
                            campo_6_nombre = ?, campo_6_nota = ?, campo_6_peso = ?,
                            campo_7_nombre = ?, campo_7_nota = ?, campo_7_peso = ?,
                            campo_8_nombre = ?, campo_8_nota = ?, campo_8_peso = ?,
                            campo_9_nombre = ?, campo_9_nota = ?, campo_9_peso = ?,
                            campo_10_nombre = ?, campo_10_nota = ?, campo_10_peso = ?,
                            comentarios_adicionales = ?
                        WHERE proceso_id = ?";
            
            $stmt_res = $this->db->prepare($sql_res);
            $stmt_res->execute([
                $data['campo_1_nombre'], $data['campo_1_nota'], $data['campo_1_peso'],
                $data['campo_2_nombre'], $data['campo_2_nota'], $data['campo_2_peso'],
                $data['campo_3_nombre'], $data['campo_3_nota'], $data['campo_3_peso'],
                $data['campo_4_nombre'], $data['campo_4_nota'], $data['campo_4_peso'],
                $data['campo_5_nombre'], $data['campo_5_nota'], $data['campo_5_peso'],
                $data['campo_6_nombre'], $data['campo_6_nota'], $data['campo_6_peso'],
                $data['campo_7_nombre'], $data['campo_7_nota'], $data['campo_7_peso'],
                $data['campo_8_nombre'], $data['campo_8_nota'], $data['campo_8_peso'],
                $data['campo_9_nombre'], $data['campo_9_nota'], $data['campo_9_peso'],
                $data['campo_10_nombre'], $data['campo_10_nota'], $data['campo_10_peso'],
                $data['comentarios'],
                $data['proceso_id']
            ]);

            $sql_proc = "UPDATE ProcesosReclutamiento SET 
                            puntuacion_final_entrevista = ? 
                         WHERE proceso_id = ?";
            
            $stmt_proc = $this->db->prepare($sql_proc);
            $stmt_proc->execute([$data['puntuacion_final'], $data['proceso_id']]);

            $this->db->commit();
            return true;

        } catch (Exception $e) {
            $this->db->rollBack();
            throw new Exception("Error al actualizar entrevista: " . $e->getMessage());
        }
    }

    public function cambiarEstadoProceso(int $proceso_id, string $nuevo_estado) {
        $sql = "UPDATE ProcesosReclutamiento SET estado_proceso = ? WHERE proceso_id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$nuevo_estado, $proceso_id]);
    }

    public function actualizarFechaEntrevista(int $proceso_id, string $fecha) {
        $sql = "UPDATE ProcesosReclutamiento SET fecha_entrevista = ? WHERE proceso_id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$fecha, $proceso_id]);
    }

    public function getDocumentosPorProceso(int $proceso_id) {
        $sql = "SELECT tipo_documento, url_archivo 
                FROM Documentos 
                WHERE proceso_id = ? 
                ORDER BY 
                    CASE 
                        WHEN tipo_documento = 'CONSOLIDADO' THEN 1
                        WHEN tipo_documento = 'FICHA_CALIFICACION' THEN 3
                        ELSE 2 
                    END,
                    fecha_carga ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$proceso_id]);
        return $stmt->fetchAll();
    }

    public function getProcesosConFicha() {
        $sql = "SELECT DISTINCT proceso_id 
                FROM Documentos 
                WHERE tipo_documento = 'FICHA_CALIFICACION' AND proceso_id IS NOT NULL";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_COLUMN, 0); 
    }
    
    public function contarEnProceso() {
        $sql = "SELECT COUNT(proceso_id) AS total 
                FROM ProcesosReclutamiento 
                WHERE estado_proceso IN ('En Evaluación', 'Evaluado')";
        $stmt = $this->db->query($sql);
        return $stmt->fetch()['total'] ?? 0;
    }
    
    public function getProcesoSimple(int $proceso_id) {
        $sql = "SELECT proceso_id, practicante_id, tipo_practica 
                FROM ProcesosReclutamiento 
                WHERE proceso_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$proceso_id]);
        return $stmt->fetch();
    }
}
?>