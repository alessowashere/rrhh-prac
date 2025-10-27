<?php
// models/PracticanteModel.php

class PracticanteModel {
    
    private $pdo;

    public function __construct() {
        $this->pdo = Database::getInstance()->getConnection();
    }

    /**
     * Obtiene la lista de practicantes (Activos y Cesados)
     * para la vista principal.
     */
    public function getPracticantesList() {
        $sql = "SELECT 
                    p.practicante_id, p.dni, p.nombres, p.apellidos, p.estado_general,
                    ep.nombre AS escuela_nombre,
                    u.nombre AS universidad_nombre
                FROM Practicantes p
                LEFT JOIN EscuelasProfesionales ep ON p.escuela_profesional_id = ep.escuela_id
                LEFT JOIN Universidades u ON ep.universidad_id = u.universidad_id
                WHERE p.estado_general IN ('Activo', 'Cesado')
                ORDER BY p.apellidos, p.nombres";
        
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll();
    }

    /**
     * Obtiene solo los datos de un practicante (para el form de editar).
     */
    public function getPracticanteDetalle(int $id) {
         $sql = "SELECT 
                    p.*,
                    ep.universidad_id
                FROM Practicantes p 
                LEFT JOIN EscuelasProfesionales ep ON p.escuela_profesional_id = ep.escuela_id
                WHERE p.practicante_id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    /**
     * Obtiene TODA la información de un practicante:
     * Detalles, Convenios, Periodos, Adendas y Documentos.
     */
    public function getInfoCompleta(int $practicante_id) {
        $info = [
            'detalle' => null,
            'convenios' => [],
            'documentos' => []
        ];

        // 1. Obtener detalles del practicante
        $info['detalle'] = $this->getPracticanteDetalle($practicante_id);
        if (!$info['detalle']) {
            return $info; // Practicante no existe
        }
        
        // 2. Obtener sus documentos
        $sql_docs = "SELECT * FROM Documentos 
                     WHERE practicante_id = ? 
                     ORDER BY fecha_carga DESC";
        $stmt_docs = $this->pdo->prepare($sql_docs);
        $stmt_docs->execute([$practicante_id]);
        $info['documentos'] = $stmt_docs->fetchAll();

        // 3. Obtener sus convenios
        $sql_conv = "SELECT c.*, pr.fecha_postulacion 
                     FROM Convenios c
                     LEFT JOIN ProcesosReclutamiento pr ON c.proceso_id = pr.proceso_id
                     WHERE c.practicante_id = ? 
                     ORDER BY c.convenio_id DESC";
        $stmt_conv = $this->pdo->prepare($sql_conv);
        $stmt_conv->execute([$practicante_id]);
        $convenios = $stmt_conv->fetchAll();

        // 4. Por cada convenio, obtener sus períodos y adendas
        foreach ($convenios as $convenio) {
            $convenio_id = $convenio['convenio_id'];
            
            // Periodos del convenio (con JOIN a Areas y Locales)
            $sql_periodos = "SELECT pc.*, a.nombre AS area_nombre, l.nombre AS local_nombre
                             FROM PeriodosConvenio pc
                             LEFT JOIN Areas a ON pc.area_id = a.area_id
                             LEFT JOIN Locales l ON pc.local_id = l.local_id
                             WHERE pc.convenio_id = ?
                             ORDER BY pc.fecha_inicio DESC";
            $stmt_periodos = $this->pdo->prepare($sql_periodos);
            $stmt_periodos->execute([$convenio_id]);
            
            // Adendas del convenio
            $sql_adendas = "SELECT * FROM Adendas 
                            WHERE convenio_id = ? 
                            ORDER BY fecha_adenda DESC";
            $stmt_adendas = $this->pdo->prepare($sql_adendas);
            $stmt_adendas->execute([$convenio_id]);

            // Añadir al array final
            $info['convenios'][] = [
                'info' => $convenio,
                'periodos' => $stmt_periodos->fetchAll(),
                'adendas' => $stmt_adendas->fetchAll()
            ];
        }

        return $info;
    }

    /**
     * Actualiza los datos de la tabla Practicantes.
     */
    public function actualizarPracticante(array $data) {
        $sql = "UPDATE Practicantes SET
                    dni = ?,
                    nombres = ?,
                    apellidos = ?,
                    fecha_nacimiento = ?,
                    email = ?,
                    telefono = ?,
                    promedio_general = ?,
                    escuela_profesional_id = ?,
                    estado_general = ?
                WHERE practicante_id = ?";
        
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            $data['dni'],
            $data['nombres'],
            $data['apellidos'],
            $data['fecha_nacimiento'],
            $data['email'],
            $data['telefono'],
            $data['promedio_general'],
            $data['escuela_id'],
            $data['estado_general'],
            $data['practicante_id']
        ]);
    }

    /**
     * Obtiene catálogos para los dropdowns de los formularios.
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
     * Cuenta los practicantes 'Activos' para el Dashboard.
     */
    public function contarActivos() {
        $sql = "SELECT COUNT(practicante_id) AS total FROM Practicantes WHERE estado_general = 'Activo'";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetch()['total'] ?? 0;
    }
    
    /**
     * NUEVO: Obtiene los conteos de practicantes por estado (Activo/Cesado).
     */
    public function getPracticanteCounts() {
        $sql = "SELECT 
                    COUNT(practicante_id) AS total,
                    SUM(CASE WHEN estado_general = 'Activo' THEN 1 ELSE 0 END) AS activos,
                    SUM(CASE WHEN estado_general = 'Cesado' THEN 1 ELSE 0 END) AS cesados
                FROM Practicantes
                WHERE estado_general IN ('Activo', 'Cesado')";
        
        $stmt = $this->pdo->query($sql);
        $counts = $stmt->fetch();
        
        return [
            'total' => $counts['total'] ?? 0,
            'activos' => $counts['activos'] ?? 0,
            'cesados' => $counts['cesados'] ?? 0
        ];
    }
}