<?php
include("../../con_db.php");
header('Content-Type: application/json');

try {
    $input = json_decode(file_get_contents('php://input'), true);
    $datos = $input['datos'] ?? [];
    $coeficiente = floatval($input['coeficiente'] ?? 0);
    $idDespacho = intval($input['idDespacho'] ?? 0);

    if (empty($datos) || !$idDespacho) {
        echo json_encode(['success' => false, 'message' => 'Datos vacíos o DespachoID inválido']);
        exit;
    }

    // 1) Actualizamos el coeficiente en la tabla despacho
    $stmtCoef = $conexion->prepare("
        UPDATE despacho
        SET coeficiente = ?
        WHERE DespachoID = ?
    ");
    $stmtCoef->bind_param("di", $coeficiente, $idDespacho);
    $stmtCoef->execute();
    $stmtCoef->close();

    // 2) Actualizamos o insertamos los items según corresponda
    $sqlUpdate = "
    UPDATE itemsliquidaciondespachoincoterms ii
    JOIN incotermsDespacho ic 
        ON ic.IdItemsLiquidacionDespachoIncoterm = ii.IdItemsLiquidacionDespachoIncoterms
    SET 
        ii.Cantidad      = ?,
        ii.ValorUnitario = ?,
        ii.ValorTotal    = ?,
        ii.Notas         = ?
    WHERE ic.IdIncotermsDespacho = ?
    ";
    $stmtUpdate = $conexion->prepare($sqlUpdate);
    if (!$stmtUpdate) throw new Exception("Error preparando UPDATE: " . $conexion->error);

    // Prepared statements for INSERT (nuevo item) -> mirror guardarliquidaciondespacho.php
    $stmtInsertItem = $conexion->prepare(
      "INSERT INTO itemsliquidaciondespachoincoterms
       (IdItemsLiquidacionDespacho, Cantidad, ValorUnitario, ValorTotal, Notas)
       VALUES (?, ?, ?, ?, ?)"
    );
    if (!$stmtInsertItem) throw new Exception("Error preparando INSERT items: " . $conexion->error);

    $stmtInsertInc = $conexion->prepare(
      "INSERT INTO incotermsdespacho
       (IdItemsLiquidacionDespachoIncoterm, IdDespacho)
       VALUES (?, ?)"
    );
    if (!$stmtInsertInc) throw new Exception("Error preparando INSERT incoterms: " . $conexion->error);

    // Comprobar existencia de IdIncotermsDespacho (si existe -> UPDATE, si no -> INSERT)
    $stmtExists = $conexion->prepare("SELECT COUNT(*) AS c FROM incotermsdespacho WHERE IdIncotermsDespacho = ?");
    if (!$stmtExists) throw new Exception("Error preparando EXISTS: " . $conexion->error);

    $errores = [];
    foreach ($datos as $d) {
        $cantidad      = floatval($d['cantidad']);
        $valorUnitario = floatval($d['valorUnitario']);
        $valorTotal    = floatval($d['valorTotal']);
        $notas         = $d['notas'] ?? '';
        $idIncoterms   = intval($d['idIncoterms']);

        // ¿Existe como IdIncotermsDespacho (pivot)?
        $stmtExists->bind_param('i', $idIncoterms);
        $stmtExists->execute();
        $resEx = $stmtExists->get_result()->fetch_assoc();
        $count = intval($resEx['c'] ?? 0);

        if ($count > 0) {
            // Hacer UPDATE
            $stmtUpdate->bind_param("dddsi", $cantidad, $valorUnitario, $valorTotal, $notas, $idIncoterms);
            if (!$stmtUpdate->execute()) {
                $errores[] = "IdIncoterms {$idIncoterms}: " . $stmtUpdate->error;
            }
        } else {
            // Interpretamos $idIncoterms como IdItemsLiquidacionDespacho (plantilla) y hacemos INSERT
            $idItemPlantilla = $idIncoterms;
            $stmtInsertItem->bind_param("iddds", $idItemPlantilla, $cantidad, $valorUnitario, $valorTotal, $notas);
            if (!$stmtInsertItem->execute()) {
                $errores[] = "Insert Item plantilla {$idItemPlantilla}: " . $stmtInsertItem->error;
                continue;
            }
            $newItemIncId = $conexion->insert_id;

            // Insertar registro en incotermsdespacho apuntando al despacho
            $stmtInsertInc->bind_param("ii", $newItemIncId, $idDespacho);
            if (!$stmtInsertInc->execute()) {
                $errores[] = "Insert Incoterm para item {$newItemIncId}: " . $stmtInsertInc->error;
            }
        }
    }

    // Cerrar statements
    $stmtUpdate->close();
    $stmtInsertItem->close();
    $stmtInsertInc->close();
    $stmtExists->close();

    // 3) Respuesta
    if (empty($errores)) {
        echo json_encode([
            'success'         => true,
            'message'         => 'Actualización exitosa',
            'datosProcesados' => $datos,
            'coeficiente'     => $coeficiente
        ]);
    } else {
        echo json_encode([
            'success'         => false,
            'message'         => 'Algunos registros no se actualizaron.',
            'errors'          => $errores,
            'datosProcesados' => $datos,
            'coeficiente'     => $coeficiente
        ]);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error inesperado.',
        'error'   => $e->getMessage()
    ]);
}
?>
