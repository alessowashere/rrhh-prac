<?php
// models/ReclutamientoModel.php

class ReclutamientoModel {
    
    private $pdo;

    public function __construct() {
        // Obtenemos la conexión PDO de la clase Database
        $this->pdo = Database::getInstance()->getConnection();
    }

    /**
     * Obtiene todos los procesos que están 'En Evaluación'
     * con los datos del practicante y su escuela.
     */
    public function getProcesosActivos() {
        $sql = "SELECT 
                    pr.proceso_id, pr.fecha_postulacion, pr.estado_proceso,
                    p.practicante_id, p.dni, p.nombres, p.apellidos,
                    ep.nombre AS escuela_nombre,
                    u.nombre AS universidad_nombre
                FROM ProcesosReclutamiento pr
                JOIN Practicantes p ON pr.practicante_id = p.practicante_id
                LEFT JOIN EscuelasProfesionales ep ON p.escuela_profesional_id = ep.escuela_id
                LEFT JOIN Universidades u ON ep.universidad_id = u.universidad_id
                WHERE pr.estado_proceso = 'En Evaluación'
                ORDER BY pr.fecha_postulacion DESC";
        
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll();
    }

    /**
     * Obtiene los catálogos de Universidades y Escuelas para los
     * formularios de registro.
     */
    public function getCatalogosParaFormulario() {
        $sql_uni = "SELECT universidad_id, nombre FROM Universidades ORDER BY nombre";
        $stmt_uni = $this->pdo->query($sql_uni);
        
        $sql_esc = "SELECT escuela_id, universidad_id, nombre FROM EscuelasProfesionales ORDER BY nombre";
        $stmt_esc = $this->pdo->query($sql_esc);

        return [
            'universidades' => $stmt_uni->fetchAll(),
            'escuelas' => $stmt_esc->fetchAll()
        ];
    }

    /**
     * Crea un nuevo registro de Practicante y su primer ProcesoReclutamiento.
     * Utiliza una transacción para asegurar la integridad de los datos.
     */
    public function crearNuevoProceso(array $data) {
        $this->pdo->beginTransaction();
        
        try {
            // 1. Verificar si el practicante (por DNI) ya existe
            $sql_find_prac = "SELECT practicante_id FROM Practicantes WHERE dni = ?";
            $stmt_find = $this->pdo->prepare($sql_find_prac);
            $stmt_find->execute([$data['dni']]);
            $practicante = $stmt_find->fetch();

            $practicante_id = null;

            if ($practicante) {
                // Si existe, usamos su ID
                $practicante_id = $practicante['practicante_id'];
                // Opcional: podrías actualizar sus datos
                // $sql_update_prac = "UPDATE Practicantes SET ... WHERE practicante_id = ?";
                // ...
            } else {
                // Si no existe, lo creamos
                $sql_new_prac = "INSERT INTO Practicantes 
                                    (dni, nombres, apellidos, fecha_nacimiento, email, telefono, promedio_general, escuela_profesional_id, estado_general) 
                                 VALUES 
                                    (?, ?, ?, ?, ?, ?, ?, ?, 'Candidato')";
                
                $stmt_new_prac = $this->pdo->prepare($sql_new_prac);
                $stmt_new_prac->execute([
                    $data['dni'],
                    $data['nombres'],
                    $data['apellidos'],
                    $data['fecha_nacimiento'] ?: null,
                    $data['email'] ?: null,
                    $data['telefono'] ?: null,
                    $data['promedio_general'] ?: null,
                    $data['escuela_id']
                ]);
                
                $practicante_id = $this->pdo->lastInsertId();
            }

            // 2. Crear el Proceso de Reclutamiento
            $sql_new_proc = "INSERT INTO ProcesosReclutamiento 
                                (practicante_id, fecha_postulacion, estado_proceso) 
                             VALUES 
                                (?, ?, 'En Evaluación')";
            
            $stmt_new_proc = $this->pdo->prepare($sql_new_proc);
            $stmt_new_proc->execute([
                $practicante_id,
                $data['fecha_postulacion']
            ]);
            
            $proceso_id = $this->pdo->lastInsertId();

            // 3. Crear el registro de 'ResultadosEntrevista' vacío
            // Esto es necesario para poder 'actualizar' (UPDATE) después, en lugar de 'insertar'
            $sql_new_res = "INSERT INTO ResultadosEntrevista (proceso_id) VALUES (?)";
            $stmt_new_res = $this->pdo->prepare($sql_new_res);
            $stmt_new_res->execute([$proceso_id]);

            // Si todo fue bien, confirmamos la transacción
            $this->pdo->commit();
            return $proceso_id;

        } catch (Exception $e) {
            // Si algo falla, revertimos
            $this->pdo->rollBack();
            // Relanzamos la excepción para que el controlador la capture
            // (especialmente útil para errores de DNI duplicado UNIQUE)
            throw new Exception("Error en la base de datos: " . $e->getMessage());
        }
    }

    /**
     * Obtiene toda la información de un proceso:
     * Practicante, Proceso y Resultados de Entrevista.
     */
    public function getProcesoCompleto(int $proceso_id) {
        $sql = "SELECT 
                    p.*, 
                    pr.*, 
                    re.*,
                    ep.nombre AS escuela_nombre,
                    u.nombre AS universidad_nombre
                FROM ProcesosReclutamiento pr
                JOIN Practicantes p ON pr.practicante_id = p.practicante_id
                LEFT JOIN ResultadosEntrevista re ON pr.proceso_id = re.proceso_id
                LEFT JOIN EscuelasProfesionales ep ON p.escuela_profesional_id = ep.escuela_id
                LEFT JOIN Universidades u ON ep.universidad_id = u.universidad_id
                WHERE pr.proceso_id = ?";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$proceso_id]);
        return $stmt->fetch();
    }

    /**
     * Actualiza la tabla ResultadosEntrevista y el puntaje final
     * en la tabla ProcesosReclutamiento.
     */
    public function actualizarEntrevista(array $data) {
        $this->pdo->beginTransaction();
        
        try {
            // 1. Actualizar la tabla de Resultados
            $sql_res = "UPDATE ResultadosEntrevista SET
                            campo_1_nombre = ?, campo_1_nota = ?,
                            campo_2_nombre = ?, campo_2_nota = ?,
                            campo_3_nombre = ?, campo_3_nota = ?,
                            campo_4_nombre = ?, campo_4_nota = ?,
                            campo_5_nombre = ?, campo_5_nota = ?,
                            campo_6_nombre = ?, campo_6_nota = ?,
                            campo_7_nombre = ?, campo_7_nota = ?,
                            campo_8_nombre = ?, campo_8_nota = ?,
                            campo_9_nombre = ?, campo_9_nota = ?,
                            campo_10_nombre = ?, campo_10_nota = ?,
                            comentarios_adicionales = ?
                        WHERE proceso_id = ?";
            
            $stmt_res = $this->pdo->prepare($sql_res);
            $stmt_res->execute([
                $data['campo_1_nombre'], $data['campo_1_nota'],
                $data['campo_2_nombre'], $data['campo_2_nota'],
                $data['campo_3_nombre'], $data['campo_3_nota'],
                $data['campo_4_nombre'], $data['campo_4_nota'],
                $data['campo_5_nombre'], $data['campo_5_nota'],
                $data['campo_6_nombre'], $data['campo_6_nota'],
                $data['campo_7_nombre'], $data['campo_7_nota'],
                $data['campo_8_nombre'], $data['campo_8_nota'],
                $data['campo_9_nombre'], $data['campo_9_nota'],
                $data['campo_10_nombre'], $data['campo_10_nota'],
                $data['comentarios'],
                $data['proceso_id']
            ]);

            // 2. Actualizar el puntaje final en la tabla de Proceso
            $sql_proc = "UPDATE ProcesosReclutamiento SET 
                            puntuacion_final_entrevista = ? 
                         WHERE proceso_id = ?";
            
            $stmt_proc = $this->pdo->prepare($sql_proc);
            $stmt_proc->execute([$data['puntuacion_final'], $data['proceso_id']]);

            $this->pdo->commit();
            return true;

        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw new Exception("Error al actualizar entrevista: " . $e->getMessage());
        }
    }

    /**
     * Cambia el estado de un ProcesoReclutamiento.
     */
    public function cambiarEstadoProceso(int $proceso_id, string $nuevo_estado) {
        $sql = "UPDATE ProcesosReclutamiento SET estado_proceso = ? WHERE proceso_id = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$nuevo_estado, $proceso_id]);
    }
}