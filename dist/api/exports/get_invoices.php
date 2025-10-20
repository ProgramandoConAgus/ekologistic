<?php
require '../../con_db.php';

if (isset($_GET['booking'])) {
    $booking = $_GET['booking'];

  $stmt = $conexion->prepare("SELECT DISTINCT i.Number_Commercial_Invoice AS numero_factura
FROM items i
JOIN container c ON i.idContainer = c.IdContainer 
  AND c.Booking_BK = ? ");
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
