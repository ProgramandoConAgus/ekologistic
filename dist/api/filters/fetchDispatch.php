<?php
header('Content-Type: application/json');
include('../../con_db.php');

try {
    // 1) Recuperar filtros
    $op       = $_GET['op']       ?? '';
    $dispatch = $_GET['dispatch'] ?? '';
    $invoice  = $_GET['invoice']  ?? '';

    // 2) Consulta base (tablas actuales)
    $sql = "
      SELECT
  d.id,
  c.num_op AS NUM_OP,
  c.Number_Container,
  c.Booking_BK,
  d.fecha_entrada AS Entry_Date,
  d.fecha_salida AS departure_date,
  d.recibo_almacen AS Receive,
  d.numero_lote AS Lot_Number,
  d.numero_factura AS Number_Commercial_Invoice,
  d.numero_parte AS Code_Product_EC,
  d.descripcion AS Description_Dispatch,
  d.modelo AS Modelo_Dispatch,

  (SELECT i.Description FROM items i 
   WHERE i.Number_Commercial_Invoice = d.numero_factura 
     AND i.Code_Product_EC = d.numero_parte
   ORDER BY i.Number_PO LIMIT 1) AS Description_Item,

  (SELECT i.Number_PO FROM items i 
   WHERE i.Number_Commercial_Invoice = d.numero_factura 
     AND i.Code_Product_EC = d.numero_parte
   ORDER BY i.Number_PO LIMIT 1) AS Number_PO,

  (SELECT SUM(i.Qty_Box) FROM items i 
   WHERE i.Number_Commercial_Invoice = d.numero_factura 
     AND i.Code_Product_EC = d.numero_parte) AS Qty_Item_Packing,

  d.palets AS palets,
  d.codigo_despacho,
  d.cantidad AS cantidad,
  (d.palets * d.cantidad) AS Total_Despachado,
  d.valor_unitario AS Unit_Value,
  (d.valor_unitario * d.cantidad) AS Value,
  d.unidad AS Unit,
  d.longitud_in AS Length_in,
  d.ancho_in AS Broad_in,
  d.altura_in AS Height_in,
  d.peso_lb AS Weight_lb,
  d.valor_unitario_restante,
  d.valor_restante,
  d.unidad_restante,
  d.longitud_in_restante,
  d.ancho_in_restante,
  d.altura_in_restante,
  d.peso_lb_restante,
  d.estado AS Status

FROM palets_cargados d
LEFT JOIN container c
  ON c.Number_Container = d.notas

WHERE d.estado = 'Cargado'


    ";

    // 3) Armar condiciones dinámicas
    $conds  = [];
    $params = [];
    $types  = '';

    if ($op !== '') {
        $conds[]  = "c.num_op LIKE ?";
        $params[] = "%{$op}%";
        $types   .= 's';
    }

    if ($dispatch !== '') {
        $conds[]  = "d.codigo_despacho LIKE ?";
        $params[] = "%{$dispatch}%";
        $types   .= 's';
    }

    if ($invoice !== '') {
        $conds[]  = "d.numero_factura LIKE ?";
        $params[] = "%{$invoice}%";
        $types   .= 's';
    }


    if (count($conds) > 0) {
        $sql .= ' AND ' . implode(' AND ', $conds);
    }

    $sql .= " ORDER BY c.num_op, d.descripcion, d.modelo;";

    // 4) Preparar y ejecutar
    $stmt = $conexion->prepare($sql);
    if ($params) {
        $stmt->bind_param($types, ...$params);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    // 5) Devolver JSON
    $data = $result->fetch_all(MYSQLI_ASSOC);
    echo json_encode($data);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
