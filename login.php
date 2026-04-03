<?php
// Permitir peticiones desde cualquier origen (CORS)
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");

// Incluir la conexión a la base de datos
require_once 'config/database.php';

/**
 * Endpoint: POST /login.php
 * Recibe: usuario, contrasena
 * Retorna: mensaje de autenticación satisfactoria o error
 */

// Verificar que la petición sea POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        "exito"   => false,
        "mensaje" => "Método no permitido. Use POST."
    ]);
    exit();
}

// Obtener los datos enviados en formato JSON
$datos = json_decode(file_get_contents("php://input"), true);

// Validar que se enviaron usuario y contraseña
if (empty($datos['usuario']) || empty($datos['contrasena'])) {
    http_response_code(400);
    echo json_encode([
        "exito"   => false,
        "mensaje" => "El usuario y la contraseña son obligatorios."
    ]);
    exit();
}

// Limpiar los datos recibidos
$usuario    = trim($datos['usuario']);
$contrasena = trim($datos['contrasena']);

// Conectar a la base de datos
$db = conectarDB();

// Buscar el usuario en la base de datos
$consulta = $db->prepare("SELECT id, usuario, contrasena FROM usuarios WHERE usuario = ?");
$consulta->bind_param("s", $usuario);
$consulta->execute();
$resultado = $consulta->get_result();

if ($resultado->num_rows === 0) {
    // El usuario no existe
    http_response_code(401);
    echo json_encode([
        "exito"   => false,
        "mensaje" => "Error en la autenticación: usuario no encontrado."
    ]);
    $consulta->close();
    $db->close();
    exit();
}

// Obtener los datos del usuario encontrado
$usuarioDB = $resultado->fetch_assoc();

// Verificar si la contraseña ingresada coincide con la encriptada
if (password_verify($contrasena, $usuarioDB['contrasena'])) {
    // Autenticación satisfactoria
    http_response_code(200);
    echo json_encode([
        "exito"   => true,
        "mensaje" => "Autenticación satisfactoria.",
        "usuario" => $usuarioDB['usuario']
    ]);
} else {
    // Contraseña incorrecta
    http_response_code(401);
    echo json_encode([
        "exito"   => false,
        "mensaje" => "Error en la autenticación: contraseña incorrecta."
    ]);
}

// Cerrar conexiones
$consulta->close();
$db->close();
?>