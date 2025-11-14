<?php
include("../../con_db.php");
header('Content-Type: application/json');

try {
    $input = json_decode(file_get_contents('php://input'), true);
    $datos = $input['datos'] ?? [];
    $totalExw     = floatval($input['totalExw'] ?? 0);
    $totalGeneral = floatval($input['totalGeneral'] ?? 0);
    $coeficiente  = floatval($input['coeficiente'] ?? 0);

    if (empty($datos)) {
        echo json_encode(['success' => false, 'message' => 'Datos vacíos']);
        exit;
    }

    $conexion->begin_transaction();

    // 1) Actualizar filas de Incoterms
    $sql = "
    UPDATE itemsliquidacionimportincoterms ii
    JOIN incotermsimport ic 
        ON ic.IdItemsLiquidacionImportIncoterm = ii.ItemsLiquidacionImportIncoterms
    SET 
        ii.Cantidad      = ?,
        ii.ValorUnitario = ?,
        ii.ValorTotal    = ?,
        ii.Notas         = ?
    WHERE ic.IdIncotermsImport = ?
    ";
    $stmt = $conexion->prepare($sql);
    if (!$stmt) throw new Exception("Error preparando UPDATE: " . $conexion->error);

    foreach ($datos as $i => $d) {
        $idIncoterms   = intval($d['idIncoterms'] ?? 0);
        $cantidad      = floatval($d['cantidad'] ?? 0);
        $valorUnitario = floatval($d['valorUnitario'] ?? 0);
        $valorTotal    = floatval($d['valorTotal'] ?? ($cantidad * $valorUnitario));
        $notas         = trim($d['notas'] ?? '');

        $stmt->bind_param("dddsi", $cantidad, $valorUnitario, $valorTotal, $notas, $idIncoterms);
        if (!$stmt->execute()) {
            throw new Exception("Fila {$i} (IdIncoterms {$idIncoterms}): " . $stmt->error);
        }
    }
    $stmt->close();

    if (isset($input['idImport'])) { // Debe venir el id del registro principal
        $idImport = intval($input['idImport']);
        $sqlCoef  = "UPDATE imports SET coeficiente = ? WHERE ImportsID = ?";
        $stmtCoef = $conexion->prepare($sqlCoef);
        if (!$stmtCoef) throw new Exception("Error preparando UPDATE coeficiente: " . $conexion->error);
        $stmtCoef->bind_param("di", $coeficiente, $idImport);
        $stmtCoef->execute();
        $stmtCoef->close();
    }

    // Si vienen facturas (invoices) actualizamos la tabla de mapeo import_invoices
    $invoiceRaw = $input['invoices'] ?? ($input['invoice'] ?? null);
    if ($invoiceRaw !== null && isset($idImport)) {
        // Normalize to array
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

        // Ensure mapping table exists (safe no-FK creation)
        $createMapping = "CREATE TABLE IF NOT EXISTS import_invoices (
          id INT AUTO_INCREMENT PRIMARY KEY,
          ImportsID INT NOT NULL,
          Invoice VARCHAR(255) NOT NULL,
          created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        $conexion->query($createMapping);

        // Delete previous mappings (use detected column name)
        $delSql = "DELETE FROM import_invoices WHERE {$mappingCol} = ?";
        $del = $conexion->prepare($delSql);
        $del->bind_param("i", $idImport);
        $del->execute();
        $del->close();

        if (!empty($invoices)) {
            $insSql = "INSERT INTO import_invoices ({$mappingCol}, Invoice) VALUES (?, ?)";
            $ins = $conexion->prepare($insSql);
            if (!$ins) throw new Exception("Error preparando INSERT en import_invoices: " . $conexion->error);
            foreach ($invoices as $inv) {
                $ins->bind_param("is", $idImport, $inv);
                $ins->execute();
            }
            $ins->close();
            // Update main imports table with first invoice for compatibility IF the column exists
            $first = $invoices[0];
            $colRes = $conexion->query("SHOW COLUMNS FROM imports LIKE 'Number_Commercial_Invoice'");
            if ($colRes && $colRes->num_rows > 0) {
                $u = $conexion->prepare("UPDATE imports SET Number_Commercial_Invoice = ? WHERE ImportsID = ?");
                if ($u) {
                    $u->bind_param("si", $first, $idImport);
                    $u->execute();
                    $u->close();
                }
            }
        }
    }

    $conexion->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Actualización exitosa',
        'datosProcesados' => $datos,
        'coeficiente' => $coeficiente
    ]);

} catch (Exception $e) {
    $conexion->rollback();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error inesperado.',
        'error' => $e->getMessage()
    ]);
}
