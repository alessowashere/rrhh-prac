<?php
// config/config.php - Configuración General y de Base de Datos

// --- CONFIGURACIÓN DE LA BASE DE DATOS ---
define('DB_HOST', 'localhost');
define('DB_USER', 'admin'); 
define('DB_PASS', 'Redlabel@');     
define('DB_NAME', 'rrhh-prac'); 
define('DB_CHARSET', 'utf8mb4');

// --- RUTAS DEL SISTEMA ---
// Ruta base para URLs (ej: http://localhost/rrhh-prac/)
define('BASE_URL', '/rrhh-prac/'); 

// Ruta física absoluta del proyecto en el servidor (para includes/requires seguros)
define('BASE_PATH', dirname(__DIR__) . '/');

?>