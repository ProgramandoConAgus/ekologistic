<?php
header('Content-Type: application/json');
include('../../con_db.php');

try {
    // 1) Recuperar filtros
    $container = $_GET['container']  ?? '';
    $op        = $_GET['op']         ?? '';
    $from      = $_GET['dateFrom']   ?? '';
    $to        = $_GET['dateTo']     ?? '';

    // 2) Construir consulta base
    $sql = "
      SELECT
        d.id,
        c.num_op                     AS NUM_OP,
        c.Number_Container           AS Number_Container,
        c.Booking_BK                 AS Booking_BK,
        d.numero_lote                AS Lot_Number,
        d.fecha_entrada              AS Entry_Date,
        d.recibo_almacen             AS recibo_almacen,
        c.Number_Commercial_Invoice  AS Number_Commercial_Invoice,
        d.numero_parte               AS Code_Product_EC,
        d.descripcion                AS Description,
        d.cantidad                   AS Qty,
        d.valor_unitario             AS Unit_Value,
        d.valor                      AS Value,
        d.unidad                     AS Unit,
        d.longitud_in                AS Length_in,
        d.ancho_in                   AS Broad_in,
        d.altura_in                  AS Height_in,
        d.peso_lb                    AS Weight_lb,
        d.estado                     AS Status
        i.Number_PO                  AS Number_PO,
      FROM container c
      INNER JOIN dispatch d
        ON c.Number_Commercial_Invoice = d.numero_factura
       AND c.Number_Container         = d.notas
      INNER JOIN items i
        ON i.idContainer = c.idContainer
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
    if ($from !== '' && $to !== '') {
        $conds[]  = "DATE(d.fecha_entrada) BETWEEN ? AND ?";
        $params[] = $from;
        $params[] = $to;
        $types   .= 'ss';
    }


    if (count($conds) > 0) {
        $sql .= ' AND ' . implode(' AND ', $conds);
    }

    $sql .= " ORDER BY c.num_op, d.numero_parte";

    // 4) Preparar y ejecutar
    $stmt = $conexion->prepare($sql);
    if ($params) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $data = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    // 5) Devolver JSON
    echo json_encode($data);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
