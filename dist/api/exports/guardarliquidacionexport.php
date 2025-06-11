<?php
include '../../con_db.php';
header('Content-Type: application/json');

try {
    $data = json_decode(file_get_contents("php://input"), true);

    $booking = $data['booking']   ?? '';
    $invoice = $data['invoice']   ?? '';
    $items   = $data['items']     ?? [];

    if (!$booking || !$invoice || empty($items)) {
        echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
        exit;
    }

    // 1) Insert en exports
    $stmtExport = $conexion->prepare(
      "INSERT INTO exports (Booking_BK, Number_Commercial_Invoice, creation_date)
       VALUES (?, ?, NOW())"
    );
    if (!$stmtExport) {
        throw new Exception("Error preparando INSERT en Exports: " . $conexion->error);
    }
    $stmtExport->bind_param("ss", $booking, $invoice);
    $stmtExport->execute();
    $idExport = $conexion->insert_id;

    // 2) Recorremos los items
    foreach ($items as $item) {
        // ahora recibimos el ID directamente
        $idItem         = intval($item['itemId']);
        $cantidad       = floatval($item['cantidad']);
        $valorUnitario  = floatval($item['valorUnitario']);
        $valorTotal     = floatval($item['valorTotal']);

        // Insert en itemsliquidacionexportincoterms
        $stmtItemInc = $conexion->prepare(
          "INSERT INTO itemsliquidacionexportincoterms
           (IdItemsLiquidacionExport, Cantidad, ValorUnitario, ValorTotal)
           VALUES (?, ?, ?, ?)"
        );
        if (!$stmtItemInc) {
            throw new Exception("Error preparando INSERT en ItemsLiquidacionExportIncoterms: " . $conexion->error);
        }
        $stmtItemInc->bind_param("iddd", $idItem, $cantidad, $valorUnitario, $valorTotal);
        $stmtItemInc->execute();
        $idItemsIncoterms = $conexion->insert_id;

        // Insert en incoterms (vincula el item-incoterm con el export)
        $stmtInc = $conexion->prepare(
          "INSERT INTO incoterms (IdItemsLiquidacionExportIncoterms, IdExports)
           VALUES (?, ?)"
        );
        if (!$stmtInc) {
            throw new Exception("Error preparando INSERT en Incoterms: " . $conexion->error);
        }
        $stmtInc->bind_param("ii", $idItemsIncoterms, $idExport);
        $stmtInc->execute();
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
