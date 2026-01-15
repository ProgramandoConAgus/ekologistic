<?php
require_once '../con_db.php';
header('Content-Type: application/json');

try {
    $input = json_decode(file_get_contents('php://input'), true);
    if (empty($input['value'])) throw new Exception('Valor de estado inválido');

    $value = filter_var($input['value'], FILTER_SANITIZE_STRING);
    $allowed_statuses = ['Cargado', 'En Almacén'];
    if (!in_array($value, $allowed_statuses, true)) throw new Exception('Estado no permitido');

    if (empty($input['id'])) throw new Exception('ID no especificado');

    $id = filter_var($input['id'], FILTER_SANITIZE_NUMBER_INT);
    $paletsCarga = isset($input['palets']) ? floatval($input['palets']) : 0;
    $cantidadCarga = isset($input['cantidad']) ? floatval($input['cantidad']) : 0;

    // Obtener registro original
    $stmt = $conexion->prepare("SELECT * FROM dispatch WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 0) throw new Exception("Registro con ID {$id} no encontrado");

    $row = $result->fetch_assoc();
    $cantidadActual = floatval($row['cantidad']);
    $paletsActual   = floatval($row['palets']);

    if ($paletsActual <= 0) throw new Exception("El número de palets registrados es inválido.");

    // Cálculo dinámico de unidades por palet
    $unidadesPorPalet = $cantidadActual / $paletsActual;
    $esperadas = $paletsCarga * $unidadesPorPalet;

    // Validaciones
    if ($cantidadCarga > $cantidadActual) throw new Exception("No se puede cargar más cajas de las disponibles.");
    if ($paletsCarga > $paletsActual) throw new Exception("No se puede cargar más palets de los disponibles.");
    if ($cantidadCarga <= 0 || $paletsCarga <= 0) throw new Exception("Cantidad a cargar: {$cantidadCarga}, Palets a cargar: {$paletsCarga}. Ambos deben ser mayores a 0.");
  
    // Ejecutar las operaciones en transacción: insertar en palets_cargados y modificar/eliminar en dispatch
    $conexion->begin_transaction();

    // Insertar en palets_cargados
    $stmtInsert = $conexion->prepare("\n    INSERT INTO palets_cargados (\n        fecha_entrada, fecha_salida, recibo_almacen, estado,\n        numero_factura, numero_lote, notas, numero_orden_compra,\n        numero_parte, descripcion, modelo, cantidad, palets,\n        valor_unitario, valor, longitud_in, ancho_in, altura_in, peso_lb,\n        unidad, codigo_despacho\n    )\n    VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)\n    ");

    if (!$stmtInsert) throw new Exception('Error prepare insert palets_cargados: ' . $conexion->error);

    $stmtInsert->bind_param(
        'sssssssssssisddddddds',
        $row['fecha_entrada'], $row['fecha_salida'], $row['recibo_almacen'], $value,
        $row['numero_factura'], $row['numero_lote'], $row['notas'], $row['numero_orden_compra'],
        $row['numero_parte'], $row['descripcion'], $row['modelo'], $cantidadCarga, $paletsCarga,
        $row['valor_unitario'], $row['valor'], $row['longitud_in'], $row['ancho_in'],
        $row['altura_in'], $row['peso_lb'], $row['unidad'], $row['codigo_despacho']
    );

    if (!$stmtInsert->execute()) {
        $conexion->rollback();
        throw new Exception('No se pudo insertar en palets_cargados: ' . $stmtInsert->error);
    }

    $insertedId = $conexion->insert_id;

    // Restar cantidad y palets
    $nuevaCantidad = $cantidadActual - $cantidadCarga;
    $nuevoPalets   = $paletsActual - $paletsCarga;

    if ($cantidadCarga == $cantidadActual && $paletsCarga == $paletsActual) {
        // COMPLETO: eliminar fila en dispatch
        $stmtDelete = $conexion->prepare("DELETE FROM dispatch WHERE id = ?");
        if (!$stmtDelete) {
            $conexion->rollback();
            throw new Exception('Error prepare delete dispatch: ' . $conexion->error);
        }
        $stmtDelete->bind_param("i", $id);
        if (!$stmtDelete->execute()) {
            $conexion->rollback();
            throw new Exception('Error delete dispatch: ' . $stmtDelete->error);
        }

        // Verificaciones: comprobar que el registro insertado existe y que dispatch fue borrado
        $checkInserted = $conexion->prepare("SELECT id FROM palets_cargados WHERE id = ?");
        $checkInserted->bind_param('i', $insertedId);
        $checkInserted->execute();
        $resIns = $checkInserted->get_result();
        if ($resIns->num_rows === 0 || $stmtDelete->affected_rows === 0) {
            $conexion->rollback();
            throw new Exception('Verificación fallida al mover registro a palets_cargados');
        }

        $conexion->commit();

        echo json_encode([
            'success' => true,
            'id' => $id,
            'inserted_cargado' => $stmtInsert->affected_rows,
            'deleted_dispatch' => $stmtDelete->affected_rows,
            'message' => "Ítem {$id} COMPLETADO: se cargaron {$cantidadCarga} cajas y {$paletsCarga} palets."
        ]);
    } else {
        // PARCIAL: actualizar dispatch con valores restantes
        $nuevoPalets = $paletsActual - $paletsCarga;
        if ($nuevoPalets < 0) $nuevoPalets = 0;

        if ($nuevoPalets >= 1) {
            // Mantener la proporción original de cajas por palet
            $nuevaCantidad = $cantidadActual;
        } else {
            // Si no quedan palets, cantidad es 0
            $nuevaCantidad = 0;
        }

        $stmtUpdate = $conexion->prepare("\n            UPDATE dispatch\n            SET cantidad = ?, palets = ?, estado = 'En Almacén'\n            WHERE id = ?\n        ");
        if (!$stmtUpdate) {
            $conexion->rollback();
            throw new Exception('Error prepare update dispatch: ' . $conexion->error);
        }
        $stmtUpdate->bind_param("ddi", $nuevaCantidad, $nuevoPalets, $id);
        if (!$stmtUpdate->execute()) {
            $conexion->rollback();
            throw new Exception('Error update dispatch: ' . $stmtUpdate->error);
        }

        // Verificaciones: comprobar que palets_cargados contiene el registro insertado y dispatch tiene los valores esperados
        $checkInserted = $conexion->prepare("SELECT id FROM palets_cargados WHERE id = ?");
        $checkInserted->bind_param('i', $insertedId);
        $checkInserted->execute();
        $resIns = $checkInserted->get_result();

        $checkDispatch = $conexion->prepare("SELECT cantidad, palets FROM dispatch WHERE id = ?");
        $checkDispatch->bind_param('i', $id);
        $checkDispatch->execute();
        $resDisp = $checkDispatch->get_result();
        $dispRow = $resDisp->fetch_assoc();

        if ($resIns->num_rows === 0 || !$dispRow) {
            $conexion->rollback();
            throw new Exception('Verificación fallida tras operación parcial');
        }

        // Optionally verify values match expected (allow small float differences)
        $diffQty = abs(floatval($dispRow['cantidad']) - $nuevaCantidad);
        $diffPal = abs(floatval($dispRow['palets']) - $nuevoPalets);
        if ($diffQty > 0.0001 || $diffPal > 0.0001) {
            $conexion->rollback();
            throw new Exception('Los valores en dispatch no coinciden con lo esperado después de la actualización');
        }

        $conexion->commit();

        echo json_encode([
            'success' => true,
            'id' => $id,
            'inserted_cargado' => $stmtInsert->affected_rows,
            'updated_dispatch' => $stmtUpdate->affected_rows,
            'message' => "Ítem {$id}: se cargaron {$cantidadCarga} cajas y {$paletsCarga} palets. Restante: {$nuevaCantidad} cajas, {$nuevoPalets} palets."
        ]);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
