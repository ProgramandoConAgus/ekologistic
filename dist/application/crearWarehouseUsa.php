<?php
session_start();
include('../usuarioClass.php');
include("../con_db.php");
$IdUsuario=$_SESSION["IdUsuario"];

$usuario= new Usuario($conexion);

$user=$usuario->obtenerUsuarioPorId($IdUsuario);

$stmtBookings = $conexion->prepare("
  SELECT DISTINCT c.Booking_BK
  FROM container c
  LEFT JOIN dispatch d ON c.Number_Container = d.notas
  WHERE d.estado != 'En Almacén' OR d.notas IS NULL
  ORDER BY c.Booking_BK;
");

  $stmtBookings->execute();
  $bookings = $stmtBookings->get_result()->fetch_all(MYSQLI_ASSOC);

?>


<!DOCTYPE html>
<html lang="en">
  <!-- [Head] start -->

  <head>
    <title>Liquidaciones | Crear WareHouse USA 2</title>
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
<!-- SweetAlert2 CDN -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

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
          <li class="breadcrumb-item"><a href="javascript: void(0)">Inventarios</a></li>
          <li class="breadcrumb-item"><a href="javascript: void(0)">WareHouse USA 2</a></li>
          <li class="breadcrumb-item" aria-current="page">Crear</li>
        </ul>
      </div>
      <div class="col-md-12">
        <div class="page-header-title">
          <h2 class="mb-0">Crear WareHouse USA 2</h2>
        </div>
      </div>
    </div>
  </div>
</div>

        <!-- [ breadcrumb ] end -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

<div class="container mt-5">
  <div class="card shadow-sm mx-auto" style="max-width: 900px;">
    <form method="POST" action="#">
      <input type="hidden" name="numero_contenedor" id="numeroContenedor">
  <div class="card-body">
    <div class="row g-3">
      <div class="col-md-4">
        <label class="form-label" for="bookingSelect">Booking</label>
        <select name="booking" id="bookingSelect" class="form-control">
          <option value="">-- Selecciona --</option>
          <?php foreach($bookings as $b): ?>
            <option value="<?= htmlspecialchars($b['Booking_BK']) ?>">
              <?= htmlspecialchars($b['Booking_BK']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-4">
        <label class="form-label" for="descripcionSelect">Descripción</label>
        <select id="descripcionSelect" name="descripcion" class="form-control" disabled>
          <option value="">-- Selecciona --</option>
        </select>
      </div>
      <div class="col-md-4">
        <label class="form-label">Fecha Entrada</label>
        <input type="date" name="fecha_entrada" class="form-control">
      </div>
      <div class="col-md-4">
        <label class="form-label">Fecha de Salida</label>
        <input type="date" name="fecha_salida" class="form-control">
      </div>
      <div class="col-md-4">
        <label class="form-label">Número de Contenedor</label>
        <input type="text" id="numeroContenedorDisplay" class="form-control" readonly>
      </div>
      <div class="col-md-4">
        <label class="form-label">Recibo de Almacén</label>
        <input type="text" name="recibo_almacen" class="form-control">
      </div>
      <div class="col-md-4">
        <label class="form-label" for="estado">Estado</label>
        <select name="estado" id="estado" class="form-control">
          <option value="">-- Selecciona --</option>
          <option value="En Almacén">En Almacén</option>
          <option value="Cargado">Cargado</option>
          <option value="Transit">Transit</option>
        </select>
      </div>
      <div class="col-md-4">
        <label class="form-label" for="facturaSelect">Número de Factura</label>
        <select id="facturaSelect" name="numero_factura" class="form-control" disabled>
          <option value="">-- Selecciona --</option>
        </select>
      </div>
      <div class="col-md-4">
        <label class="form-label" for="loteSelect">Número de Lote</label>
        <select id="loteSelect" name="numero_lote" class="form-control" disabled>
          <option value="">-- Selecciona --</option>
        </select>
      </div>
      <div class="col-md-4">
        <label class="form-label" for="ordenInput">Número de Orden de Compra</label>
        <!-- campo mixto: autocompleta o texto libre -->
        <input
          type="text"
          id="ordenInput"
          name="orden_compra"
          class="form-control"
          list="ordenList"
          disabled
        >
        <datalist id="ordenList"></datalist>
      </div>

      <div class="col-md-4">
        <label class="form-label" for="parteSelect">Número de Parte</label>
        <select id="parteSelect" name="numero_parte" class="form-control" disabled>
          <option value="">-- Selecciona --</option>
        </select>
      </div>
      <div class="col-md-4">
        <label class="form-label">Modelo</label>
        <input type="text" name="modelo" class="form-control">
      </div>
      <div class="col-md-4">
        <label class="form-label">Palets</label>
        <input type="number" name="palets" class="form-control">
      </div>
      <div class="col-md-4">
        <label class="form-label">Cantidad de Cajas</label>
        <input type="number" name="cantidad" class="form-control">
      </div>
      <div class="col-md-4">
        <label class="form-label">Cantidad Total</label>
        <input type="number" name="cantidadTotal" class="form-control">
      </div>
      <div id="totalUpdateNote" class="col-md-4" style="display:none;">
        <label class="form-label">Nota por cambio de total</label>
        <input type="text" name="nota_cambio_total" class="form-control">
      </div>
      <div class="col-md-4">
        <label class="form-label">Valor Unitario</label>
        <input type="text" name="valor_unitario" class="form-control">
      </div>
      <div class="col-md-4">
        <label class="form-label">Valor</label>
        <input type="text" name="valor" class="form-control">
      </div>
      <div class="col-md-4">
        <label class="form-label">Unidad</label>
        <input type="text" name="unidad" class="form-control">
      </div>
      <div class="col-md-4">
        <label class="form-label">Longitud (in)</label>
        <input type="number" name="longitud" class="form-control">
      </div>
      <div class="col-md-4">
        <label class="form-label">Ancho (in)</label>
        <input type="number" name="ancho" class="form-control">
      </div>
      <div class="col-md-4">
        <label class="form-label">Altura (in)</label>
        <input type="number" name="altura" class="form-control">
      </div>
      <div class="col-md-4">
        <label class="form-label">Peso (lb)</label>
        <input type="number" name="peso" step="0.01" class="form-control">
      </div>
    </div>

    <div id="extraBlock" style="display:none;" class="mt-3">
      <div class="alert alert-warning mb-3">
        Faltan <span id="remainingBoxes">0</span> cajas por registrar
      </div>
      <div class="row g-3">
        <div class="col-md-4">
          <label class="form-label">Modelo adicional</label>
          <input type="text" name="modelo_extra" class="form-control">
        </div>
        <div class="col-md-4">
          <label class="form-label">Palets Restantes</label>
          <input type="number" name="palets_restante" class="form-control">
        </div>
        <div class="col-md-4">
          <label class="form-label">Cantidad de Cajas Restantes</label>
          <input type="number" name="cantidad_restante" class="form-control">
        </div>
        <div class="col-md-4">
          <label class="form-label">Valor Unitario</label>
          <input type="text" name="valor_unitario_restante" class="form-control">
        </div>
        <div class="col-md-4">
          <label class="form-label">Valor</label>
          <input type="text" name="valor_restante" class="form-control">
        </div>
        <div class="col-md-4">
          <label class="form-label">Unidad</label>
          <input type="text" name="unidad_restante" class="form-control">
        </div>
        <div class="col-md-4">
          <label class="form-label">Longitud (in)</label>
          <input type="number" name="longitud_restante" class="form-control">
        </div>
        <div class="col-md-4">
          <label class="form-label">Ancho (in)</label>
          <input type="number" name="ancho_restante" class="form-control">
        </div>
        <div class="col-md-4">
          <label class="form-label">Altura (in)</label>
          <input type="number" name="altura_restante" class="form-control">
        </div>
        <div class="col-md-4">
          <label class="form-label">Peso (lb)</label>
          <input type="number" name="peso_restante" step="0.01" class="form-control">
        </div>
      </div>
    </div>

    <div class="mt-4 d-flex justify-content-between">
      <a href="../admins/warehouseUsaPanel.php" class="btn btn-secondary">Cancelar</a>
      <button type="submit" class="btn btn-success">Guardar Warehouse</button>
    </div>
  </div>
</form>

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
<!-- Completador de select -->
<script>
document.addEventListener('DOMContentLoaded', () => {
  const bookingSelect = document.getElementById('bookingSelect');
  const facturaSelect = document.getElementById('facturaSelect');

  if (!bookingSelect || !facturaSelect) {
    console.error('No se encontraron los elementos bookingSelect o facturaSelect');
    return;
  }

  bookingSelect.addEventListener('change', function () {
    const booking = this.value;

    // Limpiar y desactivar el segundo select
    facturaSelect.innerHTML = '<option value="">-- Selecciona --</option>';
    facturaSelect.disabled = true;

    if (!booking) return;

    fetch(`../api/exports/get_invoices.php?booking=${encodeURIComponent(booking)}`)
      .then(response => response.json())
      .then(data => {
        if (data.length > 0) {
          data.forEach(invoice => {
            const option = document.createElement('option');
            option.value = invoice;
            option.textContent = invoice;
            facturaSelect.appendChild(option);
          });
          facturaSelect.disabled = false;
        } else {
          const opt = document.createElement('option');
          opt.text = 'Sin facturas disponibles';
          opt.disabled = true;
          facturaSelect.appendChild(opt);
        }
      })
      .catch(error => {
        console.error('Error al cargar facturas:', error);
      });
  });
});
</script>




<!--Autocalcular totales-->
<!-- 1) Cálculo dinámico de totales -->
<!-- <script>
var originalTotal = 0;
document.addEventListener('DOMContentLoaded', () => {
  function formatCurrency(value) {
    let parts = value.toFixed(2).split('.');
    parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    return parts.join(',');
  }

  function parseCurrency(str) {
    return parseFloat(str.replace(/\./g, '').replace(',', '.')) || 0;
  }

  function recalculate() {
    document.querySelectorAll('tr[data-item-id]').forEach(tr => {
      const qty = parseFloat(tr.querySelector('.cantidad').value) || 0;
      const vu  = parseCurrency(tr.querySelector('.valor-unitario').value);
      const vt  = qty * vu;
      tr.querySelector('.valor-total').value = formatCurrency(vt);
    });

    document.querySelectorAll('.incoterm-item').forEach(container => {
      const incTot = Array.from(container.querySelectorAll('.valor-total'))
        .reduce((sum, input) => sum + parseCurrency(input.value), 0);
      container.querySelector('.total-incoterm').textContent = formatCurrency(incTot);
    });

    const totalGeneral = Array.from(document.querySelectorAll('.total-incoterm'))
      .reduce((sum, span) => sum + parseCurrency(span.textContent), 0);
    document.getElementById('totalGeneral').innerHTML = 'Total General: $' + formatCurrency(totalGeneral);
  }

  document.getElementById('incotermContainer')
    .addEventListener('input', e => {
      if (e.target.matches('.cantidad') || e.target.matches('.valor-unitario')) {
        recalculate();
      }
    });

  recalculate();
});
</script> -->

<script>
document.addEventListener('DOMContentLoaded', () => {
  const form       = document.querySelector('form[action="#"]');
  const facturaSel = document.getElementById('facturaSelect');
  const loteSel    = document.getElementById('loteSelect');
  const parteSel   = document.getElementById('parteSelect');
  const ordenInput = document.getElementById('ordenInput');
  const ordenList  = document.getElementById('ordenList');

  let originalTotal = 0;

  function fillSelect(selectEl, csv) {
    selectEl.innerHTML = '<option value="">-- Selecciona --</option>';
    if (!csv) return;
    const items = Array.isArray(csv) ? csv : csv.split(',');
    items.forEach(val => {
      const v = val.trim();
      if (!v) return;
      const o = document.createElement('option');
      o.value = v;
      o.textContent = v;
      selectEl.appendChild(o);
    });
    selectEl.disabled = false;
  }

  form.addEventListener('submit', e => {
    e.preventDefault();
    const data = {
      fecha_entrada: form.fecha_entrada.value,
      fecha_salida:  form.fecha_salida.value,
      recibo_almacen: form.recibo_almacen.value.trim(),
      estado:         form.estado.value.trim(),
      numero_factura: form.numero_factura.value.trim(),
      numero_lote:    form.numero_lote.value.trim(),
      numero_contenedor: form.numero_contenedor.value,
      palets:         form.palets.value.trim(),
      orden_compra:   (function(v){
                         v = v.trim();
                         return v.toLowerCase() === 'stock' ? '0' : v;
                       })(ordenInput.value),
      numero_parte:   form.numero_parte.value.trim(),
      descripcion:    form.descripcion.value.trim(),
      modelo:         form.modelo.value.trim(),
      cantidad:       parseInt(form.cantidad.value) || 0,
      valor_unitario: form.valor_unitario.value.trim(),
      valor:          form.valor.value.trim(),
      unidad:         form.unidad.value.trim(),
      longitud:       form.longitud.value,
      ancho:          form.ancho.value,
      altura:         form.altura.value,
      peso:           form.peso.value,
      palets_restante:   form.palets_restante.value,
      cantidad_restante: parseInt(form.cantidad_restante.value) || 0,
      valor_unitario_restante: form.valor_unitario_restante.value.trim(),
      valor_restante:          form.valor_restante.value.trim(),
      unidad_restante:         form.unidad_restante.value.trim(),
      longitud_restante:       form.longitud_restante.value,
      ancho_restante:          form.ancho_restante.value,
      altura_restante:         form.altura_restante.value,
      peso_restante:           form.peso_restante.value
    };

   fetch('../api/warehouseusa/guardar_manual.php', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify(data)
})
.then(async response => {
  const text = await response.text(); // leo la respuesta como texto
  console.log('Respuesta cruda del servidor:', text);

  try {
    const resp = JSON.parse(text); // intento parsear a JSON
    console.log('Respuesta JSON:', resp);

    if (response.ok && resp.success) {
      Swal.fire({ icon: 'success', title: 'Guardado', text: 'Registro creado' })
        .then(() => location.href = '../admins/warehouseUsaPanel.php');
    } else {
      Swal.fire({ icon: 'error', title: 'Error', text: resp.error || resp.message || 'Error desconocido' });
    }
  } catch (error) {
    console.error('No es JSON:', error);
    Swal.fire({ icon: 'error', title: 'Error', text: 'Respuesta no JSON del servidor. Mira la consola.' });
  }
})
.catch(err => {
  console.error('Error en fetch:', err);
  Swal.fire({ icon: 'error', title: 'Error', text: err.message || 'Error de servidor' });
});

  });

  document.getElementById('bookingSelect').addEventListener('change', function() {
    const booking   = this.value;
    const descSelect = document.getElementById('descripcionSelect');

    descSelect.innerHTML = '<option value="">-- Selecciona --</option>';
    descSelect.disabled = true;
    facturaSel.innerHTML = '<option value="">-- Selecciona --</option>'; facturaSel.disabled = true;
    loteSel.innerHTML    = '<option value="">-- Selecciona --</option>'; loteSel.disabled    = true;
    ordenList.innerHTML  = '';
    ordenInput.value     = '';
    ordenInput.disabled  = true;
    parteSel.innerHTML   = '<option value="">-- Selecciona --</option>'; parteSel.disabled   = true;
    document.querySelector('input[name="cantidadTotal"]').value = '';
    originalTotal = 0;

    if (!booking) return;

    fetch(`../api/warehouseusa/get_descriptions_by_booking.php?booking=${encodeURIComponent(booking)}`)
      .then(r => r.json())
      .then(resp => {
        if (!resp.success) {
          return Swal.fire('Error', resp.msg, 'error');
        }
        resp.descriptions.forEach(d => {
          const o = document.createElement('option');
          o.value = d;
          o.textContent = d;
          descSelect.appendChild(o);
        });
        descSelect.disabled = false;

        fillSelect(facturaSel, resp.numero_factura);
        fillSelect(loteSel,    resp.numero_lote);
        fillSelect(parteSel,   resp.numero_parte);

        if (Array.isArray(resp.numero_orden_compra)) {
          resp.numero_orden_compra.forEach(o => {
            const opt = document.createElement('option');
            opt.value = o;
            ordenList.appendChild(opt);
          });
        }
        ordenInput.disabled = false;
        document.querySelector('input[name="cantidadTotal"]').value = resp.cantidad_total;
        originalTotal = parseInt(resp.cantidad_total) || 0;

        updateRemaining();
        updateValorTotal();
        updateValorRestante();

        descSelect.dataset.booking = booking;
      })
      .catch(err => {
        console.error(err);
        Swal.fire('Error', 'No se pudo cargar descripciones', 'error');
      });
  });

  document.getElementById('descripcionSelect').addEventListener('change', function() {
    const description = this.value;
    const booking     = this.dataset.booking;
    if (!description) return;

    fetch(`../api/warehouseusa/get_item_info.php?booking=${encodeURIComponent(booking)}&description=${encodeURIComponent(description)}`)
      .then(r => r.json())
      .then(resp => {
        if (!resp.success) {
          return Swal.fire('Error', resp.msg, 'error');
        }
        const i = resp.data;

        form.valor_unitario.value = i.valor_unitario_usa;
        form.valor.value          = i.valor_usa;
        form.unidad.value         = i.unidad;
        form.peso.value           = i.peso;
        form.modelo.value         = i.modelo || '';
        form.longitud.value       = i.longitud_in || '';
        form.ancho.value          = i.ancho_in || '';
        form.altura.value         = i.altura_in || '';
        form.cantidadTotal.value  = i.cantidad || '';
        originalTotal = parseInt(i.cantidad) || 0;

        updateRemaining();
        updateValorTotal();
        updateValorRestante();

        form.modelo_extra.value              = i.modelo  || '';
        form.valor_unitario_restante.value   = i.valor_unitario_usa;
        form.valor_restante.value            = i.valor_usa;
        form.unidad_restante.value           = i.unidad;
        form.longitud_restante.value         = i.longitud_in || '';
        form.ancho_restante.value            = i.ancho_in   || '';
        form.altura_restante.value           = i.altura_in  || '';
        form.peso_restante.value             = i.peso;

        document.getElementById("numeroContenedor").value        = i.numero_contenedor || "";
        document.getElementById("numeroContenedorDisplay").value = i.numero_contenedor || "";

        facturaSel.value = i.numero_factura; facturaSel.disabled = false;
        loteSel.value    = i.numero_lote;    loteSel.disabled    = false;
        parteSel.value   = i.numero_parte;   parteSel.disabled   = false;

        ordenInput.value = i.numero_orden_compra || '';
        ordenInput.disabled = false;

        form.recibo_almacen.value = i.recibo_almacen || '';
      })
      .catch(() => Swal.fire('Error', 'No se pudo cargar el detalle', 'error'));
  });

  const paletsInput    = form.palets;
  const cajasInput     = form.cantidad;
  const totalInput     = form.cantidadTotal;
  const extraBlock     = document.getElementById('extraBlock');
  const remainingSpan  = document.getElementById('remainingBoxes');
  const noteBlock      = document.getElementById('totalUpdateNote');

  function updateRemaining() {
    const palets = parseInt(paletsInput.value) || 0;
    const cajas  = parseInt(cajasInput.value) || 0;
    const product = palets * cajas;
    let diff;

    if (product > originalTotal) {
      totalInput.value = product;
      noteBlock.style.display = 'block';
      noteBlock.querySelector('input').value = 'Total ajustado automáticamente';
      diff = 0;
    } else {
      totalInput.value = originalTotal;
      noteBlock.style.display = 'none';
      noteBlock.querySelector('input').value = '';
      diff = originalTotal - product;
    }

    remainingSpan.textContent = diff;
    extraBlock.style.display = diff > 0 ? 'block' : 'none';
    if (diff > 0) {
      ['valor_unitario','valor','unidad','longitud','ancho','altura','peso','palets','cantidad']
        .forEach(name => {
          const target = form[`${name}_restante`];
          if (target) target.value = form[name].value;
        });
      form.cantidad_restante.value = diff;
    }
    updateValorRestante();
  }

  function updateValorTotal() {
    const palets = parseFloat(paletsInput.value) || 0;
    const cajas  = parseFloat(cajasInput.value)  || 0;
    const valorUnitario = parseFloat(form.valor_unitario.value) || 0;

    const totalValor = palets * cajas * valorUnitario;
    form.valor.value = totalValor.toFixed(2);
  }

  function updateValorRestante() {
    const paletsRestante = parseFloat(form.palets_restante.value) || 0;
    const cajasRestante  = parseFloat(form.cantidad_restante.value) || 0;
    const valorUnitarioRestante = parseFloat(form.valor_unitario_restante.value) || 0;

    const totalValorRestante = paletsRestante * cajasRestante * valorUnitarioRestante;
    form.valor_restante.value = totalValorRestante.toFixed(2);
  }

  [paletsInput, cajasInput, form.valor_unitario].forEach(el =>
    el.addEventListener('input', () => {
      updateRemaining();
      updateValorTotal();
    })
  );

  [form.palets_restante, form.cantidad_restante, form.valor_unitario_restante].forEach(el =>
    el.addEventListener('input', updateValorRestante)
  );

  updateRemaining();
  updateValorTotal();
  updateValorRestante();
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
