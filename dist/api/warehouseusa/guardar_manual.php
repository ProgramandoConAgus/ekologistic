<?php
session_start();
include '../../con_db.php';
header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Método no permitido', 405);
    }

    $data = json_decode(file_get_contents('php://input'), true);
    $fechaEntrada = trim($data['fecha_entrada'] ?? '');
    $fechaSalida  = trim($data['fecha_salida'] ?? '');
    $recibo       = trim($data['recibo_almacen'] ?? '');
    $estado       = trim($data['estado'] ?? '');
    $numeroFactura= trim($data['numero_factura'] ?? '');
    $numeroLote   = trim($data['numero_lote'] ?? '');
    $palets       = trim($data['palets'] ?? '');
    $ordenCompra  = trim($data['orden_compra'] ?? '');
    $numeroParte  = trim($data['numero_parte'] ?? '');
    $descripcion  = trim($data['descripcion'] ?? '');
    $modelo       = trim($data['modelo'] ?? '');
    $cantidad     = intval($data['cantidad'] ?? 0);
    $valorUnit    = $data['valor_unitario'] === '' ? null : floatval(str_replace(',', '.', $data['valor_unitario']));
    $valor        = $data['valor'] === '' ? null : floatval(str_replace(',', '.', $data['valor']));
    $unidad       = trim($data['unidad'] ?? '');
    $longitudIn   = $data['longitud'] === '' ? null : floatval(str_replace(',', '.', $data['longitud']));
    $anchoIn      = $data['ancho'] === '' ? null : floatval(str_replace(',', '.', $data['ancho']));
    $alturaIn     = $data['altura'] === '' ? null : floatval(str_replace(',', '.', $data['altura']));
    $pesoLb       = $data['peso'] === '' ? null : floatval(str_replace(',', '.', $data['peso']));
    $valorUnitR   = $data['valor_unitario_restante'] === '' ? null : floatval(str_replace(',', '.', $data['valor_unitario_restante']));
    $valorR       = $data['valor_restante'] === '' ? null : floatval(str_replace(',', '.', $data['valor_restante']));
    $unidadR      = trim($data['unidad_restante'] ?? '');
    $longitudR    = $data['longitud_restante'] === '' ? null : floatval(str_replace(',', '.', $data['longitud_restante']));
    $anchoR       = $data['ancho_restante'] === '' ? null : floatval(str_replace(',', '.', $data['ancho_restante']));
    $alturaR      = $data['altura_restante'] === '' ? null : floatval(str_replace(',', '.', $data['altura_restante']));
    $pesoR        = $data['peso_restante'] === '' ? null : floatval(str_replace(',', '.', $data['peso_restante']));

    if ($numeroFactura === '' || $fechaEntrada === '') {
        echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
        exit;
    }

    $stmt = $conexion->prepare(
        "INSERT INTO dispatch (
            fecha_entrada,
            fecha_salida,
            recibo_almacen,
            estado,
            numero_factura,
            numero_lote,
            notas,
            numero_orden_compra,
            numero_parte,
            descripcion,
            modelo,
            cantidad,
            valor_unitario,
            valor,
            unidad,
            longitud_in,
            ancho_in,
            altura_in,
            peso_lb,
            valor_unitario_restante,
            valor_restante,
            unidad_restante,
            longitud_in_restante,
            ancho_in_restante,
            altura_in_restante,
            peso_lb_restante
        ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)"
    );
    if (!$stmt) {
        throw new Exception('Error en prepare: ' . $conexion->error);
    }
    $stmt->bind_param(
        'ssssiisisssiddsddddddsdddd',
        $fechaEntrada,
        $fechaSalida,
        $recibo,
        $estado,
        $numeroFactura,
        $numeroLote,
        $palets,
        $ordenCompra,
        $numeroParte,
        $descripcion,
        $modelo,
        $cantidad,
        $valorUnit,
        $valor,
        $unidad,
        $longitudIn,
        $anchoIn,
        $alturaIn,
        $pesoLb,
        $valorUnitR,
        $valorR,
        $unidadR,
        $longitudR,
        $anchoR,
        $alturaR,
        $pesoR
    );
    $stmt->execute();
    $stmt->close();

    echo json_encode(['success' => true, 'id' => $conexion->insert_id]);

} catch (Exception $e) {
    http_response_code($e->getCode() ?: 500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
