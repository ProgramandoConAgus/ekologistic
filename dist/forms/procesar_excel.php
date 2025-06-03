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
    // --------------------------------------------------
    // 2) Extraer datos del CONTENEDOR de la PRIMERA FILA DE DATOS (fila 1 en $rows)
    // --------------------------------------------------
    // Recuerda que rows[0] es la fila de encabezados, rows[1] es la primera fila "real"
    $primerRegistro = $rows[1];
    // Validar que haya al menos 2 filas (encabezado + 1 fila de datos)
    $numero_packing_list_pl = $primerRegistro[0] ?? '';

    // Insertar registro en Packing_List
    $fechaSubida = date('Y-m-d H:i:s');
    $status = 'pendiente';  
    $stmtPL = $conexion->prepare("
        INSERT INTO packing_list (IdPackingList,IdUsuario, Date_Created, path_file, status)
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmtPL->bind_param("iisss",$numero_packing_list_pl, $IdUsuario, $fechaSubida, $targetPath, $status);
    $stmtPL->execute();
    $idPackingList = $conexion->insert_id;
    $stmtPL->close();

   

    // Según el orden que mostraste en la imagen, los índices son:
    //  0 => numero_packing_list_pl
    //  1 => Num OP
    //  2 => Num DAE
    //  3 => Destiny POD
    //  4 => Forwarder
    //  5 => Shipping Line
    //  6 => Incoterm
    //  7 => Dispatch Date Warenhouse EC
    //  8 => Departure Port Origin EC
    //  9 => Booking BL
    //  10 => Number Container
    //  11 => Number Commercial Invoice
    //  12 => Code Product EC
    //  13 => Number LOT
    //  14 => Customer
    //  15 => Number PO
    //  16 => Description
    //  17 => Packing Unit
    //  18 => Qty Box
    //  19 => Weight Neto Per box kg
    //  20 => Weight Bruto Per box kg
    //  21 => Total Weight kg
    //  22 => ETA Date
    //  23 => Price BOX EC
    //  24 => Total Price EC
    //  25 => Price BOX USA
    //  26 => Total Price BOX USA

    // Extraemos para la tabla Container (tú eliges cuáles quieres guardar):
    $num_op                 = $primerRegistro[1] ?? '';
    $num_dae                = $primerRegistro[2] ?? '';
    $destiny_pod           = $primerRegistro[3] ?? '';
    $forwarder             = $primerRegistro[4] ?? '';
    $shipping_line         = $primerRegistro[5] ?? '';
    $incoterm              = $primerRegistro[6] ?? '';

    // Convertir fechas (Dispatch, Departure, ETA) desde columnas 7, 8 y 22 (según tu criterio real)
    // OJO: la columna 7 y 8 en tu tabla dice "Dispatch Date" y "Departure Port" (texto).
    //      A veces en un Excel guardan la fecha, a veces un texto. Adáptalo a lo que llegue.
    $dispatchDateVal = convertirFecha($primerRegistro[7]);
    // La 8 sería "Departure Port Origin EC", que puede ser más un texto que una fecha.
    $departurePortOriginEC = convertirFecha($primerRegistro[8]);
    // La 22 es ETA Date
    $etaDateVal = convertirFecha($primerRegistro[22]);

    // booking BL y container:
    $booking_bl             = $primerRegistro[9]  ?? '';
    $number_container       = $primerRegistro[10] ?? '';
    $number_commercial_invoice = $primerRegistro[11] ?? '';

    // Insertar en Container
    $stmtContainer = $conexion->prepare("
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
        ) VALUES ( ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    if (!$stmtContainer) {
        throw new Exception("Error al preparar statement Container: " . $conexion->error);
    }

    // Ajusta la cadena de tipos de bind_param a tus campos. Aquí asumo:
    // - IdPackingList => int
    // - Los demás => string, salvo fechas que igual enviamos como string
    $stmtContainer->bind_param(
        "issssssssssss",
        $numero_packing_list_pl,    
        $num_op,                     // string (o int si en tu BD es int)
        $num_dae,                    // string
        $destiny_pod,                // string
        $forwarder,                  // string
        $shipping_line,              // string
        $incoterm,                   // string
        $dispatchDateVal,            // string (YYYY-mm-dd)
        $departurePortOriginEC,      // string
        $booking_bl,                 // string
        $number_container,           // string
        $number_commercial_invoice,  // string
        $etaDateVal                  // string (YYYY-mm-dd)
    );
    $stmtContainer->execute();
    $idContainer = $conexion->insert_id;
    $stmtContainer->close();

    // --------------------------------------------------
    // 3) Insertar ÍTEMS (por cada línea desde la fila 2 en adelante)
    // --------------------------------------------------
    // Observa que el código original usaba un foreach(array_slice($rows, 1)) 
    // para procesar TODAS las filas (incluida la primera, la cual ya tomamos para “Container”).
    // Si cada fila corresponde a 1 ítem distinto con el mismo contenedor, hacemos:
    //   - Omitimos la fila de encabezado (índice 0).
    //   - Recorremos desde la fila 1 en adelante.
    //   - Pero, si la fila 1 la usaste para Container, ¿quieres también insertarla como ítem?
    //     Si la respuesta es SÍ, dejamos el slice desde (1). Si la respuesta es NO, empezamos en (2).
    // En este ejemplo, **supondré** que la fila 1 también contiene un ítem y la insertamos:

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
    if (!$stmtItems) {
        throw new Exception("Error al preparar statement Items: " . $conexion->error);
    }

    // Cadena de tipos: 
    //  i = integer, s = string, d = double/float
    // Ajusta según tus columnas en Items
    $itemsParamTypes = "issssssiidddddd";

    // Comenzamos a partir de la fila 1 (que ya usamos para container).
    // Ojo: aquí se inserta también lo que ya fue “container”.
    // Si no quieres insertar la primera fila como item, usa array_slice($rows, 2)
    foreach (array_slice($rows, 1) as $rowIndex => $rowData) {
        // 1) Revisar si la fila está “vacía” (típico en Excels con huecos)
       $estaVacia = true;
foreach ($rowData as $cell) {
    $valor = trim((string)$cell);
    // Si la celda no es vacía y su contenido no es solo un punto...
    if ($valor !== "" && $valor !== ".") {
        $estaVacia = false;
        break;
    }
}
if ($estaVacia) {
    continue;
}


        // 2) Extraer columnas de item, desde la 12 en adelante
        //    (las 12 primeras posiciones son del contenedor/encabezado).
        $code_product_ec         = $rowData[12] ?? '';
        $number_lot              = $rowData[13] ?? '';
        $customer                = $rowData[14] ?? '';
        $number_po               = $rowData[15] ?? '';
        $description             = $rowData[16] ?? '';
        $packing_unit            = $rowData[17] ?? '';
        $qty_box                 = (int)($rowData[18] ?? 0);
        $weight_neto_per_box_kg  = (float)($rowData[19] ?? 0);
        $weight_bruto_per_box_kg = (float)($rowData[20] ?? 0);
        $total_weight_kg         = (float)($rowData[21] ?? 0);
        $priceBoxEC              = (float)($rowData[23] ?? 0);
        $totalPriceEC            = (float)($rowData[24] ?? 0);
        $priceBoxUSA             = (float)($rowData[25] ?? 0);
        $totalPriceUSA           = (float)($rowData[26] ?? 0);

        // 3) Insertar el item
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
    // En caso de error, podrías eliminar el archivo subido si lo deseas
    @unlink($targetPath);
    echo "Error al procesar: " . $e->getMessage();
    exit();
}

// --------------------------------------------------
// Función de ayuda para convertir fecha Excel -> yyyy-mm-dd
// --------------------------------------------------
function convertirFecha($valorCelda)
{
    // Si tu Excel viene con formato dd/mm/yyyy, puedes hacer:
    if (!empty($valorCelda)) {
        $dateObj = DateTime::createFromFormat('d/m/Y', $valorCelda);
        if ($dateObj) {
            return $dateObj->format('Y-m-d');
        }
    }
    // Si no se pudo parsear, regresamos vacío o nulo
    return null;
}
?>
