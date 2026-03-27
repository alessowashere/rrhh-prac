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

    public function actualizarPracticante($data) {
        // CORRECCIÓN: Manejo de fecha vacía para evitar el error 22007 de MySQL
        $fecha_nac = !empty($data['fecha_nacimiento']) ? $data['fecha_nacimiento'] : null;

        $sql = "UPDATE Practicantes 
                SET nombres = ?, apellidos = ?, dni = ?, fecha_nacimiento = ?, 
                    email = ?, telefono = ?, escuela_profesional_id = ?
                WHERE practicante_id = ?";
        
        $stmt = $this->db->prepare($sql);
        
        return $stmt->execute([
            $data['nombres'],
            $data['apellidos'],
            $data['dni'],
            $fecha_nac, // Aquí pasamos la variable ya evaluada (NULL o fecha)
            $data['email'],
            $data['telefono'],
            $data['escuela_profesional_id'],
            $data['practicante_id']
        ]);
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
    public function getPracticantePorId($id) {
        // 1. Datos básicos y escuela
        $sql = "SELECT p.*, ep.nombre as escuela_nombre 
                FROM Practicantes p
                LEFT JOIN EscuelasProfesionales ep ON p.escuela_profesional_id = ep.escuela_id
                WHERE p.practicante_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        $practicante = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($practicante) {
            // 2. Traer el historial de periodos con sus áreas
            $sqlPer = "SELECT pc.*, a.nombre as area_nombre, l.nombre as local_nombre 
                    FROM PeriodosConvenio pc
                    LEFT JOIN Areas a ON pc.area_id = a.area_id
                    LEFT JOIN Locales l ON pc.local_id = l.local_id
                    WHERE pc.convenio_id IN (SELECT convenio_id FROM Convenios WHERE practicante_id = ?)
                    ORDER BY pc.fecha_inicio DESC";
            $stmtPer = $this->db->prepare($sqlPer);
            $stmtPer->execute([$id]); // CORREGIDO: flecha en lugar de punto
            $practicante['historial_periodos'] = $stmtPer->fetchAll(PDO::FETCH_ASSOC);

            // 3. Traer TODAS las adendas (Ampliaciones, Reubicaciones y el CESE con su ARCHIVO)
            $sqlAdendas = "SELECT a.* FROM Adendas a
                        JOIN Convenios c ON a.convenio_id = c.convenio_id
                        WHERE c.practicante_id = ?
                        ORDER BY a.fecha_adenda DESC";
            $stmtAdendas = $this->db->prepare($sqlAdendas);
            $stmtAdendas->execute([$id]); // CORREGIDO: flecha en lugar de punto
            $practicante['adendas'] = $stmtAdendas->fetchAll(PDO::FETCH_ASSOC);
            
            // 4. Buscar el archivo del convenio original
            $sqlConv = "SELECT estado_firma, documento_convenio_url FROM Convenios WHERE practicante_id = ? LIMIT 1";
            $stmtConv = $this->db->prepare($sqlConv);
            $stmtConv->execute([$id]); // CORREGIDO: flecha en lugar de punto
            $practicante['convenio_principal'] = $stmtConv->fetch(PDO::FETCH_ASSOC);
        }
        
        return $practicante;
    }
}
?>