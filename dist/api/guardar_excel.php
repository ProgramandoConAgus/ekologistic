<?php
session_start();
require_once '../con_db.php';
require_once '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;

// Verificar autenticación y método POST
if (!isset($_SESSION['IdUsuario']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    die('Acceso denegado');
}
$IdUsuario = $_SESSION['IdUsuario'];

// Decodificar entrada JSON
$input = json_decode(file_get_contents('php://input'), true);

// Validar datos de entrada
if (empty($input['path']) || empty($input['data']) || empty($input['packingId'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Datos incompletos']);
    exit();
}

try {
    // Verificar permisos en Packing_List
    $stmt = $conexion->prepare("SELECT IdUsuario FROM Packing_List WHERE IdPackingList = ?");
    // Si en el primer código el idPackingList se maneja como string (por ser un número con letra o similar), se usa "s"
    $stmt->bind_param("s", $input['packingId']);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0 || $result->fetch_assoc()['IdUsuario'] != $IdUsuario) {
        throw new Exception('No tienes permisos para editar este registro');
    }
    $stmt->close();

    // Convertir los datos del Excel recibidos en $input['data']
    $rows = $input['data'];

    // Validar la estructura del Excel
    // Se espera que la primera celda de la cabecera contenga "numero_packing_list"
    if (count($rows) < 2 || trim($rows[0][0]) !== 'numero_packing_list') {
        throw new Exception("Formato de Excel inválido");
    }

    // Iniciar transacción
    $conexion->begin_transaction();

    // Eliminar registros existentes para este packing (primero los Items y luego los Container)
    $stmtDeleteItems = $conexion->prepare("DELETE FROM Items WHERE idContainer IN (SELECT IdContainer FROM (SELECT IdContainer FROM Container WHERE idPackingList = ?) AS temp)");
    $stmtDeleteItems->bind_param("s", $input['packingId']);
    $stmtDeleteItems->execute();

    $stmtDeleteContainer = $conexion->prepare("DELETE FROM Container WHERE idPackingList = ?");
    $stmtDeleteContainer->bind_param("s", $input['packingId']);
    $stmtDeleteContainer->execute();

    // Se toma la primera fila de datos (después de la cabecera) para insertar en Container
    // La nueva estructura en el Excel se asume:
    // índice 0: numero_packing_list (ya existe en Packing_List)
    // índice 1: Num DAE
    // índice 2: Destiny POD
    // índice 3: Forwarder
    // índice 4: Shipping Line
    // índice 5: Incoterm
    // índice 6: Dispatch Date Warehouse EC (formato d/m/Y o número de Excel)
    // índice 7: Departure Date Port Origin EC (formato d/m/Y o número de Excel)
    // índice 8: Booking BL
    // índice 9: Number Container
    // índice 10: Number Commercial Invoice
    // ...
    // índice 21: ETA Date (formato d/m/Y o número de Excel)
    $primerRegistro = $rows[1];
    $num_dae         = $primerRegistro[1] ?? '';
    $destiny_pod     = $primerRegistro[2] ?? '';
    $forwarder       = $primerRegistro[3] ?? '';
    $shipping_line   = $primerRegistro[4] ?? '';
    $incoterm        = $primerRegistro[5] ?? '';

    // Conversión de la fecha Dispatch Date Warehouse EC (índice 6)
    if (!empty($primerRegistro[6])) {
        if (is_numeric($primerRegistro[6])) {
            $dispatchObj = Date::excelToDateTimeObject($primerRegistro[6]);
        } else {
            $dispatchObj = DateTime::createFromFormat('d/m/Y', trim($primerRegistro[6]));
        }
        if (!$dispatchObj) {
            throw new Exception("Error en la conversión de la fecha Dispatch Date Warehouse EC: valor inválido '{$primerRegistro[6]}'");
        }
        $dispatchDateVal = $dispatchObj->format('Y-m-d');
    } else {
        throw new Exception("El campo Dispatch Date Warehouse EC no puede estar vacío");
    }

    // Conversión de la fecha Departure Date Port Origin EC (índice 7)
    if (!empty($primerRegistro[7])) {
        if (is_numeric($primerRegistro[7])) {
            $departureObj = Date::excelToDateTimeObject($primerRegistro[7]);
        } else {
            $departureObj = DateTime::createFromFormat('d/m/Y', trim($primerRegistro[7]));
        }
        if (!$departureObj) {
            throw new Exception("Error en la conversión de la fecha Departure Date Port Origin EC: valor inválido '{$primerRegistro[7]}'");
        }
        $departureDateVal = $departureObj->format('Y-m-d');
    } else {
        throw new Exception("El campo Departure Date Port Origin EC no puede estar vacío");
    }

    $booking_bk               = $primerRegistro[8] ?? '';
    $number_container         = $primerRegistro[9] ?? '';
    $number_commercial_invoice = $primerRegistro[10] ?? '';

    // Conversión de la fecha ETA Date (índice 21)
    if (!empty($primerRegistro[21])) {
        if (is_numeric($primerRegistro[21])) {
            $etaObj = Date::excelToDateTimeObject($primerRegistro[21]);
        } else {
            $etaObj = DateTime::createFromFormat('d/m/Y', trim($primerRegistro[21]));
        }
        if (!$etaObj) {
            throw new Exception("Error en la conversión de la fecha ETA Date: valor inválido '{$primerRegistro[21]}'");
        }
        $etaDateVal = $etaObj->format('Y-m-d');
    } else {
        throw new Exception("El campo ETA Date no puede estar vacío");
    }

    // Insertar registro en Container
    $stmtContainer = $conexion->prepare("INSERT INTO Container (
        idPackingList, num_dae, Destinity_POD, Forwarder, Shipping_Line, Incoterm, 
        Dispatch_Date_Warehouse_EC, Departure_Date_Port_Origin_EC, Booking_BK, Number_Container, 
        Number_Commercial_Invoice, ETA_Date
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmtContainer->bind_param(
        "isssssssssss",
        $input['packingId'],
        $num_dae,
        $destiny_pod,
        $forwarder,
        $shipping_line,
        $incoterm,
        $dispatchDateVal,
        $departureDateVal,
        $booking_bk,
        $number_container,
        $number_commercial_invoice,
        $etaDateVal
    );
    if (!$stmtContainer->execute()) {
        throw new Exception('Error al insertar en Container: ' . $stmtContainer->error);
    }
    $idContainer = $conexion->insert_id;
    $stmtContainer->close();

    // Preparar la sentencia para insertar en Items
    // Se asume la siguiente correspondencia para los índices en el Excel:
    // índice 11: Code Product EC
    // índice 12: Number LOT
    // índice 13: Customer
    // índice 14: Number PO
    // índice 15: Description
    // índice 16: Packing Unit
    // índice 17: Qty Box
    // índice 18: Weight Neto Per Box kg
    // índice 19: Weight Bruto Per Box kg
    // índice 20: Total Weight kg
    // índice 22: Price BOX EC
    // índice 23: Total Price EC
    // índice 24: Price BOX USA
    // índice 25: Total Price BOX USA
    $stmtItems = $conexion->prepare("INSERT INTO Items (
        idContainer, Code_Product_EC, Number_Lot, Customer, Number_PO, Description, 
        Packing_Unit, Qty_Box, Weight_Neto_Per_Box_kg, Weight_Bruto_Per_Box_kg, Total_Weight_kg, 
        Price_Box_EC, Total_Price_EC, Price_Box_USA, Total_Price_USA
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $itemsParamTypes = "isssssiiddddddd";

    // Recorrer las filas de detalle (a partir de la fila 2 de datos; la fila 0 es la cabecera y la fila 1 ya se usó para container)
    foreach (array_slice($rows, 1) as $row) {
        // Validar que la fila no esté vacía
        $isEmpty = true;
        foreach ($row as $cell) {
            if (trim((string)$cell) !== '' && trim((string)$cell) !== '.') {
                $isEmpty = false;
                break;
            }
        }
        if ($isEmpty) continue;
        
        // Extraer datos para Items usando el nuevo orden
        $code_product_ec           = $row[11] ?? '';
        $number_lot                = $row[12] ?? '';
        $customer                  = $row[13] ?? '';
        $number_po                = $row[14] ?? '';
        $description              = $row[15] ?? '';
        $packing_unit             = (int)($row[16] ?? 0);
        $qty_box                  = (int)($row[17] ?? 0);
        $weight_neto_per_box_kg   = (float)str_replace(',', '.', $row[18] ?? 0);
        $weight_bruto_per_box_kg  = (float)str_replace(',', '.', $row[19] ?? 0);
        $total_weight_kg          = (float)str_replace(',', '.', $row[20] ?? 0);
        $priceBoxEC               = (float)str_replace(',', '.', $row[22] ?? 0);
        $totalPriceEC             = (float)str_replace(',', '.', $row[23] ?? 0);
        $priceBoxUSA              = (float)str_replace(',', '.', $row[24] ?? 0);
        $totalPriceUSA            = (float)str_replace(',', '.', $row[25] ?? 0);

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
            throw new Exception('Error al insertar en Items: ' . $stmtItems->error);
        }
    }
    $stmtItems->close();

    // Confirmar transacción
    $conexion->commit();

    echo json_encode(['success' => true]);

} catch (Exception $e) {
    $conexion->rollback();
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
