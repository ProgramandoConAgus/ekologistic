<?php
include '../../con_db.php';
header('Content-Type: application/json');

try {
    $data = json_decode(file_get_contents("php://input"), true);
    $booking = $data['booking']   ?? '';
    // invoice may be a string or an array (new multi-invoice support)
    $invoiceRaw = $data['invoice']   ?? ($data['invoices'] ?? '');
    $invoices = [];
    if (is_array($invoiceRaw)) {
        $invoices = array_values(array_filter(array_map('trim', $invoiceRaw), fn($v)=>$v !== ''));
    } elseif (is_string($invoiceRaw) && $invoiceRaw !== '') {
        // allow comma-separated list as well
        if (strpos($invoiceRaw, ',') !== false) {
            $parts = array_map('trim', explode(',', $invoiceRaw));
            $invoices = array_values(array_filter($parts, fn($v)=>$v !== ''));
        } else {
            $invoices = [trim($invoiceRaw)];
        }
    }
    $items   = $data['items']     ?? [];
    $incotermId = intval($data['incotermId'] ?? 0);
    $numOp = $data['numOp'] ?? '';
    $costoEXW= $data['costoEXW'] ?? 0;
    $coeficiente= $data['coeficiente'] ?? 0;
    
    if (!$booking || empty($invoices) || !$numOp || empty($items)) {
        echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
        exit;
    }
    

    // start transaction
    $conexion->begin_transaction();

        // Determine mapping column name if table already exists with a different schema
        $mappingCol = 'ImportsID';
        $hasImportsID = $conexion->query("SHOW COLUMNS FROM import_invoices LIKE 'ImportsID'");
        if ($hasImportsID && $hasImportsID->num_rows > 0) {
            $mappingCol = 'ImportsID';
        } else {
            // try legacy alternatives
            $try = $conexion->query("SHOW COLUMNS FROM import_invoices LIKE 'ImportID'");
            if ($try && $try->num_rows > 0) {
                $mappingCol = 'ImportID';
            } else {
                $try2 = $conexion->query("SHOW COLUMNS FROM import_invoices LIKE 'idImport'");
                if ($try2 && $try2->num_rows > 0) {
                    $mappingCol = 'idImport';
                }
            }
        }

        // Ensure a lightweight mapping table exists for imports -> invoices (no strict FK to avoid type mismatch issues)
        // If the table doesn't exist, create it with the canonical ImportsID column
        $createMapping = "CREATE TABLE IF NOT EXISTS import_invoices (
            id INT AUTO_INCREMENT PRIMARY KEY,
            ImportsID INT NOT NULL,
            Invoice VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        $conexion->query($createMapping);

        // re-evaluate mapping column after potential creation
        $hasImportsID = $conexion->query("SHOW COLUMNS FROM import_invoices LIKE 'ImportsID'");
        if ($hasImportsID && $hasImportsID->num_rows > 0) $mappingCol = 'ImportsID';

    // 1) Insert en imports
    // Use the first invoice as legacy Number_Commercial_Invoice value (if available)
    $firstInvoice = $invoices[0] ?? '';
    // detect if imports table still has Number_Commercial_Invoice
    $hasInvCol = false;
    $colRes = $conexion->query("SHOW COLUMNS FROM imports LIKE 'Number_Commercial_Invoice'");
    if ($colRes && $colRes->num_rows > 0) $hasInvCol = true;

    if ($hasInvCol) {
        $stmtImport = $conexion->prepare(
            "INSERT INTO imports (Booking_BK, Number_Commercial_Invoice, num_op, costoEXW, coeficiente,creation_date)
             VALUES (?, ?, ?, ?,?,NOW())"
        );
        if (!$stmtImport) {
            throw new Exception("Error preparando INSERT en Imports: " . $conexion->error);
        }
        $stmtImport->bind_param("sssdd", $booking, $firstInvoice, $numOp, $costoEXW, $coeficiente);
    } else {
        $stmtImport = $conexion->prepare(
            "INSERT INTO imports (Booking_BK, num_op, costoEXW, coeficiente, creation_date)
             VALUES (?, ?, ?, ?, NOW())"
        );
        if (!$stmtImport) {
            throw new Exception("Error preparando INSERT en Imports (no invoice col): " . $conexion->error);
        }
        $stmtImport->bind_param("ssdd", $booking, $numOp, $costoEXW, $coeficiente);
    }
    $stmtImport->execute();
    $idExport = $conexion->insert_id;

    // Insert mapping rows for each invoice selected
    if (!empty($invoices)) {
        $sqlMap = "INSERT INTO import_invoices ({$mappingCol}, Invoice) VALUES (?, ?)";
        $stmtMap = $conexion->prepare($sqlMap);
        if (!$stmtMap) {
            throw new Exception("Error preparando INSERT en import_invoices: " . $conexion->error);
        }
        foreach ($invoices as $inv) {
            $stmtMap->bind_param("is", $idExport, $inv);
            $stmtMap->execute();
        }
        $stmtMap->close();
    }

    // 2) Recorremos los items
    foreach ($items as $item) {
        $idItem        = intval($item['itemId']);
        $cantidad      = floatval($item['cantidad']);
        $valorUnitario = floatval($item['valorUnitario']);
        $valorTotal    = floatval($item['valorTotal']);
        $notas         = trim($item['notas'] ?? '');
        $nombre        = trim($item['nombre'] ?? '');

        if ($cantidad < 0 || $valorUnitario < 0 || $valorTotal < 0) {
            throw new Exception("Datos inválidos para el item: " . json_encode($item));
        }

        // Si es un item nuevo (itemId == 0), crear el item en itemsliquidacionimport
        if ($idItem === 0) {
            if ($nombre === '') {
                throw new Exception("Nombre requerido para nuevo item");
            }
            if ($incotermId <= 0) {
                throw new Exception("IncotermId faltante para nuevo item");
            }

            $stmtNewItem = $conexion->prepare(
                "INSERT INTO itemsliquidacionimport (NombreItems, IdTipoIncoterm, posicion) VALUES (?, ?, 9999)"
            );
            if (!$stmtNewItem) {
                throw new Exception("Error preparando INSERT en itemsliquidacionimport: " . $conexion->error);
            }
            $stmtNewItem->bind_param("si", $nombre, $incotermId);
            $stmtNewItem->execute();
            $idItem = $conexion->insert_id;
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
    $conexion->commit();
    echo json_encode(['success' => true, 'importId' => $idExport, 'invoices' => $invoices]);
} catch (Exception $e) {
    if ($conexion->errno === 0) {
        // if transaction active, rollback
        $conexion->rollback();
    } else {
        $conexion->rollback();
    }
    http_response_code(500);
    echo json_encode([
      'success' => false,
      'message' => 'Ocurrió un error al procesar los datos.',
      'error'   => $e->getMessage()
    ]);
}
?>
