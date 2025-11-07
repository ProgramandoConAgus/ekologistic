<?php
require '../../con_db.php';

if (isset($_GET['booking'])) {
    $booking = $_GET['booking'];

    // 1️⃣ Obtener facturas comerciales
    $stmt = $conexion->prepare("
        SELECT DISTINCT i.Number_Commercial_Invoice AS numero_factura
        FROM items i
        JOIN container c ON i.idContainer = c.IdContainer
        WHERE c.Booking_BK = ?
    ");
    $stmt->bind_param("s", $booking);
    $stmt->execute();
    $result = $stmt->get_result();

    $invoices = [];
    while ($row = $result->fetch_assoc()) {
        $invoices[] = $row['numero_factura'];
    }
    $stmt->close();

    // 2️⃣ Obtener total EXW (total_ec)
    $stmt2 = $conexion->prepare("
        SELECT SUM(i.Total_Price_EC) AS total_ec
        FROM items i
        JOIN container c ON i.idContainer = c.IdContainer
        WHERE c.Booking_BK = ?
    ");
    $stmt2->bind_param("s", $booking);
    $stmt2->execute();
    $result2 = $stmt2->get_result();
    $row2 = $result2->fetch_assoc();
    $total_ec = floatval($row2['total_ec'] ?? 0);
    $stmt2->close();

    // 3️⃣ Obtener números de operación
    $stmt3 = $conexion->prepare("
        SELECT DISTINCT c.num_op AS nOp
        FROM container c
        WHERE c.Booking_BK = ?
    ");
    $stmt3->bind_param("s", $booking);
    $stmt3->execute();
    $result3 = $stmt3->get_result();
    $nops = [];
    while ($row3 = $result3->fetch_assoc()) {
        $nops[] = $row3['nOp'];
    }
    $stmt3->close();

    // 4️⃣ Devolver todo en un solo JSON (igual que import)
    header('Content-Type: application/json');
    echo json_encode([
        "invoices" => $invoices,
        "total_ec" => $total_ec,
        "nops"     => $nops
    ]);
}
?>
