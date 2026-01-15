<?php
session_start();
require_once '../con_db.php';
header('Content-Type: application/json');

try {
    if (!isset($_SESSION['IdUsuario'])) {
        throw new Exception('Acceso no autorizado');
    }

    $data = json_decode(file_get_contents('php://input'), true);
    if (empty($data['id']) || !isset($data['value'])) {
        throw new Exception('Datos incompletos');
    }

    $id = filter_var($data['id'], FILTER_SANITIZE_NUMBER_INT);
    $forwarder = filter_var($data['value'], FILTER_SANITIZE_STRING);

    // Aquí podrías agregar validaciones adicionales o permisos según convenga.
    $stmt = $conexion->prepare("UPDATE container SET Forwarder = ? WHERE IdContainer = ?");
    $stmt->bind_param("si", $forwarder, $id);
    if (!$stmt->execute()) {
        throw new Exception('Error al actualizar: ' . $stmt->error);
    }
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage(),' no se'=> $data]);
}
