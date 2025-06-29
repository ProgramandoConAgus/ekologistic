<?php
require_once '../con_db.php';
header('Content-Type: application/json');

try {
    $input = json_decode(file_get_contents('php://input'), true);
    if (empty($input['value'])) {
        throw new Exception('Valor de estado inválido');
    }

    // Sanitizar y validar estado
    $value = filter_var($input['value'], FILTER_SANITIZE_STRING);
    $allowed_statuses = ['Cargado', 'En Almacén'];
    if (!in_array($value, $allowed_statuses, true)) {
        throw new Exception('Estado no permitido');
    }

    // === 1) ACTUALIZACIÓN MASIVA POR CONTENEDOR + FACTURA ===
    if (!empty($input['container']) && !empty($input['invoice'])) {
        $cont = filter_var($input['container'], FILTER_SANITIZE_STRING);
        $inv  = filter_var($input['invoice'],  FILTER_SANITIZE_STRING);

        // 1a) actualiza dispatch
        $stmt = $conexion->prepare("
            UPDATE dispatch
            SET estado = ?
            WHERE numero_factura = ?
              AND notas          = ?
        ");
        $stmt->bind_param("sss", $value, $inv, $cont);
        $stmt->execute();
        $rows_dispatch = $stmt->affected_rows;

        // 1b) actualiza container
        $stmt2 = $conexion->prepare("
            UPDATE container
            SET status = ?
            WHERE Number_Commercial_Invoice = ?
              AND Number_Container           = ?
        ");
        $stmt2->bind_param("sss", $value, $inv, $cont);
        $stmt2->execute();
        $rows_container = $stmt2->affected_rows;

        echo json_encode([
            'success'          => true,
            'container'        => $cont,
            'updated_dispatch' => $rows_dispatch,
            'updated_container'=> $rows_container,
            'message'          => "Se marcaron {$rows_dispatch} ítem(s) en dispatch y {$rows_container} contenedor(es) en container como '{$value}'."
        ]);

    // === 2) ACTUALIZACIÓN INDIVIDUAL POR ID ===
    } elseif (!empty($input['id'])) {
        $id = filter_var($input['id'], FILTER_SANITIZE_NUMBER_INT);

        // 2a) actualiza dispatch
        $stmt = $conexion->prepare("
            UPDATE dispatch
            SET estado = ?
            WHERE id = ?
        ");
        $stmt->bind_param("si", $value, $id);
        $stmt->execute();
        $rows_dispatch = $stmt->affected_rows;

        // 2b) actualiza el container relacionado
        $stmt2 = $conexion->prepare("
            UPDATE container c
            INNER JOIN dispatch d
              ON c.Number_Commercial_Invoice = d.numero_factura
             AND c.Number_Container           = d.notas
            SET c.status = ?
            WHERE d.id = ?
        ");
        $stmt2->bind_param("si", $value, $id);
        $stmt2->execute();
        $rows_container = $stmt2->affected_rows;

        echo json_encode([
            'success'          => true,
            'id'               => $id,
            'updated_dispatch' => $rows_dispatch,
            'updated_container'=> $rows_container,
            'message'          => "Ítem {$id} y su contenedor se marcaron como '{$value}'."
        ]);

    } else {
        throw new Exception('Datos insuficientes');
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
