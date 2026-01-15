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

    // Validate incoming data: invoice can be a string or an array of strings
    $invoiceIsArray = is_array($invoice);
    $invoiceCount = $invoiceIsArray ? count($invoice) : ($invoice !== '' ? 1 : 0);

    if (!$booking || $invoiceCount === 0 || empty($items)) {
        echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
        exit;
    }

    // Normalize to array for easier processing
    $invoices = $invoiceIsArray ? $invoice : [$invoice];

    // 1️⃣ Crear UNA sola fila en exports
    $firstInvoice = $invoices[0] ?? null;
    // Detect if legacy Number_Commercial_Invoice column exists
    $hasInvoiceCol = false;
    $colRes = $conexion->query("SHOW COLUMNS FROM exports LIKE 'Number_Commercial_Invoice'");
    if ($colRes && $colRes->num_rows > 0) $hasInvoiceCol = true;

    if ($hasInvoiceCol) {
        $stmtExport = $conexion->prepare(
            "INSERT INTO exports (Booking_BK, Number_Commercial_Invoice, num_op, costoEXW, coeficiente, creation_date)
             VALUES (?, ?, ?, ?, ?, NOW())"
        );
        if (!$stmtExport) throw new Exception('Error preparando INSERT exports: ' . $conexion->error);
        $stmtExport->bind_param("ssidd", $booking, $firstInvoice, $nOp, $totalExw, $coeficiente);
    } else {
        // Insert without legacy invoice column
        $stmtExport = $conexion->prepare(
            "INSERT INTO exports (Booking_BK, num_op, costoEXW, coeficiente, creation_date)
             VALUES (?, ?, ?, ?, NOW())"
        );
        if (!$stmtExport) throw new Exception('Error preparando INSERT exports (no invoice col): ' . $conexion->error);
        $stmtExport->bind_param("sidd", $booking, $nOp, $totalExw, $coeficiente);
    }
    $stmtExport->execute();
    $idExport = $conexion->insert_id;
    $stmtExport->close();

    // 2️⃣ Insertar mappings en export_invoices (tabla creada por el usuario)
    $insertInvoiceStmt = $conexion->prepare(
        "INSERT INTO export_invoices (Invoice, idExport) VALUES (?, ?)"
    );
    $insertedInvoices = [];
    foreach ($invoices as $inv) {
        $insertInvoiceStmt->bind_param("si", $inv, $idExport);
        $insertInvoiceStmt->execute();
        $insertedInvoices[] = $inv;
    }
    $insertInvoiceStmt->close();

    // 3️⃣ Recorrer items y guardar (vincularlos a este export)
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

    echo json_encode(['success' => true, 'idExport' => $idExport, 'invoices' => $insertedInvoices]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error al guardar los datos',
        'error'   => $e->getMessage()
    ]);
}
?>
