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
    if (empty($input['id']) || empty($input['fecha'])) {
        throw new Exception('Datos incompletos');
    }

    $id = filter_var($input['id'], FILTER_SANITIZE_NUMBER_INT);
    $fecha = filter_var($input['fecha'], FILTER_SANITIZE_STRING);

    // Verificar permiso del usuario uniendo container con packing_list
    $stmt = $conexion->prepare("
        SELECT c.IdContainer
        FROM container c
        INNER JOIN packing_list pl ON c.idPackingList = pl.IdPackingList
        WHERE c.IdContainer = ? AND pl.IdUsuario = ?
    ");
    $stmt->bind_param("ii", $id, $_SESSION['IdUsuario']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception('No tienes permisos para editar este registro');
    }

    // Actualizar la fecha en container
    $updateStmt = $conexion->prepare("
        UPDATE container 
        SET New_ETA_Date = ?
        WHERE IdContainer = ?
    ");
    $updateStmt->bind_param("si", $fecha, $id);
    
    if (!$updateStmt->execute()) {
        throw new Exception('Error al actualizar en base de datos');
    }

    echo json_encode(['success' => true]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
    