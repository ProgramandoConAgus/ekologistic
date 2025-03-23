<?php
// Incluimos el autoload de Composer
require '../dist/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

// Nombre del archivo Excel a leer
$inputFileName = 'archivo.xlsx';

try {
    // Cargamos el archivo Excel
    $spreadsheet = IOFactory::load($inputFileName);
} catch (\PhpOffice\PhpSpreadsheet\Reader\Exception $e) {
    die('Error al leer el archivo: ' . $e->getMessage());
}

// Obtenemos la hoja activa
$worksheet = $spreadsheet->getActiveSheet();

// Convertimos la hoja completa a un arreglo
$rows = $worksheet->toArray();

// Iniciamos la salida HTML
echo "<!DOCTYPE html>";
echo "<html lang='es'>";
echo "<head>";
echo "<meta charset='UTF-8'>";
echo "<title>Tabla desde Excel</title>";
echo "</head>";
echo "<body>";

echo "<h1>Tabla generada desde Excel</h1>";
echo "<table border='1' cellspacing='0' cellpadding='5'>";

// Recorremos las filas
foreach ($rows as $rowIndex => $row) {
    // Si quieres manejar cabecera en la primera fila, puedes hacer algo especial
    // cuando $rowIndex === 0, por ejemplo: <thead>... o <th>...
    // Aquí simplemente imprimimos todo como <tr><td>...
    echo "<tr>";
    foreach ($row as $cell) {
        echo "<td>" . htmlspecialchars($cell) . "</td>";
    }
    echo "</tr>";
}

echo "</table>";
echo "</body>";
echo "</html>";
?>
