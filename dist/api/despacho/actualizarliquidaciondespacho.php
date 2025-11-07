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

    // 2) Actualizamos los items
    $sql = "
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
    $stmt = $conexion->prepare($sql);
    if (!$stmt) throw new Exception("Error preparando UPDATE: " . $conexion->error);

    $errores = [];
    foreach ($datos as $d) {
        $cantidad      = floatval($d['cantidad']);
        $valorUnitario = floatval($d['valorUnitario']);
        $valorTotal    = floatval($d['valorTotal']);
        $notas         = $d['notas'] ?? '';
        $idIncoterms   = intval($d['idIncoterms']);

        $stmt->bind_param("dddsi", $cantidad, $valorUnitario, $valorTotal, $notas, $idIncoterms);
        if (!$stmt->execute()) {
            $errores[] = "Id {$idIncoterms}: " . $stmt->error;
        }
    }

    $stmt->close();

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
