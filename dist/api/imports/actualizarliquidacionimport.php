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
if (!$stmt) {
    throw new Exception("Error preparando UPDATE: " . $conexion->error);
}

$errores = [];
foreach ($datos as $i => $d) {
    $idIncoterms   = intval($d['idIncoterms']    ?? 0);
    $cantidad      = floatval($d['cantidad']        ?? 0);
    $valorUnitario = floatval($d['valorUnitario'] ?? 0);
    $valorTotal    = floatval($d['valorTotal']    ?? ($cantidad * $valorUnitario));
    $notas         = trim($d['notas']             ?? '');

    // Ahora bind_param tiene 5 parámetros: dddsi (double,double,double,string,integer)
    $stmt->bind_param(
        "dddsi",
        $cantidad,
        $valorUnitario,
        $valorTotal,
        $notas,
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
