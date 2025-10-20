<?php
require_once '../../con_db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ExportID'], $_POST['reason'])) {
    $exportID = intval($_POST['ExportID']);
    $reason   = trim($_POST['reason']);  

    if ($reason === '') {
        echo "Error: La razón no puede estar vacía.";
        exit;
    }

    try {
        $conexion->begin_transaction();

        $newStatus = 4;
        $stmt = $conexion->prepare("UPDATE exports SET status = ?, reason = ? WHERE ExportsID = ?");
        if (!$stmt) {
            throw new Exception("Error preparando UPDATE: " . $conexion->error);
        }

        $stmt->bind_param("isi", $newStatus, $reason, $exportID);
        $stmt->execute();

        if ($stmt->affected_rows === 0) {
            throw new Exception("No se encontró la exportación o no hubo cambios.");
        }

        $stmt->close();
        $conexion->commit();

        echo "OK";

    } catch (Exception $e) {
        $conexion->rollback();
        echo "Error: " . $e->getMessage();
    }

} else {
    echo "Solicitud inválida";
}
?>
