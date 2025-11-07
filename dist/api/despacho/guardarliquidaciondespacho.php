<?php
include "../../con_db.php";
header('Content-Type: application/json');

try {
    $data    = json_decode(file_get_contents("php://input"), true);
    $booking = trim($data['booking'] ?? '');
    $invoice = trim($data['invoice'] ?? '');
    $items   = $data['items'] ?? [];

    if (!$booking || !$invoice || empty($items)) {
        echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
        exit;
    }


    $costoEXW   = floatval($data['costoEXW'] ?? 0);
    $coeficiente = floatval($data['coeficiente'] ?? 0);
    $numOp      = intval($data['num_op'] ?? 0);

    $stmtD = $conexion->prepare(
        "INSERT INTO despacho 
         (Booking_BK, Number_Commercial_Invoice, costoEXW, coeficiente, num_op, creation_date, status)
         VALUES (?, ?, ?, ?, ?, NOW(), 1)"
      );
      $stmtD->bind_param("sdddi", $booking, $invoice, $costoEXW, $coeficiente, $numOp);
      $stmtD->execute();
      $idDespacho = $conexion->insert_id;
      $stmtD->close();

    // 2) Recorro los items
    foreach ($items as $item) {
    $idItem         = intval($item['itemId']);
    $cantidad       = floatval($item['cantidad']);
    $valorUnitario  = floatval($item['valorUnitario']);
    $valorTotal     = floatval($item['valorTotal']);
    $notas          = trim($item['notas'] ?? '');

    // Preparamos INSERT con columna Notas
    $stmtItemInc = $conexion->prepare(
      "INSERT INTO itemsliquidaciondespachoincoterms
       (IdItemsLiquidacionDespacho, Cantidad, ValorUnitario, ValorTotal, Notas)
       VALUES (?, ?, ?, ?, ?)"
    );
    if (!$stmtItemInc) {
        throw new Exception("Error preparando INSERT en itemsliquidaciondespachoincoterms: " . $conexion->error);
    }

    // Types: i = integer, d = double, s = string
    $stmtItemInc->bind_param(
        "iddds",
        $idItem,
        $cantidad,
        $valorUnitario,
        $valorTotal,
        $notas    // <-- aquí enlazamos la nota
    );

    $stmtItemInc->execute();
    $idItemsIncoterms = $conexion->insert_id;

    $stmtInc = $conexion->prepare(
    "INSERT INTO incotermsdespacho
    (IdItemsLiquidacionDespachoIncoterm, IdDespacho)
    VALUES (?, ?)"
    );
    if (!$stmtInc) {
        throw new Exception("Error preparando INSERT en incotermsdespacho: " . $conexion->error);
    }
    $stmtInc->bind_param("ii", $idItemsIncoterms, $idDespacho);
    $stmtInc->execute();
    $stmtInc->close();

    }

    echo json_encode(['success' => true]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
      'success' => false,
      'message' => 'Ocurrió un error al procesar los datos.',
      'error'   => $e->getMessage()
    ]);
}
?>
