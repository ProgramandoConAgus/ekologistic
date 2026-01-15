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

        // Construir mapa de columnas basado en el encabezado (fila 0)
        $header = array_map(function($h){
            $k = strtolower(trim((string)$h));
            $k = preg_replace('/[^a-z0-9]+/','_', $k);
            $k = trim($k, '_');
            return $k;
        }, $rows[0]);
        $colIndex = [];
        foreach ($header as $i => $h) {
            if ($h !== '') $colIndex[$h] = $i;
        }

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
        // Obtener valores del primer renglón con soporte por nombre de columna (si existe)
        $get = function($row, $name, $fallbackIndex = null) use ($colIndex) {
            if (isset($colIndex[$name])) return trim((string)($row[$colIndex[$name]] ?? ''));
            if ($fallbackIndex !== null) return trim((string)($row[$fallbackIndex] ?? ''));
            return '';
        };

        // Mapeo usando los índices que coinciden con el archivo que cargaste
        // Columna: 0=IdContainer,1=idPackingList,2=num_op,3=num_dae,4=Forwarder,5=Shipping_Line,
        // 6=Booking_BK,7=Number_Container,8=Destinity_POD,9=Incoterm,10=Dispatch_Date...,11=Departure...,12=ETA_Date
        // Fallback indices aligned con el Excel que compartiste
        $num_op           = $get($first, 'num_op', 1);
        // Si no existe 'num_op', intentar con 'dae' (índice 1)
        if ($num_op === '') $num_op = $get($first, 'dae', 1);
        $num_dae          = $get($first, 'num_dae', 1);
        $destiny_pod      = $get($first, 'destiny_pod', 2);
        $forwarder        = $get($first, 'forwarder', 3);
        $shipping_line    = $get($first, 'shipping_line', 4);
        $incoterm         = $get($first, 'incoterm', 5);
        $booking_bl       = $get($first, 'booking_bl', 8);
        $number_container = $get($first, 'number_container', 9);
        // Extraer campos de fecha / booking / container / tipo de carga (por nombre o por posición)
        // Helper: buscar índice de columna por lista de aliases o por patrón en los headers
        $findHeaderIndex = function(array $aliases, array $patterns = []) use ($colIndex) {
            // chequear aliases exactos
            foreach ($aliases as $a) {
                if (isset($colIndex[$a])) return $colIndex[$a];
            }
            // buscar por patrones dentro de las keys
            foreach ($colIndex as $k => $idx) {
                foreach ($patterns as $pat) {
                    if (strpos($k, $pat) !== false) return $idx;
                }
            }
            return null;
        };

        // aliases comunes / typos
        $dispatchIdx = $findHeaderIndex(
            ['dispatch_date_warehouse_ec','dispatch_date_warenhouse_ec','dispatch_date','dispatch_date_warehouse','dispatch_date_warenhouse'],
            ['dispatch','warehouse','warenhouse']
        );
        $departureIdx = $findHeaderIndex(
            ['departure_date_port_origin_ec','departure_port_origin_ec','departure_date','departure_port_origin'],
            ['departure','port_origin','port']
        );
        $etaIdx = $findHeaderIndex(['eta_date','eta'], ['eta']);

        // Si no encontramos por header, intentar detectar una celda con formato fecha en las primeras 15 columnas
        $detectDateInRow = function($row) use (&$convertirFecha) {
            for ($i = 0; $i < min(15, count($row)); $i++) {
                $v = trim((string)$row[$i]);
                if ($v === '') continue;
                // ignorar tokens claramente no-fecha (como alfanumériques sin / o -)
                $conv = convertirFecha($v);
                if ($conv !== '') return [$i, $v];
            }
            return [null, ''];
        };

        // resolver valores raw usando índices detectados o por escaneo
        if ($dispatchIdx !== null) {
            $dispatchDateRaw = $first[$dispatchIdx] ?? '';
        } else {
            list($found, $val) = $detectDateInRow($first);
            if ($found !== null) {
                $dispatchDateRaw = $val;
            } else {
                $dispatchDateRaw = '';
            }
        }

        if ($departureIdx !== null) {
            $departureDateRaw = $first[$departureIdx] ?? '';
        } else {
            // prefer next date-like cell after dispatch index
            list($found2, $val2) = $detectDateInRow($first);
            if ($found2 !== null) {
                $departureDateRaw = $val2;
            } else {
                $departureDateRaw = '';
            }
        }

        $etaRaw = ($etaIdx !== null) ? ($first[$etaIdx] ?? '') : '';

        // Tipo de carga: varios posibles encabezados
        $tipoDeCargaRaw = '';
        foreach (['tipodecarga','tipo_de_carga','tipode_carga','tipo_carga'] as $k) {
            if (isset($colIndex[$k])) { $tipoDeCargaRaw = $first[$colIndex[$k]] ?? ''; break; }
        }

        // Normalizar casos explícitos que normalmente vienen como '0000-00-00' y tratar como vacío
        $dRaw = trim((string)$dispatchDateRaw);
        $depRaw = trim((string)$departureDateRaw);
        if ($dRaw === '0000-00-00' || $dRaw === '0000/00/00' || $dRaw === '0') $dispatchDateRaw = '';
        if ($depRaw === '0000-00-00' || $depRaw === '0000/00/00' || $depRaw === '0') $departureDateRaw = '';

        // Convertir fechas con la función helper
        $dispatchDateVal  = convertirFecha($dispatchDateRaw);
        $departureDateVal = convertirFecha($departureDateRaw);
        $etaDateVal       = convertirFecha($etaRaw);

        // Aceptar que al menos una de las dos (dispatch o departure) exista; usar una como fallback
        if (($dispatchDateVal === '' || $dispatchDateVal === null) && ($departureDateVal === '' || $departureDateVal === null)) {
            $hdrs = array_keys($colIndex);
            $msg = "Dispatch_Date_Warehouse_EC vacío o formato inválido. " .
                "dispatchRaw='" . addslashes($dispatchDateRaw) . "' dispatchConverted='" . addslashes($dispatchDateVal) . "' " .
                "departureRaw='" . addslashes($departureDateRaw) . "' departureConverted='" . addslashes($departureDateVal) . "' ";
            $msg .= "| header_keys=" . implode(',', $hdrs) . " | first_row_sample=" . json_encode(array_slice($first,0,15));

            // También registrar en el log de debug para inspección incluso cuando fallamos aquí
            $errorDebug = [
                'timestamp' => date('c'),
                'file' => basename($targetPath),
                'dispatchRaw' => (string)$dispatchDateRaw,
                'dispatchConverted' => (string)$dispatchDateVal,
                'departureRaw' => (string)$departureDateRaw,
                'departureConverted' => (string)$departureDateVal,
                'header_keys' => $hdrs,
                'first_row_sample' => array_slice($first,0,15),
            ];
            $logPath = __DIR__ . '/../uploads/packinglists/debug_log.txt';
            @file_put_contents($logPath, json_encode($errorDebug, JSON_UNESCAPED_SLASHES) . PHP_EOL, FILE_APPEND | LOCK_EX);
            if (isset($_GET['debug']) && $_GET['debug'] == '1') {
                echo "DEBUG_ERROR: " . htmlentities(json_encode($errorDebug, JSON_UNESCAPED_UNICODE)) . "<br>";
            }

            throw new Exception($msg);
        }
        // Si falta dispatch pero sí hay departure, usar departure como dispatch
        if (($dispatchDateVal === '' || $dispatchDateVal === null) && ($departureDateVal !== '' && $departureDateVal !== null)) {
            $dispatchDateVal = $departureDateVal;
        }
        // Si falta departure pero hay dispatch, usar dispatch
        if (($departureDateVal === '' || $departureDateVal === null) && ($dispatchDateVal !== '' && $dispatchDateVal !== null)) {
            $departureDateVal = $dispatchDateVal;
        }

        // Determinar status del contenedor: DIRECTA => Despachado
        $statusContainer = 'pendiente';
        if (strtoupper(trim($tipoDeCargaRaw)) === 'DIRECTA') {
            $statusContainer = 'Despachado';
        }

        $stmtC = $conexion->prepare("\n        INSERT INTO container (\n            idPackingList,\n            num_op,\n            num_dae,\n            Destinity_POD,\n            Forwarder,\n            Shipping_Line,\n            Incoterm,\n            Dispatch_Date_Warehouse_EC,\n            Departure_Date_Port_Origin_EC,\n            Booking_BK,\n            Number_Container,\n            ETA_Date,\n            status\n        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)\n    ");
        if (!$stmtC) throw new Exception($conexion->error);
        $stmtC->bind_param(
            "issssssssssss",
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
            $etaDateVal,
            $statusContainer
        );
        // --- Debug: registrar y opcionalmente mostrar los valores antes de ejecutar INSERT ---
        $debugInfo = [
            'timestamp' => date('c'),
            'file' => basename($targetPath),
            'colIndex_keys' => array_keys($colIndex),
            'numPL' => $numPL,
            'num_op' => $num_op,
            'num_dae' => $num_dae,
            'destiny_pod' => $destiny_pod,
            'forwarder' => $forwarder,
            'shipping_line' => $shipping_line,
            'incoterm' => $incoterm,
            'dispatchRaw' => (string)$dispatchDateRaw,
            'dispatchConverted' => (string)$dispatchDateVal,
            'departureRaw' => (string)$departureDateRaw,
            'departureConverted' => (string)$departureDateVal,
            'booking_bl' => $booking_bl,
            'number_container' => $number_container,
            'etaRaw' => (string)$etaRaw,
            'etaConverted' => (string)$etaDateVal,
            'statusContainer' => $statusContainer,
        ];
        // Guardar en log (append)
        $logPath = __DIR__ . '/../uploads/packinglists/debug_log.txt';
        @file_put_contents($logPath, json_encode($debugInfo, JSON_UNESCAPED_SLASHES) . PHP_EOL, FILE_APPEND | LOCK_EX);
        // Mostrar en pantalla si se pasa ?debug=1 (útil para pruebas rápidas)
        if (isset($_GET['debug']) && $_GET['debug'] == '1') {
            echo "DEBUG_CONTAINER: " . htmlentities(json_encode($debugInfo, JSON_UNESCAPED_UNICODE)) . "<br>";
        }
        $stmtC->execute();
        $idContainer = $conexion->insert_id;
        $stmtC->close();

        // --------------------------------------------------
        // 8) INSERT en items (columnas también desplazadas +1)
        // --------------------------------------------------
        $stmtI = $conexion->prepare("\n        INSERT INTO items (\n
                idContainer,
                Number_Commercial_Invoice,
                Code_Product_EC,
                Number_Lot,
                Customer,
                Number_PO,
                Description,
                codigo_fsc,
                Packing_Unit,
                Qty_Box,
                Weight_Neto_Per_Box_kg,
                Weight_Bruto_Per_Box_kg,
                Total_Weight_kg,
                Price_Box_EC,
                Total_Price_EC,
                Price_BOX_USA,
                Total_Price_USA,
                valor_logistico_comex,
                Price_Box_EXW,
                Price_Logistic_Per_Box,
                Total_Price_Per_Box,
                Total_Price_EXW,
                Total_Price_INCOTERM,
                Type_Loade
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)\n    ");
            if (!$stmtI) throw new Exception($conexion->error);
            // tipos: idContainer (i) + 7 strings (invoice..codigo_fsc) + 2 enteros (packing_unit, qty_box)
            // + 13 doubles (weights + prices) + 1 string (Type_Loade) = 24 parámetros
            $types = "i" . str_repeat("s", 7) . "ii" . str_repeat("d", 13) . "s";

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

            // Obtener valores por nombre de columna si es posible, si no fallback a índices antiguos
            $getRow = function($row, $name, $fallbackIndex = null) use ($colIndex) {
                if (isset($colIndex[$name])) return trim((string)($row[$colIndex[$name]] ?? ''));
                if ($fallbackIndex !== null) return trim((string)($row[$fallbackIndex] ?? ''));
                return '';
            };

            $itemInvoice             = $getRow($r, 'number_commercial_inovice', 11);
            if ($itemInvoice === '') $itemInvoice = $getRow($r, 'number_commercial_invoice', 11);
            $code_product_ec         = $getRow($r, 'code_product_ec', 12);
            $number_lot              = $getRow($r, 'number_lot', 13);
            $customer                = $getRow($r, 'customer', 14);
            $number_po               = $getRow($r, 'number_po', 15);
            $description             = $getRow($r, 'description', 16);
            // Code FSC usually sits after Description
        $codigo_fsc              = $getRow($r, 'codigo_fsc', 17);
        $packing_unit            = (int)  ($getRow($r, 'packing_unit', 18) ?: 0);
        $qty_box                 = (int)  ($getRow($r, 'qty_box', 19) ?: 0);
        $weight_neto_per_box_kg  = (float)($getRow($r, 'weight_neto_per_box_kg', 20) ?: 0);
        $weight_bruto_per_box_kg = (float)($getRow($r, 'weight_bruto_per_box_kg', 21) ?: 0);
        $total_weight_kg         = (float)($getRow($r, 'total_weight_kg', 22) ?: 0);
            // Prices and logistic columns (fallback indices aligned with the sample header you sent)
        $priceBoxEC              = (float)($getRow($r, 'price_box_exw', 24) ?: 0); // Price Box EXW
        $priceBoxEXW             = $priceBoxEC;
            $priceLogisticPerBox     = (float)($getRow($r, 'price_logistic_per_box', 25) ?: 0); // nuevo
            $totalPricePerBox        = (float)($getRow($r, 'total_price_per_box', 26) ?: 0); // nuevo (x+y)
            $totalPriceEXW           = (float)($getRow($r, 'total_price_exw', 27) ?: 0); // nuevo
            $totalPriceIncoterm      = (float)($getRow($r, 'total_price_incoterm', 28) ?: 0); // nuevo
        $priceBoxUSA             = (float)($getRow($r, 'price_box_usa', 29) ?: 0);
        $totalPriceUSA           = (float)($getRow($r, 'total_price_box_usa', 30) ?: 0);
        // Total Price EC (existing DB column) - fallback to totalPricePerBox when explicit not present
        $totalPriceEC            = (float)($getRow($r, 'total_price_ec', 26) ?: $totalPricePerBox);
            // Valor logistico COMEX normalmente corresponde al "Price Logistic per box" (índice 25)
            $valor_logistico_comex   = (float)($getRow($r, 'valor_logistico_comex', 25) ?: 0);
            // Tipo de carga (texto)
            $typeLoade               = $getRow($r, 'type_loade', 31);

            $stmtI->bind_param(
                $types,
                $idContainer,
                $itemInvoice,
                $code_product_ec,
                $number_lot,
                $customer,
                $number_po,
                $description,
                $codigo_fsc,
                $packing_unit,
                $qty_box,
                $weight_neto_per_box_kg,
                $weight_bruto_per_box_kg,
                $total_weight_kg,
                $priceBoxEC,
                $totalPriceEC,
                $priceBoxUSA,
                $totalPriceUSA,
                $valor_logistico_comex,
                $priceBoxEXW,
                $priceLogisticPerBox,
                $totalPricePerBox,
                $totalPriceEXW,
                $totalPriceIncoterm,
                $typeLoade
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
        $val = trim((string)$val);
        if ($val === '') return '';

        // Si es numérico, probablemente sea fecha Excel (serial)
        if (is_numeric($val)) {
            try {
                $dt = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject((float)$val);
                return $dt->format('Y-m-d');
            } catch (Exception $e) {
                // continue
            }
        }

        // Intentar formatos comunes: d/m/Y o Y-m-d
        $d = DateTime::createFromFormat('d/m/Y', $val);
        if ($d && $d->format('d/m/Y') === $val) return $d->format('Y-m-d');
        $d2 = DateTime::createFromFormat('Y-m-d', $val);
        if ($d2 && $d2->format('Y-m-d') === $val) return $d2->format('Y-m-d');

        // Último intento: strtotime
        $ts = strtotime($val);
        if ($ts !== false) return date('Y-m-d', $ts);

        return '';
    }
        