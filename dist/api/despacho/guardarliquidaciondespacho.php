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

    // 1) Insert en despacho (tabla principal)
    $stmtDespacho = $conexion->prepare(
      "INSERT INTO despacho (Booking_BK, Number_Commercial_Invoice, creation_date, status)
       VALUES (?, ?, NOW(), 'Cargado')"
    );
    if (!$stmtDespacho) {
        throw new Exception("Error preparando INSERT en despacho: " . $conexion->error);
    }
    $stmtDespacho->bind_param("ss", $booking, $invoice);
    $stmtDespacho->execute();
    $idDespacho = $conexion->insert_id;

    // 2) Recorremos los items y guardamos en itemsliquidaciondespachoincoterms
    foreach ($items as $item) {
        $idItem         = intval($item['itemId']);
        $cantidad       = floatval($item['cantidad']);
        $valorUnitario  = floatval($item['valorUnitario']);
        $valorTotal     = floatval($item['valorTotal']);

        $stmtItemInc = $conexion->prepare(
          "INSERT INTO itemsliquidaciondespachoincoterms
           (IdItemsLiquidacionDespacho, Cantidad, ValorUnitario, ValorTotal)
           VALUES (?, ?, ?, ?)"
        );
        if (!$stmtItemInc) {
            throw new Exception("Error preparando INSERT en itemsliquidaciondespachoincoterms: " . $conexion->error);
        }

        $stmtItemInc->bind_param(
            "iddd",
            $idItem,
            $cantidad,
            $valorUnitario,
            $valorTotal
        );

        $stmtItemInc->execute();
        $idItemsIncoterms = $conexion->insert_id;

        // 3) Insert en incotermdespacho (vincula item-incoterm con despacho)
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
