<?php
header('Content-Type: application/json');

include('../../con_db.php');

// Parámetros GET
$customer  = $_GET['customer']  ?? '';
$po        = $_GET['po']        ?? '';
$container = $_GET['container'] ?? '';
$dateFrom  = $_GET['dateFrom']  ?? '';
$dateTo    = $_GET['dateTo']    ?? '';

// Consulta base
$sql = "
    SELECT 
      pl.IdPackingList     AS `ITEM #`,
      i.IdItem,
      c.num_op             AS `Num OP`,
      i.Number_PO,
      i.Customer,
      i.Description,
      i.Qty_Box,
      i.Price_Box_EC       AS `PRICE BOX EC`,
      i.Total_Price_EC     AS `TOTAL PRICE EC`,
      i.Price_Box_USA      AS `PRICE BOX USA`,
      i.Total_Price_USA    AS `TOTAL PRICE USA`,
      CASE WHEN c.status = 'Completed' THEN 'Inventory' ELSE 'Transit' END AS `STATUS`
    FROM container c
    JOIN items i   ON c.IdContainer   = i.idContainer
    JOIN packing_list pl ON pl.IdPackingList = c.idPackingList
    WHERE 1=1
";

$types  = '';      // tipos para bind_param
$values = [];      // valores a bindear

if ($customer !== '') {
    $sql    .= " AND i.Customer = ?";
    $types  .= 's';
    $values[] = $customer;
}
if ($po !== '') {
    $sql    .= " AND i.Number_PO LIKE ?";
    $types  .= 's';
    $values[] = "%{$po}%";
}
if ($container !== '') {
    $sql    .= " AND c.num_op LIKE ?";
    $types  .= 's';
    $values[] = "%{$container}%";
}

// Preparar
$stmt = $conexion->prepare($sql);
if (!$stmt) {
    http_response_code(500);
    echo json_encode(['error' => "Prepare failed: {$conexion->error}"]);
    exit;
}

// Bind dinámico si hay valores
if (!empty($values)) {
    // mysqli_stmt::bind_param requiere variables por referencia
    $bind_names[] = $types;
    for ($i=0; $i < count($values); $i++) {
        $bind_name = 'bind' . $i;
        $$bind_name = $values[$i];
        $bind_names[] = &$$bind_name;
    }
    call_user_func_array([$stmt, 'bind_param'], $bind_names);
}

// Ejecutar
if (!$stmt->execute()) {
    http_response_code(500);
    echo json_encode(['error' => "Execute failed: {$stmt->error}"]);
    exit;
}

// Obtener resultados
$result = $stmt->get_result();
$data = $result->fetch_all(MYSQLI_ASSOC);

echo json_encode($data);