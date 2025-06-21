<?php
require_once '../../con_db.php';
header('Content-Type: text/plain; charset=utf-8');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        throw new Exception('Método no permitido');
    }

    $exportID = isset($_POST['ExportID']) ? intval($_POST['ExportID']) : 0;
    $status   = isset($_POST['status'])   ? intval($_POST['status'])   : 0;
    if ($exportID <= 0 || $status <= 0) {
        http_response_code(400);
        throw new Exception('Parámetros inválidos');
    }

    $stmt = $conexion->prepare("UPDATE imports SET status = ? WHERE ImportsID = ?");
    if (!$stmt) {
        http_response_code(500);
        throw new Exception('Error al preparar la consulta: ' . $conexion->error);
    }

    $stmt->bind_param('ii', $status, $exportID);
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
