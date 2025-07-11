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

    // Validaciones
    if ($cantidadCarga > $cantidadActual) throw new Exception("No se puede cargar más cajas de las disponibles.");
    if ($paletsCarga > $paletsActual) throw new Exception("No se puede cargar más palets de los disponibles.");
    if ($cantidadCarga <= 0 || $paletsCarga <= 0) throw new Exception("Cantidad a cargar: {$cantidadCarga}, Palets a cargar: {$paletsCarga}. Ambos deben ser mayores a 0.");


    // Insertar en palets_cargados
    $stmtInsert = $conexion->prepare("
        INSERT INTO palets_cargados (
            fecha_entrada, fecha_salida, recibo_almacen, estado,
            numero_factura, numero_lote, notas, numero_orden_compra,
            numero_parte, descripcion, modelo, cantidad, palets,
            valor_unitario, valor, unidad, longitud_in, ancho_in, altura_in, peso_lb
        )
        VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)
    ");
    $stmtInsert->bind_param(
        'sssssssssssisddsdddd',
        $row['fecha_entrada'], $row['fecha_salida'], $row['recibo_almacen'], $value,
        $row['numero_factura'], $row['numero_lote'], $row['notas'], $row['numero_orden_compra'],
        $row['numero_parte'], $row['descripcion'], $row['modelo'], $cantidadCarga, $paletsCarga,
        $row['valor_unitario'], $row['valor'], $row['unidad'], $row['longitud_in'],
        $row['ancho_in'], $row['altura_in'], $row['peso_lb']
    );
    $stmtInsert->execute();

    if ($stmtInsert->affected_rows === 0) throw new Exception('No se pudo insertar en palets_cargados.');

    // Caso COMPLETO: cantidadCarga y paletsCarga coinciden con el registro
    if ($cantidadCarga == $cantidadActual && $paletsCarga == $paletsActual) {
        $stmtUpdate = $conexion->prepare("UPDATE dispatch SET estado = 'Completo' WHERE id = ?");
        $stmtUpdate->bind_param("i", $id);
        $stmtUpdate->execute();

        echo json_encode([
            'success' => true,
            'id' => $id,
            'inserted_cargado' => $stmtInsert->affected_rows,
            'updated_dispatch' => $stmtUpdate->affected_rows,
            'message' => "Ítem {$id} COMPLETADO: se cargaron {$cantidadCarga} cajas y {$paletsCarga} palets."
        ]);
    }
    // Caso cantidad = 36 y paletsCarga < paletsActual
    elseif ($cantidadCarga == 36 && $paletsCarga < $paletsActual) {
        $nuevoPalets = $paletsActual - $paletsCarga;
        if ($paletsActual == 1 && $nuevoPalets < 1) $nuevoPalets = 1;
        elseif ($nuevoPalets < 0) $nuevoPalets = 0;

        $stmtUpdate = $conexion->prepare("
            UPDATE dispatch
            SET palets = ?, estado = 'En Almacén'
            WHERE id = ?
        ");
        $stmtUpdate->bind_param("di", $nuevoPalets, $id);
        $stmtUpdate->execute();

        echo json_encode([
            'success' => true,
            'id' => $id,
            'inserted_cargado' => $stmtInsert->affected_rows,
            'updated_dispatch' => $stmtUpdate->affected_rows,
            'message' => "Ítem {$id}: cantidad 36 cargada, se descontaron {$paletsCarga} palets. Restan {$nuevoPalets} palets."
        ]);
    }
    // Cualquier otro caso → carga parcial normal
    else {
        $nuevaCantidad = $cantidadActual - $cantidadCarga;
        $nuevoPalets   = $paletsActual - $paletsCarga;

        if ($paletsActual == 1 && $nuevoPalets < 1) $nuevoPalets = 1;
        elseif ($nuevoPalets < 0) $nuevoPalets = 0;

        $stmtUpdate = $conexion->prepare("
            UPDATE dispatch
            SET cantidad = ?, palets = ?, estado = 'En Almacén'
            WHERE id = ?
        ");
        $stmtUpdate->bind_param("ddi", $nuevaCantidad, $nuevoPalets, $id);
        $stmtUpdate->execute();

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
