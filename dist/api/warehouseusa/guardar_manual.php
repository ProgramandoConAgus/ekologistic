<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
include '../../con_db.php';
header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Método no permitido', 405);
    }

    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data) throw new Exception('Datos inválidos', 400);

    function limpiarValor($v) {
        return ($v === '' || $v === null) ? null : floatval(str_replace(',', '.', $v));
    }

    // Valores comunes
    $fechaEntrada = trim($data['fecha_entrada'] ?? '');
    $fechaSalida  = trim($data['fecha_salida'] ?? '');
    $recibo       = trim($data['recibo_almacen'] ?? '');
    $estado       = trim($data['estado'] ?? '');
    $numeroFactura= trim($data['numero_factura'] ?? '');
    $numeroLote   = trim($data['numero_lote'] ?? '');
    $notas        = trim($data['numero_contenedor'] ?? ''); // 'notas' es el campo correcto
    $ordenCompra  = trim($data['orden_compra'] ?? '');
    $numeroParte  = trim($data['numero_parte'] ?? '');
    $descripcion  = trim($data['descripcion'] ?? '');
    $modelo       = trim($data['modelo'] ?? '');
    $guardadoPor  = "manual";

    if ($numeroFactura === '' || $fechaEntrada === '') {
        echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
        exit;
    }

    // Define tabla según estado
    $tableName = strtolower($estado) === 'cargado' ? 'palets_cargados' : 'dispatch';

    $sql = "INSERT INTO {$tableName} (
        fecha_entrada, fecha_salida, recibo_almacen, estado,
        numero_factura, numero_lote, notas, numero_orden_compra,
        numero_parte, descripcion, modelo, palets, cantidad,
        valor_unitario, valor, unidad, longitud_in, ancho_in, altura_in, peso_lb,
        valor_unitario_restante, valor_restante, unidad_restante,
        longitud_in_restante, ancho_in_restante, altura_in_restante, peso_lb_restante,
        guardado_por, palets_restante, cantidad_restante
    ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";

    $stmt = $conexion->prepare($sql);
    if (!$stmt) throw new Exception('Error en prepare: ' . $conexion->error);

    function insertarRegistro($stmt, $val) {
        $fecha_entrada = $val['fecha_entrada'];
        $fecha_salida = $val['fecha_salida'];
        $recibo_almacen = $val['recibo_almacen'];
        $estado = $val['estado'];
        $numero_factura = $val['numero_factura'];
        $numero_lote = $val['numero_lote'];
        $notas = $val['notas'];
        $numero_orden_compra = $val['numero_orden_compra'];
        $numero_parte = $val['numero_parte'];
        $descripcion = $val['descripcion'];
        $modelo = $val['modelo'];
        $palets = intval($val['palets']);
        $cantidad = intval($val['cantidad']);
        $valor_unitario = $val['valor_unitario'];
        $valor = $val['valor'];
        $unidad = $val['unidad'];
        $longitud = $val['longitud'];
        $ancho = $val['ancho'];
        $altura = $val['altura'];
        $peso = $val['peso'];
        $valor_unitario_restante = $val['valor_unitario_restante'];
        $valor_restante = $val['valor_restante'];
        $unidad_restante = $val['unidad_restante'];
        $longitud_restante = $val['longitud_restante'];
        $ancho_restante = $val['ancho_restante'];
        $altura_restante = $val['altura_restante'];
        $peso_restante = $val['peso_restante'];
        $guardado_por = $val['guardado_por'];
        $palets_restante = intval($val['palets_restante']);
        $cantidad_restante = intval($val['cantidad_restante']);

        $stmt->bind_param(
            'ssssssssssiiddddddddddsdddddsi',
            $fecha_entrada, $fecha_salida, $recibo_almacen, $estado,
            $numero_factura, $numero_lote, $notas, $numero_orden_compra,
            $numero_parte, $descripcion, $modelo,
            $palets, $cantidad,
            $valor_unitario, $valor, $unidad,
            $longitud, $ancho, $altura, $peso,
            $valor_unitario_restante, $valor_restante, $unidad_restante,
            $longitud_restante, $ancho_restante, $altura_restante, $peso_restante,
            $guardado_por, $palets_restante, $cantidad_restante
        );
        $stmt->execute();
    }

    // Registro principal
    $principal = [
        'fecha_entrada' => $fechaEntrada,
        'fecha_salida'  => $fechaSalida,
        'recibo_almacen'=> $recibo,
        'estado'        => $estado,
        'numero_factura'=> $numeroFactura,
        'numero_lote'   => $numeroLote,
        'notas'         => $notas,
        'numero_orden_compra' => $ordenCompra,
        'numero_parte'  => $numeroParte,
        'descripcion'   => $descripcion,
        'modelo'        => $modelo,
        'palets'        => intval(trim($data['palets'] ?? 0)),
        'cantidad'      => intval($data['cantidad'] ?? 0),
        'valor_unitario'=> limpiarValor($data['valor_unitario'] ?? ''),
        'valor'         => limpiarValor($data['valor'] ?? ''),
        'unidad'        => trim($data['unidad'] ?? ''),
        'longitud'      => limpiarValor($data['longitud'] ?? ''),
        'ancho'         => limpiarValor($data['ancho'] ?? ''),
        'altura'        => limpiarValor($data['altura'] ?? ''),
        'peso'          => limpiarValor($data['peso'] ?? ''),
        // columnas restantes null
        'valor_unitario_restante' => null,
        'valor_restante'          => null,
        'unidad_restante'         => null,
        'longitud_restante'       => null,
        'ancho_restante'          => null,
        'altura_restante'         => null,
        'peso_restante'           => null,
        'guardado_por'  => $guardadoPor,
        'palets_restante' => 0,
        'cantidad_restante' => 0
    ];
    insertarRegistro($stmt, $principal);

    // Insertar registro restante solo si ancho_restante no es null, 0 ni vacío
    $anchoRestanteRaw = $data['ancho_restante'] ?? null;
    $anchoRestante = limpiarValor($anchoRestanteRaw);

    if ($anchoRestante !== null && $anchoRestante != 0 && $anchoRestanteRaw !== '') {
        $cantidadR = intval($data['cantidad_restante'] ?? 0);
        $restante = [
            'fecha_entrada' => $fechaEntrada,
            'fecha_salida'  => $fechaSalida,
            'recibo_almacen'=> $recibo,
            'estado'        => $estado,
            'numero_factura'=> $numeroFactura,
            'numero_lote'   => $numeroLote,
            'notas'         => $notas,
            'numero_orden_compra' => $ordenCompra,
            'numero_parte'  => $numeroParte,
            'descripcion'   => $descripcion,
            'modelo'        => $modelo,
            'palets'        => intval(trim($data['palets_restante'] ?? 0)),
            'cantidad'      => $cantidadR,
            'valor_unitario'=> limpiarValor($data['valor_unitario_restante'] ?? ''),
            'valor'         => limpiarValor($data['valor_restante'] ?? ''),
            'unidad'        => trim($data['unidad_restante'] ?? ''),
            'longitud'      => limpiarValor($data['longitud_restante'] ?? ''),
            'ancho'         => $anchoRestante,
            'altura'        => limpiarValor($data['altura_restante'] ?? ''),
            'peso'          => limpiarValor($data['peso_restante'] ?? ''),
            'valor_unitario_restante' => null,
            'valor_restante'          => null,
            'unidad_restante'         => null,
            'longitud_restante'       => null,
            'ancho_restante'          => null,
            'altura_restante'         => null,
            'peso_restante'           => null,
            'guardado_por'  => $guardadoPor,
            'palets_restante' => 0,
            'cantidad_restante' => 0
        ];
        insertarRegistro($stmt, $restante);
    }

    $stmt->close();

    echo json_encode(['success' => true]);

} catch (Exception $e) {
    http_response_code($e->getCode() ?: 500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
