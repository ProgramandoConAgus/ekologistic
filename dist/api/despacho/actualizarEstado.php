<?php
require_once '../../con_db.php';
header('Content-Type: text/plain; charset=utf-8');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        throw new Exception('Método no permitido');
    }

    $despachoID = isset($_POST['DespachoID']) ? intval($_POST['DespachoID']) : 0;
    $status   = isset($_POST['status'])   ? intval($_POST['status'])   : 0;
    if ($despachoID <= 0 || $status <= 0) {
        http_response_code(400);
        throw new Exception('Parámetros inválidos');
    }

    $stmt = $conexion->prepare("UPDATE despacho SET status = ? WHERE DespachoID = ?");
    if (!$stmt) {
        http_response_code(500);
        throw new Exception('Error al preparar la consulta: ' . $conexion->error);
    }

    $stmt->bind_param('ii', $status, $despachoID);
    if (!$stmt->execute()) {
        http_response_code(500);
        throw new Exception('Error al ejecutar la consulta: ' . $stmt->error);
    }

    echo 'OK';

    $stmt->close();
    exit;

} catch (Exception $e) {
    if (http_response_code() < 400) {
        http_response_code(500);
    }
    echo $e->getMessage();
    exit;
}
