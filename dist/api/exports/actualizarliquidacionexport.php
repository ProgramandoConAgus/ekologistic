<?php
include("../../con_db.php");

header('Content-Type: application/json');

try {
    $input = json_decode(file_get_contents('php://input'), true);
    $datos = $input['datos'] ?? [];

    if (!$datos) {
        echo json_encode(['success' => false, 'message' => 'Datos vacíos']);
        exit;
    }

    $errores = 0;
    $detallesErrores = [];

    foreach ($datos as $index => $d) {
        $stmt = $conexion->prepare("
            UPDATE itemsliquidacionexportincoterms ii
            JOIN itemsliquidacionexport il ON ii.IdItemsLiquidacionExport = il.IdItemsLiquidacionExport
            JOIN tipoincoterm t ON il.IdTipoIncoterm = t.IdTipoIncoterm
            SET ii.Cantidad = ?, ii.ValorUnitario = ?, ii.ValorTotal = (? * ?)
            WHERE il.NombreItems = ? AND t.NombreTipoIncoterm = ?
        ");

        if (!$stmt) {
            throw new Exception("Error preparando consulta en índice $index: " . $conexion->error);
        }

        $stmt->bind_param(
            "dddsss",
            $d['cantidad'],
            $d['valorUnitario'],
            $d['cantidad'],
            $d['valorUnitario'],
            $d['nombreItem'],
            $d['incoterm']
        );

        if (!$stmt->execute()) {
            $errores++;
            $detallesErrores[] = "Error en item $index: " . $stmt->error;
        }
    }

    if ($errores === 0) {
        echo json_encode(['success' => true, 'message' => 'Actualización exitosa']);
    } else {
        echo json_encode([
            'success' => false,
            'message' => "Hubo $errores error(es) en la actualización.",
            'errors' => $detallesErrores
        ]);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Ocurrió un error inesperado.',
        'error' => $e->getMessage()
    ]);
}
