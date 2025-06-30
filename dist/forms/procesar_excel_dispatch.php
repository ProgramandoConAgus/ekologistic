<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

require '../con_db.php';
require '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;

// 1) Verificar sesión y método
if (!isset($_SESSION['IdUsuario']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Acceso denegado']);
    exit;
}

// 2) Validar archivo
if (!isset($_FILES['excel']) || $_FILES['excel']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode([
        'success' => false,
        'message' => 'Error en la subida (Código: ' . ($_FILES['excel']['error'] ?? 'ND') . ')'
    ]);
    exit;
}

// 3) Validar tipo MIME
$finfo    = finfo_open(FILEINFO_MIME_TYPE);
$mimeType = finfo_file($finfo, $_FILES['excel']['tmp_name']);
finfo_close($finfo);
$allowed = [
    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    'application/vnd.ms-excel'
];
if (!in_array($mimeType, $allowed)) {
    echo json_encode(['success' => false, 'message' => 'Formato no permitido']);
    exit;
}

// 4) Guardar archivo
$uploadDir = '../uploads/dispatch/';
if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
$nombre = uniqid() . '_' . basename($_FILES['excel']['name']);
$ruta   = $uploadDir . $nombre;
if (!move_uploaded_file($_FILES['excel']['tmp_name'], $ruta)) {
    echo json_encode(['success' => false, 'message' => 'No se guardó el archivo']);
    exit;
}

try {
    // 5) Cargar el Excel
    $spreadsheet = IOFactory::load($ruta);
    $rows        = $spreadsheet->getActiveSheet()->toArray();

    // 6) Preparar INSERT (19 columnas)
    $sql = "INSERT INTO dispatch (
                fecha_entrada,
                fecha_salida,
                recibo_almacen,
                estado,
                numero_factura,
                numero_lote,
                notas,
                numero_orden_compra,
                numero_parte,
                descripcion,
                modelo,
                cantidad,
                valor_unitario,
                valor,
                unidad,
                longitud_in,
                ancho_in,
                altura_in,
                peso_lb
            ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
    $stmt = $conexion->prepare($sql);
    if (!$stmt) {
        throw new Exception('Error prepare: ' . $conexion->error);
    }

    // 7) Función robusta de conversión de fechas
    function convFecha($v) {
        if (empty($v)) return null;
        // Excel serial
        if (is_numeric($v) && $v > 31) {
            $ts = Date::excelToTimestamp($v);
            return date('Y-m-d', $ts);
        }
        // Texto con "/"
        if (strpos($v, '/') !== false) {
            list($a, $b, $c) = explode('/', $v);
            // Si el primer bloque >12, asumimos DD/MM/YYYY
            if (intval($a) > 12) {
                $day   = intval($a);
                $month = intval($b);
            } else {
                // Si primer bloque ≤12, asumimos MM/DD/YYYY
                $month = intval($a);
                $day   = intval($b);
            }
            $year = intval($c);
            return sprintf('%04d-%02d-%02d', $year, $month, $day);
        }
        return null;
    }

    // 8) Recorrer filas (omitimos cabecera)
    foreach (array_slice($rows, 1) as $r) {
        // Saltar fila totalmente vacía
        $blank = true;
        foreach ($r as $c) {
            if (trim((string)$c) !== '') { $blank = false; break; }
        }
        if ($blank) continue;

        // Mapear columnas
        $f1 = convFecha($r[0]); // fecha_entrada
        $f2 = convFecha($r[1]); // fecha_salida

        $recibo       = (string) trim($r[2]);   // recibo_almacen
        $estado       = (string) trim($r[3]);   // estado
        $numero_fact  = trim($r[4]);            // numero_factura
        $numero_lote  = trim($r[5]);            // numero_lote
        $notas        = (string) trim($r[6]);   // notas
        $num_orden    = trim($r[7]);            // numero_orden_compra
        $numero_parte = (string) trim($r[8]);   // numero_parte
        $descripcion  = (string) trim($r[9]);   // descripcion
        $modelo       = (string) trim($r[10]);  // modelo
        $cantidad     = trim($r[11]);           // cantidad

        // Decimales con coma → punto
        $vu_raw = str_replace(',', '.', trim($r[12]));
        $v_raw  = str_replace(',', '.', trim($r[13]));
        $valor_unitario = is_numeric($vu_raw) ? floatval($vu_raw) : null;
        $valor          = is_numeric($v_raw)  ? floatval($v_raw)  : null;

        $unidad    = (string) trim($r[14]);                                // unidad
        $long_in   = floatval(str_replace(',', '.', trim($r[15])));       // longitud_in
        $ancho_in  = floatval(str_replace(',', '.', trim($r[16])));       // ancho_in
        $altura_in = floatval(str_replace(',', '.', trim($r[17])));       // altura_in
        $peso_lb   = floatval(str_replace(',', '.', trim($r[18])));       // peso_lb

        // 9) Bind de 19 parámetros (strings y decimales)
        $stmt->bind_param(
            'ssssssssssssddsdddd',
            $f1,            // 1 fecha_entrada (s)
            $f2,            // 2 fecha_salida  (s)
            $recibo,        // 3 recibo_almacen(s)
            $estado,        // 4 estado        (s)
            $numero_fact,   // 5 numero_factura(s)
            $numero_lote,   // 6 numero_lote   (s)
            $notas,         // 7 notas         (s)
            $num_orden,     // 8 numero_orden  (s)
            $numero_parte,  // 9 numero_parte  (s)
            $descripcion,   // 10 descripcion  (s)
            $modelo,        // 11 modelo       (s)
            $cantidad,      // 12 cantidad     (s)
            $valor_unitario,// 13 valor_unitario(d)
            $valor,         // 14 valor        (d)
            $unidad,        // 15 unidad       (s)
            $long_in,       // 16 longitud_in  (d)
            $ancho_in,      // 17 ancho_in     (d)
            $altura_in,     // 18 altura_in    (d)
            $peso_lb        // 19 peso_lb      (d)
        );
        $stmt->execute();
    }

    $stmt->close();
    echo json_encode(['success' => true, 'message' => 'Dispatch importado correctamente']);

} catch (Exception $e) {
    @unlink($ruta);
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
