<?php
// Configuración de la conexión a la base de datos
define('DB_HOST', 'localhost');
define('DB_PORT', '3307');      // Puerto MySQL de XAMPP
define('DB_NAME', 'api_auth');  // Base de datos a crear
define('DB_USER', 'root');      // Usuario por defecto de MySQL
define('DB_PASS', '');          // Contraseña (vacía por defecto en XAMPP)

/**
 * Función que crea y retorna la conexión a MySQL
 * Retorna el objeto de conexión o detiene la ejecución si falla
 */

function conectarDB() {
    $conexion = new mysqli(
        DB_HOST,
        DB_USER,
        DB_PASS,
        DB_NAME,
        DB_PORT
    );

    // Verifica si hubo error al conectar

    if ($conexion->connect_error) {
        http_response_code(500);
        echo json_encode([
            "exito"   => false,
            "mensaje" => "Error de conexión: " . $conexion->connect_error
        ]);
        exit();
    }

    // Establecer charset UTF-8 para caracteres especiales
    $conexion->set_charset("utf8");

    return $conexion;
}
?>