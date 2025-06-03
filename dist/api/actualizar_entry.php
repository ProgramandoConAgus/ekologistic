<?php
session_start();
require_once '../con_db.php';
require '../vendor/autoload.php';  // Si usas librerías externas

header('Content-Type: application/json');

try {
    if (!isset($_SESSION['IdUsuario'])) {
        throw new Exception('Acceso no autorizado', 401);
    }

    $input = json_decode(file_get_contents('php://input'), true);
    if (!isset($input['id'], $input['fecha'])) {
        throw new Exception('Parámetros requeridos: id y fecha', 400);
    }

    $idItem = filter_var($input['id'], FILTER_VALIDATE_INT);
    $fecha  = trim(filter_var($input['fecha'], FILTER_SANITIZE_STRING));

    // Formato dd/mm/YYYY -> MySQL YYYY-mm-dd o NULL
    if ($fecha === '') {
        $fechaMysql = null;
    } else {
        $fechaObj = DateTime::createFromFormat('d/m/Y', $fecha);
        if (!$fechaObj || $fechaObj->format('d/m/Y') !== $fecha) {
            throw new Exception('Formato de fecha inválido. Use dd/mm/yyyy', 400);
        }
        $fechaMysql = $fechaObj->format('Y-m-d');
    }

    // SELECT para validar permisos y traer datos para el correo
    $stmt = $conexion->prepare("
        SELECT 
            i.IdItem, 
            pl.IdPackingList, 
            u.Nombre       AS nombreUsuario,
            c.num_op       AS num_op,
            c.Booking_BK   AS booking_bl
        FROM items i
        INNER JOIN container c ON i.IdContainer = c.IdContainer
        INNER JOIN packing_list pl ON c.IdPackingList = pl.IdPackingList
        INNER JOIN usuarios u ON pl.IdUsuario = u.IdUsuario
        WHERE i.IdItem = ?
    ");
    $stmt->bind_param("i", $idItem);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res->num_rows === 0) {
        throw new Exception('Item no encontrado o sin permisos: ' . $idItem, 403);
    }
    $row            = $res->fetch_assoc();
    $idPackingList  = $row['IdPackingList'];
    $nombreUsuario  = $row['nombreUsuario'];
    $num_op         = $row['num_op'];
    $booking_bl     = $row['booking_bl'];
    $stmt->close();

    // UPDATE en items
    $update = $conexion->prepare("UPDATE items SET EntryDate = ? WHERE IdItem = ?");
    $update->bind_param("si", $fechaMysql, $idItem);
    if (!$update->execute()) {
        throw new Exception('Error al actualizar fecha: ' . $conexion->error, 500);
    }
    $update->close();

    // PROGRAMAR ALERTA POR EMAIL
    $destinatarios = [
        'export@ekopackingsas.com',
        'Kreinoso@ekopackingsas.com',
        'jjloaiza@ekopackingsas.com'
    ];
    $asunto = 'Alerta: 5 días restantes antes de cargos adicionales';
    $cuerpo = "
        <p>Estimado equipo,</p>
        <p>Se ha registrado una fecha de entrada en el warehouse, y la alerta se activa 25 días después de esta entrada. En este momento, faltan solo <strong>5 días</strong> para evitar gastos adicionales.</p>
        <p><strong>Detalles de la carga:</strong><br>
        - Número de operación: {$num_op}<br>
        - Booking: {$booking_bl}</p>
        <p>Por favor, asegúrese de tomar las acciones necesarias antes de que transcurran estos 5 días para evitar cargos adicionales.</p>
        <p>Eko Logistic Software</p>
    ";

    $stmtEmail = $conexion->prepare("
        INSERT INTO tareas_email 
            (destinatario, asunto, cuerpo, fecha_ejecucion) 
        VALUES 
            (?, ?, ?, DATE_ADD(NOW(), INTERVAL 25 DAY))
    ");
    if (!$stmtEmail) {
        throw new Exception('Error preparando tarea_email: ' . $conexion->error);
    }
    foreach ($destinatarios as $dest) {
        $stmtEmail->bind_param('sss', $dest, $asunto, $cuerpo);
        if (!$stmtEmail->execute()) {
            throw new Exception("Error al programar alerta para {$dest}: " . $conexion->error);
        }
    }
    $stmtEmail->close();

    // Respuesta JSON
    echo json_encode([
        'success' => true,
        'newDate' => $fecha,
        'message' => 'Fecha actualizada correctamente y alerta programada.'
    ]);

} catch (Exception $e) {
    http_response_code($e->getCode() ?: 500);
    echo json_encode([
        'success' => false,
        'error'   => $e->getMessage()
    ]);
}