<?php
session_start();
include('../usuarioClass.php');
include("../con_db.php");

$IdUsuario = $_SESSION["IdUsuario"];
$usuario = new Usuario($conexion);
$user = $usuario->obtenerUsuarioPorId($IdUsuario);

$idExport = $_GET["ExportID"] ?? 0;

$stmt = $conexion->prepare("SELECT Booking_BK, Number_Commercial_Invoice FROM exports WHERE ExportsID = ?");
$stmt->bind_param("i", $idExport);
$stmt->execute();
$exportData = $stmt->get_result()->fetch_assoc();

// Consulta para los incoterms y sus ítems
$query = "
SELECT 
  t.NombreTipoIncoterm,
  t.IdTipoIncoterm AS idTipo,
  i.IdIncoterms,
  il.NombreItems,
  ii.Cantidad,
  ii.ValorUnitario,
  ii.Impuesto,
  ii.ValorImpuesto,
  ii.Notas,
  (ii.Cantidad * ii.ValorUnitario) AS ValorTotal
FROM incoterms i
JOIN itemsliquidacionexportincoterms ii ON ii.IdItemsLiquidacionExportIncoterms  = i.IdItemsLiquidacionExportIncoterms 
JOIN itemsliquidacionexport il ON il.IdItemsLiquidacionExport = ii.IdItemsLiquidacionExport
JOIN tipoincoterm t ON il.IdTipoIncoterm = t.IdTipoIncoterm
WHERE i.IdExports = ?
ORDER BY i.IdIncoterms, il.NombreItems
";

$stmt = $conexion->prepare($query);
$stmt->bind_param("i", $idExport);
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
    <title>Editar Export | Eko Logistic</title>
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
        <li class="pc-item"><a class="pc-link" href="../dashboard/warehouse-inventory.php">WareHouse Inventory</a></li>
        <li class="pc-item"><a class="pc-link" href="../admins/warehouseUsaPanel.php">WareHouse USA</a></li>
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
          <li class="breadcrumb-item"><a href="javascript:void(0)">Export</a></li>
          <li class="breadcrumb-item active" aria-current="page">Editar</li>
        </ul>
      </div>
      <div class="col-md-12">
        <div class="page-header-title">
          <h2 class="mb-0">Editar</h2>
        </div>
      </div>
    </div>
  </div>
</div>

        <!-- [ breadcrumb ] end -->
<!-- Acordate de incluir Bootstrap Icons si no lo tenés -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

<div class="container mt-5">
  <div class="card shadow p-4">
    <div class="row mb-4">
      <div class="col-md-6">
        <label class="form-label fw-bold">N° Booking</label>
        <div class="form-control bg-light"><?= $exportData['Booking_BK'] ?></div>
      </div>
      <div class="col-md-6">
        <label class="form-label fw-bold">Commercial Invoice</label>
        <div class="form-control bg-light"><?= $exportData['Number_Commercial_Invoice'] ?></div>
      </div>
    </div>

    <div class="accordion" id="incotermAccordion">
  <?php $idx = 0; foreach ($incoterms as $nombreIncoterm => $items): 
    $currentTipo = intval($items[0]['idTipo']); ?>
    <div class="accordion-item">
      <h2 class="accordion-header" id="heading<?= $idx ?>">
        <button class="accordion-button <?= $idx ? 'collapsed' : '' ?>"
                type="button"
                data-bs-toggle="collapse"
                data-bs-target="#collapse<?= $idx ?>"
                aria-expanded="<?= $idx ? 'false' : 'true' ?>"
                aria-controls="collapse<?= $idx ?>">
          <?= htmlspecialchars($nombreIncoterm) ?>
        </button>
      </h2>
      <div id="collapse<?= $idx ?>"
           class="accordion-collapse collapse <?= $idx ? '' : 'show' ?>"
           aria-labelledby="heading<?= $idx ?>"
           data-bs-parent="#incotermAccordion">
        <div class="accordion-body">
       <div class="card">
  <div class="card-body">
    <div class="table-responsive">
      <table class="table table-hover table-borderless mb-0">
        <thead>
          <tr>
            <th>Descripción</th>
            <th>Cantidad</th>
            <th>Valor U.</th>
            <th>Valor T.</th>
            <?php if ($currentTipo === 3): ?>
              <th>% Impuesto</th>
              <th>Valor Impuesto</th>
              <th>Notas</th>
            <?php endif; ?>
          </tr>
        </thead>
        <tbody>
          <?php
            // inicializamos subtotales
            $subtotalSin = 0;
            $subtotalCi  = 0;
            foreach ($items as $item):
              $cant = intval($item['Cantidad']);
              $vu   = floatval($item['ValorUnitario']);
              $vt   = $cant * $vu;
              $imp  = floatval($item['Impuesto']);
              $vi   = $vt * ($imp / 100);
              $subtotalSin += $vt;
              $subtotalCi  += $vt + $vi;
          ?>
          <tr data-item-id="<?= intval($item['IdIncoterms']) ?>">
            <td><?= htmlspecialchars($item['NombreItems']) ?></td>
            <td>
              <input type="number" class="form-control form-control-sm cantidad" value="<?= $cant ?>" min="0" style="width: 80px; text-align: right;">
            </td>
            <td>
              <div class="input-group input-group-sm" style="min-width: 110px;">
                <span class="input-group-text">$</span>
                <input type="text" class="form-control valor-unitario" value="<?= number_format($vu, 2, ',', '.') ?>" style="min-width: 70px; text-align: right;">
              </div>
            </td>
            <td>
              <div class="input-group input-group-sm" style="min-width: 110px;">
                <span class="input-group-text">$</span>
                <input type="text" class="form-control valor-total" readonly value="<?= number_format($vt, 2, ',', '.') ?>" style="min-width: 70px; text-align: right;">
              </div>
            </td>
            <?php if ($currentTipo === 3): ?>
              <td>
                <div class="input-group input-group-sm" style="min-width: 90px;">
                  <input type="number" class="form-control impuesto" value="<?= $imp ?>" min="0" max="100" style="text-align: right;">
                  <span class="input-group-text">%</span>
                </div>
              </td>
              <td>
                <div class="input-group input-group-sm" style="min-width: 110px;">
                  <span class="input-group-text">$</span>
                  <input type="text" class="form-control valor-impuesto" readonly value="<?= number_format($vi, 2, ',', '.') ?>" style="min-width: 70px; text-align: right;">
                </div>
              </td>
              <td>
                <input type="text" class="form-control form-control-sm notas" value="<?= htmlspecialchars($item['Notas']) ?>" style="min-width: 140px;">
              </td>
            <?php endif; ?>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>


<div class="mt-3 text-end">
  <?php if ($currentTipo === 3): ?>
    <h6 class="mb-1">Subtotal sin impuestos: $
      <span id="subtotal-sin"><?= number_format($subtotalSin,2,',','.') ?></span>
    </h6>
    <h5 class="text-success fw-bold">
      Subtotal con impuestos: $
      <span id="subtotal-ci"><?= number_format($subtotalCi,2,',','.') ?></span>
    </h5>
  <?php else: ?>
    <h5 class="text-success fw-bold">
      Subtotal: $<span id="subtotal-sin"><?= number_format($subtotalSin,2,',','.') ?></span>
    </h5>
  <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const rows = Array.from(document.querySelectorAll('tbody tr'));
  const sinEl = document.getElementById('subtotal-sin');
  const ciEl  = document.getElementById('subtotal-ci');
  const total = document.getElementById('totalGeneral');
  function formatNumber(num) {
    return num.toFixed(2).replace('.', ',').replace(/\B(?=(\d{3})+(?!\d))/g, '.');
  }

  function recalculate() {
    let sumSin = 0, sumCi = 0;
    rows.forEach(row => {
      const qty = parseFloat(row.querySelector('.cantidad').value) || 0;
      const vu  = parseFloat(row.querySelector('.valor-unitario').value.replace(/\./g,'').replace(',','.')) || 0;
      const vt  = qty * vu;
      sumSin += vt;

      const impInput = row.querySelector('.impuesto');
      if (impInput) {
        const pct = parseFloat(impInput.value) || 0;
        const vi  = vt * pct / 100;
        sumCi += vt + vi;
        row.querySelector('.valor-impuesto').value = formatNumber(vi);
      }
      row.querySelector('.valor-total').value = formatNumber(vt);
    });

    sinEl.textContent = formatNumber(sumSin);
    if (ciEl) ciEl.textContent = formatNumber(sumCi);

    total.textContent= "Total General: $ "+formatNumber(sumCi)
  }

  // Atachar listener a cada input que afecte
  rows.forEach(row => {
    ['.cantidad','.valor-unitario','.impuesto'].forEach(sel => {
      const inp = row.querySelector(sel);
      if (inp) inp.addEventListener('input', recalculate);
    });
  });

  // cálculo inicial
  recalculate();
});
</script>

        </div>
      </div>
    </div>
  <?php $idx++; endforeach; ?>
</div>


    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-3 mt-4">
      <button class="btn btn-primary" onclick="history.back()">← Volver</button>

      <h5 id="totalGeneral" class="text-success fw-bold m-0 text-center">
        Total General: $0,00
      </h5>

      <button id="btnGuardar" class="btn btn-info">Editar</button>
    </div>

  </div>
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

<!--Sweet alert-->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<!-- Calcular totales automaticamente-->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<!-- <script>
document.addEventListener('DOMContentLoaded', () => {
  const totalGeneralEl = document.getElementById('totalGeneral');

  function actualizarTotalGeneral() {
    let totalGeneral = 0;
    // Sumamos todos los valor-total y valor-impuesto (quitando miles)
    document.querySelectorAll('.valor-total, .valor-impuesto').forEach(input => {
      const raw = input.value
        .replace(/\./g, '')   // quitar separador de miles
        .replace(',', '.');   // convertir coma decimal a punto
      totalGeneral += parseFloat(raw) || 0;
    });
    totalGeneralEl.textContent = `Total General: $${totalGeneral.toFixed(2).replace('.', ',')}`;
  }

  document.querySelectorAll('.accordion-item').forEach(accordion => {
    const rows = accordion.querySelectorAll('tbody tr');
    const totalIncotermSpan = accordion.querySelector('.total-incoterm');

    function calcularBloque() {
      let subtotal = 0;

      rows.forEach(row => {
        // parsear cantidad, valor unitario e impuesto (si existe)
        const qtyRaw = row.querySelector('.cantidad')?.value   .replace(',', '.') || '0';
        const vuRaw  = row.querySelector('.valor-unitario')?.value
                          .replace(/\./g, '').replace(',', '.') || '0';
        const impRaw = row.querySelector('.impuesto')?.value   .replace(',', '.') || '0';

        const cantidad     = parseFloat(qtyRaw) || 0;
        const valorUnitario= parseFloat(vuRaw)  || 0;
        const impuestoPct  = parseFloat(impRaw) || 0;

        const vt = cantidad * valorUnitario;
        const vi = vt * (impuestoPct / 100);

        // actualizar inputs de tota les filas si existen
        const vtEl = row.querySelector('.valor-total');
        if (vtEl) vtEl.value = vt.toFixed(2).replace('.', ',');

        const viEl = row.querySelector('.valor-impuesto');
        if (viEl) viEl.value = vi.toFixed(2).replace('.', ',');

        subtotal += vt + vi;
      });

      if (totalIncotermSpan) {
        totalIncotermSpan.textContent = subtotal.toFixed(2).replace('.', ',');
      }
      actualizarTotalGeneral();
    }

    // atachar listeners a cantidad, valor-unitario e impuesto
    rows.forEach(row => {
      ['.cantidad', '.valor-unitario', '.impuesto'].forEach(sel => {
        const input = row.querySelector(sel);
        if (input) input.addEventListener('input', calcularBloque);
      });
    });

    // cálculo inicial de este bloque
    calcularBloque();
  });
});
</script> -->




<!-- Actualizar datos-->
<script>
document.addEventListener('DOMContentLoaded', () => {
  const btn = document.getElementById('btnGuardar');
  if (!btn) return;

  btn.addEventListener('click', () => {
    const datos = [];

    document.querySelectorAll('.accordion-item tbody tr').forEach(row => {
      // 1) ID de la fila de pivot
      const idInc = parseInt(row.dataset.itemId, 10);

      // 2) Cantidad y Valor Unitario
      const qtyRaw  = row.querySelector('.cantidad')?.value       .replace(',', '.') || '0';
      const vuRaw   = row.querySelector('.valor-unitario')?.value
                       .replace(/\./g,'').replace(',', '.')       || '0';
      const cantidad      = parseFloat(qtyRaw)      || 0;
      const valorUnitario = parseFloat(vuRaw)       || 0;

      // 3) Recalcular Totales
      const valorTotal    = cantidad * valorUnitario;

      const impRaw        = row.querySelector('.impuesto')?.value .replace(',', '.') || '0';
      const impuestoPct   = parseFloat(impRaw)       || 0;
      const valorImpuesto = valorTotal * (impuestoPct / 100);

      const notas         = row.querySelector('.notas')?.value.trim() || '';

      datos.push({
        idIncoterms: idInc,
        cantidad,
        valorUnitario,
        valorTotal,
        impuestoPct,
        valorImpuesto,
        notas
      });
    });

    fetch('../api/exports/actualizarliquidacionexport.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ datos })
    })
    .then(r => r.json())
    .then(json => {
      if (json.success) {
        Swal.fire('Guardado', json.message || 'Actualizado con éxito', 'success')
          .then(() => location.reload());
      } else {
        Swal.fire('Error', json.message || 'No se pudo guardar', 'error');
      }
    })
    .catch(() => {
      Swal.fire('Error', 'Error de red o del servidor', 'error');
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