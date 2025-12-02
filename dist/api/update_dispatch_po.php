<?php
header('Content-Type: application/json');

// Leer JSON recibido
$raw = file_get_contents('php://input');
$input = json_decode($raw, true);
if (!is_array($input)) $input = $_POST;

// Esperamos payload mínimo: { id: <IdItem>, po: '<PO>' }
$id = isset($input['id']) ? intval($input['id']) : 0;
$po = $input['po'] ?? $input['Number_PO'] ?? null;
$invoice = $input['invoice'] ?? null;
$parte = $input['parte'] ?? null;

if ($po === null) {
    echo json_encode(['success' => false, 'error' => 'Parámetros incompletos. Se requiere po (y id o invoice+parte).']);
    exit;
}

include('../con_db.php');

if (!isset($conexion) || !$conexion) {
    echo json_encode(['success' => false, 'error' => 'No hay conexión a la base de datos']);
    exit;
}

// Actualizar Number_PO en tabla items
$poStr = (string)$po;
if ($id > 0) {
    // Actualizar por IdItem
    $sql = "UPDATE items SET Number_PO = ? WHERE IdItem = ?";
    $stmt = $conexion->prepare($sql);
    if (!$stmt) {
        echo json_encode(['success' => false, 'error' => 'Error prepare: ' . $conexion->error]);
        exit;
    }
    $stmt->bind_param('si', $poStr, $id);
    if (!$stmt->execute()) {
        echo json_encode(['success' => false, 'error' => 'Execute error: ' . $stmt->error]);
        exit;
    }
    $affected = $stmt->affected_rows;
    if ($affected > 0) {
        echo json_encode(['success' => true, 'message' => 'Number_PO actualizado', 'affected_rows' => $affected]);
    } else {
        echo json_encode(['success' => false, 'error' => 'No se actualizó ninguna fila. Verifique IdItem.']);
    }
    exit;
} elseif ($invoice && $parte) {
    // Actualizar todos los items que coincidan con invoice + parte
    $sql = "UPDATE items SET Number_PO = ? WHERE Number_Commercial_Invoice = ? AND Code_Product_EC = ?";
    $stmt = $conexion->prepare($sql);
    if (!$stmt) {
        echo json_encode(['success' => false, 'error' => 'Error prepare: ' . $conexion->error]);
        exit;
    }
    $stmt->bind_param('sss', $poStr, $invoice, $parte);
    if (!$stmt->execute()) {
        echo json_encode(['success' => false, 'error' => 'Execute error: ' . $stmt->error]);
        exit;
    }
    $affected = $stmt->affected_rows;
    echo json_encode(['success' => true, 'message' => "Number_PO actualizado en $affected fila(s)", 'affected_rows' => $affected]);
    exit;
} else {
    echo json_encode(['success' => false, 'error' => 'Parámetros insuficientes: se requiere id o (invoice+parte).']);
    exit;
}

?>
