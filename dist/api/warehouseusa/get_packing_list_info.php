<?php
header('Content-Type: application/json');
require '../../con_db.php';

$id = intval($_GET['packing_list'] ?? 0);
if (!$id) {
  echo json_encode(['success'=>false,'msg'=>'ID inválido']);
  exit;
}

// 2.1) Consulta: suma de cajas y concat de lotes, partes, PO e invoice
$sql = "
  SELECT
    GROUP_CONCAT(DISTINCT i.Number_Commercial_Invoice)     AS numero_factura,
    SUM(i.Qty_Box)                                         AS cantidad_total,
    GROUP_CONCAT(DISTINCT i.Number_Lot SEPARATOR ', ')     AS numero_lote,
    GROUP_CONCAT(DISTINCT i.Code_Product_EC SEPARATOR ', ')AS numero_parte,
    GROUP_CONCAT(DISTINCT i.Number_PO SEPARATOR ', ')      AS numero_orden_compra
  FROM items i
  JOIN container c ON i.idContainer = c.IdContainer
  WHERE c.idPackingList = ?
";
$stmt = $conexion->prepare($sql);
$stmt->bind_param('i', $id);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();

if ($data) {
  echo json_encode(['success'=>true,'data'=>$data]);
} else {
  echo json_encode(['success'=>false,'msg'=>'No se encontraron datos.']);
}
