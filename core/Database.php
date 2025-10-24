<?php
// core/Database.php - Clase para manejar la conexión PDO

class Database {
    private static $instance = null;
    private $pdo;
    private $host = DB_HOST;
    private $user = DB_USER;
    private $pass = DB_PASS;
    private $name = DB_NAME;
    private $charset = DB_CHARSET;

    private function __construct() {
        $dsn = "mysql:host=$this->host;dbname=$this->name;charset=$this->charset";
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $this->pdo = new PDO($dsn, $this->user, $this->pass, $options);
        } catch (\PDOException $e) {
            throw new \PDOException($e->getMessage(), (int)$e->getCode());
        }
    }

    public static function getInstance() {
        if (self::$instance == null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    // Método para obtener la conexión PDO
    public function getConnection() {
        return $this->pdo;
    }
}
?>
