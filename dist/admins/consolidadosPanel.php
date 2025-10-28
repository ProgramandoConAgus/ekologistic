<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
include('../usuarioClass.php');
include("../con_db.php");
$IdUsuario=$_SESSION["IdUsuario"];
if(!$_SESSION["IdUsuario"]){
  header("Location: ../");
}
$usuario= new Usuario($conexion);

$user=$usuario->obtenerUsuarioPorId($IdUsuario);

?>

<!DOCTYPE html>
<html lang="en">
<!-- [Head] start -->

<head>
  <title>Liquidaciones Consolidados | Eko Logistic</title>
  <!-- [Meta] -->
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta
    name="description"
    content="Light Able admin and dashboard template offer a variety of UI elements and pages, ensuring your admin panel is both fast and effective."
  />
  <meta name="author" content="phoenixcoded" />

  <!-- [Favicon] icon -->

  <link rel="stylesheet" href="../assets/css/plugins/style.css">
  <!-- [Google Font : Public Sans] icon -->
  <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

  <!-- [Tabler Icons] https://tablericons.com -->
  <link rel="stylesheet" href="../assets/fonts/tabler-icons.min.css" >
  <!-- [Feather Icons] https://feathericons.com -->
  <link rel="stylesheet" href="../assets/fonts/feather.css" >
  <!-- [Font Awesome Icons] https://fontawesome.com/icons -->
  <link rel="stylesheet" href="../assets/fonts/fontawesome.css" >
  <!-- [Material Icons] https://fonts.google.com/icons -->
  <link rel="stylesheet" href="../assets/fonts/material.css" >
  <!-- [Template CSS Files] -->
  <link rel="stylesheet" href="../assets/css/style.css" id="main-style-link" >
  <link rel="stylesheet" href="../assets/css/style-preset.css" >
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<style>
.badge-select {
  border: none;
  min-height: 30px;
  color: #fff; 
  transition: background-color .2s;
}

.badge-select.draft { background-color: #6c757d !important; }
.badge-select.final { background-color: #0d6efd !important; }
.badge-select.total { background-color: #198754 !important; }

</style>

</head>
<!-- [Head] end -->
<!-- [Body] Start -->

<body data-pc-preset="preset-1" data-pc-sidebar-theme="light" data-pc-sidebar-caption="true" data-pc-direction="ltr" data-pc-theme="light">
  <!-- [ Pre-loader ] start -->
<div class="loader-bg">
  <div class="loader-track">
    <div class="loader-fill"></div>
  </div>
</div>
<!-- [ Pre-loader ] End -->
 <!-- [ Sidebar Menu ] start -->
<nav class="pc-sidebar">
  <div class="navbar-wrapper">
    <div class="m-header">
      <a href="../dashboard/index.html" class="b-brand text-primary">
        <!-- ========   Change your logo from here   ============ -->
        <img src="../assets/images/ekologistic.png" alt="logo image" height="50px" width="180px"/>
        
      </a>
    </div>
    <div class="navbar-content">
<ul class="pc-navbar">
    <li class="pc-item pc-caption">
      <label>Navegación</label>
    </li>
     <style>
  /* Fuerza los menús con la clase 'force-open' a mantenerse desplegados */
  li.pc-item.force-open > ul.pc-submenu {
    display: block !important;
  }

  li.pc-item.force-open > a.pc-link .pc-arrow i,
  li.pc-item.open > a.pc-link .pc-arrow i {
    transform: rotate(90deg);
    transition: transform 0.2s ease;
  }
</style>

<!-- LOGISTICA (Siempre abierto) -->
<li class="pc-item pc-hasmenu open">
  <a href="#!" class="pc-link active">
    <span class="pc-micon">
      <i class="ph-duotone ph-truck"></i>
    </span>
    <span class="pc-mtext">Logística</span>
    <span class="pc-arrow">
      <i data-feather="chevron-right"></i>
    </span>
  </a>
  <ul class="pc-submenu">
    <li class="pc-item"><a class="pc-link" href="../dashboard/panel-packinglist.php">Dashboard Packing List</a></li>
    <li class="pc-item"><a class="pc-link" href="../dashboard/index.php">Dashboard Logistic</a></li>

    <!-- Inventory como submenu abierto -->
    <li class="pc-item pc-hasmenu open force-open">
      <a href="#!" class="pc-link active">
        <span class="pc-micon">
          <i class="ph-duotone ph-archive-box"></i>
        </span>
        <span class="pc-mtext">Inventory</span>
        <span class="pc-arrow">
          <i data-feather="chevron-right"></i>
        </span>
      </a>
      <ul class="pc-submenu">
        <li class="pc-item"><a class="pc-link" href="../dashboard/transit-inventory.php">Transit Inventory</a></li>
        <li class="pc-item"><a class="pc-link" href="../dashboard/warehouse-inventory.php">WareHouse USA 1</a></li>
        <li class="pc-item"><a class="pc-link" href="../admins/warehouseUsaPanel.php">WareHouse USA 2</a></li>
        <li class="pc-item"><a class="pc-link" href="../dashboard/total-inventory.php">Total Inventory</a></li>
        <li class="pc-item"><a class="pc-link" href="../dashboard/panel-dispatch.php">Warehouse Receipt</a></li>
      </ul>
    </li>
  </ul>
</li>
    <li class="pc-item pc-hasmenu">
      <a href="#!" class="pc-link">
        <span class="pc-micon">
          <i class="ph-duotone ph-currency-dollar"></i>
        </span>
        <span class="pc-mtext">Liquidaciones</span>
        <span class="pc-arrow"><i data-feather="chevron-right"></i></span>
      </a>
      <ul class="pc-submenu">
      <li class="pc-item"><a href="../admins/exportsPanel.php" class="pc-link">Exports</a></li>
        <li class="pc-item"><a  href="../admins/importsPanel.php" class="pc-link">Imports</a></li>
        <li class="pc-item"><a href="../admins/despachosPanel.php" class="pc-link">Despachos</a></li>
        <li class="pc-item"><a href="../admins/consolidadosPanel.php" class="pc-link">Consolidados</a></li>
      </ul>
    </li>
  </ul>
    </div>
</div>

    <div class="card pc-user-card">
      <div class="card-body">
        <div class="d-flex align-items-center">
       
          <div class="flex-grow-1 ms-3">
            <div class="dropdown">
              <a href="#" class="arrow-none dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false" data-bs-offset="0,20">
                <div class="d-flex align-items-center">
                  <div class="flex-grow-1 me-2">
                    <h6 class="mb-0"><?=ucfirst($user['nombre'])?> <?=ucfirst($user['apellido'])?></h6>
                    <small>Administrador</small>
                  </div>
                  <div class="flex-shrink-0">
                    <div class="btn btn-icon btn-link-secondary avtar">
                      <i class="ph-duotone ph-windows-logo"></i>    
                    </div>
                  </div>
                </div>
              </a>
              <div class="dropdown-menu">
                <ul>
                  
                  <li>
                    <a class="pc-user-links" href="../pages/login-v1.php">
                      <i class="ph-duotone ph-power"></i>
                      <span>Cerrar Sesión</span>
                    </a>
                  </li>
                </ul>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</nav>
<!-- [ Sidebar Menu ] end -->
 <!-- [ Header Topbar ] start -->
<header class="pc-header">
  <div class="header-wrapper"> <!-- [Mobile Media Block] start -->
<div class="me-auto pc-mob-drp">
  <ul class="list-unstyled">
    <!-- ======= Menu collapse Icon ===== -->
    <li class="pc-h-item pc-sidebar-collapse">
      <a href="#" class="pc-head-link ms-0" id="sidebar-hide">
        <i class="ti ti-menu-2"></i>
      </a>
    </li>
    <li class="pc-h-item pc-sidebar-popup">
      <a href="#" class="pc-head-link ms-0" id="mobile-collapse">
        <i class="ti ti-menu-2"></i>
      </a>
    </li>
   
    
  </ul>
</div>
<!-- [Mobile Media Block end] -->
<div class="ms-auto">
    <ul class="list-unstyled">
    <li class="dropdown pc-h-item">
      <a class="pc-head-link dropdown-toggle arrow-none me-0" data-bs-toggle="dropdown" href="#" role="button"
        aria-haspopup="false" aria-expanded="false">
        <i class="ph-duotone ph-sun-dim"></i>
      </a>
      <div class="dropdown-menu dropdown-menu-end pc-h-dropdown">
        <a href="#!" class="dropdown-item" onclick="layout_change('dark')">
          <i class="ph-duotone ph-moon"></i>
          <span>Noche</span>
        </a>
        <a href="#!" class="dropdown-item" onclick="layout_change('light')">
          <i class="ph-duotone ph-sun-dim"></i>
          <span>Dia</span>
        </a>
        <a href="#!" class="dropdown-item" onclick="layout_change_default()">
          <i class="ph-duotone ph-cpu"></i>
          <span>Estandar</span>
        </a>
      </div>
    </li>
    <li class="dropdown pc-h-item">
      <a class="pc-head-link dropdown-toggle arrow-none me-0" data-bs-toggle="dropdown" href="#" role="button"
        aria-haspopup="false" aria-expanded="false">
        <i class="ph-duotone ph-bell"></i>
      </a>
      <div class="dropdown-menu dropdown-notification dropdown-menu-end pc-h-dropdown">
        <div class="dropdown-header d-flex align-items-center justify-content-between">
          <h5 class="m-0">Avisos</h5>
        </div>
        <div class="dropdown-body text-wrap header-notification-scroll position-relative"
          style="max-height: calc(100vh - 235px)">
          <ul class="list-group list-group-flush">
            
            <li class="list-group-item">
              <div class="d-flex">
                <div class="flex-shrink-0">
                  <div class="avtar avtar-s bg-light-info">
                    <i class="ph-duotone ph-notebook f-18"></i>
                  </div>
                </div>
                <div class="flex-grow-1 ms-3">
                  <div class="d-flex">
                    <div class="flex-grow-1 me-3 position-relative">
                      <h6 class="mb-0 text-truncate">Recientes</h6>
                    </div>
                    <div class="flex-shrink-0">
                      <span class="text-sm">Hace unos minutos</span>
                    </div>
                  </div>
                  <p class="position-relative mt-1 mb-2">Se cambio el estado del contenedor N ° 12345.</p>
                </div>
              </div>
            </li>
          </ul>
        </div>

      </div>
    </li>
    
  </ul>
</div> </div>
</header>
<!-- [ Header ] end -->



  <!-- [ Main Content ] start -->
  <div class="pc-container">
    <div class="pc-content">
      <!-- [ breadcrumb ] start -->
     <div class="page-header">
  <div class="page-block">
    <div class="row align-items-center">
      <div class="col-md-12">
        <ul class="breadcrumb">
          <li class="breadcrumb-item"><a href="../dashboard/index.html">Inicio</a></li>
          <li class="breadcrumb-item"><a href="javascript: void(0)">Liquidaciones</a></li>
          <li class="breadcrumb-item" aria-current="page">Consolidados</li>
        </ul>
      </div>
      <div class="col-md-12">
        <div class="page-header-title">
          <h2 class="mb-0">Panel Consolidados</h2>
        </div>
      </div>
    </div>
  </div>
</div>

      <!-- [ breadcrumb ] end -->

      <!-- [ Main Content ] start -->
      <div class="row">
     <div class="col-12">
  <div class="card table-card">
    <div class="card-body pt-3">
      <div class="d-flex justify-content-end mb-3">
        <a class="text-white" href="#"><button type="button" class="btn btn-success text-white me-3" data-bs-toggle="modal" data-bs-target="#consolidadoModal">
  Generar Consolidado
</button></a>
      </div>
      <div class="table-responsive">
        <table class="table table-hover">
          <thead>
            <tr>
              <th>Origen</th>
                <th>Num_OP</th>
              <th>Booking</th>
              <th>Number Commercial Invoice</th>
              <th>Creation Date</th>
              <th>Status</th>
              <th>Action</th>
            </tr>
          </thead>
        <tbody>
        <?php
$query = "
SELECT 
  e.ExportsID AS ID,
  e.Booking_BK,
  e.Number_Commercial_Invoice,
  e.status,
  e.creation_date,
  c.num_op,
  'exports' AS origen
FROM exports e
LEFT JOIN (
  SELECT i.Number_Commercial_Invoice, ct.Booking_BK, MIN(ct.num_op) AS num_op
  FROM items i
  INNER JOIN container ct ON i.idContainer = ct.IdContainer
  GROUP BY i.Number_Commercial_Invoice, ct.Booking_BK
) c ON e.Number_Commercial_Invoice = c.Number_Commercial_Invoice
   AND e.Booking_BK = c.Booking_BK
WHERE e.status IN (2,3)

UNION ALL

SELECT 
  i.ImportsID AS ID,
  i.Booking_BK,
  i.Number_Commercial_Invoice,
  i.status,
  i.creation_date,
  c.num_op,
  'imports' AS origen
FROM imports i
LEFT JOIN (
  SELECT i.Number_Commercial_Invoice, ct.Booking_BK, MIN(ct.num_op) AS num_op
  FROM items i
  INNER JOIN container ct ON i.idContainer = ct.IdContainer
  GROUP BY i.Number_Commercial_Invoice, ct.Booking_BK
) c ON i.Number_Commercial_Invoice = c.Number_Commercial_Invoice
   AND i.Booking_BK = c.Booking_BK
WHERE i.status IN (2,3)

UNION ALL

SELECT 
  d.DespachoID AS ID,
  d.Booking_BK,
  d.Number_Commercial_Invoice,
  d.status,
  d.creation_date,
  c.num_op,
  'despacho' AS origen
FROM despacho d
LEFT JOIN (
  SELECT i.Number_Commercial_Invoice, ct.Booking_BK, MIN(ct.num_op) AS num_op
  FROM items i
  INNER JOIN container ct ON i.idContainer = ct.IdContainer
  GROUP BY i.Number_Commercial_Invoice, ct.Booking_BK
) c ON d.Number_Commercial_Invoice = c.Number_Commercial_Invoice
   AND d.Booking_BK = c.Booking_BK
WHERE d.status IN (2,3)

ORDER BY creation_date DESC;

";



$result = $conexion->query($query);

if ($result && $result->num_rows > 0) {
  while($row = $result->fetch_assoc()) { 
    
    $fechaOriginal = $row['creation_date'];
    $fecha = date('d/m/Y', strtotime($fechaOriginal));
    $hora  = date('h:i A', strtotime($fechaOriginal));
?>
<tr>
    <td><?= htmlspecialchars($row['origen']) ?></td> <!-- origen exports/imports -->
      <td><?= htmlspecialchars($row['num_op']) ?></td>
  <td><?= htmlspecialchars($row['Booking_BK']) ?></td>
  <td><?= htmlspecialchars($row['Number_Commercial_Invoice']) ?></td>
  <td><?= $fecha ?> <span class="text-muted text-sm d-block"><?= $hora ?></span></td>



  <!-- Celda de Estado -->
  <td>
    <select 
      class="badge-select badge" 
      data-id="<?= htmlspecialchars($row['ID']) ?>" 
      data-origen="<?= htmlspecialchars($row['origen']) ?>"
      style="
        appearance: none;
        min-width: 160px;   /* ancho mínimo para que entre 'Liquidacion Total' */
        width: auto;        /* ajusta al contenido */
        white-space: nowrap;/* evita que el texto se quiebre */
      ">
      <?php
        $queryselect  = "SELECT IdEstados, nombre FROM estadosliquidacion";
        $resultselect = $conexion->query($queryselect);
        while ($row2 = $resultselect->fetch_assoc()) {
          if ($row2['IdEstados'] == 4) continue;
          $isSel = ($row['status'] == $row2['IdEstados']) ? ' selected' : '';
      ?>
        <option value="<?= $row2['IdEstados'] ?>"<?= $isSel ?>>
          <?= $row2['nombre'] ?>
        </option>
      <?php } ?>
    </select>
  </td>

  <!-- Celda de Detalle -->
  <td>
    <?php 
      // URL de detalle según el origen
      if ($row['origen'] === 'exports'): 
        $detalleUrl = "../application/detalleLiquidacionExport.php?ExportID={$row['ID']}";
      elseif ($row['origen'] === 'imports'):
        $detalleUrl = "../application/detalleLiquidacionImport.php?ImportID={$row['ID']}";
      elseif ($row['origen'] === 'despacho'):
        $detalleUrl = "../application/detalleLiquidacionDespacho.php?DespachoID={$row['ID']}";
      else:
        $detalleUrl = "#";
      endif;
    ?>
    <a href="<?= $detalleUrl ?>" class="text-primary me-2">
      <i class="ti ti-eye"></i>
    </a>
  </td>

</tr>

<?php
  }
} else {
  echo "<tr><td colspan='7' class='text-center'>No hay registros disponibles.</td></tr>";
}
?>
</tbody>

        </table>
      </div>
    </div>
  </div>


  <!-- Modal para generar consolidado -->
   <div class="modal fade" id="consolidadoModal" tabindex="-1" aria-labelledby="consolidadoModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="consolidadoModalLabel">Seleccionar Número de Operación</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
     <div class="modal-body">
  <form method="post">
    <div class="mb-3">
      <label for="num_op" class="form-label">Número de Operación</label>
      <select class="form-select" id="num_op" name="num_op" required>
        <option value="">Seleccione una opción</option>
        <?php
        $queryNumOp = "
          SELECT DISTINCT c.num_op
          FROM container c
          WHERE EXISTS (
            SELECT 1 FROM exports e 
            WHERE e.Booking_BK = c.Booking_BK AND e.status = 2
          )
          AND EXISTS (
            SELECT 1 FROM imports i 
            WHERE i.Booking_BK = c.Booking_BK AND i.status = 2
          )
        ";

        $resultNumOp = $conexion->query($queryNumOp);
        if ($resultNumOp && $resultNumOp->num_rows > 0) {
          while ($rowOp = $resultNumOp->fetch_assoc()) {
            echo '<option value="' . htmlspecialchars($rowOp['num_op']) . '">' . htmlspecialchars($rowOp['num_op']) . '</option>';
          }
        } else {
          echo '<option disabled>No hay números de operación disponibles</option>';
        }
        ?>
      </select>
    </div>

    <!-- Contenedor donde se mostrarán los resultados -->
    <div id="resultados-operacion" class="mt-3"></div>

    <div class="modal-footer">
      <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
      <button type="button" id="btnGenerarExcel" class="btn btn-primary">
        Generar Excel
      </button>
    </div>
  </form>
</div>

<script>
document.getElementById('num_op').addEventListener('change', function() {
  const numOp = this.value;
  const contenedor = document.getElementById('resultados-operacion');

  if (!numOp) {
    contenedor.innerHTML = '';
    return;
  }

  fetch('../api/imports/buscarOperacion.php', {
    method: 'POST',
    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
    body: 'num_op=' + encodeURIComponent(numOp)
  })
  .then(res => res.json())
  .then(data => {
    if (data.success) {
      let html = `<h5 class="fw-bold mb-3">Liquidaciones Involucradas</h5>
      <table class="table table-bordered table-hover">
        <thead>
          <tr>
            <th>Tipo</th>
            <th>Booking</th>
            <th>Invoice</th>
            <th>Fecha creación</th>
          </tr>
        </thead>
        <tbody>`;

      data.resultados.forEach(item => {
        // Formatear fecha a dd/mm/yyyy
        const fecha = new Date(item.creation_date);
        const fechaFormateada = ('0' + fecha.getDate()).slice(-2) + '/'
                              + ('0' + (fecha.getMonth() + 1)).slice(-2) + '/'
                              + fecha.getFullYear();

        html += `
          <tr>
            <td>${item.origen}</td>
            <td>${item.Booking_BK}</td>
            <td>${item.Number_Commercial_Invoice}</td>
            <td>${fechaFormateada}</td>
          </tr>`;
      });

      html += `</tbody></table>`;
      contenedor.innerHTML = html;

    } else {
      contenedor.innerHTML = `<div class="alert alert-warning">${data.message}</div>`;
    }
  })
  .catch(err => {
    console.error(err);
    contenedor.innerHTML = `<div class="alert alert-danger">Error al consultar las liquidaciones.</div>`;
  });
});

</script>

<!-- 
<script>
  async function generarExcelConsolidado(num_op) {

     e.preventDefault();
  if (!num_op) {
    alert("Selecciona un número de operación.");
    return;
  }

  try {
    const resp = await fetch('../api/despacho/obtenerLiquidacionesConItems.php', {
  method: 'POST',
  headers: {'Content-Type': 'application/json'},
  body: JSON.stringify({ num_op })
});

    const data = await resp.json();

    if (!data.success) {
    console.error("Error desde PHP:", data.message, data.error);
    alert(`Error: ${data.message}\n${data.error || ''}`);
    return;
  }
    

    if (!data.success) {
      alert(data.message || 'Error al obtener liquidaciones');
      return;
    }

    const wb = XLSX.utils.book_new();

    for (const liq of data.liquidaciones) {
      const ws_data = [];
      ws_data.push([`Origen: ${liq.origen.toUpperCase()}`]);
      ws_data.push([`ID: ${liq.id}`, `Booking BK: ${liq.booking}`, `Invoice: ${liq.invoice}`, `Fecha: ${liq.fecha}`]);
      ws_data.push([]);
      ws_data.push(['Descripción', 'Cantidad', 'Valor Unitario', 'Valor Total']);

      let subtotal = 0;
      for (const item of liq.items) {
        ws_data.push([
          item.NombreItems,
          item.Cantidad,
          item.ValorUnitario,
          item.ValorTotal,
        ]);
        subtotal += Number(item.ValorTotal) || 0;
      }

      ws_data.push([]);
      ws_data.push(['Subtotal', '', '', subtotal]);

      const ws = XLSX.utils.aoa_to_sheet(ws_data);
      XLSX.utils.book_append_sheet(wb, ws, `${liq.origen}_${liq.id}`);
    }

    XLSX.writeFile(wb, `consolidado_liquidaciones_${num_op}.xlsx`);

  } catch (error) {
  console.error("Error JS o de red:", error);
}
}

</script> -->

    </div>
  </div>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
<script>
document.getElementById('btnGenerarExcel').addEventListener('click', async () => {
  const num_op = document.getElementById('num_op').value;
  if (!num_op) return alert('Selecciona un número de operación.');

  try {
    const resp = await fetch('../api/despacho/obtenerLiquidacionesConItems.php', {
      method: 'POST',
      headers: {'Content-Type': 'application/json'},
      body: JSON.stringify({ num_op })
    });
    const { success, liquidaciones, message } = await resp.json();
    if (!success) return alert(`Error: ${message}`);

    const wb = XLSX.utils.book_new();
    const tipos = ['exports', 'imports', 'despacho'];

    // Guardamos los subtotales por tipo para luego calcular el total general
    const subtotales = {};

    // 1️⃣ Hojas individuales
    tipos.forEach(tipo => {
      const arr = liquidaciones.filter(l => l.origen === tipo);
      if (!arr.length) return;
      const liq     = arr[0];
      const booking = liq.Booking_BK;
      const invoice = liq.Number_Commercial_Invoice;
      const fecha   = liq.fecha;
      const id      = liq.id;

      const datos = [];
      datos.push([`${tipo.toUpperCase()} • ID ${id}`]);
      datos.push([`Booking:`, booking, `Invoice:`, invoice, `Fecha:`, fecha]);
      datos.push([]);
      datos.push(['Descripción','Cantidad','Valor Unitario','Valor Total']);

      let subtotal = 0;
      liq.items.forEach(it => {
        const total = Number(it.ValorTotal) || 0;
        subtotal += total;
        datos.push([it.NombreItems, it.Cantidad, it.ValorUnitario, total]);
      });

      datos.push([]);
      datos.push(['Subtotal','','', subtotal.toLocaleString('es-AR',{minimumFractionDigits:2})]);

      subtotales[tipo] = subtotal;

      const ws = XLSX.utils.aoa_to_sheet(datos);
      XLSX.utils.book_append_sheet(wb, ws, tipo.slice(0,31));
    });

    // 2️⃣ Hoja “Resumen” con los 3 bloques
    const resumen = [];
    resumen.push([`RESUMEN CONSOLIDADO • Operación ${num_op}`], []);

    let totalGeneral = 0;

    tipos.forEach(tipo => {
      const arr = liquidaciones.filter(l => l.origen === tipo);
      if (!arr.length) return;
      const liq     = arr[0];
      const booking = liq.Booking_BK;
      const invoice = liq.Number_Commercial_Invoice;
      const fecha   = liq.fecha;
      const id      = liq.id;

      resumen.push([tipo.toUpperCase()], []);
      resumen.push([`ID ${id} — Booking: ${booking} — Invoice: ${invoice} — Fecha: ${fecha}`]);
      resumen.push(['Descripción','Cantidad','Valor Unitario','Valor Total']);

      let subtotal = 0;
      liq.items.forEach(it => {
        const total = Number(it.ValorTotal) || 0;
        subtotal += total;
        resumen.push([it.NombreItems, it.Cantidad, it.ValorUnitario, total]);
      });

      resumen.push(['Subtotal','','', subtotal.toLocaleString('es-AR',{minimumFractionDigits:2})], []);
      totalGeneral += subtotal;
    });

    // ➕ Agregamos total general
    resumen.push([]);
    resumen.push(['TOTAL GENERAL','','', totalGeneral.toLocaleString('es-AR',{minimumFractionDigits:2})]);

    const wsRes = XLSX.utils.aoa_to_sheet(resumen);
    XLSX.utils.book_append_sheet(wb, wsRes, 'Resumen');

    // 3️⃣ Descargar el archivo Excel
    XLSX.writeFile(wb, `consolidado_liquidaciones_${num_op}.xlsx`);

    // 4️⃣ Actualizar estado (igual que antes)
    try {
      const resp = await fetch('../api/despacho/actualizarEstadoConsolidado.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ num_op })
      });
      const data = await resp.json();
      if (!data.success) {
        throw new Error(data.message || 'Error desconocido');
      }
      Swal.fire('¡Listo!','Los estados se han marcado como “Total”.','success');
      window.location.reload();
    } catch (err) {
      console.error(err);
      Swal.fire('Error','No se pudieron actualizar los estados: ' + err.message,'error');
    }

  } catch (err) {
    console.error(err);
    alert('Error al generar el archivo.');
  }
});

</script>

</div>


      </div>
      <!-- [ Main Content ] end -->
    </div>
  </div>
  <!-- [ Main Content ] end -->
  <footer class="pc-footer">
      <div class="footer-wrapper container-fluid">
        <div class="row">
          <div class="col-sm-6 my-1">
            <p class="m-0">Software <a style="color:#afc97c"> EKO LOGISTIC</a></p>
          </div>
          <div class="col-sm-6 ms-auto my-1">
            <ul class="list-inline footer-link mb-0 justify-content-sm-end d-flex">
              <li class="list-inline-item"><a>Inicio</a></li>
              <li class="list-inline-item"><a>Documentación</a></li>
              <li class="list-inline-item"><a>Soporte</a></li>
            </ul>
          </div>
        </div>
      </div>
    </footer>
 <!-- Required Js -->
<script src="../assets/js/plugins/popper.min.js"></script>
<script src="../assets/js/plugins/simplebar.min.js"></script>
<script src="../assets/js/plugins/bootstrap.min.js"></script>
<script src="../assets/js/fonts/custom-font.js"></script>
<script src="../assets/js/pcoded.js"></script>
<script src="../assets/js/plugins/feather.min.js"></script>

<!-- Cambiar colores select -->
<script>
document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('.badge-select').forEach(select => {
    const applyClass = () => {
      select.classList.remove('draft','final','total');
      if (select.value === '1') select.classList.add('draft');
      else if (select.value === '2') select.classList.add('final');
      else if (select.value === '3') select.classList.add('total');
    };
    select.addEventListener('change', applyClass);
    applyClass();  
  });
});
</script>



<!--Borrar Export-->
<script>
document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('.btn-eliminar').forEach(btn => {
    btn.addEventListener('click', () => {
      const exportID = btn.getAttribute('data-id');
      if (!exportID) {
        console.error("No se encontró el ExportID en el botón.");
        return;
      }

      Swal.fire({
        title: '¿Estás seguro?',
        text: "Por favor, ingresa la razón por la que quieres eliminar esta exportación:",
        icon: 'warning',
        input: 'text',
        inputPlaceholder: 'Razón de la eliminación',
        inputAttributes: {
          'aria-label': 'Razón de la eliminación'
        },
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
        inputValidator: (value) => {
          if (!value) {
            return '¡Necesitas escribir una razón!';
          }
        }
      }).then((result) => {
        if (result.isConfirmed) {
          const reason = result.value;

          fetch('../api/exports/borrarliquidacionexport.php', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: `ExportID=${encodeURIComponent(exportID)}&reason=${encodeURIComponent(reason)}`
          })
          .then(res => res.text())
          .then(data => {
            if (data.trim() === 'OK') {
              Swal.fire(
                'Eliminado',
                'La exportación fue eliminada correctamente.',
                'success'
              ).then(() => {
                window.location.reload();
              });
            } else {
              Swal.fire('Error', data, 'error');
            }
          })
          .catch(err => {
            console.error(err);
            Swal.fire('Error', 'Ocurrió un error inesperado.', 'error');
          });
        }
      });
    });
  });
});
</script>

<script>
document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('.badge-select').forEach(select => {
    select.addEventListener('change', () => {
      const newStatus = select.value;
      const id        = select.dataset.id;
      const origen    = select.dataset.origen;  // 'exports', 'imports' o 'despacho'

      // Elegimos la URL y el nombre de parámetro según el origen
      let url, param;
      switch (origen) {
        case 'exports':
          url   = '../api/exports/actualizarEstado.php';
          param = 'ExportID';
          break;
        case 'imports':
          url   = '../api/imports/actualizarEstado.php';
          param = 'ImportID';
          break;
        case 'despacho':
          url   = '../api/despacho/actualizarEstado.php';
          param = 'DespachoID';
          break;
        default:
          console.error('Origen desconocido:', origen);
          return;
      }

      Swal.fire({
        title: 'Actualizando estado…',
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading()
      });

      // Montamos el body con el parámetro correcto
      const body = `${param}=${encodeURIComponent(id)}&status=${encodeURIComponent(newStatus)}`;

      fetch(url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body
      })
      .then(res => res.text())
      .then(text => {
        Swal.close();
        if (text.trim() === 'OK') {
          Swal.fire('¡Listo!', 'Estado actualizado correctamente.', 'success')
            .then(() => window.location.reload());
        } else {
          Swal.fire('Error', text, 'error');
        }
      })
      .catch(err => {
        Swal.close();
        console.error(err);
        Swal.fire('Error', 'Ocurrió un error al conectar con el servidor.', 'error');
      });
    });
  });
});
</script>


<script>layout_change('light');</script>




<script>layout_sidebar_change('light');</script>



<script>change_box_container('false');</script>


<script>layout_caption_change('true');</script>




<script>layout_rtl_change('false');</script>


<script>preset_change("preset-1");</script>

  <script type="module">
    import {DataTable} from "../assets/js/plugins/module.js"
    window.dt = new DataTable("#pc-dt-simple");
  </script>
  <div class="offcanvas border-0 pct-offcanvas offcanvas-end" tabindex="-1" id="offcanvas_pc_layout">
    <div class="offcanvas-header justify-content-between">
      <h5 class="offcanvas-title">Settings</h5>
      <button type="button" class="btn btn-icon btn-link-danger" data-bs-dismiss="offcanvas" aria-label="Close"><i
          class="ti ti-x"></i></button>
    </div>
    <div class="pct-body customizer-body">
      <div class="offcanvas-body py-0">
        <ul class="list-group list-group-flush">
          <li class="list-group-item">
            <div class="pc-dark">
              <h6 class="mb-1">Theme Mode</h6>
              <p class="text-muted text-sm">Choose light or dark mode or Auto</p>
              <div class="row theme-color theme-layout">
                <div class="col-4">
                  <div class="d-grid">
                    <button class="preset-btn btn active" data-value="true" onclick="layout_change('light');">
                      <span class="btn-label">Light</span>
                      <span class="pc-lay-icon"><span></span><span></span><span></span><span></span></span>
                    </button>
                  </div>
                </div>
                <div class="col-4">
                  <div class="d-grid">
                    <button class="preset-btn btn" data-value="false" onclick="layout_change('dark');">
                      <span class="btn-label">Dark</span>
                      <span class="pc-lay-icon"><span></span><span></span><span></span><span></span></span>
                    </button>
                  </div>
                </div>
                <div class="col-4">
                  <div class="d-grid">
                    <button class="preset-btn btn" data-value="default" onclick="layout_change_default();"
                      data-bs-toggle="tooltip"
                      title="Automatically sets the theme based on user's operating system's color scheme.">
                      <span class="btn-label">Default</span>
                      <span class="pc-lay-icon d-flex align-items-center justify-content-center">
                        <i class="ph-duotone ph-cpu"></i>
                      </span>
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </li>
          <li class="list-group-item">
            <h6 class="mb-1">Sidebar Theme</h6>
            <p class="text-muted text-sm">Choose Sidebar Theme</p>
            <div class="row theme-color theme-sidebar-color">
              <div class="col-6">
                <div class="d-grid">
                  <button class="preset-btn btn" data-value="true" onclick="layout_sidebar_change('dark');">
                    <span class="btn-label">Dark</span>
                    <span class="pc-lay-icon"><span></span><span></span><span></span><span></span></span>
                  </button>
                </div>
              </div>
              <div class="col-6">
                <div class="d-grid">
                  <button class="preset-btn btn active" data-value="false" onclick="layout_sidebar_change('light');">
                    <span class="btn-label">Light</span>
                    <span class="pc-lay-icon"><span></span><span></span><span></span><span></span></span>
                  </button>
                </div>
              </div>
            </div>
          </li>
          <li class="list-group-item">
            <h6 class="mb-1">Accent color</h6>
            <p class="text-muted text-sm">Choose your primary theme color</p>
            <div class="theme-color preset-color">
              <a href="#!" class="active" data-value="preset-1"><i class="ti ti-check"></i></a>
              <a href="#!" data-value="preset-2"><i class="ti ti-check"></i></a>
              <a href="#!" data-value="preset-3"><i class="ti ti-check"></i></a>
              <a href="#!" data-value="preset-4"><i class="ti ti-check"></i></a>
              <a href="#!" data-value="preset-5"><i class="ti ti-check"></i></a>
              <a href="#!" data-value="preset-6"><i class="ti ti-check"></i></a>
              <a href="#!" data-value="preset-7"><i class="ti ti-check"></i></a>
              <a href="#!" data-value="preset-8"><i class="ti ti-check"></i></a>
              <a href="#!" data-value="preset-9"><i class="ti ti-check"></i></a>
              <a href="#!" data-value="preset-10"><i class="ti ti-check"></i></a>
            </div>
          </li>
          <li class="list-group-item">
            <h6 class="mb-1">Sidebar Caption</h6>
            <p class="text-muted text-sm">Sidebar Caption Hide/Show</p>
            <div class="row theme-color theme-nav-caption">
              <div class="col-6">
                <div class="d-grid">
                  <button class="preset-btn btn active" data-value="true" onclick="layout_caption_change('true');">
                    <span class="btn-label">Caption Show</span>
                    <span
                      class="pc-lay-icon"><span></span><span></span><span><span></span><span></span></span><span></span></span>
                  </button>
                </div>
              </div>
              <div class="col-6">
                <div class="d-grid">
                  <button class="preset-btn btn" data-value="false" onclick="layout_caption_change('false');">
                    <span class="btn-label">Caption Hide</span>
                    <span
                      class="pc-lay-icon"><span></span><span></span><span><span></span><span></span></span><span></span></span>
                  </button>
                </div>
              </div>
            </div>
          </li>
          <li class="list-group-item">
            <div class="pc-rtl">
              <h6 class="mb-1">Theme Layout</h6>
              <p class="text-muted text-sm">LTR/RTL</p>
              <div class="row theme-color theme-direction">
                <div class="col-6">
                  <div class="d-grid">
                    <button class="preset-btn btn active" data-value="false" onclick="layout_rtl_change('false');">
                      <span class="btn-label">LTR</span>
                      <span class="pc-lay-icon"><span></span><span></span><span></span><span></span></span>
                    </button>
                  </div>
                </div>
                <div class="col-6">
                  <div class="d-grid">
                    <button class="preset-btn btn" data-value="true" onclick="layout_rtl_change('true');">
                      <span class="btn-label">RTL</span>
                      <span class="pc-lay-icon"><span></span><span></span><span></span><span></span></span>
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </li>
          <li class="list-group-item pc-box-width">
            <div class="pc-container-width">
              <h6 class="mb-1">Layout Width</h6>
              <p class="text-muted text-sm">Choose Full or Container Layout</p>
              <div class="row theme-color theme-container">
                <div class="col-6">
                  <div class="d-grid">
                    <button class="preset-btn btn active" data-value="false" onclick="change_box_container('false')">
                      <span class="btn-label">Full Width</span>
                      <span class="pc-lay-icon"><span></span><span></span><span></span><span><span></span></span></span>
                    </button>
                  </div>
                </div>
                <div class="col-6">
                  <div class="d-grid">
                    <button class="preset-btn btn" data-value="true" onclick="change_box_container('true')">
                      <span class="btn-label">Fixed Width</span>
                      <span class="pc-lay-icon"><span></span><span></span><span></span><span><span></span></span></span>
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </li>
          <li class="list-group-item">
            <div class="d-grid">
              <button class="btn btn-light-danger" id="layoutreset">Reset Layout</button>
            </div>
          </li>
        </ul>
      </div>
    </div>
  </div>
</body>
<!-- [Body] end -->

</html>

<?php


?>