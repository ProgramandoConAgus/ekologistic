<?php
header('Content-Type: application/json');
$input = json_decode(file_get_contents('php://input'), true);
if (!isset($input['id'], $input['po'])) {
    echo json_encode(['success'=>false, 'error'=>'Parámetros incompletos']);
    exit;
}
$id = intval($input['id']);
$po = $input['po']; 

include('../con_db.php');
$stmt = $conexion->prepare("UPDATE container 
    SET Number_Commercial_Invoice = ?
    WHERE EXISTS (
      SELECT 1 FROM dispatch d 
      WHERE d.id = ? AND container.Number_Commercial_Invoice = d.numero_factura
    )");
$stmt->bind_param('si', $po, $id);
if ($stmt->execute()) {
    echo json_encode(['success'=>true]);
} else {
    echo json_encode(['success'=>false, 'error'=>$stmt->error]);
}
