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
