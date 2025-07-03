<?php
session_start();
include('../usuarioClass.php');
include("../con_db.php");
$IdUsuario=$_SESSION["IdUsuario"];

$usuario= new Usuario($conexion);

$user=$usuario->obtenerUsuarioPorId($IdUsuario);

  $stmtBookings = $conexion->prepare("
    SELECT DISTINCT Booking_BK
    FROM container
    ORDER BY Booking_BK
  ");
  $stmtBookings->execute();
  $bookings = $stmtBookings->get_result()->fetch_all(MYSQLI_ASSOC);

?>


<!DOCTYPE html>
<html lang="en">
  <!-- [Head] start -->

  <head>
    <title>Liquidaciones | Crear Warehouse USA</title>
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
                <li class="pc-item"><a class="pc-link" href="../dashboard/transit-inventory.php">Transit Inventory</a></li>
                <li class="pc-item"><a class="pc-link" href="../dashboard/warehouse-inventory.php">WareHouse Inventory</a></li>
                <li class="pc-item"><a class="pc-link" href="../admins/warehouseUsaPanel.php">WareHouse USA</a></li>
                <li class="pc-item"><a class="pc-link" href="../dashboard/total-inventory.php">Total Inventory</a></li>
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
          <li class="breadcrumb-item"><a href="javascript: void(0)">Inventarios</a></li>
          <li class="breadcrumb-item"><a href="javascript: void(0)">Warehouse USA</a></li>
          <li class="breadcrumb-item" aria-current="page">Crear</li>
        </ul>
      </div>
      <div class="col-md-12">
        <div class="page-header-title">
          <h2 class="mb-0">Crear Warehouse USA</h2>
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
        <select id="descripcionSelect" class="form-control" disabled>
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
        <label class="form-label" for="ordenSelect">Número de Orden de Compra</label>
        <select id="ordenSelect" name="orden_compra" class="form-control" disabled>
          <option value="">-- Selecciona --</option>
        </select>
      </div>
      <div class="col-md-4">
        <label class="form-label" for="parteSelect">Número de Parte</label>
        <select id="parteSelect" name="numero_parte" class="form-control" disabled>
          <option value="">-- Selecciona --</option>
        </select>
      </div>
      <div class="col-12">
        <label class="form-label">Descripción</label>
        <textarea name="descripcion" class="form-control" rows="2"></textarea>
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

    <div class="mt-4 d-flex justify-content-between">
      <a href="../admins/despachosPanel.php" class="btn btn-secondary">Cancelar</a>
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
<!--Completador de select-->
<script>
  document.getElementById('bookingSelect').addEventListener('change', function () {
    const booking = this.value;
    const invoiceSelect = document.getElementById('invoiceSelect');

    // Limpiar y desactivar el segundo select
    invoiceSelect.innerHTML = '<option selected>Seleccionar...</option>';
    invoiceSelect.disabled = true;

    if (booking && booking !== 'Seleccionar...') {
      fetch(`../api/exports/get_invoices.php?booking=${encodeURIComponent(booking)}`)
        .then(response => response.json())
        .then(data => {
          if (data.length > 0) {
            data.forEach(invoice => {
              const option = document.createElement('option');
              option.value = invoice;
              option.textContent = invoice;
              option.selected=true;
              invoiceSelect.appendChild(option);
            });
            invoiceSelect.disabled = false;
          } else {
            const opt = document.createElement('option');
            opt.text = 'Sin facturas disponibles';
            opt.disabled = true;
            invoiceSelect.appendChild(opt);
          }
        })
        .catch(error => {
          console.error('Error al cargar facturas:', error);
        });
    }
  });
</script>



<!--Autocalcular totales-->
<!-- 1) Cálculo dinámico de totales -->
<script>
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
</script>
<script>
document.addEventListener('DOMContentLoaded', () => {
  const form = document.querySelector('form[action="#"]');
  form.addEventListener('submit', e => {
    e.preventDefault();
    const data = {
      fecha_entrada: form.fecha_entrada.value,
      fecha_salida: form.fecha_salida.value,
      recibo_almacen: form.recibo_almacen.value.trim(),
      estado: form.estado.value.trim(),
      numero_factura: form.numero_factura.value.trim(),
      numero_lote: form.numero_lote.value.trim(),
      palets: form.palets.value.trim(),
      orden_compra: form.orden_compra.value.trim(),
      numero_parte: form.numero_parte.value.trim(),
      descripcion: form.descripcion.value.trim(),
      modelo: form.modelo.value.trim(),
      cantidad: parseInt(form.cantidad.value) || 0,
      valor_unitario: form.valor_unitario.value.trim(),
      valor: form.valor.value.trim(),
      unidad: form.unidad.value.trim(),
      longitud: form.longitud.value,
      ancho: form.ancho.value,
      altura: form.altura.value,
      peso: form.peso.value
    };

    fetch('../api/warehouseusa/guardar_manual.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(data)
    })
    .then(r => r.json())
    .then(resp => {
      if (resp.success) {
        Swal.fire({ icon: 'success', title: 'Guardado', text: 'Registro creado' })
          .then(() => location.href = '../admins/warehouseUsaPanel.php');
      } else {
        Swal.fire({ icon: 'error', title: 'Error', text: resp.message || 'Error' });
      }
    })
    .catch(() => {
      Swal.fire({ icon: 'error', title: 'Error', text: 'Error de servidor' });
    });
  });
});

</script>
<script>

const facturaSel = document.getElementById('facturaSelect');
const loteSel    = document.getElementById('loteSelect');
const ordenSel   = document.getElementById('ordenSelect');
const parteSel   = document.getElementById('parteSelect');

function fillSelect(selectEl, csv) {
  selectEl.innerHTML = '<option value="">-- Selecciona --</option>';
  csv.split(',').forEach(val => {
    const v = val.trim();
    if (v) {
      const o = document.createElement('option');
      o.value = v;
      o.textContent = v;
      selectEl.appendChild(o);
    }
  });
  selectEl.disabled = false;
}
// Al cambiar booking
document.getElementById('bookingSelect').addEventListener('change', function() {
  const booking = this.value;
  const descSelect = document.getElementById('descripcionSelect');

  // reseteo
  descSelect.innerHTML = '<option value="">-- Selecciona --</option>';
  descSelect.disabled = true;

  if (!booking) return;

  fetch(`../api/warehouseusa/get_descriptions_by_booking.php?booking=${encodeURIComponent(booking)}`)
    .then(r => r.json())
    .then(resp => {
      if (!resp.success) {
        return Swal.fire('Error', resp.msg, 'error');
      }
      resp.descriptions.forEach(d => {
        const opt = document.createElement('option');
        opt.value = d;
        opt.textContent = d;
        descSelect.appendChild(opt);
      });

      fillSelect(facturaSel, resp.numero_factura);
      fillSelect(loteSel,    resp.numero_lote);
      fillSelect(ordenSel,   resp.numero_orden_compra);
      fillSelect(parteSel,   resp.numero_parte);
      descSelect.dataset.booking = booking;
      descSelect.disabled = false;
      document.querySelector('input[name="cantidadTotal"]').value = resp.cantidad_total;
      // guardo el booking para el siguiente fetch
      descSelect.dataset.booking = booking;
    })
    .catch(() => Swal.fire('Error', 'No se pudo cargar descripciones', 'error'));
});

// Al cambiar descripción
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
      // Mapea aquí los campos de tu form:
      document.querySelector('input[name="cantidad"]').value       = i.cantidad;
      document.querySelector('input[name="valor_unitario"]').value = i.valor_unitario;
      document.querySelector('input[name="valor"]').value          = i.valor;
      document.querySelector('input[name="unidad"]').value         = i.unidad;
      document.querySelector('input[name="peso"]').value           = i.peso;
      // …y cualquier otro campo que necesites
    })
    .catch(() => Swal.fire('Error', 'No se pudo cargar el detalle', 'error'));
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
