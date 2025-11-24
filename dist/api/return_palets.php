<?php
require_once __DIR__ . '/../con_db.php';
header('Content-Type: application/json');

try {
    $input = json_decode(file_get_contents('php://input'), true);
    $id = isset($input['id']) ? intval($input['id']) : null;

    if (!$id) {
        echo json_encode(['success' => false, 'message' => 'Parámetros incompletos']);
        exit;
    }

    // Iniciar transacción
    $conexion->begin_transaction();

    // Buscar registro en palets_cargados
    $stmtSel = $conexion->prepare("SELECT * FROM palets_cargados WHERE id = ? FOR UPDATE");
    $stmtSel->bind_param('i', $id);
    $stmtSel->execute();
    $res = $stmtSel->get_result();
    $row = $res->fetch_assoc();

    if (!$row) {
        $conexion->rollback();
        echo json_encode(['success' => false, 'message' => 'Registro no encontrado en palets_cargados']);
        exit;
    }

    // Intentar insertar en dispatch usando los campos de palets_cargados
    $insertSql = "INSERT INTO dispatch (
        fecha_entrada, fecha_salida, recibo_almacen, numero_factura, numero_lote, notas, numero_orden_compra,
        numero_parte, descripcion, modelo, cantidad, palets, valor_unitario, valor, longitud_in, ancho_in, altura_in, peso_lb, unidad, codigo_despacho, estado
    ) SELECT
        fecha_entrada, fecha_salida, recibo_almacen, numero_factura, numero_lote, notas, numero_orden_compra,
        numero_parte, descripcion, modelo, cantidad, palets, valor_unitario, valor, longitud_in, ancho_in, altura_in, peso_lb, unidad, codigo_despacho, 'En Almacén'
    FROM palets_cargados WHERE id = ?";

    $stmtIns = $conexion->prepare($insertSql);
    if (!$stmtIns) {
        $conexion->rollback();
        throw new Exception('Error prepare insert dispatch: ' . $conexion->error);
    }

    $stmtIns->bind_param('i', $id);
    if (!$stmtIns->execute()) {
        $conexion->rollback();
        throw new Exception('No se pudo insertar en dispatch: ' . $stmtIns->error);
    }

    if ($stmtIns->affected_rows === 0) {
        $conexion->rollback();
        throw new Exception('No se insertó ningún registro en dispatch');
    }

    // Verificación: obtener el id insertado (puede ser múltiple rows but here one)
    $dispatchId = $conexion->insert_id;

    // Borrar el registro original en palets_cargados
    $stmtDel = $conexion->prepare("DELETE FROM palets_cargados WHERE id = ?");
    if (!$stmtDel) {
        $conexion->rollback();
        throw new Exception('Error prepare delete palets_cargados: ' . $conexion->error);
    }
    $stmtDel->bind_param('i', $id);
    if (!$stmtDel->execute()) {
        $conexion->rollback();
        throw new Exception('No se pudo eliminar palets_cargados: ' . $stmtDel->error);
    }

    if ($stmtDel->affected_rows === 0) {
        $conexion->rollback();
        throw new Exception('No se eliminó el registro de palets_cargados');
    }

    // Verificaciones finales: comprobar que dispatch contiene la fila insertada
    $check = $conexion->prepare("SELECT id FROM dispatch WHERE id = ?");
    $check->bind_param('i', $dispatchId);
    $check->execute();
    $resCheck = $check->get_result();
    if ($resCheck->num_rows === 0) {
        $conexion->rollback();
        throw new Exception('Verificación fallida: dispatch no contiene el registro insertado');
    }

    // Todo ok
    $conexion->commit();

    echo json_encode(['success' => true, 'dispatch_id' => $dispatchId, 'palet_id' => $id]);
    exit;

} catch (Exception $e) {
    if ($conexion->connect_errno === 0 && $conexion->in_transaction) {
        $conexion->rollback();
    }
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    exit;
}
?>
