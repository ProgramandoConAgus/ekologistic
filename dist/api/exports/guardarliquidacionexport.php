<?php
include '../../con_db.php';
header('Content-Type: application/json');

try {
    $data = json_decode(file_get_contents("php://input"), true);

    $booking     = $data['booking']   ?? '';
    $invoice     = $data['invoice']   ?? '';
    $items       = $data['items']     ?? [];
    $nOp         = $data['nOp']       ?? null;
    $totalExw    = floatval($data['totalExw']    ?? 0);
    $coeficiente = floatval($data['coeficiente'] ?? 0);

    if (!$booking || !$invoice || empty($items)) {
        echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
        exit;
    }

    // 1️⃣ Insertar el export
    $stmtExport = $conexion->prepare(
        "INSERT INTO exports (Booking_BK, Number_Commercial_Invoice, num_op, costoEXW, coeficiente, creation_date)
         VALUES (?, ?, ?, ?, ?, NOW())"
    );
    $stmtExport->bind_param("ssidd", $booking, $invoice, $nOp, $totalExw, $coeficiente);
    $stmtExport->execute();
    $idExport = $conexion->insert_id;
    $stmtExport->close();

    // 2️⃣ Recorrer items y guardar
    foreach ($items as $item) {
        $idItem         = intval($item['itemId']);
        $cantidad       = floatval($item['cantidad']);
        $valorUnitario  = floatval($item['valorUnitario']);
        $valorTotal     = floatval($item['valorTotal']);
        $impuestoPct    = floatval($item['impuestoPct'] ?? 0);
        $valorImpuesto  = floatval($item['valorImpuesto'] ?? 0);
        $notas          = trim($item['notas'] ?? '');

        // Insert en itemsliquidacionexportincoterms
        $stmtItemInc = $conexion->prepare(
            "INSERT INTO itemsliquidacionexportincoterms
             (IdItemsLiquidacionExport, Cantidad, ValorUnitario, ValorTotal, Impuesto, ValorImpuesto, Notas)
             VALUES (?, ?, ?, ?, ?, ?, ?)"
        );
        $stmtItemInc->bind_param(
            "iddddss",
            $idItem,
            $cantidad,
            $valorUnitario,
            $valorTotal,
            $impuestoPct,
            $valorImpuesto,
            $notas
        );
        $stmtItemInc->execute();
        $idItemsIncoterms = $conexion->insert_id;
        $stmtItemInc->close();

        // Vincular item con el export en incoterms
        $stmtLinkInc = $conexion->prepare(
            "INSERT INTO incoterms (IdItemsLiquidacionExportIncoterms, IdExports) VALUES (?, ?)"
        );
        $stmtLinkInc->bind_param("ii", $idItemsIncoterms, $idExport);
        $stmtLinkInc->execute();
        $stmtLinkInc->close();
    }

    echo json_encode(['success' => true, 'idExport' => $idExport]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error al guardar los datos',
        'error'   => $e->getMessage()
    ]);
}
?>
