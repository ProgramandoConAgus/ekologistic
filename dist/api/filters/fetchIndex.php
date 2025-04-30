<?php
header('Content-Type: application/json');
include('../../con_db.php');

try {
    // Obtener parámetros de filtrado
    $customer = $_GET['customer'] ?? '';
    $po = $_GET['po'] ?? '';
    $container = $_GET['container'] ?? '';
    $start = $_GET['dateFrom'] ?? '';
    $end = $_GET['dateTo'] ?? '';

    // Construir consulta base
    $sql = "SELECT 
        pl.IdPackingList AS 'ITEM #',
        i.IdItem,
        c.Number_Container AS 'Number Container',
        c.Num_OP AS 'Num OP',
        c.Forwarder AS 'Forwarder',
        c.Shipping_Line AS 'Shipping Line',
        c.Destinity_POD AS 'Destinity POD',
        c.Departure_Date_Port_Origin_EC AS 'Departure Port Origin EC',
        c.Booking_BK AS 'Booking_BK',
        COUNT(DISTINCT c.IdContainer) AS 'Total Containers',
        SUM(i.Qty_Box) AS 'Total Boxes',
        c.ETA_Date AS 'ETA Date',
        c.New_ETA_Date AS 'NEW ETA DATE',
        SUM(i.Total_Price_EC) AS 'TOTAL PRICE EC',
        SUM(i.Total_Price_USA) AS 'TOTAL PRICE USA',
        c.Status AS 'status',
        c.IdContainer
    FROM container c
    JOIN items i ON c.IdContainer = i.idContainer
    JOIN packing_list pl ON pl.IdPackingList = c.idPackingList
    WHERE c.Status != 'completo'";

    $conditions = [];
    $params = [];
    $types = '';

    // Añadir condiciones dinámicas
    if (!empty($customer)) {
        $conditions[] = "i.Customer = ?";
        $params[] = $customer;
        $types .= 's';
    }
    
    if (!empty($po)) {
        $conditions[] = "i.Number_PO LIKE ?";
        $params[] = "%$po%";
        $types .= 's';
    }
    
    if (!empty($container)) {
        $conditions[] = "c.Num_OP LIKE ?";
        $params[] = "%$container%";
        $types .= 's';
    }
    
    if (!empty($start) && !empty($end)) {
        $conditions[] = "c.ETA_Date BETWEEN ? AND ?";
        array_push($params, "$start 00:00:00", "$end 23:59:59");
        $types .= 'ss';
    }

    // Combinar condiciones
    if (!empty($conditions)) {
        $sql .= " AND " . implode(" AND ", $conditions);
    }

    // Agrupar y ordenar
    $sql .= " GROUP BY c.Number_Container 
              ORDER BY c.Departure_Date_Port_Origin_EC DESC";

    // Preparar y ejecutar consulta
    $stmt = $conexion->prepare($sql);
    if (!$stmt) throw new Exception("Error en preparación: ".$conexion->error);

    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }

    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_all(MYSQLI_ASSOC);

    echo json_encode($data);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}