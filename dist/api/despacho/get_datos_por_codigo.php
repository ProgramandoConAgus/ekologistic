<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json');

// Conexión PDO
$host = 'localhost';
$db   = 'u981249563_ekologisticaaa';
$user = 'u981249563_agustinapontee';
$pass = 'Pca@70071';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);

    $input = json_decode(file_get_contents('php://input'), true);

    if (!isset($input['codigo_despacho']) || empty($input['codigo_despacho'])) {
        echo json_encode(['success' => false, 'error' => 'No se recibió código de despacho']);
        exit;
    }

    $codigo = $input['codigo_despacho'];

    $sql = "SELECT
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

      (SELECT SUM(i.Qty_Box)
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
      NULL AS valor_unitario_restante,
      NULL AS valor_restante,
      NULL AS unidad_restante,
      NULL AS longitud_in_restante,
      NULL AS ancho_in_restante,
      NULL AS altura_in_restante,
      NULL AS peso_lb_restante,
      d.estado AS Status

    FROM palets_cargados d
    LEFT JOIN container c
      ON c.Number_Container = d.notas

    WHERE d.codigo_despacho = :codigo
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute(['codigo' => $codigo]);
    $result = $stmt->fetchAll();

    echo json_encode(['success' => true, 'data' => $result]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Error PDO: ' . $e->getMessage()]);
}
