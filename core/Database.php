<?php
// core/Database.php - Clase Singleton para manejar la conexión PDO

class Database {
    private static $instance = null;
    private $pdo;

    // El constructor es privado para evitar que se creen múltiples instancias con "new"
    private function __construct() {
        // Utilizamos las constantes definidas previamente en config/config.php
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Manejo estricto de errores
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Devuelve arrays asociativos por defecto
            PDO::ATTR_EMULATE_PREPARES   => false,                  // Seguridad extra contra inyecciones SQL
        ];

        try {
            $this->pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (\PDOException $e) {
            // En lugar de un throw genérico, usamos die() para detener la app si no hay BD
            die("Error crítico: No se pudo conectar a la base de datos. Detalles: " . $e->getMessage());
        }
    }

    // Método para obtener la única instancia de la clase (Patrón Singleton)
    public static function getInstance() {
        if (self::$instance == null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    // Método para obtener el objeto de conexión PDO
    public function getConnection() {
        return $this->pdo;
    }
}
?>