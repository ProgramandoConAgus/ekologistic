<?php
header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', 1);
error_reporting(E_ALL);
include("../../con_db.php");

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
    exit;
}

$despachoID = $_POST['DespachoID'] ?? null;
if (!$despachoID || !ctype_digit((string)$despachoID)) {
    echo json_encode(['success' => false, 'message' => 'DespachoID no proporcionado o inválido.']);
    exit;
}

$despachoID = (int)$despachoID;

try {
    // Actualiza el estado a 0 (inactivo). Asegurate de que tu SELECT en la lista ignore status = 0.
    $stmt = $conexion->prepare("UPDATE despacho SET status = 0, reason = CONCAT(IFNULL(reason,''), '\nEliminado_por_ID_', ?) WHERE DespachoID = ?");
    if (!$stmt) throw new Exception('Error al preparar la consulta: ' . $conexion->error);

    $stmt->bind_param('ii', $despachoID, $despachoID);
    if (!$stmt->execute()) {
        throw new Exception('Error al ejecutar UPDATE: ' . $stmt->error);
    }

    if ($stmt->affected_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'No se encontró el despacho o ya estaba inactivo.']);
    } else {
        echo json_encode(['success' => true, 'message' => 'Despacho marcado como eliminado (status=0).']);
    }

    $stmt->close();
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>
