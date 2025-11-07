<?php
// Mostrar errores en desarrollo
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');

// Incluir conexión
require_once '../con_db.php'; // ajusta la ruta si es necesario

// Verificar conexión
if (!$conexion) {
    echo json_encode(['success' => false, 'error' => 'Error conexión DB']);
    exit;
}

// Obtener JSON
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['id'], $data['codigo'])) {
    echo json_encode(['success' => false, 'error' => 'Datos incompletos: faltan id o codigo']);
    exit;
}

$id = intval($data['id']);
$codigo = trim($data['codigo']);

$stmt = $conexion->prepare("UPDATE dispatch SET codigo_despacho = ? WHERE id = ?");
if (!$stmt) {
    echo json_encode(['success' => false, 'error' => "Error prepare: " . $con->error]);
    exit;
}

$stmt->bind_param("si", $codigo, $id);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Código de despacho actualizado']);
} else {
    echo json_encode(['success' => false, 'error' => "Error execute: " . $stmt->error]);
}

$stmt->close();
$conexion->close();
