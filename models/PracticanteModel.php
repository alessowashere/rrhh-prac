<?php
// models/PracticanteModel.php

// 1. Heredamos del Modelo Base
class PracticanteModel extends Model {
    
    // ¡Adiós constructor! El padre (Model) ya se encarga de instanciar $this->db

    public function getPracticantesList() {
        // 2. Cambiamos $this->pdo por $this->db
        $sql = "SELECT 
                    p.practicante_id, p.dni, p.nombres, p.apellidos, p.estado_general,
                    ep.nombre AS escuela_nombre,
                    u.nombre AS universidad_nombre,
                    (SELECT COUNT(*) FROM Documentos d WHERE d.practicante_id = p.practicante_id) AS total_docs
                FROM Practicantes p
                LEFT JOIN EscuelasProfesionales ep ON p.escuela_profesional_id = ep.escuela_id
                LEFT JOIN Universidades u ON ep.universidad_id = u.universidad_id
                WHERE p.estado_general IN ('Activo', 'Cesado')
                ORDER BY p.estado_general ASC, p.apellidos ASC";
        
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    public function getPracticanteDetalle(int $id) {
         $sql = "SELECT p.*, ep.universidad_id
                FROM Practicantes p 
                LEFT JOIN EscuelasProfesionales ep ON p.escuela_profesional_id = ep.escuela_id
                WHERE p.practicante_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function getInfoCompleta(int $practicante_id) {
        $info = ['detalle' => $this->getPracticanteDetalle($practicante_id), 'convenios' => [], 'documentos' => []];
        if (!$info['detalle']) return $info;
        
        $stmt_docs = $this->db->prepare("SELECT * FROM Documentos WHERE practicante_id = ? ORDER BY fecha_carga DESC");
        $stmt_docs->execute([$practicante_id]);
        $info['documentos'] = $stmt_docs->fetchAll();

        $stmt_conv = $this->db->prepare("SELECT c.* FROM Convenios c WHERE c.practicante_id = ? ORDER BY c.convenio_id DESC");
        $stmt_conv->execute([$practicante_id]);
        $convenios = $stmt_conv->fetchAll();

        foreach ($convenios as $conv) {
            $stmt_per = $this->db->prepare("SELECT pc.*, a.nombre as area_nombre FROM PeriodosConvenio pc LEFT JOIN Areas a ON pc.area_id = a.area_id WHERE pc.convenio_id = ? ORDER BY pc.fecha_inicio DESC");
            $stmt_per->execute([$conv['convenio_id']]);
            $info['convenios'][] = ['info' => $conv, 'periodos' => $stmt_per->fetchAll()];
        }
        return $info;
    }

    public function actualizarPracticante(array $data) {
        $sql = "UPDATE Practicantes SET dni = ?, nombres = ?, apellidos = ?, fecha_nacimiento = ?, email = ?, telefono = ?, promedio_general = ?, escuela_profesional_id = ?, estado_general = ? WHERE practicante_id = ?";
        return $this->db->prepare($sql)->execute([$data['dni'], $data['nombres'], $data['apellidos'], $data['fecha_nacimiento'], $data['email'], $data['telefono'], $data['promedio_general'], $data['escuela_id'], $data['estado_general'], $data['practicante_id']]);
    }

    public function getCatalogosParaFormulario() {
        return [
            'universidades' => $this->db->query("SELECT * FROM Universidades ORDER BY nombre")->fetchAll(),
            'escuelas' => $this->db->query("SELECT * FROM EscuelasProfesionales ORDER BY nombre")->fetchAll()
        ];
    }

    public function getPracticanteCounts() {
        $counts = $this->db->query("SELECT COUNT(*) as total, SUM(CASE WHEN estado_general='Activo' THEN 1 ELSE 0 END) as activos, SUM(CASE WHEN estado_general='Cesado' THEN 1 ELSE 0 END) as cesados FROM Practicantes WHERE estado_general IN ('Activo', 'Cesado')")->fetch();
        return ['total' => $counts['total'] ?? 0, 'activos' => $counts['activos'] ?? 0, 'cesados' => $counts['cesados'] ?? 0];
    }

    public function importarPracticanteSimple($data) {
        $sql = "INSERT INTO Practicantes (dni, nombres, apellidos, estado_general, escuela_profesional_id) 
                VALUES (?, ?, ?, ?, (SELECT escuela_id FROM EscuelasProfesionales WHERE nombre LIKE ? LIMIT 1))
                ON DUPLICATE KEY UPDATE nombres=VALUES(nombres), apellidos=VALUES(apellidos)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$data['dni'], $data['nombres'], $data['apellidos'], $data['estado'], "%".$data['escuela']."%"]);
    }
}
?>