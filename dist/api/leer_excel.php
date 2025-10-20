<?php
session_start();
require_once '../con_db.php';
require_once '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

header('Content-Type: application/json');

try {
    if (!isset($_SESSION['IdUsuario'])) {
        throw new Exception('Acceso no autorizado');
    }
    
    $excelPath = $_GET['path'] ?? '';
    $packingId = $_GET['packingId'] ?? 0;

    // Validar permisos con packing_list
    $stmt = $conexion->prepare("SELECT IdUsuario FROM packing_list WHERE IdPackingList = ?");
    $stmt->bind_param("i", $packingId);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 0 ) {
        throw new Exception('No tienes permisos para editar este registro');
    }

    // Verificar existencia del archivo
    if (!file_exists($excelPath)) {
        throw new Exception('Archivo no encontrado');
    }

    // Leer Excel
    $spreadsheet = IOFactory::load($excelPath);
    $sheet = $spreadsheet->getActiveSheet();
    
    $data = [];
    foreach ($sheet->getRowIterator() as $row) {
        $rowData = [];
        foreach ($row->getCellIterator() as $cell) {
            $rowData[] = $cell->getFormattedValue();
        }
        $data[] = $rowData;
    }

    echo json_encode($data);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
