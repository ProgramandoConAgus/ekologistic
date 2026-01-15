<?php

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
session_start();
include('../usuarioClass.php');
include("../con_db.php");

$IdUsuario = $_SESSION["IdUsuario"];
$usuario = new Usuario($conexion);
$user = $usuario->obtenerUsuarioPorId($IdUsuario);

$idImport = $_GET["ImportID"] ?? 0;

$stmt = $conexion->prepare("SELECT Booking_BK, costoEXW, num_op, coeficiente FROM imports WHERE ImportsID = ?");
if (!$stmt) {
  die("Error en prepare: " . $conexion->error);
}

$stmt->bind_param("i", $idImport);
$stmt->execute();
$importsData = $stmt->get_result()->fetch_assoc();

// Fetch mapped invoices from import_invoices mapping table (if present)
$invoicesList = [];
$mappingCol = 'ImportsID';
$has = $conexion->query("SHOW COLUMNS FROM import_invoices LIKE 'ImportsID'");
if (!($has && $has->num_rows > 0)) {
  $try = $conexion->query("SHOW COLUMNS FROM import_invoices LIKE 'ImportID'");
  if ($try && $try->num_rows > 0) $mappingCol = 'ImportID';
  else {
    $try2 = $conexion->query("SHOW COLUMNS FROM import_invoices LIKE 'idImport'");
    if ($try2 && $try2->num_rows > 0) $mappingCol = 'idImport';
  }
}

$mapSql = "SELECT Invoice FROM import_invoices WHERE {$mappingCol} = ? ORDER BY id ASC";
$mapStmt = $conexion->prepare($mapSql);
if ($mapStmt) {
  $mapStmt->bind_param('i', $idImport);
  $mapStmt->execute();
  $resMap = $mapStmt->get_result();
  while ($r = $resMap->fetch_assoc()) {
    $invoicesList[] = $r['Invoice'];
  }
  $mapStmt->close();
}

$invoicesDisplay = !empty($invoicesList) ? implode(', ', $invoicesList) : ($importsData['Number_Commercial_Invoice'] ?? '');

// Consulta para los incoterms y sus ítems
$query = "
  SELECT
    t.IdTipoIncoterm    AS idTipo,
    t.NombreTipoIncoterm,
    i.IdIncotermsImport,
    il.IdItemsLiquidacionImport,
    il.NombreItems,
    ii.Cantidad,
    ii.ValorUnitario,
    ii.Notas,
    (ii.Cantidad * ii.ValorUnitario) AS ValorTotal
  FROM incotermsimport i
  JOIN itemsliquidacionimportincoterms ii 
    ON ii.ItemsLiquidacionImportIncoterms = i.IdItemsLiquidacionImportIncoterm
  JOIN itemsliquidacionimport il 
    ON il.IdItemsLiquidacionImport = ii.IdItemsLiquidacionImport
  JOIN tipoincoterm t 
    ON il.IdTipoIncoterm = t.IdTipoIncoterm
  WHERE i.IdImports = ?
  ORDER BY il.posicion
";

$stmt = $conexion->prepare($query);
if (!$stmt) {
  die("Error en prepare: " . $conexion->error);
}

$stmt->bind_param("i", $idImport);
$stmt->execute();
$result = $stmt->get_result();

$incoterms = [];
while ($row = $result->fetch_assoc()) {
  $nombre = $row['NombreTipoIncoterm'];
  if (!isset($incoterms[$nombre])) $incoterms[$nombre] = [];
  $incoterms[$nombre][] = $row;
}
?>


<!DOCTYPE html>
<html lang="en">
  <!-- [Head] start -->

  <head>
    <title>Detalle Export | Eko Logistic</title>
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
    <link rel="icon" href="../assets/images/favicon.svg" type="image/x-icon" />
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
    <li class="pc-item pc-hasmenu open force-open">
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
          <li class="breadcrumb-item"><a href="javascript:void(0)">Liquidación</a></li>
          <li class="breadcrumb-item"><a href="javascript:void(0)">Import</a></li>
          <li class="breadcrumb-item active" aria-current="page">Detalle</li>
        </ul>
      </div>
      <div class="col-md-12">
        <div class="page-header-title">
          <h2 class="mb-0">Detalle</h2>
        </div>
      </div>
    </div>
  </div>
</div>

        <!-- [ breadcrumb ] end -->
<!-- Acordate de incluir Bootstrap Icons si no lo tenés -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
<?php
    // Antes del loop, inicializamos la variable del Total General
    $totalGeneral = 0;
?>
<div class="container mt-5">
  <div class="card shadow p-4">
    <!-- Booking & Invoice -->
    <div class="row mb-4">
      <div class="col-md-6">
        <label class="form-label fw-bold">N° Booking</label>
        <div id="booking" class="form-control bg-light"><?= htmlspecialchars($importsData['Booking_BK']) ?></div>
      </div>
      <div class="col-md-6">
  <label class="form-label fw-bold">Commercial Invoice</label>
  <div id="commercial_Invoice" class="form-control bg-light" data-invoices='<?= json_encode($invoicesList, JSON_HEX_APOS|JSON_HEX_QUOT) ?>'><?= htmlspecialchars($invoicesDisplay) ?></div>
      </div>
      <div class="col-md-6 mb-3">
        <label class="form-label fw-bold">N° Operación</label>
        <input type="text" class="form-control bg-light" value="<?= htmlspecialchars($importsData['num_op']) ?>" readonly>
      </div>
      <div class="col-md-6 mb-3">
      </div>
      <div class="col-md-6 mb-3">
        <label for="productoEXW" class="form-label" >Costo del producto EXW</label>
        <h2 id="productoEXW" data-totalecu="<?=$importsData['costoEXW']?>">$<?= $importsData['costoEXW'] ?></h2>
      </div>
      
      <div class="col-md-6 mb-3">
        <label for="coeficiente" class="form-label">COEFICIENTE %</label>
        <h2 id="coeficiente" data-coeficiente="<?= $importsData['coeficiente'] ?>">%<?= $importsData['coeficiente'] ?></h2>
      </div>
    
    </div>
    <!-- Bloques dinámicos de Incoterm -->
<div id="incotermContainer">

  <?php 
  $totalGeneral = 0;

  foreach ($incoterms as $nombreIncoterm => $items):
    $tipo = intval($items[0]['idTipo']);
  ?>
    <div class="incoterm-item mb-4" data-incoterm="<?= htmlspecialchars($nombreIncoterm) ?>">
      <h5 class="mt-3"><?= htmlspecialchars($nombreIncoterm) ?></h5>
      <table class="table table-hover table-borderless mb-2">
        <thead>
          <tr>
            <th>Descripción</th>
            <th>Cantidad</th>
            <th>Valor U.</th>
            <th>Valor T.</th>
            <th>Notas</th>
          </tr>
        </thead>
        <tbody>
                  <?php foreach ($items as $item): ?>
                    <?php
                      $totalGeneral += floatval($item['ValorTotal']);
                      $qty = floatval($item['Cantidad']);
                      $itemId = isset($item['IdItemsLiquidacionImport']) ? intval($item['IdItemsLiquidacionImport']) : 0;

                      // IDs que deben mostrarse como porcentajes (aranceles)
                      $arancelIds = [64,65,51];
                      // MPH
                      $mphIds = [18,35,49];
                      // HMF
                      $hmfIds = [19,36,50];

                      if (in_array($itemId, $arancelIds, true)) {
                        // mostrar 15.00% para 0.15
                        $displayQty = number_format($qty * 100, 2, ',', '.').'%';
                      } elseif (in_array($itemId, $mphIds, true)) {
                        // mostrar con 4 decimales para valores muy pequeños
                        $displayQty = number_format($qty * 100, 4, ',', '.').'%';
                      } elseif (in_array($itemId, $hmfIds, true)) {
                        $displayQty = number_format($qty * 100, 4, ',', '.').'%';
                      } else {
                        // por defecto mostrar número sin formateo de porcentaje
                        // si es entero mostrar sin decimales
                        if (fmod($qty, 1.0) === 0.0) {
                          $displayQty = number_format($qty, 0, ',', '.');
                        } else {
                          $displayQty = rtrim(rtrim(number_format($qty, 6, ',', '.'), '0'), ',');
                        }
                      }
                    ?>
                    <tr>
                      <td><?= htmlspecialchars($item['NombreItems']) ?></td>
                      <td><?= $displayQty ?></td>
                      <td>$<?= number_format(floatval($item['ValorUnitario']), 2, ',', '.') ?></td>
                      <td>$<?= number_format(floatval($item['ValorTotal']),   2, ',', '.') ?></td>
                      <td><?= htmlspecialchars($item['Notas']) ?></td>
                    </tr>
                  <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endforeach; ?>

  <div class="mt-4 p-3 border-top">
    <h5 class="fw-bold">Total General: $<?= number_format($totalGeneral, 2, ',', '.') ?></h5>
  </div>
</div>



    <!-- Total General y Descarga -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-3 mt-4">
      <button class="btn btn-primary" onclick="history.back()">← Volver</button>
    
      <button onclick="descargarExcel()" class="btn btn-success">Descargar en Excel</button>
    </div>
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
<script src="https://cdn.sheetjs.com/xlsx-latest/package/dist/xlsx.full.min.js"></script>

<script>
function parseARS(str) {
  if (!str && str !== 0) return 0;
  let s = String(str).trim();
  s = s.replace(/[^0-9,\.\-]/g, '');
  if (s === '') return 0;
  const hasComma = s.indexOf(',') !== -1;
  const hasDot = s.indexOf('.') !== -1;
  if (hasDot && hasComma) {
    s = s.replace(/\./g, '').replace(',', '.');
  } else if (hasComma && !hasDot) {
    s = s.replace(',', '.');
  } else if (hasDot && !hasComma) {
    const parts = s.split('.');
    if (parts.length > 2) {
      const last = parts.pop();
      s = parts.join('') + '.' + last;
    }
  }
  const val = parseFloat(s);
  return isNaN(val) ? 0 : val;
}

function descargarExcel() {
  const wb = XLSX.utils.book_new();
  const ws_data = [];

  const bookingEl = document.getElementById('booking');
  const invoiceEl = document.getElementById('commercial_Invoice');
  const exwEl = document.getElementById('productoEXW');
  const coefEl = document.getElementById('coeficiente');

  const booking = bookingEl?.textContent.trim() || '';
  const invoice = invoiceEl?.textContent.trim() || '';
  const numOpEl = document.querySelector('input[readonly]');
  const numOp = numOpEl?.value?.trim() || '';

  const exwRaw = (exwEl && (exwEl.dataset?.totalecu || exwEl.dataset?.totalEcu)) || exwEl?.textContent || '';
  const exwNum = parseARS(exwRaw);
  const coef = coefEl?.dataset?.coeficiente || '';

  ws_data.push(['N° Booking', booking]);
  ws_data.push(['N° Operación', numOp]);
  ws_data.push(['Commercial Invoice', invoice]);
  ws_data.push(['Costo EXW', exwNum.toLocaleString('es-AR', {minimumFractionDigits:2})]);
  ws_data.push(['Coeficiente (%)', '%' + coef]);
  ws_data.push(['Coeficiente (sin impuestos) (%)', '']);
  ws_data.push([]);

  document.querySelectorAll('.incoterm-item').forEach(block => {
    const incName = block.querySelector('h5')?.textContent.trim();
    const rows = Array.from(block.querySelectorAll('tbody tr'));
    if (!incName || rows.length === 0) return;

    const isCIF = incName.toLowerCase().includes('cif');
    ws_data.push([`Incoterm: ${incName}`]);
    const headers = isCIF ? ['Descripción','Cantidad','Valor U.','% Impuesto','Valor Impuesto','Valor T.','Notas'] : ['Descripción','Cantidad','Valor U.','Valor T.','Notas'];
    ws_data.push(headers);

    let subtotalSin = 0;
    let subtotalImp = 0;

    rows.forEach(tr => {
      const cols = Array.from(tr.children).map(td => td.textContent.trim());
      const desc = cols[0] || '';
      const qty = parseARS(cols[1]) || 0;
      const vu = parseARS(cols[2]) || 0;
      const pctRaw = (cols[4] || '').toString().replace('%','').replace(/\./g,'').replace(',','.');
      const pct = parseFloat(pctRaw) || 0;

      const valorSinTotal = vu * qty;
      const valorImpTotal = (vu * (pct / 100)) * qty;
      const valorTTotal = valorSinTotal + valorImpTotal;

      subtotalSin += valorSinTotal;
      subtotalImp += valorImpTotal;

      if (isCIF) {
        ws_data.push([desc, qty, vu, (isNaN(pct) ? '' : Number(pct).toFixed(2) + '%'), valorImpTotal.toLocaleString('es-AR',{minimumFractionDigits:2}), valorTTotal.toLocaleString('es-AR',{minimumFractionDigits:2}), cols[6] || '']);
      } else {
        ws_data.push([desc, qty, vu, valorTTotal.toLocaleString('es-AR',{minimumFractionDigits:2}), cols[4] || '']);
      }
    });

    if (isCIF) {
      ws_data.push(['Total sin impuestos', '', '', '', '', subtotalSin.toLocaleString('es-AR',{minimumFractionDigits:2}), '']);
      ws_data.push(['Total impuestos', '', '', '', '', subtotalImp.toLocaleString('es-AR',{minimumFractionDigits:2}), '']);
      ws_data.push(['Total con impuestos', '', '', '', '', (subtotalSin + subtotalImp).toLocaleString('es-AR',{minimumFractionDigits:2}), '']);
    } else {
      ws_data.push(['Total', '', '', subtotalSin.toLocaleString('es-AR',{minimumFractionDigits:2})]);
    }

    ws_data.push([]);
  });

  if (ws_data.length <= 3) return alert('No hay datos para exportar.');

  const ws = XLSX.utils.aoa_to_sheet(ws_data);
  XLSX.utils.book_append_sheet(wb, ws, 'DetalleImport');
  XLSX.writeFile(wb, `ImportID_<?= $idImport ?>.xlsx`);
}
</script>





<script>layout_change('light');</script>




<script>layout_sidebar_change('light');</script>



<script>change_box_container('false');</script>


<script>layout_caption_change('true');</script>




<script>layout_rtl_change('false');</script>


<script>preset_change("preset-1");</script>

    <!-- [Page Specific JS] start -->
    <script>
      // scroll-block
      var tc = document.querySelectorAll('.scroll-block');
      for (var t = 0; t < tc.length; t++) {
        new SimpleBar(tc[t]);
      }
      // quantity start
      function increaseValue(temp) {
        var value = parseInt(document.getElementById(temp).value, 10);
        value = isNaN(value) ? 0 : value;
        value++;
        document.getElementById(temp).value = value;
      }

      function decreaseValue(temp) {
        var value = parseInt(document.getElementById(temp).value, 10);
        value = isNaN(value) ? 0 : value;
        value < 1 ? (value = 1) : '';
        value--;
        document.getElementById(temp).value = value;
      }
      // quantity end
    </script>
    <!-- [Page Specific JS] end -->
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