<?php

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
session_start();
include('../usuarioClass.php');
include("../con_db.php");

$IdUsuario = $_SESSION["IdUsuario"];
$usuario = new Usuario($conexion);
$user = $usuario->obtenerUsuarioPorId($IdUsuario);
$id = $_GET["id"] ?? 0;
$stmt = $conexion->prepare(
    "SELECT d.*, i.Description AS descripcion_item
       FROM dispatch d
       LEFT JOIN container c
         ON c.Number_Container = d.notas
       LEFT JOIN items i
         ON i.Number_Commercial_Invoice = d.numero_factura
        AND i.Code_Product_EC          = d.numero_parte
        AND i.idContainer              = c.IdContainer
      WHERE d.id = ?"
);
$stmt->bind_param("i", $id);
$stmt->execute();
$warehouse = $stmt->get_result()->fetch_assoc();
?>


<!DOCTYPE html>
<html lang="en">
  <!-- [Head] start -->

  <head>
    <title>Detalle Warehouse Usa | Eko Logistic</title>
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
    <li class="pc-item pc-hasmenu">
      <a href="#!" class="pc-link">
        <span class="pc-micon">
          <i class="ph-duotone ph-truck"></i>
        </span>
        <span class="pc-mtext">Logistica</span>
        <span class="pc-arrow"><i data-feather="chevron-right"></i></span>
      </a>
      <ul class="pc-submenu">
        <li class="pc-item"><a class="pc-link" href="../dashboard/index.php">Dashboard Logistic</a></li>
        <li class="pc-item"><a class="pc-link" href="../dashboard/panel-packinglist.php">Dashboard Packing List</a></li>
        <li class="pc-item pc-hasmenu">
              <a href="#!" class="pc-link">Inventory<span class="pc-arrow"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-chevron-right"><polyline points="9 18 15 12 9 6"></polyline></svg></span></a>
              <ul class="pc-submenu" style="display: block; box-sizing: border-box; transition-property: height, margin, padding; transition-duration: 200ms; height: 0px; overflow: hidden; padding-top: 0px; padding-bottom: 0px; margin-top: 0px; margin-bottom: 0px;">
                <li class="pc-item"><a class="pc-link" href="./transit-inventory.php">Transit Inventory</a></li>
                  <li class="pc-item"><a class="pc-link" href="../admins/warehouseUsaPanel.php">WareHouse USA</a></li>
                <li class="pc-item"><a class="pc-link" href="./warehouse-inventory.php">WareHouse Inventory</a></li>
                <li class="pc-item"><a class="pc-link" href="./total-inventory.php">Total Inventory</a></li>
                <li class="pc-item"><a class="pc-link" href="../dashboard/panel-dispatch.php">Dispatch Inventory</a> </li>
              </ul>
            </li>

        <!--
        <li class="pc-item"><a class="pc-link" href="../dashboard/panel-contenedores.php">Dashboard Containers</a></li>
        <li class="pc-item"><a class="pc-link" href="../application/panel-inventarios.php">Panel Inventory</a></li>
        <li class="pc-item"><a class="pc-link">Despachos</a></li>
        <li class="pc-item"><a class="pc-link">Palets</a></li>
        <li class="pc-item"><a class="pc-link" >Ordenes de Compra</a></li>
  -->
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
          <li class="breadcrumb-item"><a href="javascript:void(0)">Inventarios</a></li>
          <li class="breadcrumb-item"><a href="javascript:void(0)">Warehouse Usa</a></li>
          <li class="breadcrumb-item active" aria-current="page">Detalle</li>
        </ul>
      </div>
      <div class="col-md-12">
        <div class="page-header-title">
          <h2 class="mb-0">Detalle Warehouse Usa</h2>
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
  <div class="card shadow-sm mx-auto" style="max-width: 900px;">
    <div class="card-body">

      <div class="row g-3">
        <div class="col-md-4">
          <label class="form-label">Fecha Entrada</label>
          <div class="form-control bg-light"><?= htmlspecialchars($warehouse['fecha_entrada']) ?></div>
        </div>
        <div class="col-md-4">
          <label class="form-label">Fecha de Salida</label>
          <div class="form-control bg-light"><?= htmlspecialchars($warehouse['fecha_salida']) ?></div>
        </div>
        <div class="col-md-4">
          <label class="form-label">Recibo de Almacén</label>
          <div class="form-control bg-light"><?= htmlspecialchars($warehouse['recibo_almacen']) ?></div>
        </div>
        <div class="col-md-4">
          <label class="form-label">Número de Contenedor</label>
          <div class="form-control bg-light"><?= htmlspecialchars($warehouse['notas']) ?></div>
        </div>
        <div class="col-md-4">
          <label class="form-label">Estado</label>
          <div class="form-control bg-light"><?= htmlspecialchars($warehouse['estado']) ?></div>
        </div>
        <div class="col-md-4">
          <label class="form-label">Número de Factura</label>
          <div class="form-control bg-light"><?= htmlspecialchars($warehouse['numero_factura']) ?></div>
        </div>
        <div class="col-md-4">
          <label class="form-label">Número de Lote</label>
          <div class="form-control bg-light"><?= htmlspecialchars($warehouse['numero_lote']) ?></div>
        </div>
        <div class="col-md-4">
          <label class="form-label">Orden de Compra</label>
          <div class="form-control bg-light"><?= htmlspecialchars($warehouse['numero_orden_compra']) ?></div>
        </div>
        <div class="col-md-4">
          <label class="form-label">Número de Parte</label>
          <div class="form-control bg-light"><?= htmlspecialchars($warehouse['numero_parte']) ?></div>
        </div>
        <div class="col-12">
          <label class="form-label">Descripción</label>
          <div class="form-control bg-light"><?= htmlspecialchars($warehouse['descripcion_item'] ?? $warehouse['descripcion']) ?></div>
        </div>
        <div class="col-md-4">
          <label class="form-label">Modelo</label>
          <div class="form-control bg-light"><?= htmlspecialchars($warehouse['modelo']) ?></div>
        </div>
        <div class="col-12">
          <label class="form-label">Palets</label>
          <div class="form-control bg-light"><?= htmlspecialchars($warehouse['palets']) ?></div>
        </div>
        <div class="col-md-4">
          <label class="form-label">Cantidad</label>
          <div class="form-control bg-light"><?= htmlspecialchars($warehouse['cantidad']) ?></div>
        </div>
        <div class="col-md-4">
          <label class="form-label">Valor Unitario</label>
          <div class="form-control bg-light"><?= htmlspecialchars($warehouse['valor_unitario']) ?></div>
        </div>
        <div class="col-md-4">
          <label class="form-label">Valor</label>
          <div class="form-control bg-light"><?= htmlspecialchars($warehouse['valor']) ?></div>
        </div>
        <div class="col-md-4">
          <label class="form-label">Unidad</label>
          <div class="form-control bg-light"><?= htmlspecialchars($warehouse['unidad']) ?></div>
        </div>
        <div class="col-md-4">
          <label class="form-label">Longitud (in)</label>
          <div class="form-control bg-light"><?= htmlspecialchars($warehouse['longitud_in']) ?></div>
        </div>
        <div class="col-md-4">
          <label class="form-label">Ancho (in)</label>
          <div class="form-control bg-light"><?= htmlspecialchars($warehouse['ancho_in']) ?></div>
        </div>
        <div class="col-md-4">
          <label class="form-label">Altura (in)</label>
          <div class="form-control bg-light"><?= htmlspecialchars($warehouse['altura_in']) ?></div>
        </div>
        <div class="col-md-4">
          <label class="form-label">Peso (lb)</label>
          <div class="form-control bg-light"><?= htmlspecialchars($warehouse['peso_lb']) ?></div>
        </div>
      </div>

<?php
  $hasRest = !empty($warehouse['valor_unitario_restante']) ||
             !empty($warehouse['valor_restante']) ||
             !empty($warehouse['unidad_restante']) ||
             !empty($warehouse['longitud_in_restante']) ||
             !empty($warehouse['ancho_in_restante']) ||
             !empty($warehouse['altura_in_restante']) ||
             !empty($warehouse['peso_lb_restante']) ||
             !empty($warehouse['palets_restante']) ||
             !empty($warehouse['cantidad_restante']);
  if ($hasRest): ?>
      <h5 class="mt-4">Datos Restantes</h5>
      <div class="row g-3">
        <?php if (!empty($warehouse['valor_unitario_restante'])): ?>
        <div class="col-md-4">
          <label class="form-label">Valor Unitario Restante</label>
          <div class="form-control bg-light"><?= htmlspecialchars($warehouse['valor_unitario_restante']) ?></div>
        </div>
        <?php endif; ?>
        <?php if (!empty($warehouse['valor_restante'])): ?>
        <div class="col-md-4">
          <label class="form-label">Valor Restante</label>
          <div class="form-control bg-light"><?= htmlspecialchars($warehouse['valor_restante']) ?></div>
        </div>
        <?php endif; ?>
        <?php if (!empty($warehouse['unidad_restante'])): ?>
        <div class="col-md-4">
          <label class="form-label">Unidad Restante</label>
          <div class="form-control bg-light"><?= htmlspecialchars($warehouse['unidad_restante']) ?></div>
        </div>
        <?php endif; ?>
        <?php if (!empty($warehouse['longitud_in_restante'])): ?>
        <div class="col-md-4">
          <label class="form-label">Longitud (in) Restante</label>
          <div class="form-control bg-light"><?= htmlspecialchars($warehouse['longitud_in_restante']) ?></div>
        </div>
        <?php endif; ?>
        <?php if (!empty($warehouse['ancho_in_restante'])): ?>
        <div class="col-md-4">
          <label class="form-label">Ancho (in) Restante</label>
          <div class="form-control bg-light"><?= htmlspecialchars($warehouse['ancho_in_restante']) ?></div>
        </div>
        <?php endif; ?>
        <?php if (!empty($warehouse['altura_in_restante'])): ?>
        <div class="col-md-4">
          <label class="form-label">Altura (in) Restante</label>
          <div class="form-control bg-light"><?= htmlspecialchars($warehouse['altura_in_restante']) ?></div>
        </div>
        <?php endif; ?>
        <?php if (!empty($warehouse['peso_lb_restante'])): ?>
        <div class="col-md-4">
          <label class="form-label">Peso (lb) Restante</label>
          <div class="form-control bg-light"><?= htmlspecialchars($warehouse['peso_lb_restante']) ?></div>
        </div>
        <?php endif; ?>
        <?php if (!empty($warehouse['palets_restante'])): ?>
        <div class="col-md-4">
          <label class="form-label">Palets Restantes</label>
          <div class="form-control bg-light"><?= htmlspecialchars($warehouse['palets_restante']) ?></div>
        </div>
        <?php endif; ?>
        <?php if (!empty($warehouse['cantidad_restante'])): ?>
        <div class="col-md-4">
          <label class="form-label">Cantidad de Cajas Restantes</label>
          <div class="form-control bg-light"><?= htmlspecialchars($warehouse['cantidad_restante']) ?></div>
        </div>
        <?php endif; ?>
      </div>
<?php endif; ?>

      <!-- Botón único de volver -->
      <div class="mt-4 d-flex justify-content-start">
        <a href="../admins/warehouseUsaPanel.php" class="btn btn-primary">
          <i class="bi bi-arrow-left"></i> Volver
        </a>
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
<script src="https://cdn.sheetjs.com/xlsx-latest/package/dist/xlsx.full.min.js"></script>

<script src="https://cdn.sheetjs.com/xlsx-latest/package/dist/xlsx.full.min.js"></script>

<script>
function descargarExcel() {
  const wb      = XLSX.utils.book_new();
  const ws_data = [];

  // Booking e Invoice
  const bookingEl = document.getElementById('booking');
  const invoiceEl = document.getElementById('commercial_Invoice');
  const booking   = bookingEl?.textContent.trim() || '';
  const invoice   = invoiceEl?.textContent.trim() || '';

  ws_data.push(['N° Booking', booking]);
  ws_data.push(['Commercial Invoice', invoice]);
  ws_data.push([]);

  let totalGeneral = 0;

  // Recorrer Incoterms
  document.querySelectorAll('.incoterm-item').forEach(block => {
    const incName = block.querySelector('h5')?.textContent.trim();
    if (!incName) return;

    ws_data.push([`Incoterm: ${incName}`]);
    ws_data.push(['Descripción','Cantidad','Valor U.','Valor T.']);

    let subtotal = 0;

    block.querySelectorAll('tbody tr').forEach(tr => {
      const cells = tr.children;
      const desc = cells[0].textContent.trim();
      const cant = cells[1].textContent.trim();
      const rawU = cells[2].textContent.replace(/[^0-9,\.]/g,'').trim();
      const rawT = cells[3].textContent.replace(/[^0-9,\.]/g,'').trim();
      const numT = parseFloat(rawT.replace(/\./g,'').replace(',','.')) || 0;

      subtotal += numT;
      totalGeneral += numT;

      ws_data.push([desc, cant, rawU, rawT]);
    });

    ws_data.push([`Total ${incName}`, '', '', subtotal.toLocaleString('es-AR',{minimumFractionDigits:2})]);
    ws_data.push([]);
  });

  if (ws_data.length <= 3) {
    return alert('No hay datos para exportar.');
  }

  // Total General al final
  ws_data.push(['']);
  ws_data.push(['Total General', '', '', totalGeneral.toLocaleString('es-AR',{minimumFractionDigits:2})]);

  // Crear hoja Excel
  const ws    = XLSX.utils.aoa_to_sheet(ws_data);
  const range = XLSX.utils.decode_range(ws['!ref']);
  const colCount = range.e.c - range.s.c + 1;

  // Estilos
  for (let R = range.s.r; R <= range.e.r; ++R) {
    const A = ws[`A${R+1}`];
    if (A && typeof A.v === 'string') {
      if (A.v.startsWith('Incoterm:')) {
        A.s = { fill:{fgColor:{rgb:'C6EFCE'}}, font:{bold:true} };
      }
      if (A.v === 'Descripción') {
        for (let C = 0; C < colCount; ++C) {
          const cell = ws[`${String.fromCharCode(65+C)}${R+1}`];
          if (cell) cell.s = { fill:{fgColor:{rgb:'FFF2CC'}}, font:{bold:true} };
        }
      }
      if (A.v.startsWith('Total')) {
        A.s = { font:{bold:true} };
      }
    }
  }

  ws['!cols'] = [
    {wch:35}, {wch:12}, {wch:15}, {wch:15}
  ];

  XLSX.utils.book_append_sheet(wb, ws, 'Liquidación');
  XLSX.writeFile(wb, 'detalle_importacion.xlsx');
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
