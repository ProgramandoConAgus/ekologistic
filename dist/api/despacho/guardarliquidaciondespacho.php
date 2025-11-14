<?php
include "../../con_db.php";
header('Content-Type: application/json');

try {
    $data    = json_decode(file_get_contents("php://input"), true);
    $booking = trim($data['booking'] ?? '');
    // support multiple invoices (array or comma-separated string)
    $invoiceRaw = $data['invoice'] ?? ($data['invoices'] ?? '');
    $invoices = [];
    if (is_array($invoiceRaw)) {
        $invoices = array_values(array_filter(array_map('trim', $invoiceRaw), fn($v)=>$v !== ''));
    } elseif (is_string($invoiceRaw) && $invoiceRaw !== '') {
        if (strpos($invoiceRaw, ',') !== false) {
            $parts = array_map('trim', explode(',', $invoiceRaw));
            $invoices = array_values(array_filter($parts, fn($v)=>$v !== ''));
        } else {
            $invoices = [trim($invoiceRaw)];
        }
    }
    $items   = $data['items'] ?? [];

    if (!$booking || empty($invoices) || empty($items)) {
        echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
        exit;
    }


    $costoEXW   = floatval($data['costoEXW'] ?? 0);
    $coeficiente = floatval($data['coeficiente'] ?? 0);
    $numOp      = intval($data['num_op'] ?? 0);

    // Create lightweight mapping table if needed (no FK to avoid type mismatch issues)
    $createMapping = "CREATE TABLE IF NOT EXISTS despacho_invoices (
      id INT AUTO_INCREMENT PRIMARY KEY,
      DespachoID INT NOT NULL,
      Invoice VARCHAR(255) NOT NULL,
      created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    $conexion->query($createMapping);

    // If any foreign keys exist on despacho_invoices, try to drop them to avoid FK failures
    try {
        $fkRes = $conexion->query("SELECT CONSTRAINT_NAME FROM information_schema.TABLE_CONSTRAINTS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'despacho_invoices' AND CONSTRAINT_TYPE = 'FOREIGN KEY'");
        if ($fkRes && $fkRes->num_rows > 0) {
            while ($fkRow = $fkRes->fetch_assoc()) {
                $fkName = $fkRow['CONSTRAINT_NAME'];
                // Attempt to drop the foreign key
                $conexion->query("ALTER TABLE despacho_invoices DROP FOREIGN KEY `" . $conexion->real_escape_string($fkName) . "`");
            }
        }
    } catch (Exception $ex) {
        // non-fatal: continue even if we cannot drop; insertion may still fail and will be reported
    }

    // Detect which mapping column name is present in the table (compatibility with existing schemas)
    $mappingCol = 'DespachoID';
    $has = $conexion->query("SHOW COLUMNS FROM despacho_invoices LIKE 'DespachoID'");
    if (!($has && $has->num_rows > 0)) {
        $has2 = $conexion->query("SHOW COLUMNS FROM despacho_invoices LIKE 'idDespacho'");
        if ($has2 && $has2->num_rows > 0) $mappingCol = 'idDespacho';
        else {
            $has3 = $conexion->query("SHOW COLUMNS FROM despacho_invoices LIKE 'IdDespacho'");
            if ($has3 && $has3->num_rows > 0) $mappingCol = 'IdDespacho';
        }
    }

        // Save main despacho row (store first invoice for legacy compatibility if column exists)
        $firstInvoice = $invoices[0] ?? '';
        $hasInvCol = false;
        $colRes = $conexion->query("SHOW COLUMNS FROM despacho LIKE 'Number_Commercial_Invoice'");
        if ($colRes && $colRes->num_rows > 0) $hasInvCol = true;

        if ($hasInvCol) {
            $stmtD = $conexion->prepare(
                "INSERT INTO despacho 
                 (Booking_BK, Number_Commercial_Invoice, costoEXW, coeficiente, num_op, creation_date, status)
                 VALUES (?, ?, ?, ?, ?, NOW(), 1)"
            );
            if (!$stmtD) throw new Exception('Error preparando INSERT despacho: ' . $conexion->error);
            $stmtD->bind_param("ssddi", $booking, $firstInvoice, $costoEXW, $coeficiente, $numOp);
        } else {
            $stmtD = $conexion->prepare(
                "INSERT INTO despacho 
                 (Booking_BK, costoEXW, coeficiente, num_op, creation_date, status)
                 VALUES (?, ?, ?, ?, NOW(), 1)"
            );
            if (!$stmtD) throw new Exception('Error preparando INSERT despacho (no invoice col): ' . $conexion->error);
            $stmtD->bind_param("sddi", $booking, $costoEXW, $coeficiente, $numOp);
        }
        $stmtD->execute();
        $idDespacho = $conexion->insert_id;
        $stmtD->close();

    // Insert despacho->invoice mapping rows (use detected mapping column)
    if (!empty($invoices)) {
        $insSql = "INSERT INTO despacho_invoices ({$mappingCol}, Invoice) VALUES (?, ?)";
        $stmtMap = $conexion->prepare($insSql);
        if ($stmtMap) {
            foreach ($invoices as $inv) {
                $stmtMap->bind_param("is", $idDespacho, $inv);
                $stmtMap->execute();
            }
            $stmtMap->close();
        } else {
            // Fallback: try generic column name idDespacho for backwards compatibility
            $stmtMap = $conexion->prepare("INSERT INTO despacho_invoices (idDespacho, Invoice) VALUES (?, ?)");
            if ($stmtMap) {
                foreach ($invoices as $inv) {
                    $stmtMap->bind_param("is", $idDespacho, $inv);
                    $stmtMap->execute();
                }
                $stmtMap->close();
            }
        }
    }

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
