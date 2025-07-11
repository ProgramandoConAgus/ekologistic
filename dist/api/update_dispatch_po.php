<?php
header('Content-Type: application/json');
$input = json_decode(file_get_contents('php://input'), true);
if (!isset($input['id'], $input['po'])) {
    echo json_encode(['success'=>false, 'error'=>'Parámetros incompletos']);
    exit;
}

$idItem = intval($input['id']); // id de dispatch
$po = $input['po']; 

include('../con_db.php');



if (!$idItem) {
    echo json_encode(['success'=>false, 'error'=>'No se encontró el item asociado', 'id'=>$idContainer]);
    exit;
}

$update = "UPDATE items SET Number_PO = ? WHERE IdItem = ?";
$stmt2 = $conexion->prepare($update);
$stmt2->bind_param('si', $po, $idItem);

if ($stmt2->execute()) {
    echo json_encode(['success'=>true]);
} else {
    echo json_encode(['success'=>false, 'error'=>$stmt2->error]);
}
?>
