<?php
header('Content-Type: application/json');
$input = json_decode(file_get_contents('php://input'), true);
if (!isset($input['id'], $input['date'])) {
    echo json_encode(['success'=>false, 'error'=>'Parámetros incompletos']);
    exit;
}
$id = intval($input['id']);
$date = $input['date'];

include('../con_db.php');
$stmt = $conexion->prepare("UPDATE dispatch SET fecha_salida = ? WHERE id = ?");
$stmt->bind_param('si', $date, $id);
if ($stmt->execute()) {
    echo json_encode(['success'=>true]);
} else {
    echo json_encode(['success'=>false, 'error'=>$stmt->error]);
}
