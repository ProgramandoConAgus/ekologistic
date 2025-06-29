<?php
header('Content-Type: application/json');
include('../../con_db.php');

try {
    $op      = $_GET['op']       ?? '';
    $lot     = $_GET['lot']      ?? '';
    $from    = $_GET['dateFrom'] ?? '';
    $to      = $_GET['dateTo']   ?? '';

    $sql = "
      SELECT
        d.id,
        i.idItem        AS idItem,
        c.num_op        AS NUM_OP,
        c.Number_Container,
        c.Booking_BK,
        d.numero_lote   AS Lot_Number,
        d.fecha_entrada AS Entry_Date,
        d.fecha_salida  AS Out_Date,
        i.Number_PO     AS Number_PO,
        d.numero_parte  AS Code_Product_EC,
        d.descripcion   AS Description,
        d.cantidad      AS Qty,
        d.valor_unitario AS Unit_Value,
        d.valor         AS Value,
        d.unidad        AS Unit,
        d.longitud_in   AS Length_in,
        d.ancho_in      AS Broad_in,
        d.altura_in     AS Height_in,
        d.peso_lb       AS Weight_lb,
        d.estado        AS Status,
        d.recibo_almacen AS Receive
      FROM container c
      JOIN dispatch  d ON c.Number_Commercial_Invoice=d.numero_factura
                      AND c.Number_Container=d.notas
      JOIN items     i ON i.idContainer = c.idContainer
      WHERE d.estado = 'Cargado'
    ";

    $conds  = [];
    $types  = '';
    $params = [];

    if ($op!=='') {
      $conds[]  = "c.num_op LIKE ?";
      $params[] = "%$op%"; $types.='s';
    }
    if ($lot!=='') {
      $conds[]  = "d.numero_lote LIKE ?";
      $params[] = "%$lot%"; $types.='s';
    }
    if ($from!=='' && $to!=='') {
      // <-- COMPARAR SOLO FECHA (sin hora)
      $conds[]  = "DATE(d.fecha_entrada) BETWEEN ? AND ?";
      $params[] = $from;           // yyyy-mm-dd
      $params[] = $to;             // yyyy-mm-dd
      $types   .= 'ss';
    }

    if (count($conds)>0) {
      $sql .= " AND ".implode(' AND ',$conds);
    }
    $sql .= " ORDER BY c.num_op, d.numero_parte";

    $stmt = $conexion->prepare($sql);
    if ($params) $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $data = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    echo json_encode($data);

} catch(Exception $e) {
    http_response_code(500);
    echo json_encode(['error'=>$e->getMessage()]);
}
