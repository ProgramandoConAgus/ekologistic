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
          e.Number_Commercial_Invoice,
          e.status,
          e.creation_date,
          c.num_op,
          'exports' AS origen
        FROM exports e
        LEFT JOIN (
          SELECT Number_Commercial_Invoice, MIN(num_op) AS num_op
          FROM container
          WHERE num_op = ?
          GROUP BY Number_Commercial_Invoice
        ) c ON e.Number_Commercial_Invoice = c.Number_Commercial_Invoice
        WHERE e.status = 2 AND c.num_op IS NOT NULL

        UNION ALL

        SELECT 
          i.ImportsID AS ID,
          i.Booking_BK,
          i.Number_Commercial_Invoice,
          i.status,
          i.creation_date,
          c.num_op,
          'imports' AS origen
        FROM imports i
        LEFT JOIN (
          SELECT Number_Commercial_Invoice, MIN(num_op) AS num_op
          FROM container
          WHERE num_op = ?
          GROUP BY Number_Commercial_Invoice
        ) c ON i.Number_Commercial_Invoice = c.Number_Commercial_Invoice
        WHERE i.status = 2 AND c.num_op IS NOT NULL

        ORDER BY creation_date DESC
    ";

    $stmt = $conexion->prepare($query);
    if (!$stmt) {
        throw new Exception("Error preparando consulta: " . $conexion->error);
    }

    $stmt->bind_param("ss", $num_op, $num_op);
    $stmt->execute();
    $result = $stmt->get_result();

    $registros = [];
    while ($row = $result->fetch_assoc()) {
        $registros[] = $row;
    }

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
