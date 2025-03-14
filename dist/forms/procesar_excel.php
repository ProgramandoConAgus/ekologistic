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

    // Insertar en packinglist
    $fechaSubida = date('Y-m-d H:i:s');
    $stmt = $conexion->prepare("INSERT INTO packinglist (idUsuario, fecha_subida, excel_path) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $IdUsuario, $fechaSubida, $targetPath);
    $stmt->execute();
    $idPackingList = $conexion->insert_id;
    $stmt->close();

    // Obtener datos comunes para contenedor (primer registro de datos, fila 2)
    $primerRegistro = $rows[1];
    // Corregir el mapeo: índice 5 es Booking_BK y 6 es Number_Container
    $numeroBooking = isset($primerRegistro[5]) ? $primerRegistro[5] : '';
    $numeroContenedor = isset($primerRegistro[6]) ? $primerRegistro[6] : '';

    // Insertar en contenedor
    $stmtContenedor = $conexion->prepare("INSERT INTO contenedor (numeroContenedor, numeroBooking, fecha, idUsuario, idPackingList) VALUES (?, ?, ?, ?, ?)");
    // Si no deseas guardar una fecha en contenedor, puedes ajustar este valor; aquí se guarda el timestamp actual.
    $fechaContenedor = date('Y-m-d H:i:s');
    $stmtContenedor->bind_param("sssii", $numeroContenedor, $numeroBooking, $fechaContenedor, $IdUsuario, $idPackingList);
    $stmtContenedor->execute();
    $idContenedor = $conexion->insert_id;
    $stmtContenedor->close();

    // Preparar la inserción en contenedordetalles
    // Mapeo de columnas según el Excel:
    // 0: Num OP
    // 1: Destinity POD
    // 2: Incoterm
    // 3: Dispatch Date Warehouse EC
    // 4: Departure Date Port Origin EC
    // 5: Booking_BK
    // 6: Number_Container
    // 7: Number_ Commercial Invoice
    // 8: Code_ Product_ EC
    // 9: Number LOT
    // 10: Customer
    // 11: Number_PO
    // 12: Description
    // 13: Packing_ Unit
    // 14: Qty_Box
    // 15: Weight_ Neto_ Per_ box_kg
    // 16: Weight_ Bruto_ Per_ box_kg
    // 17: Total_ Weight_kg
    // 18: ETA Date
    // 19: PRICE BOX EC
    // 20: TOTAL PRICE EC
    // 21: PRICE BOX USA
    // 22: TOTAL PRICE USA

    $stmtDetalles = $conexion->prepare("INSERT INTO contenedordetalles (
         num_op, destinity_pod, incoterm, dispatch_date_warehouse_ec, 
         departure_date_port_origin_ec, booking_bk, number_container, 
         number_commercial_invoice, code_product_ec, number_lot, customer, 
         number_po, description, packing_unit, qty_box, weight_neto_per_box_kg, 
         weight_bruto_per_box_kg, total_weight_kg, eta_date, price_box_ec, 
         total_price_ec, price_box_usa, total_price_usa, idPackingList
     ) VALUES (
         ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
     )");

    // Recorrer cada fila (omitiendo la cabecera)
    foreach (array_slice($rows, 1) as $row) {
        // Asignar y convertir datos de cada columna:
        $num_op = (int)$row[0];
        $destinity_pod = $row[1];
        $incoterm = $row[2];

        // Convertir fechas; se asume formato 'd/m/Y'
        $dispatchDateObj = DateTime::createFromFormat('d/m/Y', $row[3]);
        $dispatchDateVal = $dispatchDateObj ? $dispatchDateObj->format('Y-m-d') : null;

        $departureDateObj = DateTime::createFromFormat('d/m/Y', $row[4]);
        $departureDateVal = $departureDateObj ? $departureDateObj->format('Y-m-d') : null;

        $booking_bk = $row[5];
        $number_container = $row[6];
        $number_commercial_invoice = $row[7];
        $code_product_ec = $row[8];
        $number_lot = $row[9];
        $customer = $row[10];
        $number_po = $row[11];
        $description = $row[12];
        $packing_unit = $row[13];
        $qty_box = (int)$row[14];
        $weight_neto_per_box_kg = (float)$row[15];
        $weight_bruto_per_box_kg = (float)$row[16];
        $total_weight_kg = (float)$row[17];

        // ETA Date (índice 18)
        $etaDateObj = DateTime::createFromFormat('d/m/Y', $row[18]);
        $etaDateVal = $etaDateObj ? $etaDateObj->format('Y-m-d') : null;

        // Precios (índices 19 a 22)
        $priceBoxEC = (float)$row[19];
        $totalPriceEC = (float)$row[20];
        $priceBoxUSA = (float)$row[21];
        $totalPriceUSA = (float)$row[22];

        // Realizar el bind_param con 24 parámetros:
        $stmtDetalles->bind_param(
            "isssssssssssssidddsddddi",
            $num_op,
            $destinity_pod,
            $incoterm,
            $dispatchDateVal,
            $departureDateVal,
            $booking_bk,
            $number_container,
            $number_commercial_invoice,
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
            $etaDateVal,
            $priceBoxEC,
            $totalPriceEC,
            $priceBoxUSA,
            $totalPriceUSA,
            $idPackingList
        );
        $stmtDetalles->execute();
    }
    $stmtDetalles->close();

    $_SESSION['mensaje'] = "Archivo procesado correctamente";
    header("Location: importarpk.php");
    exit();

} catch (Exception $e) {
    @unlink($targetPath);
    echo "Error al procesar: " . $e->getMessage();
    exit();
}
?>
