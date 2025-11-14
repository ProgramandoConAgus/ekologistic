<?php
session_start();
include('../usuarioClass.php');
include("../con_db.php");
$IdUsuario=$_SESSION["IdUsuario"];
$usuario = new Usuario($conexion);
$user=$usuario->obtenerUsuarioPorId($IdUsuario);

?>


<!DOCTYPE html>
<html lang="en">
  <!-- [Head] start -->

  <head>
    <title>Products Details | Light Able Admin & Dashboard Template</title>
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
          <li class="breadcrumb-item"><a href="javascript: void(0)">Liquidaciones</a></li>
          <li class="breadcrumb-item"><a href="javascript: void(0)">Export</a></li>
          <li class="breadcrumb-item" aria-current="page">Crear</li>
        </ul>
      </div>
      <div class="col-md-12">
        <div class="page-header-title">
          <h2 class="mb-0">Crear Import</h2>
        </div>
      </div>
    </div>
  </div>
</div>

        <!-- [ breadcrumb ] end -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

<div class="container mt-5">
  <div class="card shadow-sm">
    <div class="card-body">

      <!-- Dropdowns -->
      <div class="row mb-4">
        <div class="row">
          <!-- Select Booking -->
          <div class="col-md-6 mb-3">
            <label for="bookingSelect" class="form-label">Booking</label>
            <select id="bookingSelect" class="form-select">
              <option selected>Seleccionar...</option>
              <?php
                $query = "SELECT DISTINCT c.Booking_BK 
                          FROM container c ";
                $result = $conexion->query($query);
                while($row = $result->fetch_assoc()) { 
              ?>
                <option value="<?= htmlspecialchars($row['Booking_BK']) ?>">
                  <?= htmlspecialchars($row['Booking_BK']) ?>
                </option>
              <?php } ?>
            </select>
          </div>

          <!-- Select Invoice -->
          <div class="col-md-6 mb-3">
            <label for="invoiceSelect" class="form-label">Commercial Invoice</label>
            <select id="invoiceSelect" class="form-select tom-select" multiple size="4" disabled>
              <option>Seleccionar...</option>
              <!-- Se autocompleta con Javascript (buscar comentario de "Completador de select")  -->
            </select>
          </div>
          <div class="col-md-6 mb-3">
            <label for="nOpSelect" class="form-label">Nº Operacion</label>
            <select id="nOpSelect" class="form-select" disabled>
              <option selected>Seleccionar...</option>
              <!-- Se autocompleta con Javascript (buscar comentario de "Completador de select")  -->
            </select>
          </div>
          <div class="col-md-6 mb-3">
          </div>
          <div class="col-md-6 mb-3">
            
            <label for="productoEXW" class="form-label">Costo del producto EXW</label>
            <h2 id="productoEXW"></h2>
          </div>
          <div class="col-md-6 mb-3">
            <label for="coeficiente" class="form-label">COEFICIENTE %</label>
            <h2 id="coeficiente"></h2>
          </div>
        </div>

      </div>

      <?php
$incoterms = [];
$res = $conexion->query("
  SELECT IdTipoIncoterm, NombreTipoIncoterm
  FROM tipoincoterm
  WHERE IdTipoIncoterm IN (4, 5, 6)
  ORDER BY IdTipoIncoterm
");
while ($inc = $res->fetch_assoc()) {
  $incoterms[] = $inc;
}
?>


  <!-- 2) Select dinámico -->
      <div class="mb-4 d-flex align-items-start gap-3">
        <div style="flex:1">
          <label for="incotermSelect" class="form-label">Elige Incoterm:</label>
          <select id="incotermSelect" class="form-select">
          <option value="">-- Selecciona --</option>
          <?php foreach ($incoterms as $inc): ?>
            <option value="<?= $inc['IdTipoIncoterm'] ?>">
              <?= htmlspecialchars($inc['NombreTipoIncoterm']) ?>
            </option>
          <?php endforeach; ?>
          </select>
        </div>
        <div class="d-flex align-items-end">
          <!-- Botón movido: ahora se muestra debajo del listado -->
        </div>
      </div>

      <!-- 3) Contenedores dinámicos -->
      <div id="incotermContainer">
        <?php foreach ($incoterms as $inc): ?>
          <div class="incoterm-item" data-incoterm="<?= $inc['IdTipoIncoterm'] ?>" style="display: none;">
            <h5 class="mt-3"><?= htmlspecialchars($inc['NombreTipoIncoterm']) ?></h5>
            <table class="table table-hover table-borderless mb-0">
              <thead>
                <tr>
                  <th>Descripción</th>
                  <th>Cantidad</th>
                  <th>Valor U.</th>
                  <th>Valor T.</th>
                  <th>Notas</th>
                  <?php
                   if($inc['IdTipoIncoterm']==3){
                    ?>
                  <th>% Impuesto</th>
                  <th>Valor Impuesto</th>
                  <?php
                   }
                   ?>
                </tr>
              </thead>
              <tbody>
                <?php
                  $items = $conexion->query(
                    "SELECT IdItemsLiquidacionImport, NombreItems , posicion
                    FROM itemsliquidacionimport
                    WHERE IdTipoIncoterm = {$inc['IdTipoIncoterm']}
                    Order By posicion"
                  );
                  while ($row = $items->fetch_assoc()):
                ?>
                <tr data-item-id="<?= $row['IdItemsLiquidacionImport'] ?>">
                  <td><?= htmlspecialchars($row['NombreItems']) ?></td>
                  <td><input type="number" class="form-control form-control-sm cantidad" value="0" min="0"></td>
                  <td>
                    <div class="input-group input-group-sm">
                      <span class="input-group-text">$</span>
                      <input type="text" class="form-control valor-unitario" value="0,00">
                    </div>
                  </td>
                  <td>
                    <div class="input-group input-group-sm">
                      <span class="input-group-text">$</span>
                      <input type="text" class="form-control valor-total " value="0,00" readonly>
                    </div>
                  </td>
                  <td>
                    <input type="text" class="form-control form-control-sm notas" placeholder="Notas">
                  </td>
                    <!-- NUEVAS COLUMNAS -->
                   <?php
                   if($inc['IdTipoIncoterm']==3){
                    ?>
                  <td>
                    <div class="input-group input-group-sm">
                      <input type="number" class="form-control impuesto" value="0" min="0" max="100">
                      <span class="input-group-text">%</span>
                    </div>
                  </td>
                  <td>
                    <div class="input-group input-group-sm">
                      <span class="input-group-text">$</span>
                      <input type="text" class="form-control valor-impuesto" value="0,00" readonly>
                    </div>
                  </td>
                </tr>
                <?php
                   }
                endwhile; ?>
              </tbody>
            </table>

            <h5 class="mt-4 text-success fw-bold">
              Total <?= htmlspecialchars($inc['NombreTipoIncoterm']) ?>: $
              <span class="total-incoterm" data-incoterm-total="<?= $inc['IdTipoIncoterm'] ?>">0,00</span>
            </h5>
          </div>
        <?php endforeach; ?>
      </div>

      <!-- Botón global para agregar item (moved here so it's visible under the incoterm list) -->
      <div class="mt-3 mb-3 d-flex gap-2">
        <button id="btnAgregarItem" type="button" class="btn btn-outline-primary">+ Agregar item</button>
        <button id="btnAgregarExtras" type="button" class="btn btn-outline-secondary">+ Agregar Extras</button>
      </div>

      <!-- Botones y Total General -->
      <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-3 mt-4">
        <button class="btn btn-primary" onclick="window.location.href = '../admins/importsPanel.php'">Volver</button>
        <h5 id="totalGeneral" class="text-success fw-bold m-0 text-center">
          Total General: $0,00
        </h5>
        <button id="btnGuardar" type="button" class="btn btn-success">Guardar</button>
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


<!-- Script para mostrar/ocultar -->
<script>
  const sel = document.getElementById('incotermSelect');
  const bloques = document.querySelectorAll('.incoterm-item');
  sel.addEventListener('change', () => {
    bloques.forEach(b => {
      b.style.display = (b.dataset.incoterm === sel.value) ? 'block' : 'none';
    });
  });
</script>
<!-- Tom Select (vanilla, no jQuery) for improved multiselect UX -->
<link href="https://cdn.jsdelivr.net/npm/tom-select/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/tom-select/dist/js/tom-select.complete.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function(){
  const invoiceSel = document.getElementById('invoiceSelect');
  if (invoiceSel) {
    try {
      window.invoiceTomSelect = new TomSelect('#invoiceSelect', {
        plugins: ['remove_button'],
        maxItems: null,
        dropdownParent: 'body',
        placeholder: 'Seleccione facturas...'
      });
    } catch(e) {
      console.warn('TomSelect init failed', e);
    }
  }
});
</script>
<script>
document.addEventListener("DOMContentLoaded", function () {

  function normalizaNumero(v) {
    // Acepta formatos: "15%", "15,5%", "1.234,56", "1234.56" y "0.15"
    if (v === undefined || v === null) return 0;
    let s = String(v).trim();
    if (s === '') return 0;
    // Si viene con porcentaje, convertir a decimal
    if (s.indexOf('%') !== -1) {
      s = s.replace('%', '').replace(',', '.').trim();
      const n = parseFloat(s);
      return isNaN(n) ? 0 : n / 100;
    }
    // Si contiene coma como decimal (formato local), eliminar separador de miles
    if (s.indexOf(',') !== -1) {
      s = s.replace(/\./g, '').replace(',', '.');
    } else {
      s = s.replace(/,/g, '.');
    }
    const n = parseFloat(s);
    return isNaN(n) ? 0 : n;
  }

  function recalcularFila(tr) {
    const qty = normalizaNumero(tr.querySelector(".cantidad")?.value);
    const vu  = normalizaNumero(tr.querySelector(".valor-unitario")?.value);

    const vt = qty * vu;
    tr.querySelector(".valor-total").value = vt.toFixed(2);

    // Si existe impuesto en esta fila (solo para incoterm 3)
    const impInput = tr.querySelector(".impuesto");
    if (impInput) {
      const imp = normalizaNumero(impInput.value);
      const vi = vt * (imp / 100);
      tr.querySelector(".valor-impuesto").value = vi.toFixed(2);
    }
  }

  function totalBloque(block) {
    let sum = 0;
    block.querySelectorAll("tbody tr").forEach(tr => {
      const vt = normalizaNumero(tr.querySelector(".valor-total")?.value);
      const vi = normalizaNumero(tr.querySelector(".valor-impuesto")?.value);
      sum += vt + vi;
    });
    const id = block.dataset.incoterm;
    document.querySelector(`[data-incoterm-total="${id}"]`).textContent = sum.toFixed(2);
    return sum;
  }

  function recalcularTodo() {
    const visible = document.querySelector('.incoterm-item[style*="block"]');
    if (!visible) return;

    const general = totalBloque(visible);
    document.getElementById("totalGeneral").textContent = `Total General: $${general.toFixed(2)}`;

    const totalECU = normalizaNumero(document.getElementById("productoEXW").dataset.totalEcu);
    if (totalECU > 0) {
      const coef = (general / totalECU) * 100;
      document.getElementById("coeficiente").textContent = coef.toFixed(2) + "%";
    }
  }

  function attachEvents() {
    document.querySelectorAll(".incoterm-item tbody tr").forEach(tr => {
      ["cantidad", "valor-unitario", "impuesto"].forEach(cls => {
        const input = tr.querySelector(`.${cls}`);
        if (!input) return;
        input.addEventListener("input", () => {
          recalcularFila(tr);
          recalcularTodo();
        });
      });
      // Si la fila tiene un input de cantidad marcado como porcentaje, agregar focus/blur
      const qty = tr.querySelector('.cantidad');
      if (qty && qty.dataset && qty.dataset.isPercent) {
        qty.addEventListener('focus', () => {
          let v = String(qty.value || '').trim();
          if (v.indexOf('%') !== -1) {
            v = v.replace('%', '').replace(',', '.').trim();
          } else {
            const n = parseFloat(v.replace(',', '.'));
            if (!isNaN(n) && n <= 1) v = (n * 100).toString();
          }
          qty.value = v;
        });
        qty.addEventListener('blur', () => {
          let v = String(qty.value || '').trim().replace(',', '.');
          let n = parseFloat(v);
          if (isNaN(n)) n = 0;
          qty.value = (n).toFixed(2).replace('.', ',') + '%';
          recalcularFila(tr);
          recalcularTodo();
        });
      }
    });
  }

  attachEvents();

  // Adjunta eventos a una fila nueva (evita re-iterar sobre todas)
  function attachEventsToRow(tr) {
    ["cantidad", "valor-unitario", "impuesto"].forEach(cls => {
      const input = tr.querySelector(`.${cls}`);
      if (!input) return;
      input.addEventListener('input', () => {
        recalcularFila(tr);
        recalcularTodo();
      });
    });

    // Si el campo cantidad es porcentaje, agregar focus/blur (compatibilidad)
    const qty = tr.querySelector('.cantidad');
    if (qty && qty.dataset && qty.dataset.isPercent) {
      qty.addEventListener('focus', () => {
        let v = String(qty.value || '').trim();
        if (v.indexOf('%') !== -1) v = v.replace('%', '').replace(',', '.').trim();
        qty.value = v;
      });
      qty.addEventListener('blur', () => {
        let v = String(qty.value || '').trim().replace(',', '.');
        let n = parseFloat(v);
        if (isNaN(n)) n = 0;
        if (qty.dataset.decimal) {
          qty.value = (n).toFixed(4).replace('.', ',') + '%';
        } else {
          qty.value = (n).toFixed(2).replace('.', ',') + '%';
        }
        recalcularFila(tr);
        recalcularTodo();
      });
    }

    // delete button
    const del = tr.querySelector('.btn-delete-item');
    if (del) {
      del.addEventListener('click', () => {
        tr.remove();
        recalcularTodo();
      });
    }
  }

  // Crea una fila vacía para el incoterm indicado
  function crearFilaNueva(incotermId) {
    const tr = document.createElement('tr');
    tr.dataset.itemId = '0'; // nuevo item temporal

    // columnas base: descripcion, cantidad, valor unitario, valor total, notas
    let html = '';
    html += `<td><input type="text" class="form-control nombre-items" placeholder="Nombre del item"></td>`;
    html += `<td><input type="number" class="form-control form-control-sm cantidad" value="0" min="0"></td>`;
    html += `<td><div class="input-group input-group-sm"><span class="input-group-text">$</span><input type="text" class="form-control valor-unitario" value="0,00"></div></td>`;
    html += `<td><div class="input-group input-group-sm"><span class="input-group-text">$</span><input type="text" class="form-control valor-total" value="0,00" readonly></div></td>`;
    html += `<td><div class="d-flex gap-2"><input type="text" class="form-control form-control-sm notas" placeholder="Notas"><button type="button" class="btn btn-sm btn-outline-danger btn-delete-item">Eliminar</button></div></td>`;

    // si incoterm 3, agregar impuesto y valor impuesto
    if (Number(incotermId) === 3) {
      html += `<td><div class="input-group input-group-sm"><input type="number" class="form-control impuesto" value="0" min="0" max="100"><span class="input-group-text">%</span></div></td>`;
      html += `<td><div class="input-group input-group-sm"><span class="input-group-text">$</span><input type="text" class="form-control valor-impuesto" value="0,00" readonly></div></td>`;
    }

    tr.innerHTML = html;
    return tr;
  }

  // boton agregar item global
  const btnAgregar = document.getElementById('btnAgregarItem');
  if (btnAgregar) {
    btnAgregar.addEventListener('click', () => {
      const incSel = document.getElementById('incotermSelect');
      const incId = incSel.value;
      if (!incId) return Swal.fire('Selecciona un Incoterm', 'Elige primero el incoterm donde agregar el item', 'warning');

      const container = document.querySelector(`.incoterm-item[data-incoterm="${incId}"]`);
      if (!container) return Swal.fire('Error', 'No se encontró el contenedor del incoterm', 'error');
      const tbody = container.querySelector('tbody');
      if (!tbody) return Swal.fire('Error', 'Tabla inválida', 'error');

      const nueva = crearFilaNueva(incId);
      tbody.appendChild(nueva);
      attachEventsToRow(nueva);
      recalcularFila(nueva);
      recalcularTodo();
    });
  }

  // boton agregar extras (pre-fill nombre = 'Extras')
  const btnAgregarExtras = document.getElementById('btnAgregarExtras');
  if (btnAgregarExtras) {
    btnAgregarExtras.addEventListener('click', () => {
      const incSel = document.getElementById('incotermSelect');
      const incId = incSel.value;
      if (!incId) return Swal.fire('Selecciona un Incoterm', 'Elige primero el incoterm donde agregar Extras', 'warning');

      const container = document.querySelector(`.incoterm-item[data-incoterm="${incId}"]`);
      if (!container) return Swal.fire('Error', 'No se encontró el contenedor del incoterm', 'error');
      const tbody = container.querySelector('tbody');
      if (!tbody) return Swal.fire('Error', 'Tabla inválida', 'error');

      const nueva = crearFilaNueva(incId);
      // prefill name and make it readonly to indicate it's an Extras template (optional)
      const nombreInput = nueva.querySelector('.nombre-items');
      if (nombreInput) {
        nombreInput.value = 'Extras';
        // allow editing if user wants, so don't set readonly by default
        // nombreInput.readOnly = true;
      }

      tbody.appendChild(nueva);
      attachEventsToRow(nueva);
      recalcularFila(nueva);
      recalcularTodo();
    });
  }

  // Cambiar incoterm (mostrar tabla y recalcular)
  document.getElementById("incotermSelect").addEventListener("change", e => {
    document.querySelectorAll(".incoterm-item").forEach(b =>
      b.style.display = (b.dataset.incoterm === e.target.value) ? "block" : "none"
    );
    recalcularTodo();
  });

  // Booking → cargar valores ECU + setear automáticos
  document.getElementById("bookingSelect").addEventListener("change", function () {
    const booking = this.value;
    if (!booking || booking === "Seleccionar...") return;

    fetch(`../api/imports/get_invoices.php?booking=${booking}`)
      .then(r => r.json())
      .then(data => {
        const totalEC = data.total_ec;
        const exwEl = document.getElementById("productoEXW");
        exwEl.textContent = `$${totalEC.toFixed(2)}`;
        exwEl.dataset.totalEcu = totalEC;

        // Completar selects
        const invoice = document.getElementById("invoiceSelect");
        const nops = document.getElementById("nOpSelect");
        invoice.innerHTML = "<option>Seleccionar...</option>";
        nops.innerHTML = "<option>Seleccionar...</option>";

        data.invoices?.forEach(invoiceVal => {
          if (window.invoiceTomSelect) {
            window.invoiceTomSelect.addOption({ value: invoiceVal, text: invoiceVal });
            window.invoiceTomSelect.addItem(invoiceVal);
          } else {
            invoice.insertAdjacentHTML("beforeend", `<option value="${invoiceVal}" selected>${invoiceVal}</option>`);
          }
        });
        data.nops?.forEach(n => nops.insertAdjacentHTML("beforeend", `<option>${n}</option>`));
  invoice.disabled = false;
  if (window.invoiceTomSelect) window.invoiceTomSelect.enable();
        nops.disabled = false;

        // Set automáticos segun IDs
        document.querySelectorAll('tr[data-item-id]').forEach(tr => {
          const id = Number(tr.dataset.itemId);
          const qty = tr.querySelector(".cantidad");
          const vu = tr.querySelector(".valor-unitario");

          // Arancel EDITABLE
          if ([64,65,51].includes(id)) {
            // Mostrar como porcentaje visualmente (15%) pero mantener calculo en decimal (0.15)
            qty.type = 'text';
            qty.dataset.isPercent = '1';
            // Mostrar con 2 decimales y coma como separador decimal
            qty.value = (0.15 * 100).toFixed(2).replace('.', ',') + '%';
            vu.value = totalEC;
          }

          // MPH fijo (mostrar como porcentaje muy pequeño)
          if ([18,35,49].includes(id)) {
            qty.type = 'text';
            qty.dataset.isPercent = '1';
            const pct = 0.003464 * 100; // 0.3464%
            qty.value = pct.toFixed(4).replace('.', ',') + '%';
            vu.value = totalEC;
            qty.readOnly = true;
            // store actual decimal in dataset for calculations if needed
            qty.dataset.decimal = '0.003464';
          }

          // HMF fijo (mostrar como porcentaje muy pequeño)
          if ([19,36,50].includes(id)) {
            qty.type = 'text';
            qty.dataset.isPercent = '1';
            const pct = 0.00125 * 100; // 0.125%
            qty.value = pct.toFixed(4).replace('.', ',') + '%';
            vu.value = totalEC;
            qty.readOnly = true;
            qty.dataset.decimal = '0.00125';
          }

          // Attach focus/blur handlers for percentage inputs so they are editable nicely
          if (qty && qty.dataset && qty.dataset.isPercent) {
            qty.addEventListener('focus', () => {
              let v = String(qty.value || '').trim();
              if (v.indexOf('%') !== -1) {
                v = v.replace('%', '').replace(',', '.').trim();
              } else {
                const n = parseFloat(v.replace(',', '.'));
                if (!isNaN(n) && n <= 1) v = (n * 100).toString();
              }
              qty.value = v;
            });

            qty.addEventListener('blur', () => {
              let v = String(qty.value || '').trim().replace(',', '.');
              let n = parseFloat(v);
              if (isNaN(n)) n = 0;
              // si el campo tiene dataset.decimal lo uso para formatear con precision
              if (qty.dataset.decimal) {
                // mostrar con 4 decimales para valores muy pequeños
                qty.value = (n).toFixed(4).replace('.', ',') + '%';
              } else {
                qty.value = (n).toFixed(2).replace('.', ',') + '%';
              }
              recalcularFila(tr);
              recalcularTodo();
            });
          }

          // recalcular fila
          recalcularFila(tr);
        });

        recalcularTodo();
      });
  });

});
</script>
<script>
document.addEventListener('DOMContentLoaded', () => {
  const btnGuardar = document.getElementById('btnGuardar');
  const bookingEl = document.getElementById('bookingSelect');
  const invoiceEl = document.getElementById('invoiceSelect');
  const selectEl = document.getElementById('incotermSelect');
  const nOpEl = document.getElementById('nOpSelect');
  const exwEl = document.getElementById('productoEXW');
  const coeficienteEl = document.getElementById('coeficiente');

  if (!btnGuardar) return console.warn('No se encontró #btnGuardar');

  btnGuardar.addEventListener('click', () => {
    console.log('✔ Click en Guardar');
    const numOp = nOpEl.value.trim();
    const booking = bookingEl.value.trim();
    // collect invoices as array (supports Tom Select)
    let invoices = [];
    if (window.invoiceTomSelect) {
      invoices = window.invoiceTomSelect.getValue();
      if (!Array.isArray(invoices)) invoices = [invoices];
    } else if (invoiceEl) {
      invoices = Array.from(invoiceEl.selectedOptions || []).map(o => o.value).filter(v => v && v !== 'Seleccionar...');
    }
    const incotermId = selectEl.value;
    const costoEXW = parseFloat(exwEl.dataset.totalEcu || "0");
    const coeficiente = parseFloat(coeficienteEl.textContent.replace('%', '').trim() || "0");

    if (!booking || invoices.length === 0 || !incotermId || !numOp) {
      return Swal.fire({
        icon: 'warning',
        title: 'Faltan datos',
        text: 'Completa Booking, Invoice, Nº Operación e Incoterm antes de guardar.'
      });
    }

    const items = [];
    // helper para parsear cantidades que pueden venir como "15%" o como números
    function parseSmartNumber(s) {
      if (s === undefined || s === null) return 0;
      let str = String(s).trim();
      if (str === '') return 0;
      if (str.indexOf('%') !== -1) {
        str = str.replace('%', '').replace(',', '.');
        const n = parseFloat(str);
        return isNaN(n) ? 0 : n / 100;
      }
      if (str.indexOf(',') !== -1) {
        str = str.replace(/\./g, '').replace(',', '.');
      } else {
        str = str.replace(/,/g, '.');
      }
      const n = parseFloat(str);
      return isNaN(n) ? 0 : n;
    }

    document.querySelectorAll(`.incoterm-item[data-incoterm="${incotermId}"] tbody tr`)
      .forEach(tr => {
        const itemId = +tr.dataset.itemId;
        const rawCantidad = tr.querySelector('.cantidad')?.value;
        const cantidad = parseSmartNumber(rawCantidad) || 0;
  const rawVU = tr.querySelector('.valor-unitario')?.value || '0';
  // usar parseSmartNumber para manejar correctamente separadores de miles y decimales
  const valorUnitNum = parseSmartNumber(rawVU) || 0;
  const valorUnit = Number(valorUnitNum.toFixed(2));
        const valorTotal = cantidad * valorUnit;
        const notas = (tr.querySelector('.notas')?.value || '').trim();
        // impuesto: entrada suele ser '5' (porcentaje). Permitimos '5%' también.
        function parsePercentValue(s) {
          if (s === undefined || s === null) return 0;
          let str = String(s).trim();
          if (str.indexOf('%') !== -1) {
            str = str.replace('%', '').replace(',', '.');
          }
          str = str.replace(/\./g, '').replace(',', '.');
          const n = parseFloat(str);
          return isNaN(n) ? 0 : n;
        }

        const impuesto = parsePercentValue(tr.querySelector('.impuesto')?.value || "0") || 0;
        const valorImpuesto = valorTotal * (impuesto / 100);

        const nombreItem = tr.querySelector('.nombre-items')?.value?.trim() || '';

        items.push({
          itemId,
          nombre: nombreItem,
          cantidad: cantidad.toFixed(6), // mantener precisión si viene en fracciones
          valorUnitario: valorUnit.toFixed(2),
          valorTotal: valorTotal.toFixed(2),
          notas,
          impuesto: impuesto.toFixed(2),
          valorImpuesto: valorImpuesto.toFixed(2)
        });
      });

    console.log({ booking, invoices, incotermId, costoEXW, coeficiente, items });
    fetch('../api/imports/guardarliquidacionimport.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ booking, invoice: invoices, numOp, costoEXW, coeficiente, incotermId: incotermId, items })
    })
      .then(r => r.json())
      .then(resp => {
        console.log('Respuesta del servidor:', resp);
        if (resp.success) {
          Swal.fire('¡Guardado!', '', 'success')
          window.location.href = '../admins/importsPanel.php';
        } else {
          Swal.fire('Error', resp.message, 'error');
        }
      })
      .catch(err => {
        console.error(err);
        Swal.fire('Error', 'No se pudo conectar al servidor.', 'error');
      });
  });
});
</script>
<!--Guardado de datos-->








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