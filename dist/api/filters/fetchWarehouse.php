<?php
header('Content-Type: application/json');
include('../../con_db.php');

try {
    // 1) Recuperar filtros
    $container = $_GET['container']  ?? '';
    $op        = $_GET['op']         ?? '';
    $lot       = $_GET['lot']        ?? '';
    $from      = $_GET['dateFrom']   ?? '';
    $to        = $_GET['dateTo']     ?? '';

    // 2) Consulta base con el JOIN exacto
    $sql = "
      SELECT
        d.id,
        i.idItem                      AS idItem,
        c.num_op                      AS NUM_OP,
        c.Number_Container            AS Number_Container,
        c.Booking_BK                  AS Booking_BK,
        d.numero_lote                 AS Lot_Number,
        d.fecha_entrada               AS Entry_Date,
        i.Number_Commercial_Invoice   AS Number_Commercial_Invoice,
        d.numero_parte                AS Code_Product_EC,
        d.descripcion                 AS Description,
        d.cantidad                    AS Qty,
        d.valor_unitario              AS Unit_Value,
        d.valor                       AS Value,
        d.unidad                      AS Unit,
        d.longitud_in                 AS Length_in,
        d.ancho_in                    AS Broad_in,
        d.altura_in                   AS Height_in,
        d.peso_lb                     AS Weight_lb,
        d.estado                      AS Status,
        d.recibo_almacen              AS Receive,
        i.Number_PO                   AS Number_PO
      FROM container c
      INNER JOIN items i
        ON i.idContainer = c.idContainer
      INNER JOIN dispatch d
        ON d.numero_factura   = i.Number_Commercial_Invoice
       AND d.notas            = c.Number_Container
       AND d.numero_parte     = i.Code_Product_EC
      WHERE d.estado = 'En Almacén'
    ";

    // 3) Condiciones dinámicas
    $conds  = [];
    $params = [];
    $types  = '';

    if ($container !== '') {
        $conds[]  = "c.Number_Container LIKE ?";
        $params[] = "%{$container}%";
        $types   .= 's';
    }
    if ($op !== '') {
        $conds[]  = "c.num_op LIKE ?";
        $params[] = "%{$op}%";
        $types   .= 's';
    }
    if ($lot !== '') {
        $conds[]  = "d.numero_lote LIKE ?";
        $params[] = "%{$lot}%";
        $types   .= 's';
    }
    if ($from !== '' && $to !== '') {
        $conds[]  = "d.fecha_entrada BETWEEN ? AND ?";
        $params[] = $from . ' 00:00:00';
        $params[] = $to   . ' 23:59:59';
        $types   .= 'ss';
    } elseif ($from !== '') {
        $conds[]  = "d.fecha_entrada >= ?";
        $params[] = $from . ' 00:00:00';
        $types   .= 's';
    } elseif ($to !== '') {
        $conds[]  = "d.fecha_entrada <= ?";
        $params[] = $to   . ' 23:59:59';
        $types   .= 's';
    }

    if (count($conds) > 0) {
        // añadimos cada condición con AND
        $sql .= ' AND ' . implode(' AND ', $conds);
    }

    $sql .= " ORDER BY c.num_op, d.numero_parte";

    // 4) Preparar y ejecutar
    $stmt = $conexion->prepare($sql);
    if ($params) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();

    // 5) Devolver resultado
    $data = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    echo json_encode($data);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
