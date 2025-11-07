<?php
header('Content-Type: application/json');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include('../../con_db.php'); // ✅ Usa tu conexión ya existente

try {
    // Leer el cuerpo JSON
    $input = json_decode(file_get_contents('php://input'), true);

    if (!isset($input['codigo_despacho']) || empty(trim($input['codigo_despacho']))) {
        echo json_encode(['success' => false, 'error' => 'No se recibió código de despacho.']);
        exit;
    }

    $codigo = trim($input['codigo_despacho']);

    // Consulta SQL principal
    $sql = "
        SELECT
            d.id,
            c.num_op AS NUM_OP,
            c.Number_Container,
            c.Booking_BK,
            d.codigo_despacho,
            d.fecha_entrada AS Entry_Date,
            d.recibo_almacen AS Receive,
            d.numero_lote AS Lot_Number,
            d.numero_factura AS Number_Commercial_Invoice,
            d.numero_parte AS Code_Product_EC,
            d.descripcion AS Description_Dispatch,
            d.modelo AS Modelo_Dispatch,

            (SELECT i.Description
             FROM items i
             WHERE i.Number_Commercial_Invoice = d.numero_factura
               AND i.Code_Product_EC = d.numero_parte
             ORDER BY i.Number_PO
             LIMIT 1) AS Description_Item,

            (SELECT i.Number_PO
             FROM items i
             WHERE i.Number_Commercial_Invoice = d.numero_factura
               AND i.Code_Product_EC = d.numero_parte
             ORDER BY i.Number_PO
             LIMIT 1) AS Number_PO,

            (SELECT i.Packing_Unit
             FROM items i
             WHERE i.Number_Commercial_Invoice = d.numero_factura
               AND i.Code_Product_EC = d.numero_parte) AS Qty_Item_Packing,

            d.palets AS palets,
            d.cantidad AS cantidad,
            (d.palets * d.cantidad) AS Total_Despachado,
            d.valor_unitario AS Unit_Value,
            (d.valor_unitario * d.cantidad) AS Value,
            d.unidad AS Unit,
            d.longitud_in AS Length_in,
            d.ancho_in AS Broad_in,
            d.altura_in AS Height_in,
            d.peso_lb AS Weight_lb,
            d.estado AS Status
        FROM palets_cargados d
        LEFT JOIN container c
            ON c.Number_Container = d.notas
        WHERE d.codigo_despacho = ?
    ";

    // Usar conexión mysqli de con_db.php
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param('s', $codigo);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    echo json_encode(['success' => true, 'data' => $result]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Error: ' . $e->getMessage()]);
}
