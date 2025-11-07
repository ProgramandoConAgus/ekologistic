<?php
include '../../con_db.php';
header('Content-Type: application/json');

try {
    $data = json_decode(file_get_contents("php://input"), true);

    $booking = $data['booking']   ?? '';
    $invoice = $data['invoice']   ?? '';
    $items   = $data['items']     ?? [];
    $numOp = $data['numOp'] ?? '';
    $costoEXW= $data['costoEXW'] ?? 0;
    $coeficiente= $data['coeficiente'] ?? 0;
    
    if (!$booking || !$invoice || !$numOp || empty($items)) {
        echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
        exit;
    }
    

    // 1) Insert en exports
    $stmtImport = $conexion->prepare(
        "INSERT INTO imports (Booking_BK, Number_Commercial_Invoice, num_op, costoEXW, coeficiente,creation_date)
         VALUES (?, ?, ?, ?,?,NOW())"
    );
    if (!$stmtImport) {
        throw new Exception("Error preparando INSERT en Imports: " . $conexion->error);
    }
    $stmtImport->bind_param("sssdd", $booking, $invoice, $numOp, $costoEXW, $coeficiente);
    $stmtImport->execute();
    $idExport = $conexion->insert_id;

    // 2) Recorremos los items
    foreach ($items as $item) {
        $idItem        = intval($item['itemId']);
        $cantidad      = floatval($item['cantidad']);
        $valorUnitario = floatval($item['valorUnitario']);
        $valorTotal    = floatval($item['valorTotal']);
        $notas         = trim($item['notas'] ?? '');

        if ($cantidad < 0 || $valorUnitario < 0 || $valorTotal < 0) {
            throw new Exception("Datos inválidos para el item: " . json_encode($item));
        }

        // Preparamos INSERT incluyendo la columna Notas
        $stmtItemInc = $conexion->prepare(
            "INSERT INTO itemsliquidacionimportincoterms
             (IdItemsLiquidacionImport, Cantidad, ValorUnitario, ValorTotal, Notas)
             VALUES (?, ?, ?, ?, ?)"
        );
        if (!$stmtItemInc) {
            throw new Exception("Error preparando INSERT en itemsliquidacionimportincoterms: " 
                                . $conexion->error);
        }

        // 'i' → integer, 'd' → double, 's' → string
        $stmtItemInc->bind_param(
            "iddds",
            $idItem,
            $cantidad,
            $valorUnitario,
            $valorTotal,
            $notas      // ← enlazamos la nota aquí
        );

        $stmtItemInc->execute();
        $idItemsIncoterms = $conexion->insert_id;

        // 3) Ahora insertamos en incotermsimport
        $stmtInc = $conexion->prepare(
            "INSERT INTO incotermsimport
             (IdItemsLiquidacionImportIncoterm, IdImports)
             VALUES (?, ?)"
        );
        if (!$stmtInc) {
            throw new Exception("Error preparando INSERT en incotermsimport: " 
                                . $conexion->error);
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
