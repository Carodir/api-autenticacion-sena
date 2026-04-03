<?php
// Permitir peticiones desde cualquier origen (CORS)
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");

// Incluir la conexión a la base de datos
require_once 'config/database.php';

/**
 * Endpoint: POST /registro.php
 * Recibe: usuario, contrasena
 * Retorna: mensaje de éxito o error en formato JSON
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

// Limpiar los datos para evitar inyección SQL
$usuario    = trim($datos['usuario']);
$contrasena = trim($datos['contrasena']);

// Conectar a la base de datos
$db = conectarDB();

// Verificar si el usuario ya existe
$consulta = $db->prepare("SELECT id FROM usuarios WHERE usuario = ?");
$consulta->bind_param("s", $usuario);
$consulta->execute();
$consulta->store_result();

if ($consulta->num_rows > 0) {
    // El usuario ya está registrado
    http_response_code(409);
    echo json_encode([
        "exito"   => false,
        "mensaje" => "El usuario ya existe."
    ]);
    $consulta->close();
    $db->close();
    exit();
}

// Encriptar la contraseña antes de guardarla
$contrasenaEncriptada = password_hash($contrasena, PASSWORD_BCRYPT);

// Insertar el nuevo usuario en la base de datos
$insertar = $db->prepare("INSERT INTO usuarios (usuario, contrasena) VALUES (?, ?)");
$insertar->bind_param("ss", $usuario, $contrasenaEncriptada);

if ($insertar->execute()) {
    // Registro exitoso
    http_response_code(201);
    echo json_encode([
        "exito"   => true,
        "mensaje" => "Usuario registrado exitosamente."
    ]);
} else {
    // Error al guardar
    http_response_code(500);
    echo json_encode([
        "exito"   => false,
        "mensaje" => "Error al registrar el usuario."
    ]);
}

// Cerrar conexiones
$insertar->close();
$db->close();
?>