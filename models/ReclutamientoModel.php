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

    public function actualizarEntrevista($datos) {
        // 1. PASO A: Verificar si ya existen notas para este proceso_id
        $sqlCheck = "SELECT resultado_id FROM ResultadosEntrevista WHERE proceso_id = :proceso_id";
        $stmtCheck = $this->db->prepare($sqlCheck);
        $stmtCheck->execute([':proceso_id' => $datos['proceso_id']]);
        $existe = $stmtCheck->fetchColumn();

        // 2. PASO B: Insertar o Actualizar en la tabla ResultadosEntrevista
        if ($existe) {
            $sql = "UPDATE ResultadosEntrevista SET 
                    comentarios_adicionales = :comentarios,
                    campo_1_nombre = :c1_nom, campo_1_nota = :c1_nota,
                    campo_2_nombre = :c2_nom, campo_2_nota = :c2_nota,
                    campo_3_nombre = :c3_nom, campo_3_nota = :c3_nota,
                    campo_4_nombre = :c4_nom, campo_4_nota = :c4_nota,
                    campo_5_nombre = :c5_nom, campo_5_nota = :c5_nota,
                    campo_6_nombre = :c6_nom, campo_6_nota = :c6_nota
                    WHERE proceso_id = :proceso_id";
        } else {
            $sql = "INSERT INTO ResultadosEntrevista 
                    (proceso_id, comentarios_adicionales,
                    campo_1_nombre, campo_1_nota,
                    campo_2_nombre, campo_2_nota,
                    campo_3_nombre, campo_3_nota,
                    campo_4_nombre, campo_4_nota,
                    campo_5_nombre, campo_5_nota,
                    campo_6_nombre, campo_6_nota)
                    VALUES 
                    (:proceso_id, :comentarios,
                    :c1_nom, :c1_nota, :c2_nom, :c2_nota,
                    :c3_nom, :c3_nota, :c4_nom, :c4_nota,
                    :c5_nom, :c5_nota, :c6_nom, :c6_nota)";
        }

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':proceso_id', $datos['proceso_id'], PDO::PARAM_INT);
        $stmt->bindValue(':comentarios', $datos['comentarios']); // Mapeado correctamente a comentarios_adicionales

        for ($i = 1; $i <= 6; $i++) {
            $stmt->bindValue(':c'.$i.'_nom',  $datos['campo_'.$i.'_nombre']);
            $stmt->bindValue(':c'.$i.'_nota', $datos['campo_'.$i.'_nota']);
            // NOTA: Ignoramos silenciosamente los pesos ('campo_X_peso') 
            // del controlador porque no existen columnas para eso en bd.sql
        }
        $stmt->execute();
        
        // 3. PASO C: Guardar la Nota Final en la tabla ProcesosReclutamiento
        $sqlProceso = "UPDATE ProcesosReclutamiento 
                       SET puntuacion_final_entrevista = :puntuacion
                       WHERE proceso_id = :proceso_id";
        $stmtProceso = $this->db->prepare($sqlProceso);
        $stmtProceso->bindValue(':puntuacion', $datos['puntuacion_final']);
        $stmtProceso->bindValue(':proceso_id', $datos['proceso_id'], PDO::PARAM_INT);
        $stmtProceso->execute();
    }

    public function cambiarEstadoProceso($proceso_id, $estado) {
        $sql = "UPDATE ProcesosReclutamiento SET estado_proceso = :estado WHERE proceso_id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':estado' => $estado, ':id' => $proceso_id]);
    }

    public function actualizarFechaEntrevista($proceso_id, $fecha) {
        $sql = "UPDATE ProcesosReclutamiento SET fecha_entrevista = :fecha WHERE proceso_id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':fecha' => $fecha, ':id' => $proceso_id]);
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