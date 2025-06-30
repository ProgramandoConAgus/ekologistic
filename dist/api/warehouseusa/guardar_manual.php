<?php
session_start();
include '../con_db.php';
header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Método no permitido', 405);
    }

    $data = json_decode(file_get_contents('php://input'), true);
    $cajas    = intval($data['cajas']    ?? 0);
    $palets   = intval($data['palets']   ?? 0);
    $lote     = trim($data['lote']       ?? '');
    $po       = trim($data['po']         ?? '');
    $desc     = trim($data['descripcion']?? '');
    $invoice  = trim($data['invoice']    ?? '');
    $fecha    = trim($data['fecha_ingreso'] ?? '');
    $receive  = trim($data['warehouse_receive'] ?? '');

    if ($invoice === '' || $fecha === '') {
        echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
        exit;
    }

    $stmt = $conexion->prepare(
        "INSERT INTO dispatch (cantidad, palets, numero_lote, numero_orden_compra, descripcion, numero_factura, fecha_entrada, recibo_almacen, estado) VALUES (?,?,?,?,?,?,?,?, 'Cargado')"
    );
    if (!$stmt) {
        throw new Exception('Error en prepare: ' . $conexion->error);
    }
    $stmt->bind_param('iissssss', $cajas, $palets, $lote, $po, $desc, $invoice, $fecha, $receive);
    $stmt->execute();
    $stmt->close();

    echo json_encode(['success' => true, 'id' => $conexion->insert_id]);

} catch (Exception $e) {
    http_response_code($e->getCode() ?: 500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
