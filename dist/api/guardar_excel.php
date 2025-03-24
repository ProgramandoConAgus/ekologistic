<?php
session_start();
require_once '../con_db.php';
require_once '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

// Verificar autenticación y método POST (si es necesario)
if (!isset($_SESSION['IdUsuario']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    die('Acceso denegado');
}
$IdUsuario = $_SESSION['IdUsuario'];

$input = json_decode(file_get_contents('php://input'), true);

// Validar datos de entrada
if (empty($input['path']) || empty($input['data']) || empty($input['packingId'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Datos incompletos']);
    exit();
}

try {
    // Verificar permisos en packing_list
    $stmt = $conexion->prepare("SELECT IdUsuario FROM packing_list WHERE IdPackingList = ?");
    $stmt->bind_param("i", $input['packingId']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0 || $result->fetch_assoc()['IdUsuario'] != $IdUsuario) {
        throw new Exception('No tienes permisos para editar este registro');
    }

    // Convertir datos del Excel que ya se han recibido (en $input['data'])
    $rows = $input['data'];
    
    // Validar estructura (por ejemplo, que la cabecera sea la esperada)
    if (count($rows) < 2 || trim($rows[0][0]) !== 'Num OP') {
        throw new Exception("Formato de Excel inválido");
    }

    // Opcional: Actualizar el archivo Excel si se requiere
    // (Aquí se asume que ya se ha guardado el archivo o se usa el contenido enviado)

    // Iniciar transacción
    $conexion->begin_transaction();

    // Eliminar registros existentes para este packing
    $stmtDeleteItems = $conexion->prepare("DELETE FROM items WHERE idContainer IN (SELECT IdContainer FROM (SELECT IdContainer FROM container WHERE idPackingList = ?) AS temp)");
    $stmtDeleteItems->bind_param("i", $input['packingId']);
    $stmtDeleteItems->execute();

    $stmtDeleteContainer = $conexion->prepare("DELETE FROM container WHERE idPackingList = ?");
    $stmtDeleteContainer->bind_param("i", $input['packingId']);
    $stmtDeleteContainer->execute();

    // Tomar la primera fila (o la fila que contenga los datos generales) para insertar en container
    $primerRegistro = $rows[1];
    $num_op = (int)($primerRegistro[0] ?? 0);
    $destinity_pod = $primerRegistro[1] ?? '';
    $incoterm = $primerRegistro[2] ?? '';
    
    // Convertir fechas (dispatch, departure, ETA)
    $dispatchDateVal = null;
    if (!empty($primerRegistro[3])) {
        $dispatchDateObj = DateTime::createFromFormat('d/m/Y', $primerRegistro[3]);
        $dispatchDateVal = $dispatchDateObj ? $dispatchDateObj->format('Y-m-d') : null;
    }
    $departureDateVal = null;
    if (!empty($primerRegistro[4])) {
        $departureDateObj = DateTime::createFromFormat('d/m/Y', $primerRegistro[4]);
        $departureDateVal = $departureDateObj ? $departureDateObj->format('Y-m-d') : null;
    }
    $etaDateVal = null;
    if (!empty($primerRegistro[18])) {
        $etaDateObj = DateTime::createFromFormat('d/m/Y', $primerRegistro[18]);
        $etaDateVal = $etaDateObj ? $etaDateObj->format('Y-m-d') : null;
    }
    $booking_bk = $primerRegistro[5] ?? '';
    $number_container = $primerRegistro[6] ?? '';
    $number_commercial_invoice = $primerRegistro[7] ?? '';

    // Insertar en Container (único registro)
    $stmtContainer = $conexion->prepare("INSERT INTO container (
        idPackingList, num_op, Destinity_POD, Incoterm, Dispatch_Date_Warehouse_EC, 
        Departure_Date_Port_Origin_EC, Booking_BK, Number_Container, Number_Commercial_Invoice, ETA_Date
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmtContainer->bind_param(
        "iissssssss",
        $input['packingId'],
        $num_op,
        $destinity_pod,
        $incoterm,
        $dispatchDateVal,
        $departureDateVal,
        $booking_bk,
        $number_container,
        $number_commercial_invoice,
        $etaDateVal
    );
    if (!$stmtContainer->execute()) {
        throw new Exception('Error al insertar en container: ' . $stmtContainer->error);
    }
    $idContainer = $conexion->insert_id;
    $stmtContainer->close();

    // Preparar inserción en Items para cada fila (a partir de la fila 2, ya que la fila 1 es la cabecera)
    $stmtItems = $conexion->prepare("INSERT INTO items (
        idContainer, Code_Product_EC, Number_Lot, Customer, Number_PO, Description, 
        Packing_Unit, Qty_Box, Weight_Neto_Per_Box_kg, Weight_Bruto_Per_Box_kg, Total_Weight_kg, 
        Price_Box_EC, Total_Price_EC, Price_Box_USA, Total_Price_USA
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $itemsParamTypes = "isssssiiddddddd";

    // Procesar cada fila de detalle
    foreach (array_slice($rows, 1) as $row) {
        // Validar fila vacía
        $isEmpty = true;
        foreach ($row as $cell) {
            if (!empty(trim($cell ?? ''))) {
                $isEmpty = false;
                break;
            }
        }
        if ($isEmpty) continue;
        
        // Extraer datos para items (se omiten los campos que ya se usaron en container)
        $code_product_ec = $row[8] ?? '';
        $number_lot = $row[9] ?? '';
        $customer = $row[10] ?? '';
        $number_po = $row[11] ?? '';
        $description = $row[12] ?? '';
        $packing_unit = $row[13] ?? '';
        $qty_box = (int)($row[14] ?? 0);
        $weight_neto_per_box_kg = (float)($row[15] ?? 0);
        $weight_bruto_per_box_kg = (float)($row[16] ?? 0);
        $total_weight_kg = (float)($row[17] ?? 0);
        $priceBoxEC = (float)($row[19] ?? 0);
        $totalPriceEC = (float)($row[20] ?? 0);
        $priceBoxUSA = (float)($row[21] ?? 0);
        $totalPriceUSA = (float)($row[22] ?? 0);
        
        $stmtItems->bind_param(
            $itemsParamTypes,
            $idContainer,
            $code_product_ec,
            $number_lot,
            $customer,
            $number_po,
            $description,
            $packing_unit,
            $qty_box,
            $weight_neto_per_box_kg,
            $weight_bruto_per_box_kg,
            $total_weight_kg,
            $priceBoxEC,
            $totalPriceEC,
            $priceBoxUSA,
            $totalPriceUSA
        );
        if (!$stmtItems->execute()) {
            throw new Exception('Error al insertar en items: ' . $stmtItems->error);
        }
    }
    $stmtItems->close();
    $conexion->commit();
    
    echo json_encode(['success' => true]);
    
} catch (Exception $e) {
    $conexion->rollback();
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
