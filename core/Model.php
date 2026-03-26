<?php
// core/Model.php - Modelo base para toda la aplicación

class Model {
    // Protected permite que los modelos hijos (como PracticanteModel) accedan a esta variable
    protected $db; 

    public function __construct() {
        // Obtenemos la conexión segura que preparamos en el Hito 1
        $this->db = Database::getInstance()->getConnection();
    }
}
?>