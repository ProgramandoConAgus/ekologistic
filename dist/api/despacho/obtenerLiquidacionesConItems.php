<?php
include "../../con_db.php";
header('Content-Type: application/json');

try {
    $input = json_decode(file_get_contents('php://input'), true);
    $num_op = trim($input['num_op'] ?? '');

    if (!$num_op) {
        throw new Exception("Número de operación no recibido");
    }

    // Consulta para obtener las liquidaciones (exports, imports, despacho) asociadas
    $query = "
      SELECT 
        'exports' AS origen,
        e.ExportsID AS id,
        e.Booking_BK,
        e.Number_Commercial_Invoice,
        DATE_FORMAT(e.creation_date, '%d/%m/%Y') as fecha
      FROM exports e
      JOIN container c ON c.Number_Commercial_Invoice = e.Number_Commercial_Invoice
      WHERE c.num_op = ? AND e.status = 2

      UNION ALL

      SELECT 
        'imports' AS origen,
        i.ImportsID AS id,
        i.Booking_BK,
        i.Number_Commercial_Invoice,
        DATE_FORMAT(i.creation_date, '%d/%m/%Y') as fecha
      FROM imports i
      JOIN container c ON c.Number_Commercial_Invoice = i.Number_Commercial_Invoice
      WHERE c.num_op = ? AND i.status = 2

      UNION ALL

      SELECT 
        'despacho' AS origen,
        d.DespachoID AS id,
        d.Booking_BK,
        d.Number_Commercial_Invoice,
        DATE_FORMAT(d.creation_date, '%d/%m/%Y') as fecha
      FROM despacho d
      JOIN container c ON c.Number_Commercial_Invoice = d.Number_Commercial_Invoice
      WHERE c.num_op = ? AND d.status = 2
    ";

    $stmt = $conexion->prepare($query);
    $stmt->bind_param("sss", $num_op, $num_op, $num_op);
    $stmt->execute();
    $res = $stmt->get_result();

    $liquidaciones = [];
    while ($row = $res->fetch_assoc()) {
        $liquidaciones[] = $row;
    }

    if (empty($liquidaciones)) {
        echo json_encode(['success' => false, 'message' => 'No se encontraron liquidaciones para este número de operación.']);
        exit;
    }

    // Ahora buscar los items para cada liquidación
    foreach ($liquidaciones as &$liq) {
        if ($liq['origen'] === 'exports' || $liq['origen'] === 'imports') {
            $tablaIncoterms = $liq['origen'] === 'exports' ? 'incotermsexport' : 'incotermsimport';
            $tablaItemsInc = $liq['origen'] === 'exports' ? 'itemsliquidacionexportincoterms' : 'itemsliquidacionimportincoterms';
            $tablaItems = $liq['origen'] === 'exports' ? 'itemsliquidacionexport' : 'itemsliquidacionimport';

            $itemsQuery = "
              SELECT 
                il.NombreItems,
                ii.Cantidad,
                ii.ValorUnitario,
                (ii.Cantidad * ii.ValorUnitario) AS ValorTotal
              FROM $tablaIncoterms i
              JOIN $tablaItemsInc ii ON ii.IdItemsLiquidacion" . ucfirst($liq['origen']) . "Incoterms = i.IdItemsLiquidacion" . ucfirst($liq['origen']) . "Incoterm
              JOIN $tablaItems il ON il.IdItemsLiquidacion" . ucfirst($liq['origen']) . " = ii.IdItemsLiquidacion" . ucfirst($liq['origen']) . "
              WHERE i.Id" . ucfirst($liq['origen']) . " = ?
            ";
        } else if ($liq['origen'] === 'despacho') {
            $itemsQuery = "
              SELECT 
                il.NombreItems,
                ii.Cantidad,
                ii.ValorUnitario,
                (ii.Cantidad * ii.ValorUnitario) AS ValorTotal
              FROM incotermsdespacho i
              JOIN itemsliquidaciondespachoincoterms ii ON ii.IdItemsLiquidacionDespachoIncoterms = i.IdItemsLiquidacionDespachoIncoterm
              JOIN itemsliquidaciondespacho il ON il.IdItemsLiquidacionDespacho = ii.IdItemsLiquidacionDespacho
              WHERE i.DespachoID = ?
            ";
        } else {
            $liq['items'] = [];
            continue;
        }

        $stmtItems = $conexion->prepare($itemsQuery);
        $stmtItems->bind_param("i", $liq['id']);
        $stmtItems->execute();
        $resItems = $stmtItems->get_result();

        $items = [];
        while ($item = $resItems->fetch_assoc()) {
            $items[] = $item;
        }
        $liq['items'] = $items;
    }
    unset($liq);

    echo json_encode(['success' => true, 'liquidaciones' => $liquidaciones]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>