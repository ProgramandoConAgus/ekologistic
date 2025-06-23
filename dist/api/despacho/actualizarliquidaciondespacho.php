<?php
include("../../con_db.php");
header('Content-Type: application/json');

try {
    $input = json_decode(file_get_contents('php://input'), true);
    $datos = $input['datos'] ?? [];

    if (empty($datos)) {
        echo json_encode(['success' => false, 'message' => 'Datos vacíos']);
        exit;
    }

    // Preparamos la consulta JOIN con la tabla incoterms para filtrar por IdIncoterms
    $sql = "
      UPDATE itemsliquidaciondespachoincoterms ii
      JOIN incotermsDespacho ic 
        ON ic.IdItemsLiquidacionDespachoIncoterm = ii.IdItemsLiquidacionDespachoIncoterms
      SET 
        ii.Cantidad       = ?,
        ii.ValorUnitario  = ?,
        ii.ValorTotal     = ?
      WHERE ic.IdIncotermsDespacho = ?
    ";
    $stmt = $conexion->prepare($sql);
    if (!$stmt) {
        throw new Exception("Error preparando UPDATE: " . $conexion->error);
    }
$errores = [];
foreach ($datos as $i => $d) {
    $idIncoterms   = intval($d['idIncoterms']    ?? 0);
    $cantidad      = floatval($d['cantidad']        ?? 0);
    $valorUnitario = floatval($d['valorUnitario'] ?? 0);
    $valorTotal    = floatval($d['valorTotal']    ?? ($cantidad * $valorUnitario));

    $stmt->bind_param(
        "dddi",
        $cantidad,
        $valorUnitario,
        $valorTotal,
        $idIncoterms
    );

    if (!$stmt->execute()) {
        $errores[] = "Fila {$i} (IdIncoterms {$idIncoterms}): " . $stmt->error;
    }
}


    // Respuesta con los datos que efectivamente procesó
    if (empty($errores)) {
        echo json_encode([
            'success'         => true,
            'message'         => 'Actualización exitosa',
            'datosProcesados' => $datos
        ]);
    } else {
        echo json_encode([
            'success'         => false,
            'message'         => 'Algunos registros no se actualizaron.',
            'errors'          => $errores,
            'datosProcesados' => $datos
        ]);
    }

    $stmt->close();

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error inesperado.',
        'error'   => $e->getMessage()
    ]);
}
?>