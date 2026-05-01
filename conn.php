<?php
// Conexión a la base de datos local
$DB_HOST = 'localhost';     // local
$DB_PORT = 3306;            // local
$DB_NAME = 'casabonsai';    // local
$DB_USER = 'root';          // local
$DB_PASS = '';              // local    


// conexión a la base de datos remota
//$DB_HOST = 'db5018975305.hosting-data.io';
//$DB_PORT = 3306;
//$DB_NAME = 'dbs14946003';
//$DB_USER = 'dbu3380391';
//$DB_PASS = 'kfeputo123XD';

//


// DEBUG: poner false en producción
$php_error_debug = false;
if (!empty($php_error_debug)) {
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);
}

$dsn = "mysql:host={$DB_HOST};port={$DB_PORT};dbname={$DB_NAME};charset=utf8mb4";
try {
    $pdo = new PDO($dsn, $DB_USER, $DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (Exception $e) {
    if (!empty($php_error_debug)) {
        echo "Error de conexión: " . htmlspecialchars($e->getMessage());
    } else {
        // Mensaje genérico para no exponer detalles en producción
        echo "Error de conexión a la base de datos. Contacta al administrador.";
    }
    exit;
}

if (!function_exists('e')) {
    function e($v){ return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8'); }
}
