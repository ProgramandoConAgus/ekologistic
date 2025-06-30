<?php
// ../api/despacho/actualizarEstadoConsolidado.php
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
header('Content-Type: application/json');

include "../../con_db.php";

try {
    $input = json_decode(file_get_contents('php://input'), true);
    $num_op = trim($input['num_op'] ?? '');

    if (!$num_op) {
        throw new Exception("Falta el número de operación");
    }

    // Arrancamos transacción
    $conexion->begin_transaction();

    // 1) Exports
    $sql = "
      UPDATE exports e
      JOIN container c ON c.Number_Commercial_Invoice = e.Number_Commercial_Invoice
      SET e.status = 3
      WHERE c.num_op = ? AND e.status = 2
    ";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("s", $num_op);
    $stmt->execute();

    // 2) Imports
    $sql = "
      UPDATE imports i
      JOIN container c ON c.Number_Commercial_Invoice = i.Number_Commercial_Invoice
      SET i.status = 3
      WHERE c.num_op = ? AND i.status = 2
    ";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("s", $num_op);
    $stmt->execute();

    // 3) Despacho
    $sql = "
      UPDATE despacho d
      JOIN container c ON c.Number_Commercial_Invoice = d.Number_Commercial_Invoice
      SET d.status = 3
      WHERE c.num_op = ? AND d.status = 2
    ";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("s", $num_op);
    $stmt->execute();

    // Commit
    $conexion->commit();

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    if ($conexion->in_transaction) {
        $conexion->rollback();
    }
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
