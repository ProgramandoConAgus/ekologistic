<?php
session_start();
require_once '../con_db.php';

header('Content-Type: application/json');

try {
    // Validar autenticación
    if (!isset($_SESSION['IdUsuario'])) {
        throw new Exception('Acceso no autorizado');
    }

    $input = json_decode(file_get_contents('php://input'), true);

    // Validar datos de entrada
    if (empty($input['id']) || !isset($input['palets_carga']) || !isset($input['load_qty'])) {
        throw new Exception('Datos incompletos');
    }

    $id = filter_var($input['id'], FILTER_SANITIZE_NUMBER_INT);
    $paletsCarga = filter_var($input['palets_carga'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $loadQty = filter_var($input['load_qty'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);

    // Verificar permiso del usuario (opcional según tu lógica)
    $stmt = $conexion->prepare("
        SELECT id
        FROM dispatch
        WHERE id = ?
    ");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        throw new Exception('No tienes permisos para editar este registro');
    }

    // Actualizar palets_carga y load_qty
    $updateStmt = $conexion->prepare("
        UPDATE dispatch
        SET palets = ?, cantidad = ?
        WHERE id = ?
    ");
    $updateStmt->bind_param("ddi", $paletsCarga, $loadQty, $id);

    if (!$updateStmt->execute()) {
        throw new Exception('Error al actualizar en base de datos');
    }

    echo json_encode(['success' => true]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
