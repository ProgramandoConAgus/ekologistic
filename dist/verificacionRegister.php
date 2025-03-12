<?php
header('Content-Type: application/json');
require_once 'con_db.php';  // Este archivo debe definir la variable $conexion (mysqli)

// Verificar que se esté usando el método POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Método de solicitud no permitido']);
    exit;
}

// Obtener y decodificar el JSON recibido
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['nombre'], $data['apellido'], $data['email'], $data['password'])) {
    echo json_encode(['error' => 'Faltan datos requeridos']);
    exit;
}

// Convertir a minúsculas y eliminar espacios extras
$nombre   = strtolower(trim($data['nombre']));
$apellido = strtolower(trim($data['apellido']));
$email    = strtolower(trim($data['email']));
$passwordPlain = $data['password'];

// Hashear la contraseña
$passwordHash = password_hash($passwordPlain, PASSWORD_DEFAULT);

// Verificar que el email no esté registrado
$stmt = $conexion->prepare("SELECT COUNT(*) as count FROM usuarios WHERE email = ?");
if (!$stmt) {
    echo json_encode(['error' => 'Error en la consulta: ' . $conexion->error]);
    exit;
}
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$count = $row['count'];
$stmt->close();

if ($count > 0) {
    echo json_encode(['success' => false, 'message' => 'El email ya está registrado']);
    exit;
}

// Insertar nuevo usuario
$stmt = $conexion->prepare("INSERT INTO usuarios (nombre, apellido, email, password) VALUES (?, ?, ?, ?)");
if (!$stmt) {
    echo json_encode(['error' => 'Error en la preparación de la consulta: ' . $conexion->error]);
    exit;
}
$stmt->bind_param("ssss", $nombre, $apellido, $email, $passwordHash);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Usuario registrado correctamente']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error al registrar el usuario: ' . $stmt->error]);
}
$stmt->close();
$conexion->close();
?>
