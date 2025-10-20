<?php
header('Content-Type: application/json');

// Leer JSON recibido
$input = json_decode(file_get_contents('php://input'), true);

// Validar presencia de parámetros
if (!isset($input['id'], $input['field'], $input['value'])) {
    echo json_encode(['success' => false, 'error' => 'Parámetros incompletos']);
    exit;
}

$idItem = intval($input['id']);
$field = $input['field'];
$value = $input['value'];

include('../con_db.php');

// Validar id recibido
if (!$idItem) {
    echo json_encode(['success' => false, 'error' => 'No se encontró el item asociado']);
    exit;
}

// Whitelist de campos editables
$allowedFields = ['Number_PO', 'Length_in', 'Broad_in', 'Height_in', 'Weight_lb', 'departure_date'];

if (!in_array($field, $allowedFields)) {
    echo json_encode(['success' => false, 'error' => 'Campo no permitido']);
    exit;
}

// Preparar query con nombre de campo dinámico
$update = "UPDATE palets_cargados SET `$field` = ? WHERE IdItem = ?";
$stmt = $conexion->prepare($update);

if (!$stmt) {
    echo json_encode(['success' => false, 'error' => $conexion->error]);
    exit;
}

$stmt->bind_param("si", $value, $idItem);

// Ejecutar
if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Dato actualizado']);
} else {
    echo json_encode(['success' => false, 'error' => $stmt->error]);
}
?>
