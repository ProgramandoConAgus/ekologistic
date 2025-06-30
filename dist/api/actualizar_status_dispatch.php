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

    // === ACTUALIZACIÓN MASIVA SOLO EN DISPATCH POR CONTENEDOR ===
    if (!empty($input['container'])) {
        $cont = filter_var($input['container'], FILTER_SANITIZE_STRING);

        $stmt = $conexion->prepare("
            UPDATE dispatch
            SET estado = ?
            WHERE notas = ?
        ");
        $stmt->bind_param("ss", $value, $cont);
        $stmt->execute();
        $rows_dispatch = $stmt->affected_rows;

        echo json_encode([
            'success'          => true,
            'container'        => $cont,
            'updated_dispatch' => $rows_dispatch,
            'message'          => "Se marcaron {$rows_dispatch} ítem(s) en dispatch del container '{$cont}' como '{$value}'."
        ]);

    // === ACTUALIZACIÓN INDIVIDUAL POR ID ===
    } elseif (!empty($input['id'])) {
        $id = filter_var($input['id'], FILTER_SANITIZE_NUMBER_INT);

        $stmt = $conexion->prepare("
            UPDATE dispatch
            SET estado = ?
            WHERE id = ?
        ");
        $stmt->bind_param("si", $value, $id);
        $stmt->execute();
        $rows_dispatch = $stmt->affected_rows;

        echo json_encode([
            'success'          => true,
            'id'               => $id,
            'updated_dispatch' => $rows_dispatch,
            'message'          => "Ítem {$id} en dispatch marcado como '{$value}'."
        ]);

    } else {
        throw new Exception('Datos insuficientes: se requiere id o container');
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
