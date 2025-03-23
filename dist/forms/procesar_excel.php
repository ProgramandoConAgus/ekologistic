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
    $numeroBooking = $primerRegistro[5] ?? '';
    $numeroContenedor = $primerRegistro[6] ?? '';

    // Insertar en contenedor
    $stmtContenedor = $conexion->prepare("INSERT INTO contenedor (numeroContenedor, numeroBooking, fecha, idUsuario, idPackingList) VALUES (?, ?, ?, ?, ?)");
    $fechaContenedor = date('Y-m-d H:i:s');
    $stmtContenedor->bind_param("sssii", $numeroContenedor, $numeroBooking, $fechaContenedor, $IdUsuario, $idPackingList);
    $stmtContenedor->execute();
    $idContenedor = $conexion->insert_id;
    $stmtContenedor->close();

    // Preparar inserción en contenedordetalles
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

    // Procesar filas
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

        // Procesar datos de la fila
        $num_op = (int)($row[0] ?? 0);
        $destinity_pod = $row[1] ?? '';
        $incoterm = $row[2] ?? '';

        // Procesar fechas
        $dispatchDateVal = null;
        if (!empty($row[3])) {
            $dispatchDateObj = DateTime::createFromFormat('d/m/Y', $row[3]);
            $dispatchDateVal = $dispatchDateObj ? $dispatchDateObj->format('Y-m-d') : null;
        }

        $departureDateVal = null;
        if (!empty($row[4])) {
            $departureDateObj = DateTime::createFromFormat('d/m/Y', $row[4]);
            $departureDateVal = $departureDateObj ? $departureDateObj->format('Y-m-d') : null;
        }

        // Resto de campos
        $booking_bk = $row[5] ?? '';
        $number_container = $row[6] ?? '';
        $number_commercial_invoice = $row[7] ?? '';
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

        // Procesar ETA Date
        $etaDateVal = null;
        if (!empty($row[18])) {
            $etaDateObj = DateTime::createFromFormat('d/m/Y', $row[18]);
            $etaDateVal = $etaDateObj ? $etaDateObj->format('Y-m-d') : null;
        }

        // Campos numéricos
        $priceBoxEC = (float)($row[19] ?? 0);
        $totalPriceEC = (float)($row[20] ?? 0);
        $priceBoxUSA = (float)($row[21] ?? 0);
        $totalPriceUSA = (float)($row[22] ?? 0);

        // Ejecutar inserción
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