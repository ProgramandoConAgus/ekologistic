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
      JOIN items i ON e.Number_Commercial_Invoice = i.Number_Commercial_Invoice
      JOIN container c ON i.idContainer = c.IdContainer
      SET e.status = 3
      WHERE c.num_op = ? AND e.status = 2
    ";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("s", $num_op);
    $stmt->execute();

    // 2) Imports
    $sql = "
      UPDATE imports i2
      JOIN items i ON i2.Number_Commercial_Invoice = i.Number_Commercial_Invoice
      JOIN container c ON i.idContainer = c.IdContainer
      SET i2.status = 3
      WHERE c.num_op = ? AND i2.status = 2
    ";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("s", $num_op);
    $stmt->execute();

    // 3) Despacho
    $sql = "
      UPDATE despacho d
      JOIN items i ON d.Number_Commercial_Invoice = i.Number_Commercial_Invoice
      JOIN container c ON i.idContainer = c.IdContainer
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
    $conexion->rollback();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
