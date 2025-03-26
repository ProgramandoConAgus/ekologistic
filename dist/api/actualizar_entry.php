<?php
session_start();
require_once '../con_db.php';

header('Content-Type: application/json');

try {
    if (!isset($_SESSION['IdUsuario'])) {
        throw new Exception('Acceso no autorizado', 401);
    }

    $input = json_decode(file_get_contents('php://input'), true);

    // Validación mejorada
    if (!isset($input['id'], $input['fecha'])) {
        throw new Exception('Parámetros requeridos: id y fecha', 400);
    }

    $idItem = filter_var($input['id'], FILTER_VALIDATE_INT);
    $fecha = trim(filter_var($input['fecha'], FILTER_SANITIZE_STRING));

    // Validación de fecha vacía
    if (empty($fecha)) {
        $fechaMysql = null;
    } else {
        $fechaObj = DateTime::createFromFormat('d/m/Y', $fecha);
        if (!$fechaObj || $fechaObj->format('d/m/Y') !== $fecha) {
            throw new Exception('Formato de fecha inválido. Use dd/mm/yyyy', 400);
        }
        $fechaMysql = $fechaObj->format('Y-m-d');
    }

    // Verificar permiso de usuario y existencia del item
    $stmt = $conexion->prepare("
        SELECT i.IdItem 
        FROM items i
        INNER JOIN container c ON i.IdContainer = c.IdContainer
        INNER JOIN packing_list pl ON c.idPackingList = pl.IdPackingList
        WHERE i.IdItem = ? AND pl.IdUsuario = ?
    ");
    $stmt->bind_param("ii", $idItem, $_SESSION['IdUsuario']);
    $stmt->execute();
    
    if ($stmt->get_result()->num_rows === 0) {
        throw new Exception('Item no encontrado o sin permisos', 403);
    }

    // Actualizar con posible NULL
    $update = $conexion->prepare("
        UPDATE items 
        SET EntryDate = ?
        WHERE IdItem = ?
    ");
    $update->bind_param("si", $fechaMysql, $idItem);
    
    if (!$update->execute()) {
        throw new Exception('Error al actualizar fecha: ' . $conexion->error, 500);
    }

    echo json_encode([
        'success' => true,
        'newDate' => $fecha ?: '',
        'message' => 'Fecha actualizada correctamente'
    ]);

} catch (Exception $e) {
    http_response_code($e->getCode() ?: 500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}