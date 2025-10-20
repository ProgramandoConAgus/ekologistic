<?php
session_start();
include('../usuarioClass.php');
include("../con_db.php");
$IdUsuario=$_SESSION["IdUsuario"];
if(!$_SESSION["IdUsuario"]){
  header("Location: ../");
}

// Primero ejecutamos la consulta original como ya lo tienes
$usuario = new Usuario($conexion);
$user = $usuario->obtenerUsuarioPorId($IdUsuario);
$start = isset($_GET['start']) ? $_GET['start'] : null;
$end = isset($_GET['end']) ? $_GET['end'] : null;


$sql = "SELECT 
  pl.IdPackingList AS 'ITEM #',
  i.IdItem,
  c.num_op AS 'Num OP',
  i.Number_PO,
  i.Customer,
  i.Description,
  i.Qty_Box,
  i.Price_Box_EC AS 'PRICE BOX EC',
  i.Total_Price_EC AS 'TOTAL PRICE EC',
  i.Price_Box_USA AS 'PRICE BOX USA',
  i.Total_Price_USA AS 'TOTAL PRICE USA',
  c.status AS 'STATUS'
FROM container c
JOIN items i ON c.IdContainer = i.idContainer
JOIN packing_list pl on pl.IdPackingList = c.idPackingList;
";

$result = $conexion->query($sql);

// Contadores totales
$total_inventory_boxes = 0; // pongo boxes por si despues los quieren ver
$total_transit_boxes = 0; // pongo boxes por si despues los quieren ver
$total_inventory_price = 0;
$total_transit_price = 0;

// Guardo en array
$items = [];
while ($row = $result->fetch_assoc()) {
    $items[] = $row;
    
    
    if ($row['STATUS'] == 'Completed') {
        $status = "Inventory";
        $total_inventory_boxes += $row['Qty_Box'];
        $total_inventory_price += $row['TOTAL PRICE EC'];
    } else {
        $status = "Transit";
        $total_transit_boxes += $row['Qty_Box'];
        $total_transit_price += $row['TOTAL PRICE EC'];
    }
}

// Reseteo
$result->data_seek(0);

?>

<!DOCTYPE html>
<html lang="en">
  <!-- [Head] start -->

  <head>
    <title>Total Inventory | Eko Logistic</title>
    <!-- [Meta] -->
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />

      <!-- [Favicon] icon -->
  <link rel="icon" href="../assets/images/ekologistic.png" type="image/x-icon" />

    <!-- jQuery (DataTables lo necesita) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- DataTables CSS y JS -->
<link   href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css" rel="stylesheet"/>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css"/>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>

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

    <style>
    .eta-date-picker {
        transition: border-color 0.3s ease;
        min-width: 120px;
    }
    .eta-date-picker.border-success {
        border-color: #28a745 !important;
    }
    /* Estilos para las tarjetas de resumen */
    .prod-p-card {
      border-radius: 10px;
      margin-bottom: 0;
    }

    .prod-p-card .card-body {
      padding: 15px;
    }

    .prod-icon {
      width: 50px;
      height: 50px;
      display: flex;
      justify-content: center;
      align-items: center;
    }

    .f-36 {
      font-size: 36px;
    }
    .table-responsive {
      position: relative;    
      overflow: auto;       
    }

    .table-responsive .pagination-wrapper {
      position: absolute;
      bottom: 10px;          
      left: 50%;            
      transform: translateX(-50%);
      width: auto;          
      margin: 0;
      padding: 0.5rem 0;     
    }
    .table-responsive .pagination-wrapper .pagination {
      margin: 0;                   
    }
    .table-responsive .pagination-wrapper .pagination li.page-item {
      margin: 0 0.125rem;          
    }
    .table-responsive .pagination-wrapper .pagination li.page-item .page-link {
      padding: 0.375rem 0.75rem;   
    }
    .table-responsive .pagination-wrapper .pagination li.page-item.active .page-link {
      background-color: #0d6efd;   
      border-color:     #0d6efd;
      color:            #fff;
    }

    .table-responsive {
      position: relative;
      padding-bottom: 3.5rem;      
      overflow-x: auto;
    }
    .table-responsive .pagination-wrapper {
      position: absolute;
      bottom:      0.5rem;         
      left:        50%;
      transform:   translateX(-50%);
      z-index:     10;
      background:  #fff;           
      padding:     0.25rem 0;      
    }

    </style>
   <link  rel="stylesheet"  href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css"/>
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
          <li class="breadcrumb-item"><a href="../dashboard/index.php">Inicio</a></li>
          <li class="breadcrumb-item"><a href="javascript: void(0)">Logistica</a></li>
          <li class="breadcrumb-item" aria-current="page">Total Inventory</li>
        </ul>
      </div>
      <div class="col-md-12">
        <div class="d-flex justify-content-between align-items-center">
          <div class="page-header-title">
            <h2 class="mb-0">Total Inventory</h2>
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
                          
                      $<?= number_format($total_inventory_price, 2) ?>
                    </h3>
                    
                    <span>Monto en Inventario</span>
                   
                  </div>
                </div>
              </div>
            </div>
            
            <!-- Segunda tarjeta -->
            <div class="card prod-p-card bg-primary mb-0">
              <div class="card-body">
                <div class="d-flex align-items-center">
                  <div class="prod-icon">
                    <i class="ti ti-truck text-white f-36"></i>
                  </div>
                  <div class="ms-auto text-end text-white">
                   <h3 class="mb-0 text-white">
                        $<?= number_format($total_transit_price, 2) ?>
                    </h3>
                     <span>Monto en Tránsito</span>
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
        <!-- Reemplazar la sección del input por un botón que abra el modal -->
        <div class="card-header d-flex align-items-center justify-content-between py-3">
          <h5 class="mb-0">Contenedores en Tránsito</h5>
          <div class="d-flex gap-2 align-items-center">
            <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#filterModal">
              <i class="ti ti-filter"></i> Filtros avanzados
            </button>
            <button class="btn btn-sm btn-secondary" onclick="limpiarFiltrosAvanzados()">
              <i class="ti ti-x"></i> Limpiar
            </button>
            <button id="btnDescargarVisible" class="btn btn-sm btn-success">
              <i class="ti ti-download"></i> Descargar Excel
            </button>
          </div>

        </div>

<!-- Modal de filtros -->
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
                          <th>Num OP</th>
                          <th>Number_PO</th>
                          <th>Customer</th>
                          <th>Description</th>
                          <th>Qty_Box</th>
                          <th>PRICE BOX EC</th>
                          <th>TOTAL PRICE EC</th>
                          <th>PRICE BOX USA</th>
                          <th>TOTAL PRICE USA</th>
                          <th>STATUS</th>
                      </tr>
                  </thead>
                  <tbody>
                      <?php while ($row = $result->fetch_assoc()) { 
                          // Asigna un color de badge según el estado
                          if($row['STATUS']=='Completed')
                          {
                            $row['STATUS']="Inventory";
                          }
                          else{
                            $row['STATUS']="Transit";
                          }
                          $badge_color = match($row['STATUS']) {
                              'Transit' => 'bg-primary',
                              'Inventory' => 'bg-warning',
                              default => 'bg-secondary'
                          };
                      ?>
                      <tr>
                          <td><?= $row['Num OP'] ?></td>
                          <td><?= $row['Number_PO'] ?></td>
                          <td><?= $row['Customer'] ?></td>
                          <td><?= $row['Description'] ?></td>
                          <td><?= $row['Qty_Box'] ?></td>
                          <td>$<?= number_format($row['PRICE BOX EC'], 2) ?></td>
                          <td>$<?= number_format($row['TOTAL PRICE EC'], 2) ?></td>
                          <td>$<?= number_format($row['PRICE BOX USA'], 2) ?></td>
                          <td>$<?= number_format($row['TOTAL PRICE USA'], 2) ?></td>
                          <td><span class="badge <?= $badge_color ?>"><?= $row['STATUS'] ?></span></td>
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
<!--ACTUALIZAR FECHA ETA-->
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/es.js"></script>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.sheetjs.com/xlsx-latest/package/dist/xlsx.full.min.js"></script>
<!-- DataTables CSS/JS ya los tenés cargados -->
<script>
var dt;
const dtConfig = {
  paging:         true,
  pageLength:     10,
  pagingType:     'simple_numbers',
  language: {
    paginate: {
      previous: '«',
      next:     '»'
    }
  },

  lengthChange:   false,
  searching:      false,
  info:           false,
  ordering:       false,

  dom: 't<"pagination-wrapper"p>'
};

function initDataTable() {
  dt = $('#pc-dt-simple').DataTable(dtConfig);
  $('.pagination-wrapper')
    .appendTo( $('#pc-dt-simple').closest('.table-responsive') );
}

$(document).ready(() => {
  initDataTable();
});
</script>



<script>
document.getElementById("btnDescargarVisible").addEventListener("click", async function () {
  Swal.fire({
    title: 'Generating Excel...',
    text: 'Please wait a moment.',
    allowOutsideClick: false,
    didOpen: () => Swal.showLoading()
  });

  const customer  = document.getElementById('customerFilter').value;
  const po        = document.getElementById('poFilter').value;
  const container = document.getElementById('containerFilter').value;

  const params = new URLSearchParams();
  if (customer)  params.append('customer', customer);
  if (po)        params.append('po', po);
  if (container) params.append('container', container);

  try {
    const res = await fetch(`../api/filters/fetchTotalInventory.php?${params.toString()}`);
    if (!res.ok) throw new Error(res.statusText);
    const rows = await res.json();

    const ws = XLSX.utils.json_to_sheet(rows);
    const wb = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(wb, ws, "Data");

    const wbout = XLSX.write(wb, { bookType: "xlsx", type: "array" });
    const blob = new Blob([wbout], { type: "application/octet-stream" });

    // 👉 Nombre dinámico con fecha actual
    const today = new Date();
    const formattedDate = today.toISOString().split('T')[0];
    const fileName = `total_inventory_summary_${formattedDate}.xlsx`;

    const url = URL.createObjectURL(blob);
    const a = document.createElement("a");
    a.href = url;
    a.download = fileName;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);

    Swal.close();
    Swal.fire({
      icon: 'success',
      title: 'Downloaded!',
      text: 'The Excel file was created successfully.',
      timer: 2000,
      showConfirmButton: false
    });
  } catch (err) {
    console.error('Error generating Excel:', err);
    Swal.close();
    Swal.fire({
      icon: 'error',
      title: 'Error',
      text: 'Failed to generate the Excel file.'
    });
  }
});
</script>



<script>
async function aplicarFiltrosAvanzados() {
  const customer  = document.getElementById('customerFilter').value;
  const po        = document.getElementById('poFilter').value;
  const container = document.getElementById('containerFilter').value;

  const params = new URLSearchParams();
  if (customer)  params.append('customer', customer);
  if (po)        params.append('po', po);
  if (container) params.append('container', container);

  try {
    const res = await fetch(`../api/filters/fetchTotalInventory.php?${params.toString()}`);
    if (!res.ok) throw new Error(res.statusText);
    const rows = await res.json();

    const tbody = document.querySelector('#pc-dt-simple tbody');
    if (dt) {
      dt.destroy();
      $('.pagination-wrapper').remove();
    }
    tbody.innerHTML = '';

    rows.forEach(row => {
      const badgeClass = row.STATUS === 'Transit' ? 'bg-primary' : 'bg-warning';
      const tr = `
        <tr>
          <td>${row['Num OP']}</td>
          <td>${row['Number_PO']}</td>
          <td>${row['Customer']}</td>
          <td>${row['Description']}</td>
          <td>${row['Qty_Box']}</td>
          <td>$${Number(row['PRICE BOX EC']).toFixed(2)}</td>
          <td>$${Number(row['TOTAL PRICE EC']).toFixed(2)}</td>
          <td>$${Number(row['PRICE BOX USA']).toFixed(2)}</td>
          <td>$${Number(row['TOTAL PRICE USA']).toFixed(2)}</td>
          <td><span class="badge ${badgeClass}">${row.STATUS}</span></td>
        </tr>`;
      tbody.insertAdjacentHTML('beforeend', tr);
    });
    initDataTable();

    // Cerrar modal (Bootstrap 5)
    const modalEl = document.getElementById('filterModal');
    const modal   = bootstrap.Modal.getInstance(modalEl);
    modal.hide();

  } catch (err) {
    console.error('Error al cargar datos:', err);
  }
}

async function limpiarFiltrosAvanzados() {
  // Limpiar los inputs
  document.getElementById('customerFilter').value = '';
  document.getElementById('poFilter').value = '';
  document.getElementById('containerFilter').value = '';

  const params = new URLSearchParams();
  // No se agregan filtros porque están vacíos

  try {
    const res = await fetch(`../api/filters/fetchTotalInventory.php?${params.toString()}`);
    if (!res.ok) throw new Error(res.statusText);
    const rows = await res.json();

    const tbody = document.querySelector('#pc-dt-simple tbody');
    if (dt) {
      dt.destroy();
      $('.pagination-wrapper').remove();
    }
    tbody.innerHTML = '';

    rows.forEach(row => {
      const badgeClass = row.STATUS === 'Transit' ? 'bg-primary' : 'bg-warning';
      const tr = `
        <tr>
          <td>${row['Num OP']}</td>
          <td>${row['Number_PO']}</td>
          <td>${row['Customer']}</td>
          <td>${row['Description']}</td>
          <td>${row['Qty_Box']}</td>
          <td>$${Number(row['PRICE BOX EC']).toFixed(2)}</td>
          <td>$${Number(row['TOTAL PRICE EC']).toFixed(2)}</td>
          <td>$${Number(row['PRICE BOX USA']).toFixed(2)}</td>
          <td>$${Number(row['TOTAL PRICE USA']).toFixed(2)}</td>
          <td><span class="badge ${badgeClass}">${row.STATUS}</span></td>
        </tr>`;
      tbody.insertAdjacentHTML('beforeend', tr);
    });
    initDataTable();

    // También cerrar modal si quieres (opcional)
    const modalEl = document.getElementById('filterModal');
    const modal   = bootstrap.Modal.getInstance(modalEl);
    if (modal) modal.hide();

  } catch (err) {
    console.error('Error al cargar datos:', err);
  }
}

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
