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

    // Validar datos
    if (empty($input['id']) || empty($input['fecha'])) {
        throw new Exception('Datos incompletos');
    }

    $id = filter_var($input['id'], FILTER_SANITIZE_NUMBER_INT);
    $fecha = filter_var($input['fecha'], FILTER_SANITIZE_STRING);

    // Verificar permiso del usuario
    $stmt = $conexion->prepare("
        SELECT cd.IdContenedoresDetalles 
        FROM contenedordetalles cd
        INNER JOIN contenedor c ON cd.idPackingList = c.idPackingList
        WHERE cd.IdContenedoresDetalles = ? AND c.idUsuario = ?
    ");
    $stmt->bind_param("ii", $id, $_SESSION['IdUsuario']);
    $stmt->execute();
    
    if (!$stmt->get_result()->num_rows) {
        throw new Exception('No tienes permisos para editar este registro');
    }

    // Actualizar fecha
    $updateStmt = $conexion->prepare("
        UPDATE contenedordetalles 
        SET new_eta_date = ?
        WHERE IdContenedoresDetalles = ?
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