<?php
header('Content-Type: application/json');
include('../../con_db.php');

try {
    // Obtener parámetros de filtrado
    $container = $_GET['container'] ?? '';
    $start = $_GET['dateFrom'] ?? '';
    $end = $_GET['dateTo'] ?? '';

    // Construir consulta base
    $sql = "SELECT 
        pl.IdPackingList AS 'ITEM #',
        MAX(c.num_op) AS 'Num OP',
        MAX(c.Destinity_POD) AS 'Destinity POD',
        MAX(c.Booking_BK) AS 'Booking_BK',
        MAX(c.Number_Container) AS 'Number_Container',
        SUM(i.Qty_Box) AS 'Qty_Box',
        SUM(i.Total_Price_EC) AS 'TOTAL PRICE EC',
        pl.Date_Created AS 'Date created',
        DATE_FORMAT(pl.Date_Created, '%H:%i') AS 'Hour',
        CONCAT(u.nombre, ' ', u.apellido) AS 'User Name',
        pl.path_file AS 'File Home',
        pl.status AS 'STATUS'
    FROM 
        packing_list pl
    JOIN 
        usuarios u ON pl.IdUsuario = u.IdUsuario
    JOIN 
        container c ON pl.IdPackingList = c.IdPackingList
    JOIN 
        items i ON c.IdContainer = i.IdContainer
    WHERE 1=1";

    $conditions = [];
    $params = [];
    $types = '';

    // Añadir condiciones dinámicas
    if (!empty($container)) {
        $conditions[] = "c.num_op LIKE ?";
        $params[] = "%$container%";
        $types .= 's';
    }
    
    if (!empty($start) && !empty($end)) {
        $conditions[] = "pl.Date_Created BETWEEN ? AND ?";
        array_push($params, "$start 00:00:00", "$end 23:59:59");
        $types .= 'ss';
    }

    // Combinar condiciones
    if (!empty($conditions)) {
        $sql .= " AND " . implode(" AND ", $conditions);
    }

    // Agrupar y ordenar
    $sql .= " GROUP BY pl.IdPackingList";

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