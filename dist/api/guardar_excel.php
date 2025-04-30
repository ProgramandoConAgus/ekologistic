<?php
session_start();
require_once '../con_db.php';
require_once '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

// Verificar autenticación y método POST
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

    // Los datos ya vienen parseados en JSON: matriz de filas
    $rows = $input['data'];

    // Validar que haya al menos encabezado + 1 fila
    if (count($rows) < 2) {
        throw new Exception("No hay datos para procesar");
    }

    // Iniciar transacción
    $conexion->begin_transaction();

    // Borrar detalles previos y container
    $stmtDeleteItems = $conexion->prepare("
        DELETE FROM items 
        WHERE idContainer IN (
            SELECT IdContainer 
            FROM (SELECT IdContainer FROM container WHERE idPackingList = ?) AS tmp
        )
    ");
    $stmtDeleteItems->bind_param("i", $input['packingId']);
    $stmtDeleteItems->execute();

    $stmtDeleteContainer = $conexion->prepare("DELETE FROM container WHERE idPackingList = ?");
    $stmtDeleteContainer->bind_param("i", $input['packingId']);
    $stmtDeleteContainer->execute();

    // Tomar la primera fila de datos (rows[1])
    $primerRegistro = $rows[1];

    // Mapear según el orden real del Excel:
    // 0=>numero_pl, 1=>Num OP, 2=>Num DAE, 3=>Destiny POD, 4=>Forwarder, 5=>Shipping Line,
    // 6=>Incoterm, 7=>Dispatch Date, 8=>Departure Port Origin, 9=>Booking BL,
    //10=>Number Container, 11=>Number Commercial Invoice, 22=>ETA Date
    $num_op               = (int)($primerRegistro[1]  ?? 0);
    $destinity_pod        = $primerRegistro[3]       ?? '';
    $incoterm             = $primerRegistro[6]       ?? '';
    $dispatchDateVal      = convertirFecha($primerRegistro[7]);
    $departurePortOrigin  = $primerRegistro[8]       ?? '';
    $booking_bk           = $primerRegistro[9]       ?? '';
    $number_container     = $primerRegistro[10]      ?? '';
    $number_commercial_invoice = $primerRegistro[11] ?? '';
    $etaDateVal           = convertirFecha($primerRegistro[22]);

    // Insertar nuevo container
    $stmtContainer = $conexion->prepare("
        INSERT INTO container (
            idPackingList,
            num_op,
            Destinity_POD,
            Incoterm,
            Dispatch_Date_Warehouse_EC,
            Departure_Date_Port_Origin_EC,
            Booking_BK,
            Number_Container,
            Number_Commercial_Invoice,
            ETA_Date
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmtContainer->bind_param(
        "iissssssss",
        $input['packingId'],
        $num_op,
        $destinity_pod,
        $incoterm,
        $dispatchDateVal,
        $departurePortOrigin,
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

    // Preparar inserción de items
    $stmtItems = $conexion->prepare("
        INSERT INTO items (
            idContainer,
            Code_Product_EC,
            Number_Lot,
            Customer,
            Number_PO,
            Description,
            Packing_Unit,
            Qty_Box,
            Weight_Neto_Per_Box_kg,
            Weight_Bruto_Per_Box_kg,
            Total_Weight_kg,
            Price_Box_EC,
            Total_Price_EC,
            Price_Box_USA,
            Total_Price_USA
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $itemsParamTypes = "isssssiiddddddd";

    // Recorrer cada fila de detalle (desde la 2ª fila de Excel)
    foreach (array_slice($rows, 1) as $rowData) {
        // omitir filas vacías o con sólo "."
        $isEmpty = true;
        foreach ($rowData as $cell) {
            $v = trim((string)$cell);
            if ($v !== "" && $v !== ".") {
                $isEmpty = false;
                break;
            }
        }
        if ($isEmpty) {
            continue;
        }

        // Extraer campos de item (índices 12–26 según Excel)
        $code_product_ec           = $rowData[12] ?? '';
        $number_lot                = $rowData[13] ?? '';
        $customer                  = $rowData[14] ?? '';
        $number_po                 = $rowData[15] ?? '';
        $description               = $rowData[16] ?? '';
        $packing_unit              = $rowData[17] ?? '';
        $qty_box                   = (int)   ($rowData[18] ?? 0);
        $weight_neto_per_box_kg    = (float) ($rowData[19] ?? 0);
        $weight_bruto_per_box_kg   = (float) ($rowData[20] ?? 0);
        $total_weight_kg           = (float) ($rowData[21] ?? 0);
        $priceBoxEC                = (float) ($rowData[23] ?? 0);
        $totalPriceEC              = (float) ($rowData[24] ?? 0);
        $priceBoxUSA               = (float) ($rowData[25] ?? 0);
        $totalPriceUSA             = (float) ($rowData[26] ?? 0);

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

/**
 * Convierte un valor dd/mm/YYYY a YYYY-mm-dd.
 * Devuelve null si el valor está vacío, es "." o no parsea.
 */
function convertirFecha($valorCelda)
{
    $v = trim((string)$valorCelda);
    if ($v === "" || $v === ".") {
        return null;
    }
    $d = DateTime::createFromFormat('d/m/Y', $v);
    return $d ? $d->format('Y-m-d') : null;
}
?>
