<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

require '../con_db.php';
require '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;

if (!isset($_SESSION['IdUsuario']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Acceso denegado']);
    exit;
}

if (!isset($_FILES['excel']) || $_FILES['excel']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'message' => 'Error en la subida']);
    exit;
}

$finfo = finfo_open(FILEINFO_MIME_TYPE);
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

$uploadDir = '../uploads/dispatch/';
if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
$nombre = uniqid() . '_' . basename($_FILES['excel']['name']);
$ruta = $uploadDir . $nombre;
if (!move_uploaded_file($_FILES['excel']['tmp_name'], $ruta)) {
    echo json_encode(['success' => false, 'message' => 'No se guardó el archivo']);
    exit;
}

try {
    $spreadsheet = IOFactory::load($ruta);
    $rows = $spreadsheet->getActiveSheet()->toArray();

    $sqlDispatch = "INSERT INTO dispatch (
        fecha_entrada, fecha_salida, recibo_almacen, estado,
        numero_factura, numero_lote, notas, numero_orden_compra,
        numero_parte, descripcion, modelo, cantidad, palets,
        valor_unitario, valor, unidad, longitud_in, ancho_in, altura_in, peso_lb
    ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";

    $stmtDispatch = $conexion->prepare($sqlDispatch);
    if (!$stmtDispatch) throw new Exception('Error prepare dispatch: ' . $conexion->error);

    $sqlCargado = str_replace('dispatch', 'palets_cargados', $sqlDispatch);
    $stmtCargado = $conexion->prepare($sqlCargado);
    if (!$stmtCargado) throw new Exception('Error prepare palets_cargados: ' . $conexion->error);

    function convFecha($v) {
        if (empty($v)) return null;
        if (is_numeric($v) && $v > 31) {
            $ts = Date::excelToTimestamp($v);
            return date('Y-m-d', $ts);
        }
        if (strpos($v, '/') !== false) {
            list($a, $b, $c) = explode('/', $v);
            if (intval($a) > 12) { $day = intval($a); $month = intval($b); }
            else { $month = intval($a); $day = intval($b); }
            $year = intval($c);
            return sprintf('%04d-%02d-%02d', $year, $month, $day);
        }
        return null;
    }

    $agrupados = [];
    $conteos = [];

    foreach (array_slice($rows, 1) as $r) {
        $blank = true;
        foreach ($r as $c) {
            if (trim((string)$c) !== '') { $blank = false; break; }
        }
        if ($blank) continue;

        $estado = trim($r[3]);

        if ($estado === 'Cargado') {
            // Insert directo a palets_cargados
            $f1 = convFecha($r[0]); $f2 = convFecha($r[1]);
            $recibo = trim($r[2]); $numero_fact = trim($r[4]); $numero_lote = trim($r[5]);
            $notas = trim($r[6]); $num_orden = trim($r[7]);
            $numero_parte = trim($r[8]); $descripcion = trim($r[9]); $modelo = trim($r[10]);
            $cantidad = intval(trim($r[11]));
            $vu_raw = str_replace(',', '.', trim($r[12]));
            $v_raw  = str_replace(',', '.', trim($r[13]));
            $valor_unitario = is_numeric($vu_raw) ? floatval($vu_raw) : null;
            $valor = is_numeric($v_raw) ? floatval($v_raw) : null;
            $unidad = trim($r[14]);
            $long_in = floatval(str_replace(',', '.', trim($r[15])));
            $ancho_in = floatval(str_replace(',', '.', trim($r[16])));
            $altura_in = floatval(str_replace(',', '.', trim($r[17])));
            $peso_lb = floatval(str_replace(',', '.', trim($r[18])));
            $palets = 1;

            $stmtCargado->bind_param(
                'sssssssssssisddsdddd',
                $f1, $f2, $recibo, $estado, $numero_fact, $numero_lote, $notas,
                $num_orden, $numero_parte, $descripcion, $modelo, $cantidad, $palets,
                $valor_unitario, $valor, $unidad, $long_in, $ancho_in, $altura_in, $peso_lb
            );
            $stmtCargado->execute();
        } else {
            $key = trim($r[9]) . '|' . trim($r[10]) . '|' . trim($r[8]) . '|' . trim($r[11]);
            if (!isset($agrupados[$key])) {
                $agrupados[$key] = [
                    'cantidad_total' => 0,
                    'info' => $r
                ];
                $conteos[$key] = 0;
            }
            $agrupados[$key]['cantidad_total'] += intval(trim($r[11]));
            $conteos[$key]++;
        }
    }

   foreach ($agrupados as $key => $grupo) {
    $f1 = convFecha($grupo['info'][0]); 
    $f2 = convFecha($grupo['info'][1]);
    $recibo = trim($grupo['info'][2]); 
    $estado = trim($grupo['info'][3]);
    $numero_fact = trim($grupo['info'][4]); 
    $numero_lote = trim($grupo['info'][5]);
    $notas = trim($grupo['info'][6]); 
    $num_orden = trim($grupo['info'][7]);
    $numero_parte = trim($grupo['info'][8]); 
    $descripcion = trim($grupo['info'][9]);
    $modelo = trim($grupo['info'][10]);
    $vu_raw = str_replace(',', '.', trim($grupo['info'][12]));
    $v_raw  = str_replace(',', '.', trim($grupo['info'][13]));
    $valor_unitario = is_numeric($vu_raw) ? floatval($vu_raw) : null;
    $valor = is_numeric($v_raw) ? floatval($v_raw) : null;
    $unidad = trim($grupo['info'][14]);
    $long_in = floatval(str_replace(',', '.', trim($grupo['info'][15])));
    $ancho_in = floatval(str_replace(',', '.', trim($grupo['info'][16])));
    $altura_in = floatval(str_replace(',', '.', trim($grupo['info'][17])));
    $peso_lb = floatval(str_replace(',', '.', trim($grupo['info'][18])));

    // Extraigo cantidad de la clave, que es homogénea para el grupo
    $capacidad_pallet = intval(explode('|', $key)[3]);
    $cantidad_total = $grupo['cantidad_total'];

    $palets_completos = intdiv($cantidad_total, $capacidad_pallet);
    $remanente = $cantidad_total % $capacidad_pallet;

    if ($palets_completos > 0) {
        $cantidad = $capacidad_pallet;
        $palets = $palets_completos;
        $stmtDispatch->bind_param(
            'sssssssssssisddsdddd',
            $f1, $f2, $recibo, $estado, $numero_fact, $numero_lote, $notas,
            $num_orden, $numero_parte, $descripcion, $modelo, $cantidad, $palets,
            $valor_unitario, $valor, $unidad, $long_in, $ancho_in, $altura_in, $peso_lb
        );
        $stmtDispatch->execute();
    }

    if ($remanente > 0) {
        $cantidad = $remanente;
        $palets = 1;
        $stmtDispatch->bind_param(
            'sssssssssssisddsdddd',
            $f1, $f2, $recibo, $estado, $numero_fact, $numero_lote, $notas,
            $num_orden, $numero_parte, $descripcion, $modelo, $cantidad, $palets,
            $valor_unitario, $valor, $unidad, $long_in, $ancho_in, $altura_in, $peso_lb
        );
        $stmtDispatch->execute();
    }
}

    $stmtDispatch->close();
    $stmtCargado->close();
    echo json_encode(['success' => true, 'message' => 'Dispatch importado correctamente']);

} catch (Exception $e) {
    @unlink($ruta);
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>
