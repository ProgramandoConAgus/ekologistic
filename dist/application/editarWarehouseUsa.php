<?php
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
    <title>Editar Warehouse Usa | Eko Logistic</title>
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
          <li class="breadcrumb-item active" aria-current="page">Editar</li>
        </ul>
      </div>
      <div class="col-md-12">
        <div class="page-header-title">
          <h2 class="mb-0">Editar Warehouse Usa</h2>
        </div>
      </div>
    </div>
  </div>
</div>

        <!-- [ breadcrumb ] end -->
<!-- Acordate de incluir Bootstrap Icons si no lo tenés -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

<div class="container mt-5">
  <div class="card shadow-sm mx-auto" style="max-width: 900px;">
    <form method="POST" action="#" id="editForm">
      <input type="hidden" name="id" value="<?= $id ?>">
      <input type="hidden" name="numero_contenedor" value="<?= htmlspecialchars($warehouse['notas']) ?>">
      <div class="card-body">

        <div class="row g-3">
          <?php if (!empty($warehouse['palets_restante'])): ?>
          <div class="col-md-4">
            <label class="form-label">Palets Restante</label>
            <input type="number" name="palets_restante" class="form-control" value="<?= htmlspecialchars($warehouse['palets_restante']) ?>">
          </div>
          <?php endif; ?>
          <?php if (!empty($warehouse['cantidad_restante'])): ?>
          <div class="col-md-4">
            <label class="form-label">Cajas Restantes</label>
            <input type="number" name="cantidad_restante" class="form-control" value="<?= htmlspecialchars($warehouse['cantidad_restante']) ?>">
          </div>
          <?php endif; ?>
          <div class="col-md-4">
            <label class="form-label">Fecha Entrada</label>
            <input type="date" name="fecha_entrada" class="form-control" value="<?= htmlspecialchars($warehouse['fecha_entrada']) ?>">
          </div>
          <div class="col-md-4">
            <label class="form-label">Fecha de Salida</label>
            <input type="date" name="fecha_salida" class="form-control" value="<?= htmlspecialchars($warehouse['fecha_salida']) ?>">
          </div>
          <div class="col-md-4">
            <label class="form-label">Número de Contenedor</label>
            <input type="text" class="form-control" value="<?= htmlspecialchars($warehouse['notas']) ?>" readonly>
          </div>
          <div class="col-md-4">
            <label class="form-label">Recibo de Almacén</label>
            <input type="text" name="recibo_almacen" class="form-control" value="<?= htmlspecialchars($warehouse['recibo_almacen']) ?>">
          </div>
          <div class="col-md-4">
            <label class="form-label">Estado</label>
            <input type="text" name="estado" class="form-control" value="<?= htmlspecialchars($warehouse['estado']) ?>">
          </div>
          <div class="col-md-4">
            <label class="form-label">Número de Factura</label>
            <input type="text" name="numero_factura" class="form-control" value="<?= htmlspecialchars($warehouse['numero_factura']) ?>">
          </div>
          <div class="col-md-4">
            <label class="form-label">Número de Lote</label>
            <input type="text" name="numero_lote" class="form-control" value="<?= htmlspecialchars($warehouse['numero_lote']) ?>">
          </div>
          <div class="col-md-4">
            <label class="form-label">Número de Orden de Compra</label>
            <input type="text" name="orden_compra" class="form-control" value="<?= htmlspecialchars($warehouse['numero_orden_compra']) ?>">
          </div>
          <div class="col-md-4">
            <label class="form-label">Número de Parte</label>
            <input type="text" name="numero_parte" class="form-control" value="<?= htmlspecialchars($warehouse['numero_parte']) ?>">
          </div>
          <div class="col-12">
          <label class="form-label">Descripción</label>
            <textarea name="descripcion" class="form-control" rows="2"><?= htmlspecialchars($warehouse['descripcion_item'] ?? $warehouse['descripcion']) ?></textarea>
          </div>
          <div class="col-md-4">
            <label class="form-label">Modelo</label>
            <input type="text" name="modelo" class="form-control" value="<?= htmlspecialchars($warehouse['modelo']) ?>">
          </div>
          <div class="col-12">
            <label class="form-label">Palets</label>
            <input type="text" name="palets" class="form-control" value="<?= htmlspecialchars($warehouse['palets']) ?>">
          </div>
          <div class="col-md-4">
            <label class="form-label">Cantidad</label>
            <input type="number" name="cantidad" class="form-control" value="<?= htmlspecialchars($warehouse['cantidad']) ?>">
          </div>
          <div class="col-md-4">
            <label class="form-label">Valor Unitario</label>
            <input type="text" name="valor_unitario" class="form-control" value="<?= htmlspecialchars($warehouse['valor_unitario']) ?>">
          </div>
          <div class="col-md-4">
            <label class="form-label">Valor</label>
            <input type="text" name="valor" class="form-control" value="<?= htmlspecialchars($warehouse['valor']) ?>">
          </div>
          <div class="col-md-4">
            <label class="form-label">Unidad</label>
            <input type="text" name="unidad" class="form-control" value="<?= htmlspecialchars($warehouse['unidad']) ?>">
          </div>
          <div class="col-md-4">
            <label class="form-label">Longitud (in)</label>
            <input type="number" name="longitud" class="form-control" value="<?= htmlspecialchars($warehouse['longitud_in']) ?>">
          </div>
          <div class="col-md-4">
            <label class="form-label">Ancho (in)</label>
            <input type="number" name="ancho" class="form-control" value="<?= htmlspecialchars($warehouse['ancho_in']) ?>">
          </div>
          <div class="col-md-4">
            <label class="form-label">Altura (in)</label>
            <input type="number" name="altura" class="form-control" value="<?= htmlspecialchars($warehouse['altura_in']) ?>">
          </div>
          <div class="col-md-4">
            <label class="form-label">Peso (lb)</label>
            <input type="number" name="peso" step="0.01" class="form-control" value="<?= htmlspecialchars($warehouse['peso_lb']) ?>">
          </div>
        </div>

<?php
  $hasRest = !empty($warehouse['palets_restante']) ||
             !empty($warehouse['cantidad_restante']) ||
             !empty($warehouse['valor_unitario_restante']) ||
             !empty($warehouse['valor_restante']) ||
             !empty($warehouse['unidad_restante']) ||
             !empty($warehouse['longitud_in_restante']) ||
             !empty($warehouse['ancho_in_restante']) ||
             !empty($warehouse['altura_in_restante']) ||
             !empty($warehouse['peso_lb_restante']);
  if ($hasRest): ?>
        <h5 class="mt-4">Datos Restantes</h5>
        <div class="row g-3">
          <?php if (!empty($warehouse['valor_unitario_restante'])): ?>
          <div class="col-md-4">
            <label class="form-label">Valor Unitario Restante</label>
            <input type="text" name="valor_unitario_restante" class="form-control" value="<?= htmlspecialchars($warehouse['valor_unitario_restante']) ?>">
          </div>
          <?php endif; ?>
          <?php if (!empty($warehouse['valor_restante'])): ?>
          <div class="col-md-4">
            <label class="form-label">Valor Restante</label>
            <input type="text" name="valor_restante" class="form-control" value="<?= htmlspecialchars($warehouse['valor_restante']) ?>">
          </div>
          <?php endif; ?>
          <?php if (!empty($warehouse['unidad_restante'])): ?>
          <div class="col-md-4">
            <label class="form-label">Unidad Restante</label>
            <input type="text" name="unidad_restante" class="form-control" value="<?= htmlspecialchars($warehouse['unidad_restante']) ?>">
          </div>
          <?php endif; ?>
          <?php if (!empty($warehouse['longitud_in_restante'])): ?>
          <div class="col-md-4">
            <label class="form-label">Longitud (in) Restante</label>
            <input type="number" name="longitud_restante" class="form-control" value="<?= htmlspecialchars($warehouse['longitud_in_restante']) ?>">
          </div>
          <?php endif; ?>
          <?php if (!empty($warehouse['ancho_in_restante'])): ?>
          <div class="col-md-4">
            <label class="form-label">Ancho (in) Restante</label>
            <input type="number" name="ancho_restante" class="form-control" value="<?= htmlspecialchars($warehouse['ancho_in_restante']) ?>">
          </div>
          <?php endif; ?>
          <?php if (!empty($warehouse['altura_in_restante'])): ?>
          <div class="col-md-4">
            <label class="form-label">Altura (in) Restante</label>
            <input type="number" name="altura_restante" class="form-control" value="<?= htmlspecialchars($warehouse['altura_in_restante']) ?>">
          </div>
          <?php endif; ?>
          <?php if (!empty($warehouse['peso_lb_restante'])): ?>
          <div class="col-md-4">
            <label class="form-label">Peso (lb) Restante</label>
            <input type="number" name="peso_restante" step="0.01" class="form-control" value="<?= htmlspecialchars($warehouse['peso_lb_restante']) ?>">
          </div>
          <?php endif; ?>
        </div>
<?php endif; ?>

        <div class="mt-4 d-flex justify-content-between">
          <a href="../admins/warehouseUsaPanel.php" class="btn btn-primary">
            <i class="bi bi-arrow-left"></i> Volver
          </a>
          <button type="submit" class="btn btn-info">Editar</button>
        </div>
      </div>
    </form>
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
<script>
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
</script>


<script>
document.getElementById('editForm').addEventListener('submit', e => {
  e.preventDefault();
  const form = e.target;
  const data = {
    id: form.id.value,
    fecha_entrada: form.fecha_entrada.value,
    fecha_salida: form.fecha_salida.value,
    recibo_almacen: form.recibo_almacen.value.trim(),
    estado: form.estado.value.trim(),
    numero_factura: form.numero_factura.value.trim(),
    numero_lote: form.numero_lote.value.trim(),
    numero_contenedor: form.numero_contenedor.value,
    palets: form.palets.value.trim(),
    orden_compra: (function(v){ v = v.trim(); return v.toLowerCase()==='stock' ? '0' : v; })(form.orden_compra.value),
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
    peso: form.peso.value,
    palets_restante: form.palets_restante?.value.trim() || '',
    cantidad_restante: parseInt(form.cantidad_restante?.value) || 0,
    valor_unitario_restante: form.valor_unitario_restante?.value.trim() || '',
    valor_restante: form.valor_restante?.value.trim() || '',
    unidad_restante: form.unidad_restante?.value.trim() || '',
    longitud_restante: form.longitud_restante?.value || '',
    ancho_restante: form.ancho_restante?.value || '',
    altura_restante: form.altura_restante?.value || '',
    peso_restante: form.peso_restante?.value || ''
  };

  fetch('../api/warehouseusa/actualizar_manual.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(data)
  })
  .then(r => r.json())
  .then(resp => {
    if (resp.success) {
      Swal.fire({icon:'success',title:'Actualizado',text:'Registro actualizado'})
        .then(() => location.href = '../admins/warehouseUsaPanel.php');
    } else {
      Swal.fire({icon:'error',title:'Error',text: resp.message || 'Error'});
    }
  })
  .catch(() => {
    Swal.fire({icon:'error',title:'Error',text:'Error de servidor'});
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
