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
    $worksheet = $spreadsheet->getSheetByName('Sheet1');
    if (!$worksheet) {
        throw new Exception("Hoja 'packing_list' no encontrada");
    }
    $rows = $worksheet->toArray();

    // Validar estructura del Excel (cabecera)
    if (count($rows) < 2 ) {
        throw new Exception("Formato de Excel inválido");
    }

    // Extraer el valor de "numero_ packing List_pl" para usarlo como IdPackingList
    // Se asume que en cada fila este campo es el mismo si se trata de un mismo packing list.
    $packing_number = $rows[1][0] ?? '';
    if(empty($packing_number)){
        throw new Exception("El valor de 'numero_ packing List_pl' está vacío.");
    }

    // Insertar en Packing_List: se inserta manualmente el IdPackingList obtenido de la columna packing_number
    $fechaSubida = date('Y-m-d H:i:s');
    $status = 'pendiente';
    $stmtPL = $conexion->prepare("INSERT INTO Packing_List (IdPackingList, IdUsuario, Date_Created, path_file, status) VALUES (?, ?, ?, ?, ?)");
    $stmtPL->bind_param("sisss", $packing_number, $IdUsuario, $fechaSubida, $targetPath, $status);
    $stmtPL->execute();
    // Como se asigna manualmente el IdPackingList se utiliza el mismo valor para las relaciones en Container
    $idPackingList = $packing_number;
    $stmtPL->close();

    // Preparar sentencia para insertar en Container
    // Se insertan los siguientes campos:
    // 0: numero_ packing List_pl        --> ya se usó para IdPackingList
    // 1: Num DAE                        --> num_dae
    // 2: Destiny POD                    --> destiny_pod
    // 3: Forwarder                      --> forwarder
    // 4: Shipping Line                  --> shipping_line
    // 5: Incoterm                       --> incoterm
    // 6: Dispatch Date Warenhouse EC    --> dispatch_date_warehouse_ec (formato d/m/Y)
    // 7: Departure Port Origin EC       --> departure_port_origin_ec (formato d/m/Y)
    // 8: Booking BL                     --> booking_bk
    // 9: Number Container               --> number_container
    // 10: Number Commercial Inovice      --> number_commercial_invoice
    // 21: ETA Date                      --> eta_date (formato d/m/Y)
    $stmtContainer = $conexion->prepare("INSERT INTO Container (
        idPackingList, num_dae, Destinity_POD, Forwarder, Shipping_Line, Incoterm, 
        Dispatch_Date_Warehouse_EC, Departure_Date_Port_Origin_EC, Booking_BK, Number_Container, 
        Number_Commercial_Invoice, ETA_Date
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    // Preparar sentencia para insertar en Items
    // Se insertan los siguientes campos:
    // 11: Code Product EC           --> code_product_ec
    // 12: Number LOT                --> number_lot
    // 13: Customer                  --> customer
    // 14: Number PO                 --> number_po
    // 15: Description               --> description
    // 16: Packing Unit              --> packing_unit
    // 17: Qty Box                   --> qty_box
    // 18: Weight Neto Per box kg    --> weight_neto_per_box_kg
    // 19: Weight Bruto Per box kg   --> weight_bruto_per_box_kg
    // 20: Total Weight kg           --> total_weight_kg
    // 22: Price BOX EC              --> price_box_ec
    // 23: Total Price EC            --> total_price_ec
    // 24: Price BOX USA             --> price_box_usa
    // 25: Total Price BOX USA       --> total_price_box_usa
    $stmtItems = $conexion->prepare("INSERT INTO Items (
        idContainer, Code_Product_EC, Number_Lot, Customer, Number_PO, Description, 
        Packing_Unit, Qty_Box, Weight_Neto_Per_Box_kg, Weight_Bruto_Per_Box_kg, Total_Weight_kg, 
        Price_Box_EC, Total_Price_EC, Price_Box_USA, Total_Price_USA
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    // Cadena de tipos para Items: i, s, s, s, s, s, i, i, d, d, d, d, d, d, d
    $itemsParamTypes = "isssssiiddddddd";

    // Recorrer cada fila (a partir de la fila 2, ya que la primera es la cabecera)
    foreach (array_slice($rows, 1) as $row) {
        $isEmpty = true;
        foreach ($row as $cell) {
            $value = trim((string)$cell);
            if ($value !== '' && $value !== '.') {
                $isEmpty = false;
                break;
            }
        }
        if ($isEmpty) {
            continue; // Salta la fila si todas las celdas están vacías o son puntos.
        } else {
            // La fila tiene al menos un valor distinto a una cadena vacía o un punto.
            print_r($row);
            echo "<br>Tiene algo<br>";
        }


        // Extraer datos para Container (índices basados en la cabecera):
        // índice 1: Num DAE
        $num_dae = $row[1] ?? '';
        // índice 2: Destiny POD
        $destiny_pod = $row[2] ?? '';
        // índice 3: Forwarder
        $forwarder = $row[3] ?? '';
        // índice 4: Shipping Line
        $shipping_line = $row[4] ?? '';
        // índice 5: Incoterm
        $incoterm = $row[5] ?? '';
        
        // índice 6: Dispatch Date Warenhouse EC
        $dispatch_date_val = null;
        if (!empty($row[6])) {
            if (is_numeric($row[6])) {
                $dispatchObj = Date::excelToDateTimeObject($row[6]);
            } else {
                $dispatchObj = DateTime::createFromFormat('d/m/Y', trim($row[6]));
            }
            if (!$dispatchObj) {
                throw new Exception("Error en la conversión de la fecha Dispatch Date Warenhouse EC: valor inválido '{$row[6]}'");
            }
            $dispatch_date_val = $dispatchObj->format('Y-m-d');
        } else {
            throw new Exception("El campo Dispatch Date Warenhouse EC no puede estar vacío");
        }
        
        // índice 7: Departure Port Origin EC
        $departure_date_val = null;
        if (!empty($row[7])) {
            if (is_numeric($row[7])) {
                $departureObj = Date::excelToDateTimeObject($row[7]);
            } else {
                $departureObj = DateTime::createFromFormat('d/m/Y', trim($row[7]));
            }
            if (!$departureObj) {
                throw new Exception("Error en la conversión de la fecha Departure Port Origin EC: valor inválido '{$row[7]}'");
            }
            $departure_date_val = $departureObj->format('Y-m-d');
        } else {
            throw new Exception("El campo Departure Port Origin EC no puede estar vacío");
        }
        
        // índice 8: Booking BL
        $booking_bk = $row[8] ?? '';
        // índice 9: Number Container
        $number_container = $row[9] ?? '';
        // índice 10: Number Commercial Inovice
        $number_commercial_invoice = $row[10] ?? '';
        
        // índice 21: ETA Date
        $eta_date_val = null;
        if (!empty($row[21])) {
            if (is_numeric($row[21])) {
                $etaObj = Date::excelToDateTimeObject($row[21]);
            } else {
                $etaObj = DateTime::createFromFormat('d/m/Y', trim($row[21]));
            }
            if (!$etaObj) {
                throw new Exception("Error en la conversión de la fecha ETA Date: valor inválido '{$row[21]}'");
            }
            $eta_date_val = $etaObj->format('Y-m-d');
        } else {
            throw new Exception("El campo ETA Date no puede estar vacío");
        }
        
        // Insertar registro en Container
        $stmtContainer->bind_param(
            "ssssssssssss",
            $idPackingList,
            $num_dae,
            $destiny_pod,
            $forwarder,
            $shipping_line,
            $incoterm,
            $dispatch_date_val,
            $departure_date_val,
            $booking_bk,
            $number_container,
            $number_commercial_invoice,
            $eta_date_val
        );
        $stmtContainer->execute();
        // Dado que IdContainer es autogenerado, se captura el valor insertado para relacionarlo en Items
        $idContainer = $conexion->insert_id;

        // Extraer datos para Items:
        // índice 11: Code Product EC
        $code_product_ec = $row[11] ?? '';
        // índice 12: Number LOT
        $number_lot = $row[12] ?? '';
        // índice 13: Customer
        $customer = $row[13] ?? '';
        // índice 14: Number PO
        $number_po = $row[14] ?? '';
        // índice 15: Description
        $description = $row[15] ?? '';
        // índice 16: Packing Unit
        $packing_unit = (int)($row[16] ?? 0);
        // índice 17: Qty Box
        $qty_box = (int)($row[17] ?? 0);
        // índice 18: Weight Neto Per box kg (convierte comas a puntos)
        $weight_neto_per_box_kg = (float)str_replace(',', '.', $row[18] ?? 0);
        // índice 19: Weight Bruto Per box kg (convierte comas a puntos)
        $weight_bruto_per_box_kg = (float)str_replace(',', '.', $row[19] ?? 0);
        // índice 20: Total Weight kg (convierte comas a puntos)
        $total_weight_kg = (float)str_replace(',', '.', $row[20] ?? 0);
        // índice 22: Price BOX EC (convierte comas a puntos)
        $price_box_ec = (float)str_replace(',', '.', $row[22] ?? 0);
        // índice 23: Total Price EC (convierte comas a puntos)
        $total_price_ec = (float)str_replace(',', '.', $row[23] ?? 0);
        // índice 24: Price BOX USA (convierte comas a puntos)
        $price_box_usa = (float)str_replace(',', '.', $row[24] ?? 0);
        // índice 25: Total Price BOX USA (convierte comas a puntos)
        $total_price_box_usa = (float)str_replace(',', '.', $row[25] ?? 0);

        // Insertar registro en Items
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
            $price_box_ec,
            $total_price_ec,
            $price_box_usa,
            $total_price_box_usa
        );
        $stmtItems->execute();
    }
    
    $stmtContainer->close();
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
