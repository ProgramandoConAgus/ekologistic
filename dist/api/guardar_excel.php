<?php
session_start();
require_once '../con_db.php';
require_once '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

header('Content-Type: application/json');

// 1) Autenticación y método
if (!isset($_SESSION['IdUsuario']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(403);
    echo json_encode(['success'=>false,'error'=>'Acceso denegado']);
    exit();
}
$IdUsuario = $_SESSION['IdUsuario'];

// 2) Leer input
$input = json_decode(file_get_contents('php://input'), true);
if (empty($input['path']) || empty($input['data']) || empty($input['packingId'])) {
    http_response_code(400);
    echo json_encode(['success'=>false,'error'=>'Datos incompletos']);
    exit();
}
$excelPath = $input['path'];
$rows      = $input['data'];
$packingId = (int)$input['packingId'];

// 3) Verificar permisos
$stmt = $conexion->prepare("SELECT IdUsuario FROM packing_list WHERE IdPackingList = ?");
$stmt->bind_param("i", $packingId);
$stmt->execute();
$res = $stmt->get_result();
if ($res->num_rows === 0 ) {
    http_response_code(403);
    echo json_encode(['success'=>false,'error'=>'No tienes permisos']);
    exit();
}

// 4) Iniciar transacción
$conexion->begin_transaction();

try {
    // 5) Reescribir el Excel
    $spreadsheet = IOFactory::load($excelPath);
    $sheet       = $spreadsheet->getActiveSheet();
    $sheet->fromArray($rows, null, 'A1', true, false);
    $ext         = strtolower(pathinfo($excelPath, PATHINFO_EXTENSION));
    $writerType  = $ext === 'xls' ? 'Xls' : 'Xlsx';
    $writer      = IOFactory::createWriter($spreadsheet, $writerType);
    $writer->save($excelPath);

    // 6) Borrar datos antiguos
    $stmtDelItems = $conexion->prepare("
        DELETE i FROM items i
        JOIN container c ON i.idContainer = c.IdContainer
        WHERE c.idPackingList = ?
    ");
    $stmtDelItems->bind_param("i", $packingId);
    $stmtDelItems->execute();

    $stmtDelCont = $conexion->prepare("DELETE FROM container WHERE idPackingList = ?");
    $stmtDelCont->bind_param("i", $packingId);
    $stmtDelCont->execute();

    // 7) Insertar nuevo container
    $first = $rows[1] ?? [];

    // variables intermedias
    $num_op          = (int)   ($first[1]  ?? 0);
    $num_dae         =         ($first[2]  ?? '');
    $dest_pod        =         ($first[3]  ?? '');
    $forwarder       =         ($first[4]  ?? '');
    $ship_line       =         ($first[5]  ?? '');
    $incoterm        =         ($first[6]  ?? '');
    $dispatch_date   = convertirFecha($first[7]);  // Dispatch
    $departure_date  = convertirFecha($first[8]);  // ¡Convertir aquí también!
    $booking_bl      =         ($first[9]  ?? '');
    $container_num   =         ($first[10] ?? '');
    $invoice_num     =         ($first[11] ?? '');
    $eta_date        = convertirFecha($first[22]); // ETA

    $stmtCont = $conexion->prepare("
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
            Number_Commercial_Invoice,
            ETA_Date
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmtCont->bind_param(
        "iisssssssssss",
        $packingId,
        $num_op,
        $num_dae,
        $dest_pod,
        $forwarder,
        $ship_line,
        $incoterm,
        $dispatch_date,
        $departure_date,
        $booking_bl,
        $container_num,
        $invoice_num,
        $eta_date
    );
    $stmtCont->execute();
    $idContainer = $conexion->insert_id;

    // 8) Insertar items como antes
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
    $types = "isssssiiddddddd";

    foreach (array_slice($rows, 1) as $row) {
        $empty = true;
        foreach ($row as $c) {
            if (trim((string)$c) !== "" && trim((string)$c) !== ".") {
                $empty = false;
                break;
            }
        }
        if ($empty) continue;

        // intermedias para bind
        $c12 = $row[12] ?? '';
        $c13 = $row[13] ?? '';
        $c14 = $row[14] ?? '';
        $c15 = $row[15] ?? '';
        $c16 = $row[16] ?? '';
        $c17 = $row[17] ?? '';
        $c18 = (int)   ($row[18] ?? 0);
        $c19 = (float) ($row[19] ?? 0);
        $c20 = (float) ($row[20] ?? 0);
        $c21 = (float) ($row[21] ?? 0);
        $c23 = (float) ($row[23] ?? 0);
        $c24 = (float) ($row[24] ?? 0);
        $c25 = (float) ($row[25] ?? 0);
        $c26 = (float) ($row[26] ?? 0);

        $stmtItems->bind_param(
            $types,
            $idContainer,
            $c12, $c13, $c14, $c15, $c16, $c17,
            $c18, $c19, $c20, $c21, $c23, $c24, $c25, $c26
        );
        $stmtItems->execute();
    }

    // 9) Commit
    $conexion->commit();
    echo json_encode(['success'=>true]);
    exit();

} catch (Exception $e) {
    $conexion->rollback();
    http_response_code(500);
    echo json_encode(['success'=>false,'error'=>$e->getMessage()]);
    exit();
}

// helper
function convertirFecha($v) {
    $v = trim((string)$v);
    if ($v === "" || $v === ".") return null;
    $d = DateTime::createFromFormat('d/m/Y', $v);
    return $d ? $d->format('Y-m-d') : null;
}
