<?php
header('Content-Type: application/json');

// Incluir el archivo de conexión a la base de datos usando mysqli
require_once 'con_db.php';

// Verificar que se esté usando el método POST
if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    echo json_encode(['error' => 'Método de solicitud no permitido']);
    exit;
}

// Obtener y decodificar el JSON enviado
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['email'], $data['password'])) {
    echo json_encode(['error' => 'Faltan datos requeridos']);
    exit;
}

// Asignar los valores recibidos y convertir el email a minúsculas
$email = strtolower(trim($data['email']));
$password = $data['password'];

// Preparar la consulta SQL para buscar al usuario por email
$stmt = $conexion->prepare("SELECT * FROM usuarios WHERE email = ? LIMIT 1");
if (!$stmt) {
    echo json_encode(['error' => 'Error en la preparación de la consulta: ' . $conexion->error]);
    exit;
}

$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $user = $result->fetch_assoc()) {
    // Verificar la contraseña usando password_verify (asegúrate de que en la BD esté almacenado el hash)
    if (password_verify($password, $user['password'])) {
        echo json_encode(['success' => true, 'message' => 'Inicio de sesión exitoso']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Contraseña incorrecta']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'El usuario no existe']);
}

$stmt->close();
$conexion->close();
?>
