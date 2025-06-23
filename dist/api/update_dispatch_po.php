<?php
header('Content-Type: application/json');
$input = json_decode(file_get_contents('php://input'), true);
if (!isset($input['id'], $input['po'])) {
    echo json_encode(['success'=>false, 'error'=>'Parámetros incompletos']);
    exit;
}

$id = intval($input['id']); // id de dispatch
$po = $input['po']; 

include('../con_db.php');

// Primero obtener el id_container asociado a ese dispatch
$query = "SELECT c.id 
          FROM container c
          INNER JOIN dispatch d 
              ON c.Number_Commercial_Invoice = d.numero_factura
             AND c.Number_Container = d.notas
          WHERE d.id = ?";

$stmt = $conexion->prepare($query);
$stmt->bind_param('i', $id);
$stmt->execute();
$stmt->bind_result($idContainer);
$stmt->fetch();
$stmt->close();

if (!$idContainer) {
    echo json_encode(['success'=>false, 'error'=>'No se encontró el container asociado']);
    exit;
}

// Actualizar Number_PO en items para ese containera
$update = "UPDATE items SET Number_PO = ? WHERE id_container = ?";
$stmt2 = $conexion->prepare($update);
$stmt2->bind_param('si', $po, $idContainer);

if ($stmt2->execute()) {
    echo json_encode(['success'=>true]);
} else {
    echo json_encode(['success'=>false, 'error'=>$stmt2->error]);
}
?>
