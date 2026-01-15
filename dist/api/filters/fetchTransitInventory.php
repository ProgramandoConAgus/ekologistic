<?php
header('Content-Type: application/json');
include('../../con_db.php');

try {
    // Obtener todos los parámetros de filtrado
    $container = $_GET['container'] ?? '';
    $customer = $_GET['customer'] ?? '';
    $po = $_GET['po'] ?? '';
    $start = $_GET['dateFrom'] ?? '';
    $end = $_GET['dateTo'] ?? '';

    // Construir consulta base
    $sql = "SELECT 
        pl.IdPackingList AS 'ITEM #',
        i.IdItem,
        c.num_op AS 'Num OP',
        c.Booking_BK,
        c.Number_Container,
        i.Number_Lot AS 'Number LOT',
        i.Number_PO,
        i.Customer,
        i.Description,
        i.Qty_Box,
        i.Price_Box_EC AS 'PRICE BOX EC',
        i.Total_Price_EC AS 'TOTAL PRICE EC',
        i.valor_logistico_comex AS 'VALOR_LOGISTICO_COMEX',
        (i.valor_logistico_comex * i.Qty_Box) AS 'COMEX TOTAL',
        c.ETA_Date AS 'ETA Date',
        c.New_ETA_DATE AS 'NEW ETA DATE'
    FROM container c
    JOIN items i ON c.IdContainer = i.idContainer
    JOIN packing_list pl ON pl.IdPackingList = c.idPackingList
    WHERE c.status != 'Completed'";

    $conditions = [];
    $params = [];
    $types = '';

    // Añadir condiciones dinámicas
    if (!empty($container)) {
        $conditions[] = "c.num_op LIKE ?";
        $params[] = "%$container%";
        $types .= 's';
    }
    
    if (!empty($customer)) {
        $conditions[] = "i.Customer LIKE ?";
        $params[] = "%$customer%";
        $types .= 's';
    }
    
    if (!empty($po)) {
        $conditions[] = "i.Number_PO LIKE ?";
        $params[] = "%$po%";
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

    // Ordenar por fecha ETA
    $sql .= " ORDER BY c.ETA_Date DESC";

    // Preparar y ejecutar consulta
    $stmt = $conexion->prepare($sql);
    if (!$stmt) throw new Exception("Error en preparación: ".$conexion->error);

    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }

    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_all(MYSQLI_ASSOC);

    // Formatear fechas
    foreach ($data as &$row) {
        $row['ETA Date'] = date('d/m/Y', strtotime($row['ETA Date']));
        $row['NEW ETA DATE'] = $row['NEW ETA DATE'] 
            ? date('d/m/Y', strtotime($row['NEW ETA DATE']))
            : 'No cargado';
    }

    echo json_encode($data);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}