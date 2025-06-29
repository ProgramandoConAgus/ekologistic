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

    // 1) Inserto el despacho principal
    $stmtD = $conexion->prepare(
      "INSERT INTO despacho (Booking_BK, Number_Commercial_Invoice, creation_date, status)
       VALUES (?, ?, NOW(), 'Cargado')"
    );
    $stmtD->bind_param("ss", $booking, $invoice);
    $stmtD->execute();
    $idDespacho = $conexion->insert_id;
    $stmtD->close();

    // 2) Recorro los items
    foreach ($items as $item) {
        $incotermId     = intval($item['incotermId']);
        $itemId         = intval($item['itemId']);             // puede venir 0 para New Delivery
        $cantidad       = floatval($item['cantidad']);
        $valorUnitario  = floatval($item['valorUnitario']);
        $valorTotal     = floatval($item['valorTotal']);
        $descripcion    = trim($item['descripcion']);

        // ——————————
        // Si es “New Delivery” (itemId = 0), lo creo en itemsliquidaciondespacho
        if ($itemId <= 0) {
            $stmtNew = $conexion->prepare(
              "INSERT INTO itemsliquidaciondespacho (IdTipoIncoterm, NombreItems)
               VALUES (?, ?)"
            );
            if (!$stmtNew) {
                throw new Exception("Error preparando INSERT nuevo item: " . $conexion->error);
            }
            $stmtNew->bind_param("is", $incotermId, $descripcion);
            $stmtNew->execute();
            $itemId = $conexion->insert_id;  // ahora tenemos un ID válido
            $stmtNew->close();
        }

        // 3) Inserto en itemsliquidaciondespachoincoterms
        $stmtII = $conexion->prepare(
          "INSERT INTO itemsliquidaciondespachoincoterms
           (IdItemsLiquidacionDespacho, Cantidad, ValorUnitario, ValorTotal)
           VALUES (?, ?, ?, ?)"
        );
        if (!$stmtII) {
            throw new Exception("Error preparando INSERT en itemsliquidaciondespachoincoterms: " . $conexion->error);
        }
        $stmtII->bind_param("iddd", $itemId, $cantidad, $valorUnitario, $valorTotal);
        $stmtII->execute();
        $idIncotermItem = $conexion->insert_id;
        $stmtII->close();

        // 4) Inserto en incotermsdespacho para vincular con el despacho
        $stmtID = $conexion->prepare(
          "INSERT INTO incotermsdespacho
           (IdItemsLiquidacionDespachoIncoterm, IdDespacho)
           VALUES (?, ?)"
        );
        if (!$stmtID) {
            throw new Exception("Error preparando INSERT en incotermsdespacho: " . $conexion->error);
        }
        $stmtID->bind_param("ii", $idIncotermItem, $idDespacho);
        $stmtID->execute();
        $stmtID->close();
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
