<?php
session_start();
include('../usuarioClass.php');
include("../con_db.php");
$IdUsuario=$_SESSION["IdUsuario"];

$usuario= new Usuario($conexion);

$user=$usuario->obtenerUsuarioPorId($IdUsuario);

?>


<!DOCTYPE html>
<html lang="en">
  <!-- [Head] start -->

  <head>
    <title>Liquidaciones | Crear Despacho</title>
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
          <li class="breadcrumb-item"><a href="javascript: void(0)">Liquidaciones</a></li>
          <li class="breadcrumb-item"><a href="javascript: void(0)">Despacho</a></li>
          <li class="breadcrumb-item" aria-current="page">Crear</li>
        </ul>
      </div>
      <div class="col-md-12">
        <div class="page-header-title">
          <h2 class="mb-0">Crear Despacho Liquidación</h2>
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
$query = "
  SELECT DISTINCT c.Booking_BK 
  FROM container c
  
";

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
            <select id="invoiceSelect" class="form-select" disabled>
              <option selected>Seleccionar...</option>
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
  WHERE IdTipoIncoterm = 7
");
while ($inc = $res->fetch_assoc()) {
  $incoterms[] = $inc;
}
?>


      <!-- 2) Select dinámico -->
      <div class="mb-4">
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

      <!-- 3) Contenedores dinámicos -->
      <div id="incotermContainer">
        <?php foreach ($incoterms as $inc): ?>
          <div class="incoterm-item" data-incoterm="<?= $inc['IdTipoIncoterm'] ?>" style="display: none;">
            <h5 class="mt-3"><?= htmlspecialchars($inc['NombreTipoIncoterm']) ?></h5>
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
            <th>Notas</th>
            <?php if($inc['IdTipoIncoterm'] == 3): ?>
              <th>% Impuesto</th>
              <th>Valor Impuesto</th>
            <?php endif; ?>
          </tr>
        </thead>
        <tbody>
          <?php
          $items = $conexion->query(
              "SELECT IdItemsLiquidacionDespacho, NombreItems 
              FROM itemsliquidaciondespacho
              WHERE IdTipoIncoterm = {$inc['IdTipoIncoterm']}
              AND NombreItems NOT IN ('repalletize + Fix pallet')"
          );
          while ($row = $items->fetch_assoc()):
          ?>
            <tr data-item-id="<?= $row['IdItemsLiquidacionDespacho'] ?>">
              <td><?= htmlspecialchars($row['NombreItems']) ?></td>
              <td>
                <input type="number" class="form-control form-control-sm cantidad" value="0" min="0" style="width: 80px; text-align: right;">
              </td>
              <td>
                <div class="input-group input-group-sm" style="min-width: 110px;">
                  <span class="input-group-text">$</span>
                  <input type="text" class="form-control valor-unitario" value="0,00" style="min-width: 70px; text-align: right;">
                </div>
              </td>
              <td>
                <div class="input-group input-group-sm" style="min-width: 110px;">
                  <span class="input-group-text">$</span>
                  <input type="text" class="form-control valor-total" value="0,00" readonly style="min-width: 70px; text-align: right;">
                </div>
              </td>
              <td>
                <input type="text" class="form-control form-control-sm notas" value="" placeholder="Notas" style="min-width: 120px;">
              </td>
              <?php if($inc['IdTipoIncoterm'] == 3): ?>
                <td>
                  <div class="input-group input-group-sm" style="min-width: 90px;">
                    <input type="number" class="form-control impuesto" value="0" min="0" max="100" style="text-align: right;">
                    <span class="input-group-text">%</span>
                  </div>
                </td>
                <td>
                  <div class="input-group input-group-sm" style="min-width: 110px;">
                    <span class="input-group-text">$</span>
                    <input type="text" class="form-control valor-impuesto" value="0,00" readonly style="min-width: 70px; text-align: right;">
                  </div>
                </td>
              <?php endif; ?>
            </tr>
          <?php endwhile; ?>
        </tbody>
        <tfoot>
          <tr>
            <td colspan="<?= ($inc['IdTipoIncoterm'] == 3) ? 7 : 5 ?>" class="text-center">
              <button id="addNewDeliveryBtn" type="button" class="btn btn-primary me-2">
                Agregar New Delivery
              </button>
              <button id="addStorageBtn" type="button" class="btn btn-primary">
                Agregar Storage
              </button>
            </td>
          </tr>
        </tfoot>
      </table>
    </div>
  </div>
</div>



           
          </div>
        <?php endforeach; ?>
      </div>

      <!-- Botones y Total General -->
      <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-3 mt-4">
        <button class="btn btn-primary" onclick="window.location.href = '../admins/despachosPanel.php'">Volver</button>
        <h5 id="totalGeneral" class="text-success fw-bold m-0 text-center">
        Total General: $0,00
        </h5>

        <button type="button" class="btn btn-success">Guardar</button>
      </div>

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



<!--Completador de select-->
<script>
document.getElementById('bookingSelect').addEventListener('change', function () {
  const booking = this.value;
  const invoiceSelect = document.getElementById('invoiceSelect');
  const nOpSelect = document.getElementById('nOpSelect');
  const productoEXW = document.getElementById('productoEXW');

  // Limpiar y desactivar los selects y el costo del producto EXW
  invoiceSelect.innerHTML = '<option selected>Seleccionar...</option>';
  invoiceSelect.disabled = true;
  nOpSelect.innerHTML = '<option selected>Seleccionar...</option>';
  nOpSelect.disabled = true;
  productoEXW.textContent = '';

  if (booking && booking !== 'Seleccionar...') {
    fetch(`../api/despacho/get_invoices.php?booking=${encodeURIComponent(booking)}`)
      .then(response => response.json())
      .then(data => {
        // Llenar select de facturas
        if (data.invoices.length > 0) {
          data.invoices.forEach(invoice => {
            const option = document.createElement('option');
            option.value = invoice;
            option.textContent = invoice;
            invoiceSelect.appendChild(option);
          });
          invoiceSelect.disabled = false;
        } else {
          const opt = document.createElement('option');
          opt.text = 'Sin facturas disponibles';
          opt.disabled = true;
          invoiceSelect.appendChild(opt);
        }

        // Llenar select de números de operación
        if (data.nops.length > 0) {
          data.nops.forEach(op => {
            const option = document.createElement('option');
            option.value = op;
            option.textContent = op;
            nOpSelect.appendChild(option);
          });
          nOpSelect.disabled = false;
        }

        // Mostrar el costo del producto EXW
        productoEXW.textContent = `$${data.total_ec.toFixed(2)}`;
        productoEXW.dataset.totalecu = data.total_ec.toFixed(2);
      })
      .catch(error => {
        console.error('Error al cargar datos:', error);
      });
  }
});

</script>

<!--Autocalcular totales-->
<script>
  document.addEventListener('DOMContentLoaded', () => {
    const totalEl     = document.getElementById('totalGeneral');
    const contenedor  = document.getElementById('incotermContainer');
    const selectorInc = document.getElementById('incotermSelect');

    // Formatea 1234.5 → "1.234,50"
    function formatCurrency(value) {
      let parts = value.toFixed(2).split('.');
      parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, '.');
      return parts.join(',');
    }
    // "1.234,50" → 1234.5
    function parseCurrency(str) {
      return parseFloat(str.replace(/\./g,'').replace(',','.')) || 0;
    }

    function recalculateGeneral() {
      // Sólo filas de bloques visibles
      const filas = contenedor.querySelectorAll('.incoterm-item')
        .forEach(bloque => {
          bloque.style.display; // forzar reflow si hiciera falta  
        });
      let total = 0;
      document.querySelectorAll('.incoterm-item[style*="display: block"] tbody tr')
        .forEach(tr => {
          const vtInput = tr.querySelector('.valor-total');
          const viInput = tr.querySelector('.valor-impuesto');
          const vt = vtInput ? parseCurrency(vtInput.value) : 0;
          const vi = viInput ? parseCurrency(viInput.value) : 0;
          total += vt + vi;
        });
      totalEl.textContent = 'Total General: $' + formatCurrency(total);
      totalEl.dataset.totalgeneral = total;

      const coeficiente=total/(parseFloat(document.getElementById('productoEXW').dataset.totalecu))*100;
      document.getElementById('coeficiente').textContent=coeficiente.toFixed(2)+' %';
      document.getElementById('coeficiente').dataset.coeficiente=coeficiente;
      if(coeficiente.isNaN || !isFinite(coeficiente)){
        document.getElementById('coeficiente').textContent='0.00 %';
      }
    }

    // Disparamos recalculate cada vez que el usuario cambie un qty, V.U. o impuesto
    contenedor.addEventListener('input', e => {
      if (e.target.matches('.cantidad, .valor-unitario, .impuesto')) {
        // primero actualizamos el valor-total y valor-impuesto de la misma fila
        const tr = e.target.closest('tr');
        const qty = parseFloat(tr.querySelector('.cantidad').value) || 0;
        const vu  = parseCurrency(tr.querySelector('.valor-unitario').value);
        const vt  = qty * vu;
        tr.querySelector('.valor-total').value = formatCurrency(vt);

        const impEl = tr.querySelector('.impuesto');
        if (impEl) {
          const imp = parseFloat(impEl.value) || 0;
          const vi  = vt * (imp / 100);
          tr.querySelector('.valor-impuesto').value = formatCurrency(vi);
        }

        recalculateGeneral();
      }
    });

    // También recalculamos cuando cambias de Incoterm para esconder/mostrar bloques
    selectorInc.addEventListener('change', () => {
      // tu código de show/hide ya estaba bien:
      document.querySelectorAll('.incoterm-item').forEach(b => {
        b.style.display = (b.dataset.incoterm === selectorInc.value) ? 'block' : 'none';
      });
      recalculateGeneral();
    });

    // cálculo inicial
    recalculateGeneral();
  });
</script>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const bookingEl   = document.getElementById('bookingSelect');
  const invoiceEl   = document.getElementById('invoiceSelect');
  const selectEl    = document.getElementById('incotermSelect');
  const btnGuardar  = document.querySelector('.btn-success');

  btnGuardar.addEventListener('click', () => {
    const booking    = bookingEl.value.trim();
    const invoice    = invoiceEl.value.trim();
    const incotermId = selectEl.value;

    if (!booking || !invoice || !incotermId) {
      return Swal.fire({
        icon: 'warning',
        title: 'Faltan datos',
        text: 'Completa Booking, Invoice e Incoterm antes de guardar.'
      });
    }

    const bloque = document.querySelector(`.incoterm-item[data-incoterm="${incotermId}"]`);
    if (!bloque) return;

    const items = [];
    bloque.querySelectorAll('tbody tr').forEach(tr => {
      const itemId      = parseInt(tr.dataset.itemId, 10);
      const descripcion = tr.children[0].textContent.trim();

      // Cantidad y valor unitario
      const rawCant = tr.querySelector('.cantidad').value;
      const rawVU   = tr.querySelector('.valor-unitario').value;

      // Convertimos formatos con coma decimal a punto decimal
      const cantidad      = rawCant === '' ? null : parseFloat(rawCant.replace(',', '.')) || 0;
      const valorUnitario = rawVU   === '' ? null : parseFloat(rawVU.replace(',', '.')) || 0;
      const valorTotal    = (cantidad || 0) * (valorUnitario || 0);
      const notasEl = tr.querySelector('.notas');
      const notas   = notasEl ? notasEl.value.trim() : '';

      items.push({
        incotermId,
        itemId,
        descripcion,
        cantidad,
        valorUnitario,
        valorTotal,
        notas      
      });
    });

    // Tomamos los 3 datos extras
    const costoEXW      = parseFloat(document.getElementById('productoEXW').dataset.totalecu) || 0;
    const num_op   = document.getElementById('nOpSelect')?.value;
    const coeficiente        = parseFloat(document.getElementById('coeficiente').dataset.coeficiente) || 0;

    fetch('../api/despacho/guardarliquidaciondespacho.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        booking,
        invoice,
        items,
        costoEXW,
        num_op,
        coeficiente
      })
    })
    .then(r => r.json())
    .then(resp => {
      if (resp.success) {
        Swal.fire({ icon: 'success', title: '¡Guardado!', text: 'Correcto.' })
          window.location.href = '../admins/despachosPanel.php';
      } else {
        Swal.fire({ icon: 'error', title: 'Error', text: resp.message });
      }
    })
    .catch(() => {
      Swal.fire({ icon: 'error', title: 'Error', text: 'Error del servidor.' });
    });
  });
});

</script>



















<script>
document.getElementById('addNewDeliveryBtn').addEventListener('click', () => {
  const tbody = document.querySelector('table tbody');

  // Buscar el item-id del ítem original con nombre "New delivery"
  const original = [...tbody.querySelectorAll('tr[data-item-id]')].find(tr =>
    tr.children[0].textContent.trim().toLowerCase() === 'new delivery'
  );
  const itemId = original ? original.dataset.itemId : '';

  const tr = document.createElement('tr');
  tr.setAttribute('data-item-id', itemId);

  tr.innerHTML = `
    <td>New delivery</td>
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
        <input type="text" class="form-control valor-total" value="0,00" readonly>
      </div>
    </td>
    <td>
      <input type="text" class="form-control form-control-sm notas" placeholder="Notas">
    </td>
    <td>
      <button type="button" class="btn btn-danger btn-sm btn-delete-row">Eliminar</button>
    </td>
  `;

  const addButtonRow = tbody.querySelector('tr:last-child');
  tbody.insertBefore(tr, addButtonRow);
});

document.getElementById('addNewDeliveryBtn').addEventListener('click', () => {
  const tbody = document.querySelector('table tbody');
  const itemId = 1; // ID real de "New delivery" si ya está creado

  const tr = document.createElement('tr');
  tr.setAttribute('data-item-id', itemId);

  tr.innerHTML = `
    <td>New delivery</td>
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
        <input type="text" class="form-control valor-total" value="0,00" readonly>
      </div>
    </td>
    <td>
      <input type="text" class="form-control form-control-sm notas" placeholder="Notas">
    </td>
    <td>
      <button type="button" class="btn btn-danger btn-sm btn-delete-row">Eliminar</button>
    </td>
  `;

  const addButtonRow = tbody.querySelector('tr:last-child');
  tbody.insertBefore(tr, addButtonRow);
});

document.getElementById('addStorageBtn').addEventListener('click', () => {
  const tbody = document.querySelector('table tbody');
  const itemId = 2; // ID real de "Storage about 30 days off price x pallet"

  const tr = document.createElement('tr');
  tr.setAttribute('data-item-id', itemId);

  tr.innerHTML = `
    <td>Storage about 30 days off price x pallet</td>
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
        <input type="text" class="form-control valor-total" value="0,00" readonly>
      </div>
    </td>
    <td>
      <input type="text" class="form-control form-control-sm notas" placeholder="Notas">
    </td>
    <td>
      <button type="button" class="btn btn-danger btn-sm btn-delete-row">Eliminar</button>
    </td>
  `;

  const addButtonRow = tbody.querySelector('tr:last-child');
  tbody.insertBefore(tr, addButtonRow);
});


// Delegación para eliminar filas con botón "Eliminar"
document.querySelector('table tbody').addEventListener('click', (e) => {
  if (e.target.classList.contains('btn-delete-row')) {
    const row = e.target.closest('tr');
    if (row) row.remove();
  }
});

</script>












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