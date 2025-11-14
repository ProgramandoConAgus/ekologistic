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

    // 1) Exports - update by Booking_BK derived from containers with the given num_op
    $sql = "
      UPDATE exports e
      SET e.status = 3
      WHERE e.Booking_BK IN (
         SELECT DISTINCT c.Booking_BK
         FROM items it
         JOIN container c ON it.idContainer = c.IdContainer
         WHERE c.num_op = ?
      ) AND e.status = 2
    ";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("s", $num_op);
    $stmt->execute();

    // 2) Imports - update by Booking_BK derived from containers with the given num_op
    $sql = "
      UPDATE imports i2
      SET i2.status = 3
      WHERE i2.Booking_BK IN (
         SELECT DISTINCT c.Booking_BK
         FROM items it
         JOIN container c ON it.idContainer = c.IdContainer
         WHERE c.num_op = ?
      ) AND i2.status = 2
    ";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("s", $num_op);
    $stmt->execute();

    // 3) Despacho - update by Booking_BK derived from containers with the given num_op
    $sql = "
      UPDATE despacho d
      SET d.status = 3
      WHERE d.Booking_BK IN (
         SELECT DISTINCT c.Booking_BK
         FROM items it
         JOIN container c ON it.idContainer = c.IdContainer
         WHERE c.num_op = ?
      ) AND d.status = 2
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
