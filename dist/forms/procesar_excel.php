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

// Validar existencia del archivo
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
    $worksheet = $spreadsheet->getSheetByName('packing_list');
    if (!$worksheet) {
        throw new Exception("Hoja 'packing_list' no encontrada");
    }
    $rows = $worksheet->toArray();

    // Validar estructura del Excel (cabecera)
    if (count($rows) < 2 || trim($rows[0][0]) !== 'Num OP') {
        throw new Exception("Formato de Excel inválido");
    }

    // Insertar en Packing_List
    $fechaSubida = date('Y-m-d H:i:s');
    // Se asume un status inicial, por ejemplo, 'pendiente'
    $status = 'pendiente';
    $stmtPL = $conexion->prepare("INSERT INTO Packing_List (IdUsuario, Date_Created, path_file, status) VALUES (?, ?, ?, ?)");
    $stmtPL->bind_param("isss", $IdUsuario, $fechaSubida, $targetPath, $status);
    $stmtPL->execute();
    $idPackingList = $conexion->insert_id;
    $stmtPL->close();

    // Obtener datos comunes para Container (usando el primer registro, fila 2)
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

    // Insertar en Container
    $stmtContainer = $conexion->prepare("INSERT INTO Container (
        IdPackingList, num_op, Booking_BK, Number_Container, Number_Commercial_Invoice, 
        Destinity_POD, Incoterm, Dispatch_Date_Warehouse_EC, Departure_Date_Port_Origin_EC, ETA_Date
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmtContainer->bind_param(
        "iissssssss",
        $idPackingList,
        $num_op,
        $booking_bk,
        $number_container,
        $number_commercial_invoice,
        $destinity_pod,
        $incoterm,
        $dispatchDateVal,
        $departureDateVal,
        $etaDateVal
    );
    $stmtContainer->execute();
    $idContainer = $conexion->insert_id;
    $stmtContainer->close();

    // Preparar inserción en Items (por cada línea del Excel)
    $stmtItems = $conexion->prepare("INSERT INTO Items (
        IdContainer, Code_Product_EC, Number_Lot, Customer, Number_PO, Description, 
        Packing_Unit, Qty_Box, Weight_Neto_Per_Box_kg, Weight_Bruto_Per_Box_kg, Total_Weight_kg, 
        Price_Box_EC, Total_Price_EC, Price_Box_USA, Total_Price_USA
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    // La cadena de tipos es: i, s, s, s, s, s, i, i, d, d, d, d, d, d, d
    $itemsParamTypes = "isssssiiddddddd";

    // Procesar filas (a partir de la fila 2, ya que la fila 1 es la cabecera)
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

        // Cada fila representa un ítem, se extraen los datos:
        // (Se ignoran campos que ya se usaron en Container)
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
        // Los campos de precio (si vienen en el Excel)
        $priceBoxEC = (float)($row[19] ?? 0);
        $totalPriceEC = (float)($row[20] ?? 0);
        $priceBoxUSA = (float)($row[21] ?? 0);
        $totalPriceUSA = (float)($row[22] ?? 0);

        // Ejecutar inserción en Items
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
?>
