<?php
require_once '../con_db.php';
header('Content-Type: application/json');

try {
    $input = json_decode(file_get_contents('php://input'), true);
    if (empty($input['id']) || empty($input['value'])) {
        throw new Exception('Datos inválidos');
    }

    $id = filter_var($input['id'], FILTER_SANITIZE_NUMBER_INT);
    $value = filter_var($input['value'], FILTER_SANITIZE_STRING);

    // Validar que el estado sea uno de los permitidos
    $allowed_statuses = ['Cargado', 'En Almacén'];
    if (!in_array($value, $allowed_statuses)) {
        throw new Exception('Estado no permitido');
    }

    // Actualizar solo el campo "estado" en la tabla "dispatch"
    $stmt = $conexion->prepare("UPDATE dispatch SET estado = ? WHERE id = ?");
    $stmt->bind_param("si", $value, $id);
    $stmt->execute();

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
