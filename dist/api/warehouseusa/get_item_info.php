<?php
header('Content-Type: application/json; charset=utf-8');
require '../../con_db.php';

$booking     = $_GET['booking']     ?? '';
$description = $_GET['description'] ?? '';
if (!$booking || !$description) {
    echo json_encode(['success'=>false,'msg'=>'Faltan parámetros requeridos']);
    exit;
}

$sql = "
SELECT
    i.Qty_Box                       AS cantidad,
    i.Price_Box_EC                  AS valor_unitario,
    i.Total_Price_EC                AS valor,
    i.Packing_Unit                  AS unidad,
    ROUND(i.Total_Weight_kg*2.20462,2) AS peso,

    i.Number_Commercial_Invoice     AS numero_factura,
    i.Number_Lot                    AS numero_lote,
    i.Number_PO                     AS numero_orden_compra,
    i.Code_Product_EC               AS numero_parte,

    d.recibo_almacen                AS recibo_almacen

  FROM items i
  JOIN container c 
    ON i.idContainer = c.IdContainer

  -- Sólo unimos por Number_Container, para traer el recibo
  LEFT JOIN dispatch d
    ON d.notas = c.Number_Container

  WHERE c.Booking_BK   = ?
    AND i.Description  = ?
  LIMIT 1
";
$sql = "
SELECT
    i.Qty_Box                       AS cantidad,
    i.Price_Box_EC                  AS valor_unitario,
    i.Total_Price_EC                AS valor,
    i.Packing_Unit                  AS unidad,
    ROUND(i.Total_Weight_kg*2.20462,2) AS peso,

    i.Number_Commercial_Invoice     AS numero_factura,
    i.Number_Lot                    AS numero_lote,
    i.Number_PO                     AS numero_orden_compra,
    i.Code_Product_EC               AS numero_parte,

    d.recibo_almacen                AS recibo_almacen

  FROM items i
  JOIN container c 
    ON i.idContainer = c.IdContainer

  -- Sólo unimos por Number_Container, para traer el recibo
  LEFT JOIN dispatch d
    ON d.notas = c.Number_Container

  WHERE c.Booking_BK   = ?
    AND i.Description  = ?
  LIMIT 1
";


$stmt = $conexion->prepare($sql);
$stmt->bind_param('ss', $booking, $description);
$stmt->execute();
$item = $stmt->get_result()->fetch_assoc();

if (!$item) {
    echo json_encode(['success'=>false,'msg'=>'Producto no encontrado']);
    exit;
}

echo json_encode(['success'=>true,'data'=>$item], JSON_UNESCAPED_UNICODE);
