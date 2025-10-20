<?php
require_once '../con_db.php';
header('Content-Type: application/json');

try {
    $input = json_decode(file_get_contents('php://input'), true);

    if (empty($input['container']) || empty($input['invoice']) || empty($input['value'])) {
        throw new Exception('Datos incompletos');
    }

    $container = $conexion->real_escape_string($input['container']);
    $invoice   = $conexion->real_escape_string($input['invoice']);
    $value     = $conexion->real_escape_string($input['value']);

    $allowed_statuses = ['Cargado', 'En Almacén'];
    if (!in_array($value, $allowed_statuses)) {
        throw new Exception('Estado no permitido');
    }

    // Actualizar todos los despachos que coincidan con el container e invoice
    $stmt = $conexion->prepare("
        UPDATE dispatch 
        SET estado = ?
        WHERE notas = ? AND numero_factura = ?
    ");
    $stmt->bind_param("sss", $value, $container, $invoice);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        echo json_encode([
            'success' => true,
            'message' => "Se actualizó el estado de {$stmt->affected_rows} ítem(s).",
            'container' => $container
        ]);
    } else {
        throw new Exception("No se encontraron registros para actualizar.");
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
