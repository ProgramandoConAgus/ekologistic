<?php
// fetchIndex.php
header('Content-Type: application/json');
include('../../con_db.php');

try {
    // Obtener parámetros de filtrado
    $numOp   = $_GET['container'] ?? '';
    $destiny = $_GET['destiny']   ?? '';
    $start   = $_GET['dateFrom']  ?? '';
    $end     = $_GET['dateTo']    ?? '';

    // Consulta base
    $sql = "
      SELECT 
        pl.IdPackingList AS 'ITEM #',
        i.IdItem,
        c.Number_Container AS 'Number Container',
        c.Num_OP           AS 'Num OP',
        c.Forwarder,
        c.Shipping_Line    AS 'Shipping Line',
        c.Destinity_POD    AS 'Destinity POD',
        c.Departure_Date_Port_Origin_EC AS 'Departure Port Origin EC',
        c.Booking_BK,
        COUNT(DISTINCT c.IdContainer)   AS 'Total Containers',
        SUM(i.Qty_Box)                  AS 'Total Boxes',
        c.ETA_Date       AS 'ETA Date',
        c.New_ETA_Date   AS 'NEW ETA DATE',
        SUM(i.Total_Price_EC)  AS 'TOTAL PRICE EC',
        SUM(i.Total_Price_USA) AS 'TOTAL PRICE USA',
        c.Status,
        c.IdContainer
      FROM container c
      JOIN items i   ON c.IdContainer = i.idContainer
      JOIN packing_list pl ON pl.IdPackingList = c.idPackingList
      WHERE c.Status != 'completo'
    ";

    $conditions = [];
    $params     = [];
    $types      = '';

    if (!empty($numOp)) {
        $conditions[] = "c.Num_OP LIKE ?";
        $params[]     = "%{$numOp}%";
        $types       .= 's';
    }
    if (!empty($destiny)) {
        $conditions[] = "c.Destinity_POD LIKE ?";
        $params[]     = "%{$destiny}%";
        $types       .= 's';
    }
    if (!empty($start) && !empty($end)) {
        $conditions[] = "c.ETA_Date BETWEEN ? AND ?";
        $params[]     = "{$start} 00:00:00";
        $params[]     = "{$end}   23:59:59";
        $types       .= 'ss';
    }

    if ($conditions) {
        $sql .= " AND " . implode(" AND ", $conditions);
    }

    $sql .= "
      GROUP BY c.Number_Container
      ORDER BY c.Departure_Date_Port_Origin_EC DESC
    ";

    $stmt = $conexion->prepare($sql);
    if (!$stmt) {
        throw new Exception("Error en preparación: " . $conexion->error);
    }

    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }

    $stmt->execute();
    $result = $stmt->get_result();
    $data   = $result->fetch_all(MYSQLI_ASSOC);

    echo json_encode($data);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
