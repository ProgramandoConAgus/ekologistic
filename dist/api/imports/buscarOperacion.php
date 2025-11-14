<?php
include("../../con_db.php");
header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        throw new Exception('Método no permitido');
    }

    $num_op = isset($_POST['num_op']) ? trim($_POST['num_op']) : '';
    if (empty($num_op)) {
        http_response_code(400);
        throw new Exception('Número de operación no recibido');
    }

    $query = "
             SELECT 
    e.ExportsID AS ID,
    e.Booking_BK,
    e.status,
    e.creation_date,
    c.num_op,
    'exports' AS origen
FROM exports e
LEFT JOIN (
    -- aggregate by booking (mapping moved to invoice tables)
    SELECT ct.Booking_BK, MIN(ct.num_op) AS num_op
    FROM items i
    INNER JOIN container ct ON i.idContainer = ct.IdContainer
    WHERE ct.num_op = ?
    GROUP BY ct.Booking_BK
) c ON e.Booking_BK = c.Booking_BK
WHERE e.status = 2 AND c.num_op IS NOT NULL

UNION ALL

SELECT 
    i.ImportsID AS ID,
    i.Booking_BK,
    i.status,
    i.creation_date,
    c.num_op,
    'imports' AS origen
FROM imports i
LEFT JOIN (
    SELECT ct.Booking_BK, MIN(ct.num_op) AS num_op
    FROM items i
    INNER JOIN container ct ON i.idContainer = ct.IdContainer
    WHERE ct.num_op = ?
    GROUP BY ct.Booking_BK
) c ON i.Booking_BK = c.Booking_BK
WHERE i.status = 2 AND c.num_op IS NOT NULL

UNION ALL

SELECT 
    d.DespachoID AS ID,
    d.Booking_BK,
    d.status,
    d.creation_date,
    c.num_op,
    'despacho' AS origen
FROM despacho d
LEFT JOIN (
    SELECT ct.Booking_BK, MIN(ct.num_op) AS num_op
    FROM items i
    INNER JOIN container ct ON i.idContainer = ct.IdContainer
    WHERE ct.num_op = ?
    GROUP BY ct.Booking_BK
) c ON d.Booking_BK = c.Booking_BK
WHERE d.status = 2 AND c.num_op IS NOT NULL

ORDER BY creation_date DESC

    ";

    $stmt = $conexion->prepare($query);
    if (!$stmt) {
        throw new Exception("Error preparando consulta: " . $conexion->error);
    }

    // Bind con 3 parámetros para los 3 '?' en la consulta
    $stmt->bind_param("sss", $num_op, $num_op, $num_op);

    if (!$stmt->execute()) {
        throw new Exception("Error ejecutando consulta: " . $stmt->error);
    }

    $result = $stmt->get_result();

    $registros = [];
    while ($row = $result->fetch_assoc()) {
        $registros[] = $row;
    }

    // Attach mapped invoices per record (Number_Commercial_Invoice) using helper
    include_once __DIR__ . '/../helpers/mapping.php';
    foreach ($registros as &$r) {
        $origin = $r['origen'] ?? '';
        $id = intval($r['ID'] ?? 0);
        $invs = fetch_mapped_invoices($conexion, $origin, $id);
        $r['Number_Commercial_Invoice'] = !empty($invs) ? implode(', ', $invs) : '';
    }
    unset($r);

    if (empty($registros)) {
        echo json_encode([
            'success' => false,
            'message' => 'No se encontraron registros para esta operación.'
        ]);
        exit;
    }

    echo json_encode([
        'success' => true,
        'resultados' => $registros
    ]);
    exit;

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error en la consulta.',
        'error'   => $e->getMessage()
    ]);
    exit;
}
?>
