<?php
session_start();
include('../usuarioClass.php');
include("../con_db.php");
$IdUsuario=$_SESSION["IdUsuario"];
if (!isset($_SESSION['IdUsuario'])) {
  header("Location: ../");
}
if(!$_SESSION["IdUsuario"]){
  header("Location: ../");
}


$usuario= new Usuario($conexion);

$user=$usuario->obtenerUsuarioPorId($IdUsuario);

$start = isset($_GET['start']) ? $_GET['start'] : null;
$end = isset($_GET['end']) ? $_GET['end'] : null;


// Construir consulta base
$sql = "SELECT 
  pl.IdPackingList AS 'ITEM #',
  i.EntryDate,
  i.IdItem,
  c.num_op AS 'Num OP',
  c.Booking_BK,
  c.Number_Commercial_Invoice AS 'Number_Commercial Invoice',
  i.Number_Lot AS 'Number LOT',
  i.Number_PO,
  i.Customer,
  i.Description,
  i.Qty_Box,
  i.Price_Box_EC AS 'PRICE BOX EC',
  i.Total_Price_EC AS 'TOTAL PRICE EC',
  i.Total_Price_USA AS 'TOTAL PRICE USA',
  c.Number_Container AS 'Number_Container'
FROM container c
JOIN items i ON c.IdContainer = i.idContainer
JOIN packing_list pl ON pl.IdPackingList = c.idPackingList
WHERE c.status = 'Completed';
";


$result = $conexion->query($sql);

// Contadores totales
$total_inventory_boxes = 0; // pongo boxes por si despues los quieren ver
$total_transit_boxes = 0; // pongo boxes por si despues los quieren ver
$total_price = 0;

// Guardo en array
$items = [];
while ($row = $result->fetch_assoc()) {
    $items[] = $row;
    
    $total_price += $row['TOTAL PRICE EC'];
    
}

// Reseteo
$result->data_seek(0);

?>

<!DOCTYPE html>
<html lang="en">
  <!-- [Head] start -->

  <head>
    <title>Warehouse Invetory | Eko Logistic</title>
    <!-- [Meta] -->
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />


       <!-- [Favicon] icon -->
  <link rel="icon" href="../assets/images/ekologistic.png" type="image/x-icon" />


    <!-- map-vector css -->
    <link rel="stylesheet" href="../assets/css/plugins/jsvectormap.min.css">
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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <style>
    .eta-date-picker {
        transition: border-color 0.3s ease;
        min-width: 120px;
    }
    .eta-date-picker.border-success {
        border-color: #28a745 !important;
    }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="./tipografia.css">

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
                <li class="pc-item"><a class="pc-link" href="./warehouse-inventory.php">WareHouse Inventory</a></li>
                <li class="pc-item"><a class="pc-link" href="./total-inventory.php">Total Inventory</a></li>
                <li class="pc-item"><a class="pc-link" href="#">Dispatch Inventory (Proximamente)</a> </li>
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
        <li class="pc-item"><a class="pc-link">Exports</a></li>
        <li class="pc-item"><a class="pc-link">Imports</a></li>
        <li class="pc-item"><a class="pc-link">Despachos</a></li>
        <li class="pc-item"><a class="pc-link">Consolidados</a></li>
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
                  <li class="breadcrumb-item"><a href="../dashboard/index.php">Inicio</a></li>
                  <li class="breadcrumb-item"><a href="javascript: void(0)">Logistica</a></li>
                  <li class="breadcrumb-item"><a href="javascript: void(0)">Inventory</a></li>
                  <li class="breadcrumb-item" aria-current="page">Warehouse Inventory</li>
                </ul>
              </div>
               <div class="col-md-12">
        <div class="d-flex justify-content-between align-items-center">
          <div class="page-header-title">
            <h2 class="mb-0">Warehouse Inventory</h2>
          </div>
          
          <div class="d-flex gap-3">
            <!-- Primera tarjeta -->
            <div class="card prod-p-card bg-warning mb-0">
              <div class="card-body">
                <div class="d-flex align-items-center">
                  <div class="prod-icon">
                    <i class="ti ti-package text-white f-36"></i>
                  </div>
                  <div class="ms-auto text-end text-white">
                    
                      <h3 class="mb-0 text-white">
                          
                      $<?= number_format($total_price, 2) ?>
                    </h3>
                    
                    <span>Monto Total</span>
                   
                  </div>
                </div>
              </div>
            </div>
            
          </div>
        </div>
      </div>
            </div>
          </div>
        </div>
        <!-- [ breadcrumb ] end -->
        <!-- [ Main Content ] start -->
        <div class="row">
       
          
        <div class="col-md-12 col-xl-12">
    <div class="card table-card">
        <div class="card-header d-flex align-items-center justify-content-between py-3">
          <h5 class="mb-0"></h5>
          <div class="d-flex gap-2 align-items-center">
            <div class="d-flex gap-2 align-items-center">
                <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#filterModal">
                  <i class="ti ti-filter"></i> Filtros avanzados
                </button>
                <button class="btn btn-sm btn-secondary" onclick="limpiarFiltrosAvanzados()">
                  <i class="ti ti-x"></i> Limpiar
                </button>
              </div>
          </div>
      </div>  
      <div class="modal fade" id="filterModal" tabindex="-1" aria-labelledby="filterModalLabel" aria-hidden="true">
          <div class="modal-dialog">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title" id="filterModalLabel">Filtros avanzados</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <div class="modal-body">
                <form id="filterForm">
                  <!-- Filtro por Cliente -->
                  <div class="mb-3">
                    <label for="customerFilter" class="form-label">Cliente</label>
                    <select class="form-select" id="customerFilter">
                      <option value="">Todos los clientes</option>
                      <?php
                      // Resetear el puntero del resultado para obtener valores únicos
                      $result->data_seek(0);
                      $customers = [];
                      while ($row = $result->fetch_assoc()) {
                        if (!in_array($row['Customer'], $customers)) {
                          $customers[] = $row['Customer'];
                          echo '<option value="' . htmlspecialchars($row['Customer']) . '">' . htmlspecialchars($row['Customer']) . '</option>';
                        }
                      }
                      $result->data_seek(0); // Resetear para el bucle principal
                      ?>
                    </select>
                  </div>
                  
                  <!-- Filtro por Number PO -->
                  <div class="mb-3">
                    <label for="poFilter" class="form-label">Número de PO</label>
                    <input type="text" class="form-control" id="poFilter" placeholder="Ingrese número de PO">
                  </div>
                  
                  <!-- Filtro por Numero OP (Container) -->
                  <div class="mb-3">
                    <label for="containerFilter" class="form-label">Número de Contenedor (OP)</label>
                    <input type="text" class="form-control" id="containerFilter" placeholder="Ingrese número de contenedor">
                  </div>
                  <!--
                  <div class="mb-3">
                    <label for="containerFilter" class="form-label">Date Created</label><br>
                    <input type="text" id="rangoFechas" class="form-control form-control-sm" placeholder="Seleccione rango" style="max-width: 220px;" readonly>
                  </div>
                    -->
                </form>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-primary" onclick="aplicarFiltrosAvanzados()">Aplicar filtros</button>
              </div>
            </div>
          </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
            <table class="table table-hover" id="pc-dt-simple">
                    <thead>
                        <tr>
                            <th>Entry Date</th>
                            <th>Num OP</th>
                            <th>Booking_BK</th>
                            <th>Number_Container</th>
                            <th>Number_Commercial_Invoice</th>
                            <th>Number LOT</th>
                            <th>Number_PO</th>
                            <th>Customer</th>
                            <th>Description</th>
                            <th>Qty_Box</th>
                            <th>Price Box Ec</th>
                            <th>TOTAL PRICE EC</th>
                            <th>TOTAL PRICE USA</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        while ($row = $result->fetch_assoc()) {
                           
                        ?>
                        <tr>
                            <td>
                                <input type="text" 
                                    class="entry-date-picker form-control form-control-sm" 
                                    data-id="<?= $row['IdItem'] ?>" 
                                    value="<?= !empty($row['EntryDate']) && $row['EntryDate'] != '0000-00-00' 
                                                ? date('d/m/Y', strtotime($row['EntryDate'])) 
                                                : '' ?>"
                                    placeholder="dd/mm/yyyy">
                            </td>
                            <td><?= $row['Num OP'] ?></td>
                            <td><?= $row['Booking_BK'] ?></td>
                            <td><?= $row['Number_Container'] ?></td>
                            <td><?= $row['Number_Commercial Invoice'] ?></td>
                            <td><?= $row['Number LOT']?></td>
                            <td><?= $row['Number_PO'] ?></td>
                            <td><?= $row['Customer'] ?></td>
                            <td><?= $row['Description'] ?></td>
                            <td><?= $row['Qty_Box'] ?></td>
                            <td>$<?= number_format($row['PRICE BOX EC'], 2) ?></td>
                            <td>$<?= number_format($row['TOTAL PRICE EC'], 2) ?></td>
                            <td>$<?= number_format($row['TOTAL PRICE USA'], 2) ?></td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
function confirmDelete(blNumber) {
    if (confirm(`¿Estás seguro de que quieres eliminar el BL ${blNumber}?`)) {
        // Aquí puedes agregar la lógica para eliminar el BL
        console.log(`BL ${blNumber} eliminado.`);
    }
}
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
<!--ACTUALIZAR FECHA ENTRY-->
<script>

async function actualizarFechaEntry(id, fecha, instance) {
    try {
        const body = {
            id: parseInt(id),
            fecha: fecha || ''
        };

        const response = await fetch('../api/actualizar_entry.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify(body)
        });

        const result = await response.json();
        
        if(!response.ok || !result.success) {
            instance.setDate(instance.input.value, true, 'd/m/Y');
            Swal.fire('Error', result.error || 'Error al actualizar', 'error');
        } else {
            instance.element.classList.add('border-success');
            setTimeout(() => {
                instance.element.classList.remove('border-success');
            }, 2000);
        }
    } catch (error) {
        console.error('Error:', error);
        instance.setDate(instance.input.value, true, 'd/m/Y');
        Swal.fire('Error', 'Error de conexión', 'error');
        
    }
}

// Inicializar datepickers
function initEntryDatePickers() {
    document.querySelectorAll('.entry-date-picker').forEach(input => {
        if (!input._flatpickr) {
            flatpickr(input, {
                dateFormat: "d/m/Y",
                locale: "es",
                allowInput: true,
                placeholder: "dd/mm/yyyy",
                onChange: function(selectedDates, dateStr, instance) {
                    const itemId = instance.element.dataset.id;
                    actualizarFechaEntry(itemId, dateStr, instance);
                }
            });
        }
    });
}
</script>

<!-- FILTRO DE FECHA -->
 
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/es.js"></script> <!-- Soporte en español -->
<script>
// Inicializar Flatpickr
const rangoFechas = flatpickr("#rangoFechas", {
    mode: "range", // Modo de rango
    locale: "es", // Idioma español
    dateFormat: "Y-m-d", // Formato para la URL
    altInput: true, // Muestra fechas en formato legible
    altFormat: "d/m/Y", // Formato visual
    static: true, // Calendario fijo
    allowInput: false, // Evita edición manual
    onReady: function(selectedDates, dateStr, instance) {
        // Restaurar fechas si existen en la URL
        const params = new URLSearchParams(window.location.search);
        if(params.has('start') && params.has('end')) {
            instance.setDate([params.get('start'), params.get('end')]);
        }
    }
});

// Función para aplicar filtro
function aplicarFiltro() {
    const fechas = rangoFechas.selectedDates;
    
    if (fechas.length === 2) {
        const start = fechas[0].toISOString().split('T')[0];
        const end = fechas[1].toISOString().split('T')[0];
        window.location.href = `?start=${start}&end=${end}`;
    } else {
        alert('¡Seleccione un rango de fechas válido!');
    }
}

// Función para limpiar filtro
function limpiarFiltro() {
    rangoFechas.clear();
    window.location.href = window.location.pathname;
}
</script>




<script>
  function formatPHPDate(dateString) {
    if (!dateString || dateString === '0000-00-00') return '';
    
    // Convertir fecha PHP (Y-m-d) a Date object
    const [year, month, day] = dateString.split('-');
    const date = new Date(`${year}-${month}-${day}`);
    
    return isNaN(date) ? '' : 
        `${String(date.getDate()).padStart(2, '0')}/${String(date.getMonth() + 1).padStart(2, '0')}/${date.getFullYear()}`;
}
async function aplicarFiltrosAvanzados() {
    const customer = document.getElementById('customerFilter').value;
    const po = document.getElementById('poFilter').value;
    const container = document.getElementById('containerFilter').value;
    
    const params = new URLSearchParams();
    if (customer) params.append('customer', customer);
    if (po) params.append('po', po);
    if (container) params.append('container', container);

    try {
        const res = await fetch(`../api/filters/fetchWarehouseInventory.php?${params.toString()}`);
        if (!res.ok) throw new Error(res.statusText);
        const rows = await res.json();

        const tbody = document.querySelector('#pc-dt-simple tbody');
        tbody.innerHTML = '';

        rows.forEach(row => {
          const entryDate = formatPHPDate(row['EntryDate']);


            const tr = `
            <tr>
                <td>
                    <input type="text" 
                        class="entry-date-picker form-control form-control-sm" 
                        data-id="${row['IdItem'] || ''}" 
                        value="${entryDate}" 
                        placeholder="dd/mm/yyyy">
                </td>
                <td>${row['Num OP'] || ''}</td>
                <td>${row['Booking_BK'] || ''}</td>
                <td>${row['Number_Container'] || ''}</td>
                <td>${row['Number_Commercial Invoice'] || ''}</td>
                <td>${row['Number LOT'] || ''}</td>
                <td>${row['Number_PO'] || ''}</td>
                <td>${row['Customer'] || ''}</td>
                <td>${row['Description'] || ''}</td>
                <td>${row['Qty_Box'] || 0}</td>
                <td>$${Number(row['PRICE BOX EC'] || 0).toFixed(2)}</td>
                <td>$${Number(row['TOTAL PRICE EC'] || 0).toFixed(2)}</td>
            </tr>`;
            tbody.insertAdjacentHTML('beforeend', tr);
        });
        initEntryDatePickers();

        // Cerrar modal
        const modalEl = document.getElementById('filterModal');
        const modal = bootstrap.Modal.getInstance(modalEl);
        modal.hide();

    } catch (err) {
        console.error('Error al cargar datos:', err);
    }
}

function formatPHPDate(dateString) {
    if (!dateString) return '';
    const date = new Date(dateString);
    return isNaN(date) ? '' : 
        `${String(date.getDate()).padStart(2, '0')}/${String(date.getMonth() + 1).padStart(2, '0')}/${date.getFullYear()}`;
}

async function limpiarFiltrosAvanzados() {
    document.getElementById('customerFilter').value = '';
    document.getElementById('poFilter').value = '';
    document.getElementById('containerFilter').value = '';
    initEntryDatePickers();

    await aplicarFiltrosAvanzados();
}
document.addEventListener('DOMContentLoaded', function() {
    initEntryDatePickers();
});
</script>


    <!-- [Page Specific JS] start -->
    <script src="../assets/js/plugins/apexcharts.min.js"></script>
    <script src="../assets/js/plugins/jsvectormap.min.js"></script>
    <script src="../assets/js/plugins/world.js"></script>
    <script src="../assets/js/plugins/world-merc.js"></script>
    <script src="../assets/js/pages/dashboard-default.js"></script>
    <!-- [Page Specific JS] end -->
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
  </body>
  <!-- [Body] end -->
</html>


<?php

?>