<?php
header('Content-Type: application/json');
include('../../con_db.php'); 

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

try {
    $sql = "
        SELECT DISTINCT codigo_despacho
        FROM palets_cargados
        WHERE codigo_despacho IS NOT NULL
          AND codigo_despacho != ''
    ";

    $result = $conexion->query($sql);
    $codigos = [];

    while ($row = $result->fetch_assoc()) {
        $codigos[] = $row['codigo_despacho'];
    }

    echo json_encode([
        'success' => true,
        'codigos' => $codigos
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Error: ' . $e->getMessage()
    ]);
}
