<?php
session_start();
include('../usuarioClass.php');
include("../con_db.php");
$IdUsuario = $_SESSION["IdUsuario"] ?? null;
if (!$IdUsuario) {
    header("Location: ../");
    exit;
}
$usuario = new Usuario($conexion);
$user = $usuario->obtenerUsuarioPorId($IdUsuario);

$sql = "SELECT
    d.id,
    c.num_op AS NUM_OP,
    c.Number_Container AS Number_Container,
    c.Booking_BK,
    d.numero_lote AS Lot_Number,
    d.fecha_entrada AS Entry_Date,
    d.fecha_salida AS Out_Date,
    c.Number_Commercial_Invoice AS Number_Commercial_Invoice,
    d.numero_parte AS Code_Product_EC,
    i.Number_PO AS Number_PO,
    d.descripcion AS Description,
    d.cantidad AS Qty,
    d.valor_unitario AS Unit_Value,
    d.valor AS Value,
    d.unidad AS Unit,
    d.longitud_in AS Length_in,
    d.ancho_in AS Broad_in,
    d.altura_in AS Height_in,
    d.peso_lb AS Weight_lb,
    d.estado AS Status
FROM container c
INNER JOIN dispatch d ON c.Number_Commercial_Invoice = d.numero_factura
   AND c.Number_Container = d.notas
INNER JOIN items i ON i.idContainer = c.idContainer
WHERE d.estado = 'Cargado'
ORDER BY c.num_op, d.numero_parte";

$result = $conexion->query($sql);
if (!$result) {
    die("Error en la consulta: " . $conexion->error);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Pre Warehouse USA - Carga Manual</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" />
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" />
</head>
<body>
<div class="container my-4">
    <h2>Pre Warehouse USA - Carga Manual</h2>
    <div class="table-responsive">
        <table id="pre-warehouse-table" class="table table-striped">
            <thead>
                <tr>
                    <th>NUM OP</th>
                    <th>Container</th>
                    <th>Booking</th>
                    <th>PO Number</th>
                    <th>Entry Date</th>
                    <th>Dispatch Date</th>
                    <th>Code Product EC</th>
                    <th>Description</th>
                    <th>Qty</th>
                    <th>Unit Value</th>
                    <th>Value</th>
                    <th>Unit</th>
                    <th>Length (in)</th>
                    <th>Broad (in)</th>
                    <th>Height (in)</th>
                    <th>Weight (lb)</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['NUM_OP']) ?></td>
                    <td><?= htmlspecialchars($row['Number_Container']) ?></td>
                    <td><?= htmlspecialchars($row['Booking_BK']) ?></td>
                    <td><?= htmlspecialchars($row['Number_PO']) ?></td>
                    <td><?= htmlspecialchars($row['Entry_Date']) ?></td>
                    <td><?= htmlspecialchars($row['Out_Date']) ?></td>
                    <td><?= htmlspecialchars($row['Code_Product_EC']) ?></td>
                    <td><?= htmlspecialchars($row['Description']) ?></td>
                    <td><?= htmlspecialchars($row['Qty']) ?></td>
                    <td><?= htmlspecialchars($row['Unit_Value']) ?></td>
                    <td><?= htmlspecialchars($row['Value']) ?></td>
                    <td><?= htmlspecialchars($row['Unit']) ?></td>
                    <td><?= htmlspecialchars($row['Length_in']) ?></td>
                    <td><?= htmlspecialchars($row['Broad_in']) ?></td>
                    <td><?= htmlspecialchars($row['Height_in']) ?></td>
                    <td><?= htmlspecialchars($row['Weight_lb']) ?></td>
                    <td><?= htmlspecialchars($row['Status']) ?></td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
<script>
$(function () {
    $('#pre-warehouse-table').DataTable();
});
</script>
</body>
</html>
