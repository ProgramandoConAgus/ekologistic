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
        ii.Cantidad      = ?,
        ii.ValorUnitario = ?,
        ii.ValorTotal    = ?,
        ii.Notas         = ?
    WHERE ic.IdIncotermsDespacho = ?
    ";

$stmt = $conexion->prepare($sql);

// ...

foreach ($datos as $d) {
    $cantidad      = floatval($d['cantidad']);
    $valorUnitario = floatval($d['valorUnitario']);
    $valorTotal    = floatval($d['valorTotal']);
    $notas         = $d['notas'];           // cadena
    $idIncoterms   = intval($d['idIncoterms']);

    // tipos: d, d, d, s, i
    $stmt->bind_param(
      "dddsi",
      $cantidad,
      $valorUnitario,
      $valorTotal,
      $notas,
      $idIncoterms
    );

    if (! $stmt->execute()) {
      $errores[] = "Id {$idIncoterms}: " . $stmt->error;
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