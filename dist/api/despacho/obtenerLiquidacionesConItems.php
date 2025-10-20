<?php
include "../../con_db.php";
header('Content-Type: application/json');

try {
    $input  = json_decode(file_get_contents('php://input'), true);
    $num_op = trim($input['num_op'] ?? '');

    if (!$num_op) {
        throw new Exception("Número de operación no recibido");
    }

    // 1) Traigo las liquidaciones asociadas a ese num_op
    $sql = "
      SELECT 'exports' AS origen, e.ExportsID AS id, e.Booking_BK, e.Number_Commercial_Invoice, DATE_FORMAT(e.creation_date, '%d/%m/%Y') AS fecha
      FROM exports e
      JOIN items i ON e.Number_Commercial_Invoice = i.Number_Commercial_Invoice
      JOIN container c ON i.idContainer = c.IdContainer
      WHERE c.num_op = ? AND e.status = 2

      UNION ALL

      SELECT 'imports' AS origen, i2.ImportsID AS id, i2.Booking_BK, i2.Number_Commercial_Invoice, DATE_FORMAT(i2.creation_date, '%d/%m/%Y') AS fecha
      FROM imports i2
      JOIN items i ON i2.Number_Commercial_Invoice = i.Number_Commercial_Invoice
      JOIN container c ON i.idContainer = c.IdContainer
      WHERE c.num_op = ? AND i2.status = 2

      UNION ALL

      SELECT 'despacho' AS origen, d.DespachoID AS id, d.Booking_BK, d.Number_Commercial_Invoice, DATE_FORMAT(d.creation_date, '%d/%m/%Y') AS fecha
      FROM despacho d
      JOIN items i ON d.Number_Commercial_Invoice = i.Number_Commercial_Invoice
      JOIN container c ON i.idContainer = c.IdContainer
      WHERE c.num_op = ? AND d.status = 2
    ";


    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("sss", $num_op, $num_op, $num_op);
    $stmt->execute();
    $res = $stmt->get_result();

    $liquidaciones = [];
    while ($row = $res->fetch_assoc()) {
        $liquidaciones[] = $row;
    }

    if (empty($liquidaciones)) {
        echo json_encode([
          'success' => false,
          'message' => 'No se encontraron liquidaciones para este número de operación.'
        ]);
        exit;
    }

    // 2) Por cada liquidación, traigo sus items según el origen
    foreach ($liquidaciones as &$liq) {
        switch ($liq['origen']) {
            case 'exports':
                $itemsSql = "
                  SELECT 
                    il.NombreItems,
                    ii.Cantidad,
                    ii.ValorUnitario,
                    (ii.Cantidad * ii.ValorUnitario) AS ValorTotal
                  FROM itemsliquidacionexportincoterms ii
                  JOIN incoterms i 
                    ON ii.IdItemsLiquidacionExportIncoterms = i.IdItemsLiquidacionExportIncoterms
                  JOIN itemsliquidacionexport il 
                    ON il.IdItemsLiquidacionExport = ii.IdItemsLiquidacionExport
                  WHERE i.IdExports = ?
                ";
                break;

            case 'imports':
                $itemsSql = "
                  SELECT 
                    il.NombreItems,
                    ii.Cantidad,
                    ii.ValorUnitario,
                    (ii.Cantidad * ii.ValorUnitario) AS ValorTotal
                  FROM itemsliquidacionimportincoterms ii
                  JOIN incotermsimport i 
                    ON ii.ItemsLiquidacionImportIncoterms  = i.IdItemsLiquidacionImportIncoterm 
                  JOIN itemsliquidacionimport il 
                    ON il.IdItemsLiquidacionImport = ii.IdItemsLiquidacionImport
                  WHERE i.IdImports = ?
                ";
                break;

            case 'despacho':
                $itemsSql = "
                  SELECT 
                    il.NombreItems,
                    ii.Cantidad,
                    ii.ValorUnitario,
                    (ii.Cantidad * ii.ValorUnitario) AS ValorTotal
                  FROM itemsliquidaciondespachoincoterms ii
                  JOIN incotermsdespacho i 
                    ON ii.IdItemsLiquidacionDespachoIncoterms = i.IdItemsLiquidacionDespachoIncoterm 
                  JOIN itemsliquidaciondespacho il 
                    ON il.IdItemsLiquidacionDespacho = ii.IdItemsLiquidacionDespacho
                  WHERE i.IdDespacho = ?
                ";
                break;

            default:
                $liq['items'] = [];
                continue 2;
        }

        $st = $conexion->prepare($itemsSql);
        $st->bind_param("i", $liq['id']);
        $st->execute();
        $rItems = $st->get_result();

        $items = [];
        while ($it = $rItems->fetch_assoc()) {
            $items[] = $it;
        }
        $liq['items'] = $items;
        $st->close();
    }
    unset($liq);

    echo json_encode([
      'success'       => true,
      'liquidaciones' => $liquidaciones
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
      'success' => false,
      'message' => $e->getMessage()
    ]);
}
