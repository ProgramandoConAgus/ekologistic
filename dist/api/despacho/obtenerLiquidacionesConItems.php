<?php
include "../../con_db.php";
header('Content-Type: application/json');

try {
    $input  = json_decode(file_get_contents('php://input'), true);
    $num_op = trim($input['num_op'] ?? '');

    if (!$num_op) {
        throw new Exception("Número de operación no recibido");
    }

    // 1) Traigo las liquidaciones asociadas a ese num_op (no usamos Number_Commercial_Invoice directo)
    // Buscamos por Booking_BK que tenga items con ese num_op
    $sql = "
      SELECT 'exports' AS origen, e.ExportsID AS id, e.Booking_BK, DATE_FORMAT(e.creation_date, '%d/%m/%Y') AS fecha,
        IFNULL(e.costoEXW, 0) AS costoEXW, IFNULL(e.coeficiente, 0) AS coeficiente, IFNULL(e.num_op, '') AS num_op
      FROM exports e
      WHERE e.Booking_BK IN (
         SELECT DISTINCT c.Booking_BK
         FROM items it
         JOIN container c ON it.idContainer = c.IdContainer
         WHERE c.num_op = ?
      ) AND e.status = 2

      UNION ALL

      SELECT 'imports' AS origen, i2.ImportsID AS id, i2.Booking_BK, DATE_FORMAT(i2.creation_date, '%d/%m/%Y') AS fecha,
        IFNULL(i2.costoEXW, 0) AS costoEXW, IFNULL(i2.coeficiente, 0) AS coeficiente, IFNULL(i2.num_op, '') AS num_op
      FROM imports i2
      WHERE i2.Booking_BK IN (
         SELECT DISTINCT c.Booking_BK
         FROM items it
         JOIN container c ON it.idContainer = c.IdContainer
         WHERE c.num_op = ?
      ) AND i2.status = 2

      UNION ALL

      SELECT 'despacho' AS origen, d.DespachoID AS id, d.Booking_BK, DATE_FORMAT(d.creation_date, '%d/%m/%Y') AS fecha,
        IFNULL(d.costoEXW, 0) AS costoEXW, IFNULL(d.coeficiente, 0) AS coeficiente, IFNULL(d.num_op, '') AS num_op
      FROM despacho d
      WHERE d.Booking_BK IN (
         SELECT DISTINCT c.Booking_BK
         FROM items it
         JOIN container c ON it.idContainer = c.IdContainer
         WHERE c.num_op = ?
      ) AND d.status = 2
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
  include_once __DIR__ . '/../helpers/mapping.php';

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

  // 3) Adjuntar facturas mapeadas (Number_Commercial_Invoice) para cada liquidación
  foreach ($liquidaciones as &$l) {
    $invs = fetch_mapped_invoices($conexion, $l['origen'], intval($l['id']));
    $l['Number_Commercial_Invoice'] = !empty($invs) ? implode(', ', $invs) : '';
  }
  unset($l);

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
