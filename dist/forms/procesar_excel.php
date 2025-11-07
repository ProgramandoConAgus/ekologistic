<?php
session_start();
require '../con_db.php';
require '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

// 1) Verificar autenticación y POST
if (!isset($_SESSION['IdUsuario']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    die('Acceso denegado');
}
$IdUsuario = $_SESSION['IdUsuario'];

// 2) Validar archivo subido
if (!isset($_FILES['excel']) || $_FILES['excel']['error'] !== UPLOAD_ERR_OK) {
    $_SESSION['error'] = "Error en la subida del archivo (Código: " . $_FILES['excel']['error'] . ")";
    header("Location: importarpk.php");
    exit();
}
$allowed = [
    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    'application/vnd.ms-excel',
];
$finfo    = finfo_open(FILEINFO_MIME_TYPE);
$mimeType = finfo_file($finfo, $_FILES['excel']['tmp_name']);
finfo_close($finfo);
if (!in_array($mimeType, $allowed)) {
    $_SESSION['error'] = "Solo se permiten archivos Excel (.xlsx)";
    header("Location: importarpk.php");
    exit();
}

// 3) Guardar el archivo
$uploadDir  = '../uploads/packinglists/';
if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
$fileName   = uniqid() . '_' . basename($_FILES['excel']['name']);
$targetPath = $uploadDir . $fileName;
if (!move_uploaded_file($_FILES['excel']['tmp_name'], $targetPath)) {
    $_SESSION['error'] = "Error al guardar el archivo";
    header("Location: importarpk.php");
    exit();
}

try {
    // 4) Iniciar transacción
    $conexion->begin_transaction();

    // 5) Leer Excel como array
    $spreadsheet = IOFactory::load($targetPath);
    $sheet       = $spreadsheet->getActiveSheet();
    if (!$sheet) throw new Exception("No se encontró hoja activa");
    $rows = $sheet->toArray();
    if (count($rows) < 2) throw new Exception("El archivo no contiene datos");

    // --------------------------------------------------
    // 6) INSERT en packing_list
    // --------------------------------------------------
    $first = $rows[1];
    $numPL = $first[0];  // columna 0

    $stmtPL = $conexion->prepare("
        INSERT INTO packing_list
            (IdPackingList, IdUsuario, Date_Created, path_file, status)
        VALUES (?, ?, ?, ?, ?)
    ");
    if (!$stmtPL) throw new Exception($conexion->error);
    $now    = date('Y-m-d H:i:s');
    $status = 'pendiente';
    $stmtPL->bind_param("iisss", $numPL, $IdUsuario, $now, $targetPath, $status);
    $stmtPL->execute();
    $stmtPL->close();

    // --------------------------------------------------
    // 7) INSERT en container (ahora con Num OP en columna 1)
    // --------------------------------------------------
    $num_op           = trim((string)$first[1]);        // **columna 1** = Num OP
    $num_dae          = trim((string)$first[2]);        // columna 2
    $destiny_pod      = trim((string)$first[3]);        // columna 3
    $forwarder        = trim((string)$first[4]);        // columna 4
    $shipping_line    = trim((string)$first[5]);        // columna 5
    $incoterm         = trim((string)$first[6]);        // columna 6
    $dispatchDateVal  = convertirFecha($first[7]);      // columna 7
    $rawDeparture     = trim((string)$first[8]);        // columna 8
    $dObj             = DateTime::createFromFormat('d/m/Y', $rawDeparture);
    $departureDateVal = ($dObj && $dObj->format('d/m/Y') === $rawDeparture)
                          ? $dObj->format('Y-m-d')
                          : $dispatchDateVal;
    $booking_bl       = trim((string)$first[9]);        // columna 9
    $number_container = trim((string)$first[10]);       // columna 10
    $etaDateVal       = convertirFecha($first[22]);     // columna 22 (antes 21)

    $stmtC = $conexion->prepare("
        INSERT INTO container (
            idPackingList,
            num_op,
            num_dae,
            Destinity_POD,
            Forwarder,
            Shipping_Line,
            Incoterm,
            Dispatch_Date_Warehouse_EC,
            Departure_Date_Port_Origin_EC,
            Booking_BK,
            Number_Container,
            ETA_Date
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    if (!$stmtC) throw new Exception($conexion->error);
    $stmtC->bind_param(
        "isssssssssss",
        $numPL,
        $num_op,
        $num_dae,
        $destiny_pod,
        $forwarder,
        $shipping_line,
        $incoterm,
        $dispatchDateVal,
        $departureDateVal,
        $booking_bl,
        $number_container,
        $etaDateVal
    );
    $stmtC->execute();
    $idContainer = $conexion->insert_id;
    $stmtC->close();

    // --------------------------------------------------
    // 8) INSERT en items (columnas también desplazadas +1)
    // --------------------------------------------------
    $stmtI = $conexion->prepare("
        INSERT INTO items (
            idContainer,
            Number_Commercial_Invoice,
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
            Price_BOX_USA,
            Total_Price_USA
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    if (!$stmtI) throw new Exception($conexion->error);
    // 1 entero + 7 strings + 1 entero + 7 doubles = 16 parámetros
    $types = "i" . str_repeat("s", 7) . "i" . str_repeat("d", 7);

    foreach (array_slice($rows, 1) as $r) {
        // Saltar filas vacías
        $empty = true;
        foreach ($r as $c) {
            if (trim((string)$c) !== '' && trim((string)$c) !== '.') {
                $empty = false;
                break;
            }
        }
        if ($empty) continue;

        // Mapear columnas *desplazadas +1*
        $itemInvoice             = trim((string)($r[11] ?? ''));  // col 11
        $code_product_ec         = trim((string)($r[12] ?? ''));  // col 12
        $number_lot              = trim((string)($r[13] ?? ''));  // col 13
        $customer                = trim((string)($r[14] ?? ''));  // col 14
        $number_po               = trim((string)($r[15] ?? ''));  // col 15
        $description             = trim((string)($r[16] ?? ''));  // col 16
        $packing_unit            = trim((string)($r[17] ?? ''));  // col 17
        $qty_box                 = (int)  ($r[18] ?? 0);         // col 18
        $weight_neto_per_box_kg  = (float)($r[19] ?? 0);         // col 19
        $weight_bruto_per_box_kg = (float)($r[20] ?? 0);         // col 20
        $total_weight_kg         = (float)($r[21] ?? 0);         // col 21
        $priceBoxEC              = (float)($r[23] ?? 0);         // col 23
        $totalPriceEC            = (float)($r[24] ?? 0);         // col 24
        $priceBoxUSA             = (float)($r[25] ?? 0);         // col 25
        $totalPriceUSA           = (float)($r[26] ?? 0);         // col 26

        $stmtI->bind_param(
            $types,
            $idContainer,
            $itemInvoice,
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
        $stmtI->execute();
        if ($stmtI->errno) {
            throw new Exception("Error ítem: " . $stmtI->error);
        }
    }
    $stmtI->close();

    // 9) Commit y éxito
    $conexion->commit();
    $_SESSION['mensaje'] = "Archivo procesado correctamente";
    header("Location: ../dashboard/panel-packinglist.php");

    exit;

} catch (Exception $e) {
    // Rollback y limpiar
    $conexion->rollback();
    @unlink($targetPath);
    echo "Error al procesar: " . $e->getMessage();
    exit;
}

/**
 * Convierte 'd/m/Y' a 'Y-m-d'. Si falla, devuelve cadena vacía.
 */
function convertirFecha($val) {
    if (!empty($val)) {
        $d = DateTime::createFromFormat('d/m/Y', $val);
        if ($d && $d->format('d/m/Y') === $val) {
            return $d->format('Y-m-d');
        }
    }
    return '';
}
    