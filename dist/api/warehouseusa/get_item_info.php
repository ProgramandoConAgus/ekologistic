<?php
header('Content-Type: application/json');
require '../../con_db.php';

// Obtener parámetros
$booking     = $_GET['booking']     ?? '';
$description = $_GET['description'] ?? '';
if (!$booking || !$description) {
    echo json_encode(['success' => false, 'msg' => 'Faltan parámetros requeridos']);
    exit;
}

// Consulta para obtener datos del ítem
$sql = "
SELECT
    i.Qty_Box             AS cantidad,
    i.Price_Box_EC        AS valor_unitario,
    i.Total_Price_EC      AS valor,
    i.Packing_Unit        AS unidad,
    i.Total_Weight_kg     AS peso_kg
FROM items i
JOIN container c ON i.idContainer = c.IdContainer
WHERE c.Booking_BK = ?
  AND i.Description = ?
LIMIT 1";

$stmt = $conexion->prepare($sql);
$stmt->bind_param('ss', $booking, $description);
$stmt->execute();
$item = $stmt->get_result()->fetch_assoc();

if (!$item) {
    echo json_encode(['success' => false, 'msg' => 'Producto no encontrado']);
    exit;
}

// Convertir peso de kg a libras (lb)
$peso_lb = round($item['peso_kg'] * 2.20462, 2);

// Preparar respuesta
$response = [
    'success' => true,
    'data'    => [
        'cantidad'        => $item['cantidad'],       // Cantidad de cajas
        'valor_unitario'  => $item['valor_unitario'], // Valor unitario (EC)
        'valor'           => $item['valor'],          // Valor total (EC)
        'unidad'          => $item['unidad'],         // Packing unit
        'peso'            => $peso_lb                  // Peso total en libras
    ]
];

echo json_encode($response);
