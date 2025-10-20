<?php
header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);

// Validar que llegan los parámetros necesarios
if (!isset($input['id'], $input['field'], $input['value'])) {
    echo json_encode(['success' => false, 'error' => 'Parámetros incompletos']);
    exit;
}

$idItem = intval($input['id']);
$field = $input['field'];
$value = $input['value'];

include('../con_db.php');

// Validar id válido
if (!$idItem) {
    echo json_encode(['success' => false, 'error' => 'ID inválido']);
    exit;
}

// Lista blanca de campos permitidos para evitar inyección SQL
$allowedFields = [
    'Number_PO',
    'Length_in',
    'Broad_in',
    'Height_in',
    'Weight_lb',
    'departure_date',
    'NEW_ETA_DATE', // si usás este campo
    // agregar más según tabla y columnas permitidas
];

// Verificar que el campo solicitado está permitido
if (!in_array($field, $allowedFields)) {
    echo json_encode(['success' => false, 'error' => 'Campo no permitido']);
    exit;
}

// Preparar consulta SQL con el campo dinámico (protegido)
$updateSql = "UPDATE palets_cargados SET `$field` = ? WHERE IdItem = ?";

$stmt = $conexion->prepare($updateSql);
if (!$stmt) {
    echo json_encode(['success' => false, 'error' => $conexion->error]);
    exit;
}

// Bind parameters, se asume string para todos para simplificar
$stmt->bind_param("si", $value, $idItem);

// Ejecutar la actualización
if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Campo actualizado correctamente']);
} else {
    echo json_encode(['success' => false, 'error' => $stmt->error]);
}
