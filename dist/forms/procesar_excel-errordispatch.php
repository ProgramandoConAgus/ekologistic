<?php
session_start();
require '../con_db.php';
require '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

// Verificar autenticación y método POST
if (!isset($_SESSION['IdUsuario']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    die('Acceso denegado');
}
$IdUsuario = $_SESSION['IdUsuario'];

// Validar existencia del archivo subido
if (!isset($_FILES['excel']) || $_FILES['excel']['error'] !== UPLOAD_ERR_OK) {
    $_SESSION['error'] = "Error en la subida del archivo (Código: " . $_FILES['excel']['error'] . ")";
    header("Location: importarpk.php");
    exit();
}

// Validar tipo de archivo
$allowedTypes = [
    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    'application/vnd.ms-excel'
];
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mimeType = finfo_file($finfo, $_FILES['excel']['tmp_name']);
finfo_close($finfo);

if (!in_array($mimeType, $allowedTypes)) {
    $_SESSION['error'] = "Solo se permiten archivos Excel (.xlsx)";
    header("Location: importarpk.php");
    exit();
}

// Configurar directorio de subida
$uploadDir = '../uploads/packinglists/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

// Mover archivo
$fileName = uniqid() . '_' . basename($_FILES['excel']['name']);
$targetPath = $uploadDir . $fileName;
if (!move_uploaded_file($_FILES['excel']['tmp_name'], $targetPath)) {
    $_SESSION['error'] = "Error al guardar el archivo";
    header("Location: importarpk.php");
    exit();
}

try {
    // Procesar Excel
    $spreadsheet = IOFactory::load($targetPath);

    $worksheet = $spreadsheet->getActiveSheet();
    if (!$worksheet) {
        throw new Exception("No se encontró ninguna hoja activa en el archivo Excel");
    }


    // Convertir la hoja en un array 
    $rows = $worksheet->toArray();
    // Verificar al menos encabezado + 1 fila
    if (count($rows) < 2) {
        throw new Exception("El archivo no contiene datos suficientes");
    }

    $primerRegistro = $rows[1];

    // ---------------------------
    // 1) Insertar registro en Packing_List
    // ---------------------------
    $numero_packing_list_pl = $primerRegistro[0] ?? '';
    $fechaSubida = date('Y-m-d H:i:s');
    $status = 'pendiente';
    $stmtPL = $conexion->prepare("INSERT INTO packing_list (IdPackingList,IdUsuario,Date_Created,path_file,status) VALUES (?, ?, ?, ?, ?)");
    $stmtPL->bind_param("iisss", $numero_packing_list_pl, $IdUsuario, $fechaSubida, $targetPath, $status);
    $stmtPL->execute();
    $idPackingList = $conexion->insert_id;
    $stmtPL->close();
    
  var_dump($primerRegistro);
exit();


    // ---------------------------
    // 2) Insertar en Container (sin Num OP, índices desplazados)
    // ---------------------------
    $num_dae                  = $primerRegistro[1] ?? '';
    $destiny_pod              = $primerRegistro[2] ?? '';
    $forwarder                = $primerRegistro[3] ?? '';
    $shipping_line            = $primerRegistro[4] ?? '';
    $incoterm                 = $primerRegistro[5] ?? '';

    // Fechas: Dispatch (col 6), Departure (col 7), ETA (col 21)
    $dispatchDateVal          = convertirFecha($primerRegistro[6]);
    $departurePortOriginEC    = $primerRegistro[7] ?? '';
    $etaDateVal               = convertirFecha($primerRegistro[21]);

    $booking_bl               = $primerRegistro[8]  ?? '';
    $number_container         = $primerRegistro[9]  ?? '';
    $number_commercial_invoice= $primerRegistro[10] ?? '';

    $stmtContainer = $conexion->prepare("INSERT INTO container (
            idPackingList,
            num_dae,
            Destinity_POD,
            Forwarder,
            Shipping_Line,
            Incoterm,
            Dispatch_Date_Warehouse_EC,
            Departure_Date_Port_Origin_EC,
            Booking_BK,
            Number_Container,
            Number_Commercial_Invoice,
            ETA_Date
        ) VALUES ( ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    if (!$stmtContainer) {
        throw new Exception("Error al preparar statement Container: " . $conexion->error);
    }
    $stmtContainer->bind_param(
        "isssssssssss",
        $numero_packing_list_pl,
        $num_dae,
        $destiny_pod,
        $forwarder,
        $shipping_line,
        $incoterm,
        $dispatchDateVal,
        $departurePortOriginEC,
        $booking_bl,
        $number_container,
        $number_commercial_invoice,
        $etaDateVal
    );
    $stmtContainer->execute();
    $idContainer = $conexion->insert_id;
    $stmtContainer->close();

    // ---------------------------
    // 3) Insertar ÍTEMS (líneas desde fila 1 en adelante)
    // ---------------------------
    $stmtItems = $conexion->prepare("INSERT INTO items (
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
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    if (!$stmtItems) {
        throw new Exception("Error al preparar statement Items: " . $conexion->error);
    }
    $itemsParamTypes = "issssssiidddddd";

    foreach (array_slice($rows, 1) as $rowData) {
        // Omitir filas vacías
        $isEmpty = true;
        foreach ($rowData as $cell) {
            if (trim((string)$cell) !== '' && trim((string)$cell) !== '.') {
                $isEmpty = false;
                break;
            }
        }
        if ($isEmpty) continue;

        // Índices desplazados: a partir de col 11 en adelante para producto
        $code_product_ec         = $rowData[11] ?? '';
        $number_lot              = $rowData[12] ?? '';
        $customer                = $rowData[13] ?? '';
        $number_po               = $rowData[14] ?? '';
        $description             = $rowData[15] ?? '';
        $packing_unit            = $rowData[16] ?? '';
        $qty_box                 = (int)($rowData[17] ?? 0);
        $weight_neto_per_box_kg  = (float)($rowData[18] ?? 0);
        $weight_bruto_per_box_kg = (float)($rowData[19] ?? 0);
        $total_weight_kg         = (float)($rowData[20] ?? 0);
        $priceBoxEC              = (float)($rowData[22] ?? 0);
        $totalPriceEC            = (float)($rowData[23] ?? 0);
        $priceBoxUSA             = (float)($rowData[24] ?? 0);
        $totalPriceUSA           = (float)($rowData[25] ?? 0);

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
        $stmtItems->execute();
    }
    $stmtItems->close();

    $_SESSION['mensaje'] = "Archivo procesado correctamente";
    header("Location: importarpk.php");
    exit();

} catch (Exception $e) {
    @unlink($targetPath);
    echo "Error al procesar: " . $e->getMessage();
    exit();
}

// Función de ayuda para convertir fecha Excel -> yyyy-mm-dd
function convertirFecha($valorCelda)
{
    if (!empty($valorCelda)) {
        $dateObj = DateTime::createFromFormat('d/m/Y', $valorCelda);
        if ($dateObj) {
            return $dateObj->format('Y-m-d');
        }
    }
    return null;
}
?>
