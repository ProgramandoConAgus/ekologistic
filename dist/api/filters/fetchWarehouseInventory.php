<?php
header('Content-Type: application/json');
include('../../con_db.php');

try {
    // Obtener parámetros de filtrado
    $container = $_GET['container'] ?? '';
    $customer = $_GET['customer'] ?? '';
    $po = $_GET['po'] ?? '';
    $start = $_GET['dateFrom'] ?? '';
    $end = $_GET['dateTo'] ?? '';

    // Construir consulta base modificada
    $sql = "SELECT 
        pl.IdPackingList AS 'ITEM #',
        i.EntryDate,
        i.IdItem,
        c.num_op AS 'Num OP',
        c.Booking_BK,
        c.Number_Commercial_Invoice AS 'Number_Commercial Invoice',
        i.Number_Lot AS 'Number LOT',
        i.Number_PO,
        i.Customer,
        i.Description,
        i.Qty_Box,
        i.Price_Box_EC AS 'PRICE BOX EC',
        i.Total_Price_EC AS 'TOTAL PRICE EC',
        c.Number_Container AS 'Number_Container'
    FROM container c
    JOIN items i ON c.IdContainer = i.idContainer
    JOIN packing_list pl ON pl.IdPackingList = c.idPackingList
    WHERE c.status = 'Completed'";  // Filtro principal modificado

    $conditions = [];
    $params = [];
    $types = '';

    // Añadir condiciones dinámicas
    if (!empty($container)) {
        $conditions[] = "c.Number_Container LIKE ?";
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
        $conditions[] = "i.EntryDate BETWEEN ? AND ?";
        array_push($params, "$start 00:00:00", "$end 23:59:59");
        $types .= 'ss';
    }

    // Combinar condiciones
    if (!empty($conditions)) {
        $sql .= " AND " . implode(" AND ", $conditions);
    }

    // Ordenar por fecha de entrada
    $sql .= " ORDER BY i.EntryDate DESC";

    // Preparar y ejecutar consulta
    $stmt = $conexion->prepare($sql);
    if (!$stmt) throw new Exception("Error en preparación: ".$conexion->error);

    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }

    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_all(MYSQLI_ASSOC);

    // Formatear fechas de entrada
    foreach ($data as &$row) {
        $row['EntryDate'] = $row['EntryDate'] 
            ? date('d/m/Y', strtotime($row['EntryDate']))
            : '';
    }

    echo json_encode($data);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}