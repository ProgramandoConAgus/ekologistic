<?php
require '../../con_db.php';

if (isset($_GET['booking'])) {
    $booking = $_GET['booking'];

    $stmt = $conexion->prepare("SELECT DISTINCT d.numero_factura 
                                 FROM container c 
                                 INNER JOIN dispatch d 
                                 ON c.Number_Commercial_Invoice = d.numero_factura 
                                 AND c.Number_Container = d.notas 
                                 WHERE d.estado = 'Cargado' AND c.Booking_BK = ?");
    $stmt->bind_param("s", $booking);
    $stmt->execute();
    $result = $stmt->get_result();

    $invoices = [];
    while ($row = $result->fetch_assoc()) {
        $invoices[] = $row['numero_factura'];
    }

    header('Content-Type: application/json');
    echo json_encode($invoices);
}
?>
