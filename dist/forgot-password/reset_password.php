<?php
header('Content-Type: application/json');
require_once '../con_db.php';  // Archivo de conexión que define $conexion

// Verificar que se use el método POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Método no permitido']);
    exit;
}

// Obtener y decodificar el JSON recibido
$data = json_decode(file_get_contents('php://input'), true);
if (!isset($data['token'], $data['newPassword'])) {
    echo json_encode(['status' => 'error', 'message' => 'Datos incompletos']);
    exit;
}

$token = trim($data['token']);
$newPassword = $data['newPassword'];

// Buscar en la tabla password_resets el token recibido
$stmt = $conexion->prepare("SELECT email FROM password_resets WHERE token = ?");
if (!$stmt) {
    echo json_encode(['status' => 'error', 'message' => 'Error en la consulta: ' . $conexion->error]);
    exit;
}
$stmt->bind_param("s", $token);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['status' => 'error', 'message' => 'Token inválido o expirado']);
    exit;
}

$row = $result->fetch_assoc();
$email = $row['email'];
$stmt->close();

// Hashear la nueva contraseña
$hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

// Actualizar la contraseña en la tabla usuarios
$stmt = $conexion->prepare("UPDATE usuarios SET password = ? WHERE email = ?");
if (!$stmt) {
    echo json_encode(['status' => 'error', 'message' => 'Error en la consulta: ' . $conexion->error]);
    exit;
}
$stmt->bind_param("ss", $hashedPassword, $email);
if (!$stmt->execute()) {
    echo json_encode(['status' => 'error', 'message' => 'Error al actualizar la contraseña']);
    exit;
}
$stmt->close();

// Opcional: Eliminar el token de la tabla password_resets
$stmt = $conexion->prepare("DELETE FROM password_resets WHERE token = ?");
if ($stmt) {
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $stmt->close();
}

echo json_encode(['status' => 'success', 'message' => 'La contraseña ha sido actualizada exitosamente']);
exit;
?>
