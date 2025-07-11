<?php
// Mostrar errores en pantalla
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Configurar cabecera JSON
header('Content-Type: application/json');

// Leer payload JSON
$input = json_decode(file_get_contents('php://input'), true);

// Validar parámetros obligatorios
if (!isset($input['id'], $input['field'], $input['value'])) {
    echo json_encode(['success'=>false, 'error'=>'Parámetros incompletos']);
    exit;
}

$id    = intval($input['id']);
$field = $input['field'];
$value = $input['value'];

include('../con_db.php');

// Definir campos permitidos para palets_cargados y su equivalencia real en la DB
$camposPaletsMap = [
    'Length_in'       => 'longitud_in',
    'Broad_in'        => 'ancho_in',
    'Height_in'       => 'altura_in',
    'Weight_lb'       => 'peso_lb',
    'fecha_salida'    => 'fecha_salida',
    'departure_date'  => 'fecha_salida'
];

if ($field === 'Number_PO') {
    // Obtener numero_orden_compra y numero_parte desde palets_cargados
    $sql = "SELECT numero_factura, numero_parte , descripcion FROM palets_cargados WHERE id = ?";
    $stmt = $conexion->prepare($sql);
    if(!$stmt){
        echo json_encode(['success'=>false, 'error'=>'Error en prepare select: '.$conexion->error]);
        exit;
    }
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $palet = $result->fetch_assoc();

    if (!$palet) {
        echo json_encode(['success'=>false, 'error'=>'No se encontró el registro en palets_cargados']);
        exit;
    }

    $numero_factura = $palet['numero_factura'];
    $numero_parte        = $palet['numero_parte'];
    $descripcion        = $palet['descripcion'];

    // Buscar el IdItem en items con esos datos
    $sql ="SELECT IdItem 
        FROM items 
        WHERE Number_Commercial_Invoice = ? 
        AND Code_Product_EC = ? 
        AND Description COLLATE utf8mb4_unicode_ci LIKE CONCAT(?, '%')
        LIMIT 1";

   
    $stmt = $conexion->prepare($sql);
    if(!$stmt){
        echo json_encode(['success'=>false, 'error'=>'Error en prepare select items: '.$conexion->error]);
        exit;
    }
    $stmt->bind_param('sss', $numero_orden_compra, $numero_parte,  $descripcion );
    $stmt->execute();
    $result = $stmt->get_result();
    $item = $result->fetch_assoc();

    if (!$item) {
        echo json_encode(['success'=>false, 'error'=>'No se encontró el item correspondiente en items']);
        exit;
    }

    $idItem = $item['IdItem'];

    // Actualizar Number_PO en items
    $sql = "UPDATE items SET Number_PO = ? WHERE IdItem = ?";
    $stmt = $conexion->prepare($sql);
    if(!$stmt){
        echo json_encode(['success'=>false, 'error'=>'Error en prepare update items: '.$conexion->error]);
        exit;
    }
    $stmt->bind_param('si', $value, $idItem);

} elseif (array_key_exists($field, $camposPaletsMap)) {
    // Actualizar en palets_cargados
    $dbField = $camposPaletsMap[$field];
    $sql = "UPDATE palets_cargados SET `$dbField` = ? WHERE id = ?";
    $stmt = $conexion->prepare($sql);
    if(!$stmt){
        echo json_encode(['success'=>false, 'error'=>'Error en prepare update palets: '.$conexion->error]);
        exit;
    }
    $stmt->bind_param('si', $value, $id);

} else {
    echo json_encode(['success'=>false, 'error'=>'Campo no permitido']);
    exit;
}

// Ejecutar y devolver respuesta
if ($stmt->execute()) {
    echo json_encode(['success'=>true, 'message'=>"Campo $field actualizado"]);
} else {
    echo json_encode(['success'=>false, 'error'=>$stmt->error]);
}
