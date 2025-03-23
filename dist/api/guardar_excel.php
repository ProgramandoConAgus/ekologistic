<?php
session_start();
require_once '../con_db.php';
require_once '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

header('Content-Type: application/json');

try {
    // Validar autenticación
    if (!isset($_SESSION['IdUsuario'])) {
        throw new Exception('Acceso no autorizado');
    }

    $input = json_decode(file_get_contents('php://input'), true);
    
    // Validar datos de entrada
    if (empty($input['path']) || empty($input['data']) || empty($input['packingId'])) {
        throw new Exception('Datos incompletos');
    }

    // Verificar permisos
    $stmt = $conexion->prepare("SELECT idUsuario FROM packinglist WHERE IdPacking = ?");
    $stmt->bind_param("i", $input['packingId']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0 || $result->fetch_assoc()['idUsuario'] != $_SESSION['IdUsuario']) {
        throw new Exception('No tienes permisos para editar este registro');
    }

    // Filtrar filas vacías
    $filteredData = [];
    foreach ($input['data'] as $row) {
        $hasData = false;
        foreach ($row as $cell) {
            if (!empty(trim($cell ?? ''))) {
                $hasData = true;
                break;
            }
        }
        if ($hasData) $filteredData[] = $row;
    }

    // Actualizar Excel
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    
    foreach ($filteredData as $rowIndex => $row) {
        foreach ($row as $colIndex => $value) {
            $colLetter = Coordinate::stringFromColumnIndex($colIndex + 1);
            $cellCoordinate = $colLetter . ($rowIndex + 1);
            $sheet->setCellValue($cellCoordinate, $value);
        }
    }

    $writer = new Xlsx($spreadsheet);
    $writer->save($input['path']);

    // Actualizar base de datos
    $conexion->begin_transaction();
    
    try {
        // Eliminar registros existentes
        $stmtDelete = $conexion->prepare("DELETE FROM contenedordetalles WHERE idPackingList = ?");
        $stmtDelete->bind_param("i", $input['packingId']);
        $stmtDelete->execute();
        
        // Insertar nuevos registros
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

        foreach ($filteredData as $rowIndex => $row) {
            if ($rowIndex === 0) continue; // Saltar cabecera
            
            // Validación redundante
            $isEmpty = true;
            foreach ($row as $cell) {
                if (!empty(trim($cell ?? ''))) {
                    $isEmpty = false;
                    break;
                }
            }
            if ($isEmpty) continue;

            // Mapeo de datos
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

            // ETA Date
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
                $input['packingId']
            );
            $stmtDetalles->execute();
        }
        
        $stmtDetalles->close();
        $conexion->commit();
        
        echo json_encode(['success' => true]);
        
    } catch (Exception $e) {
        $conexion->rollback();
        throw new Exception('Error al actualizar la base de datos: ' . $e->getMessage());
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}