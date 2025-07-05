<?php
header('Content-Type: application/json');
require '../../con_db.php';

// Obtener parámetro Booking
$booking = $_GET['booking'] ?? '';
if (!$booking) {
    echo json_encode(['success' => false, 'msg' => 'Booking inválido']);
    exit;
}

// 1) Obtener descripciones únicas para el Booking
$sql1 = "
SELECT DISTINCT i.Description
FROM items i
JOIN container c ON i.idContainer = c.IdContainer
WHERE c.Booking_BK = ?
ORDER BY i.Description
";
$stmt1 = $conexion->prepare($sql1);
$stmt1->bind_param('s', $booking);
$stmt1->execute();
$res1 = $stmt1->get_result();
$descriptions = [];
while ($row = $res1->fetch_assoc()) {
    $descriptions[] = $row['Description'];
}

$sql2 = "
SELECT
    i.Qty_Box AS cantidad_total,
    GROUP_CONCAT(DISTINCT i.Number_Commercial_Invoice) AS numero_factura,
    GROUP_CONCAT(DISTINCT i.Number_Lot SEPARATOR ', ') AS numero_lote,
    GROUP_CONCAT(DISTINCT d.numero_orden_compra SEPARATOR ', ') AS numero_orden_compra,
    GROUP_CONCAT(DISTINCT i.Code_Product_EC SEPARATOR ', ') AS numero_parte
FROM items i
JOIN container c ON i.idContainer = c.IdContainer
LEFT JOIN dispatch d
  ON i.Number_Commercial_Invoice = d.numero_factura
 AND c.Number_Container          = d.notas
 AND d.numero_parte              = i.Code_Product_EC
WHERE c.Booking_BK = ?
";
$stmt2 = $conexion->prepare($sql2);
$stmt2->bind_param('s', $booking);
$stmt2->execute();
$res2 = $stmt2->get_result()->fetch_assoc();

$cantidadTotal      = (int) $res2['cantidad_total'];
$numeroFactura      = $res2['numero_factura'];
$numeroLote         = $res2['numero_lote'];
$numeroOrdenCompra  = $res2['numero_orden_compra'];
$numeroParte        = $res2['numero_parte'];

// 3) Devolver JSON con descripciones y datos adicionales
echo json_encode([
    'success'            => true,
    'descriptions'       => $descriptions,
    'cantidad_total'     => $cantidadTotal,
    'numero_factura'     => $numeroFactura,
    'numero_lote'        => $numeroLote,
    'numero_orden_compra'=> $numeroOrdenCompra,
    'numero_parte'       => $numeroParte,
]);
