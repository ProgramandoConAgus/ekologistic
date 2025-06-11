<?php
session_start();
include('../usuarioClass.php');
include("../con_db.php");

$IdUsuario=$_SESSION["IdUsuario"];
if(!$_SESSION["IdUsuario"]){
  header("Location: ../");
}
$usuario= new Usuario($conexion);

$user=$usuario->obtenerUsuarioPorId($IdUsuario);

$start = isset($_GET['start']) ? $_GET['start'] : null;
$end = isset($_GET['end']) ? $_GET['end'] : null;


// Habilitar el reporte de errores en MySQLi para lanzar excepciones
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    // Construir consulta base
    $sql = "SELECT 
        pl.IdPackingList AS 'ITEM #',
        i.IdItem ,
        c.Number_Container AS 'Number Container',
        c.Num_OP AS 'Num OP',
        c.Forwarder AS 'Forwarder',
        c.Shipping_Line AS 'Shipping Line',
        c.Destinity_POD AS 'Destinity POD',
        c.Departure_Date_Port_Origin_EC AS 'Departure Port Origin EC',
        c.Booking_BK AS 'Booking_BK',
        COUNT(DISTINCT c.IdContainer) AS 'Total Containers',
        SUM(i.Qty_Box) AS 'Total Boxes',
        c.ETA_Date AS 'ETA Date',
        c.New_ETA_Date AS 'NEW ETA DATE',
        SUM(i.Total_Price_EC) AS 'TOTAL PRICE EC',
        SUM(i.Total_Price_USA) AS 'TOTAL PRICE USA',
        c.Status AS 'status',
        c.IdContainer
    FROM 
        container c
    JOIN 
        items i ON c.IdContainer = i.idContainer
    JOIN packing_list pl on pl.IdPackingList=c.idPackingList
    WHERE 
        c.Status != 'completo'";

    if ($start && $end) {
        $sql .= " AND c.ETA_Date BETWEEN '$start 00:00:00' AND '$end 23:59:59'";
    }

    $sql .= " GROUP BY c.Number_Container 
              ORDER BY pl.IdPackingList DESC";

    // Ejecutar la consulta
    $result = $conexion->query($sql);

    // Si llegamos aquí, la consulta se ejecutó correctamente
    // Procede con la lógica para procesar los resultados
} catch (mysqli_sql_exception $e) {
    // Captura el error y muestra un mensaje con el error de MySQL
    echo "Error en la consulta: " . $e->getMessage();
}


?>

<!DOCTYPE html>
<html lang="en">
  <!-- [Head] start -->

  <head>
    <title>Dashboard Logistic</title>
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
    <!-- DataTables con estilo Bootstrap 5 -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

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
    
  /* Centrar el paginador */
  #pc-dt-simple_paginate {
    text-align: center !important;
    float: none !important;
    display: flex;
    justify-content: center;
  }

  /* Asegura que el scroll solo afecte a la tabla */
  .table-wrapper {
    overflow-x: auto;
  }

  /* Centrar el paginador */
  #pc-dt-simple_wrapper .dataTables_paginate {
    margin-top: 1rem;
    display: flex !important;
    justify-content: center !important;
  }

  /* Evitar que el paginador esté dentro del área con scroll */
  #pc-dt-simple_wrapper {
    overflow-x: visible;
  }
</style>
<style>
  #custom-paginator {
    display: flex;
    justify-content: center;
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
                  <li class="breadcrumb-item" aria-current="page">Dashboard Logistic</li>
                </ul>
              </div>
              <div class="col-md-12">
                <div class="page-header-title">
                  <h2 class="mb-0">Dashboard Logistic</h2>
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
          <h5 class="mb-0">Contenedores en Tránsito</h5>
          <div class="d-flex gap-2 align-items-center">
            <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#filterModal">
              <i class="ti ti-filter"></i> Filtros avanzados
            </button>
            <button class="btn btn-sm btn-secondary" onclick="limpiarFiltrosAvanzados()">
              <i class="ti ti-x"></i> Limpiar
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
                <!-- Filtro por Num OP -->
                <div class="mb-3">
                  <label for="numOpFilter" class="form-label">Num OP</label>
                  <input type="text" class="form-control" id="numOpFilter" placeholder="Ingrese número de OP">
                </div>

                <!-- Filtro por Destiny POD -->
                <div class="mb-3">
                  <label for="destinyFilter" class="form-label">Destiny POD</label>
                  <input type="text" class="form-control" id="destinyFilter" placeholder="Ingrese Destiny POD">
                </div>

                <!-- Filtro por ETA Date -->
                <div class="mb-3">
                  <label for="rangoFechas" class="form-label">ETA Date</label><br>
                  <input
                    type="text"
                    id="rangoFechas"
                    class="form-control form-control-sm"
                    placeholder="Seleccione rango"
                    style="max-width: 220px;"
                    readonly>
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
                            <th>Forwarder</th>
                            <th>Shipping Line</th>
                            <th>Destinity POD</th>
                            <th>Departure Date <br> Port Origin EC</th>
                            <th>Booking_BK</th>
                            <th>Number Container</th>
                            <th>Total Boxes</th>
                            <th>ETA Date</th>
                            <th>NEW ETA DATE</th>
                            <th>TOTAL PRICE EC</th>
                            <th>TOTAL PRICE USA</th>
                            <th>STATUS‎ ‎ ‎ ‎ ‎ ‎ ‎ ‎ ‎ ‎ ‎ ‎ ‎ ‎ ‎ ‎ ‎ ‎ ‎ ‎ ‎ ‎ ‎ </th>
                          <!--  <th>Acciones</th>-->
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        while ($row = $result->fetch_assoc()) {
                            $badge_color = match($row['status']) {
                                'Transito' => 'bg-success',
                                'Transito Demorado' => 'bg-warning',
                                default => 'bg-secondary'
                            };
                        ?>  
                        <tr>
                            <td><?= $row['Num OP'] ?></td>
                            <td>
                                <div class="input-group input-group-sm">
                                    <input type="text" 
                                          class="forwarder-input form-control" 
                                          data-id="<?= $row['IdContainer'] ?>" 
                                          value="<?= htmlspecialchars($row['Forwarder']) ?>">
                                    <button class="btn btn-primary save-forwarder" 
                                            data-id="<?= $row['IdContainer'] ?>" 
                                            style="display: none;">
                                        <i class="ti ti-device-floppy"></i> <!-- Icono de guardar -->
                                    </button>
                                </div>
                            </td>

                            <td>
                                <div class="input-group input-group-sm">
                                    <input type="text" 
                                          class="shipping-input form-control" 
                                          data-id="<?= $row['IdContainer'] ?>" 
                                          value="<?= htmlspecialchars($row['Shipping Line']) ?>">
                                    <button class="btn btn-primary save-shipping" 
                                            data-id="<?= $row['IdContainer'] ?>" 
                                            style="display: none;">
                                        <i class="ti ti-device-floppy"></i>
                                    </button>
                                </div>
                            </td>

                            <td><?= $row['Destinity POD'] ?></td>
                            <td><?= date('d/m/Y', strtotime($row['Departure Port Origin EC'])) ?></td>
                            <td><?= $row['Booking_BK'] ?></td>
                            <td><?= $row['Number Container'] ?></td>
                            <td><?= $row['Total Boxes'] ?></td>
                            <td><?= date('d/m/Y', strtotime($row['ETA Date'])) ?></td>
                            <td>
                              <input type="text" 
                                    class="eta-date-picker form-control form-control-sm" 
                                    data-id="<?= $row['IdContainer'] ?>" 
                                    value="<?= (!empty($row['NEW ETA DATE']) && $row['NEW ETA DATE'] != '0000-00-00') 
                                                  ? date('d/m/Y', strtotime($row['NEW ETA DATE'])) 
                                                  : '' ?>"
                                    placeholder="00/00/0000">
                            </td>

                            <td>$<?= number_format($row['TOTAL PRICE EC'], 2) ?></td>
                            <td>$<?= number_format($row['TOTAL PRICE USA'], 2) ?></td>
                            <td >
                                <select class="form-select form-select-sm status-select bg-light text-dark border-0 rounded-3 shadow-sm fs-6" 
                                        data-id="<?= $row['IdContainer'] ?>">
                                    <option value="Transit" <?= $row['status'] == 'Transit' ? 'selected' : '' ?>>Transit</option>
                                    <option value="Transit Delayed" <?= $row['status'] == 'Transit Delayed' ? 'selected' : '' ?>>Transit Delayed</option>
                                    <option value="Completed" <?= $row['status'] == 'Completed' ? 'selected' : '' ?>>Completed</option>
                                </select>
                            </td>

                  
                            <!--   <td>
                                <a href="editar-contenedor.php?id=<?= $row['ITEM #'] ?>" class="btn btn-icon btn-sm">
                                    <i class="ti ti-edit"></i>
                                </a>
                            </td>
                          -->
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
            <!--<div id="custom-paginator" class="mt-3"></div>-->
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
<!-- ACTUALIZAR STATUS -->
<script>
  
  // Añadir esta función helper
function formatCurrency(value) {
    return Number(value).toLocaleString('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
}
  async function actualizarFechaETA(id, fecha, instance) {
        try {
            const response = await fetch('../api/actualizar_eta.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({ id: id, fecha: fecha })
            });

            const result = await response.json();
            
            if(!result.success) {
                // Restaurar valor anterior
                const originalDate = instance.element.value;
                instance.setDate(originalDate, true);
                alert('Error al actualizar: ' + result.error);
            } else {
                // Actualizar placeholder si se borró
                if(!fecha) {
                    instance.element.value = '';
                }
                // Feedback visual
                instance.element.classList.add('border-success');
                setTimeout(() => {
                    instance.element.classList.remove('border-success');
                }, 2000);
            }
        } catch (error) {
            console.error('Error:', error);
            instance.setDate(instance.element.value, true);
            alert('Error de conexión');
        }
    }

</script>




<!-- ACTUALIZAR FORWARDER Y SHIPPING LINE-->
 <script>
  document.addEventListener('DOMContentLoaded', function() {
    // Función genérica para manejar cambios en los inputs
    function handleInputChange(inputClass, buttonClass) {
        document.querySelectorAll(inputClass).forEach(input => {
            input.addEventListener('input', () => {
                const id = input.dataset.id;
                const btn = document.querySelector(`${buttonClass}[data-id="${id}"]`);
                btn.style.display = 'inline-block';
            });
        });
    }

    // Manejadores para Forwarder y Shipping Line
    handleInputChange('.forwarder-input', '.save-forwarder');
    handleInputChange('.shipping-input', '.save-shipping');

    // Función genérica para actualizar datos en la base de datos
    function handleSaveClick(buttonClass, inputClass, endpoint) {
        document.querySelectorAll(buttonClass).forEach(button => {
            button.addEventListener('click', async () => {
                const id = button.dataset.id;
                const input = document.querySelector(`${inputClass}[data-id="${id}"]`);
                const newValue = input.value.trim();

                try {
                    const response = await fetch(endpoint, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ id: id, value: newValue })
                    });
                    const result = await response.json();
                    if (result.success) {
                        button.style.display = 'none';
                    } else {
                        alert('Error al actualizar: ' + result.error);
                    }
                } catch (error) {
                    alert('Error de conexión');
                }
            });
        });
    }

    // Aplicar evento de guardar para Forwarder y Shipping Line
    handleSaveClick('.save-forwarder', '.forwarder-input', '../api/update_forwarder.php');
    handleSaveClick('.save-shipping', '.shipping-input', '../api/update_shipping.php');
});


 </script>

<!--ACTUALIZAR FECHA ETA-->
<script>
document.addEventListener('DOMContentLoaded', function() {
    flatpickr(".eta-date-picker", {
        dateFormat: "d/m/Y",
        locale: "es",
        allowInput: true,
        placeholder: "00/00/0000",
        onChange: function(selectedDates, dateStr, instance) {
            const contenedorId = instance.element.dataset.id;
            const nuevaFecha = selectedDates[0] ? selectedDates[0].toISOString().split('T')[0] : null;
            
            // Si el usuario borra el input
            if(dateStr === "") {
                nuevaFecha = null;
            }

            actualizarFechaETA(contenedorId, nuevaFecha, instance);
        }
    });

    
});
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
function initDatePickers() {
  // — 1) DATE-RANGE PICKER para #rangoFechas —
  const rangoInput = document.getElementById('rangoFechas');
  if (rangoInput) {
    // destruir instancia previa (si existe)
    if (rangoInput._flatpickr) {
      rangoInput._flatpickr.destroy();
    }
    flatpickr(rangoInput, {
      mode: "range",
      dateFormat: "d/m/Y",
      locale: "es",
      rangeSeparator: " - ",
      allowInput: true,
      placeholder: "Seleccione rango"
    });
  }

  // — 2) ETA PICKERS para cada .eta-date-picker —
  document.querySelectorAll('.eta-date-picker').forEach(input => {
    // destruir instancia previa
    if (input._flatpickr) {
      input._flatpickr.destroy();
    }
    flatpickr(input, {
      dateFormat: "d/m/Y",
      locale: "es",
      allowInput: true,
      placeholder: "00/00/0000",
      onChange: function(selectedDates, dateStr, instance) {
        const contenedorId = instance.element.dataset.id;
        // toma valor ISO o null
        const nuevaFecha = dateStr
          ? selectedDates[0].toISOString().split('T')[0]
          : null;
        actualizarFechaETA(contenedorId, nuevaFecha, instance);
      }
    });
  });
}
document.addEventListener('DOMContentLoaded', function () {
    // Inicializar listeners al cargar la página
    initStatusListeners();
    initInputHandlers();
    initDatePickers();
});

// Función para inicializar listeners de status
function initStatusListeners() {
    document.querySelectorAll('.status-select').forEach(select => {
        select.removeEventListener('change', handleStatusChange);
        select.addEventListener('change', handleStatusChange);
    });
}

// Manejador de cambios para status
function handleStatusChange() {
    const id = this.getAttribute('data-id');
    const value = this.value;
    actualizarStatus(id, value);
}

// Función para actualizar el estado
async function actualizarStatus(id, value) {
    try {
        // Mostrar alerta de carga
        Swal.fire({
            title: 'Cargando',
            text: 'Por favor, espere...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        const response = await fetch('../api/actualizar_status.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id, value })
        });
        
        const data = await response.json();
        
        // Cerrar la alerta de carga
        Swal.close();

        if (data.success) {
            Swal.fire({
                title: 'Éxito',
                text: data.message,
                icon: 'success',
                confirmButtonText: 'Continuar'
            });
        } else {
            Swal.fire({
                title: 'Error',
                text: data.error || 'Error desconocido',
                icon: 'error',
                confirmButtonText: 'Entendido'
            });
        }
    } catch (error) {
        console.error('Error:', error);
        // Cerrar la alerta de carga si falló la conexión
        Swal.close();
        Swal.fire({
            title: 'Error de conexión',
            text: 'No se pudo contactar al servidor',
            icon: 'error',
            confirmButtonText: 'Entendido'
        });
    }
}

// Función para inicializar handlers de inputs
function initInputHandlers() {
    // Manejadores para Forwarder
    handleInputChange('.forwarder-input', '.save-forwarder');
    handleSaveClick('.save-forwarder', '.forwarder-input', '../api/update_forwarder.php');
    
    // Manejadores para Shipping Line
    handleInputChange('.shipping-input', '.save-shipping');
    handleSaveClick('.save-shipping', '.shipping-input', '../api/update_shipping.php');
}

// Función genérica para cambios en inputs
function handleInputChange(inputClass, buttonClass) {
    document.querySelectorAll(inputClass).forEach(input => {
        input.addEventListener('input', () => {
            const id = input.dataset.id;
            const btn = document.querySelector(`${buttonClass}[data-id="${id}"]`);
            if (btn) btn.style.display = 'inline-block';
        });
    });
}

// Función genérica para guardar datos
function handleSaveClick(buttonClass, inputClass, endpoint) {
    document.querySelectorAll(buttonClass).forEach(button => {
        button.addEventListener('click', async () => {
            const id = button.dataset.id;
            const input = document.querySelector(`${inputClass}[data-id="${id}"]`);
            const newValue = input.value.trim();

            try {
                const response = await fetch(endpoint, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id: id, value: newValue })
                });
                
                const result = await response.json();
                if (result.success) {
                    button.style.display = 'none';
                    Swal.fire('Éxito', 'Cambios guardados', 'success');
                } else {
                    Swal.fire('Error', result.error || 'Error al guardar', 'error');
                }
            } catch (error) {
                Swal.fire('Error', 'Error de conexión', 'error');
            }
        });
    });
}

// filtros.js

// Función para convertir texto de fecha a ISO
function toISODate(str) {
  let d, m, y;
  if (str.includes("-")) {
    [y, m, d] = str.split("-");
  } else if (str.includes("/")) {
    [d, m, y] = str.split("/");
  }
  if (isNaN(d) || isNaN(m) || isNaN(y)) return null;
  y = y.padStart(4, "20");             // Asegura año 4 dígitos
  return `${y}-${m.padStart(2, "0")}-${d.padStart(2, "0")}`;
}

async function aplicarFiltrosAvanzados() {
  const numOp   = document.getElementById("numOpFilter").value.trim();
  const destiny = document.getElementById("destinyFilter").value.trim();
  const rango   = document.getElementById("rangoFechas").value.trim();
  const params  = new URLSearchParams();

  if (numOp)   params.append("container", numOp);
  if (destiny) params.append("destiny", destiny);

  if (rango) {
    const partes = rango.split(" a ").map(s => s.trim());
    if (partes.length === 2) {
      const desdeISO = toISODate(partes[0]);
      const hastaISO = toISODate(partes[1]);
      if (desdeISO) params.append("dateFrom", desdeISO);
      if (hastaISO) params.append("dateTo", hastaISO);
    } else {
      console.error("Formato de rango inválido. Usa 'YYYY-MM-DD a YYYY-MM-DD' o 'DD/MM/YYYY a DD/MM/YYYY'.");
    }
  }

  try {
    const res = await fetch(`../api/filters/fetchIndex.php?${params.toString()}`);
    if (!res.ok) throw new Error(res.statusText);
    const containers = await res.json();

    const tbody = document.querySelector('#pc-dt-simple tbody');
    tbody.innerHTML = '';
    containers.forEach(c => {
    const tr = `
      <tr>
        <td>${c['Num OP']}</td>
        <td>
          <div class="input-group input-group-sm">
            <input type="text"
                  class="forwarder-input form-control"
                  data-id="${c.IdContainer}"
                  value="${c.Forwarder || ''}">
            <button class="btn btn-primary save-forwarder"
                    data-id="${c.IdContainer}"
                    style="display: none;">
              <i class="ti ti-device-floppy"></i>
            </button>
          </div>
        </td>
        <td>
          <div class="input-group input-group-sm">
            <input type="text"
                  class="shipping-input form-control"
                  data-id="${c.IdContainer}"
                  value="${c['Shipping Line'] || ''}">
            <button class="btn btn-primary save-shipping"
                    data-id="${c.IdContainer}"
                    style="display: none;">
              <i class="ti ti-device-floppy"></i>
            </button>
          </div>
        </td>
        <td>${c['Destinity POD']}</td>
        <td>${formatDate(c['Departure Port Origin EC'].replace(/-/g, '/'))}</td>
        <td>${c.Booking_BK}</td>
        <td>${c['Number Container']}</td>
        <td>${c['Total Boxes']}</td>
        <td>${formatDate(c['ETA Date'].replace(/-/g, '/'))}</td>
        <td>
          <input type="text"
                class="eta-date-picker form-control form-control-sm"
                data-id="${c.IdContainer}"
                value="${
                  c['NEW ETA DATE']
                    ? formatDate(c['NEW ETA DATE'].replace(/-/g, '/'))
                    : ''
                }"
                placeholder="00/00/0000">
        </td>
        <td>$${formatCurrency(c['TOTAL PRICE EC'])}</td>
        <td>$${formatCurrency(c['TOTAL PRICE USA'])}</td>
        <td>
          <select class="form-select form-select-sm status-select bg-light text-dark border-0 rounded-3 shadow-sm fs-6"
                  data-id="${c.IdContainer}">
            <option value="Transit" ${c.status === 'Transit' ? 'selected' : ''}>Transit</option>
            <option value="Transit Delayed" ${c.status === 'Transit Delayed' ? 'selected' : ''}>Transit Delayed</option>
            <option value="Completed" ${c.status === 'Completed' ? 'selected' : ''}>Completed</option>
          </select>
        </td>
      </tr>`;
    tbody.insertAdjacentHTML('beforeend', tr);
  });


    // Cerrar modal y re-inicializar
    bootstrap.Modal.getInstance(document.getElementById('filterModal')).hide();
    initStatusListeners();
    initInputHandlers();
    initDatePickers();
  } catch (err) {
    console.error('Error al aplicar filtros:', err);
  }
}

async function limpiarFiltrosAvanzados() {
  // Reset inputs
  document.getElementById("numOpFilter").value   = '';
  document.getElementById("destinyFilter").value = '';
  document.getElementById("rangoFechas").value   = '';

  // Destruir pickers
  document.querySelectorAll('.eta-date-picker').forEach(inp => {
    if (inp._flatpickr) inp._flatpickr.destroy();
  });

  try {
    const res = await fetch(`../api/filters/fetchIndex.php`);
    if (!res.ok) throw new Error(res.statusText);
    const containers = await res.json();

    const tbody = document.querySelector('#pc-dt-simple tbody');
    tbody.innerHTML = '';
    containers.forEach(c => {
    const tr = `
      <tr>
        <td>${c['Num OP']}</td>
        <td>
          <div class="input-group input-group-sm">
            <input type="text"
                  class="forwarder-input form-control"
                  data-id="${c.IdContainer}"
                  value="${c.Forwarder || ''}">
            <button class="btn btn-primary save-forwarder"
                    data-id="${c.IdContainer}"
                    style="display: none;">
              <i class="ti ti-device-floppy"></i>
            </button>
          </div>
        </td>
        <td>
          <div class="input-group input-group-sm">
            <input type="text"
                  class="shipping-input form-control"
                  data-id="${c.IdContainer}"
                  value="${c['Shipping Line'] || ''}">
            <button class="btn btn-primary save-shipping"
                    data-id="${c.IdContainer}"
                    style="display: none;">
              <i class="ti ti-device-floppy"></i>
            </button>
          </div>
        </td>
        <td>${c['Destinity POD']}</td>
        <td>${formatDate(c['Departure Port Origin EC'].replace(/-/g, '/'))}</td>
        <td>${c.Booking_BK}</td>
        <td>${c['Number Container']}</td>
        <td>${c['Total Boxes']}</td>
        <td>${formatDate(c['ETA Date'].replace(/-/g, '/'))}</td>
        <td>
          <input type="text"
                class="eta-date-picker form-control form-control-sm"
                data-id="${c.IdContainer}"
                value="${
                  c['NEW ETA DATE']
                    ? formatDate(c['NEW ETA DATE'].replace(/-/g, '/'))
                    : ''
                }"
                placeholder="00/00/0000">
        </td>
        <td>$${formatCurrency(c['TOTAL PRICE EC'])}</td>
        <td>$${formatCurrency(c['TOTAL PRICE USA'])}</td>
        <td>
          <select class="form-select form-select-sm status-select bg-light text-dark border-0 rounded-3 shadow-sm fs-6"
                  data-id="${c.IdContainer}">
            <option value="Transit" ${c.status === 'Transit' ? 'selected' : ''}>Transit</option>
            <option value="Transit Delayed" ${c.status === 'Transit Delayed' ? 'selected' : ''}>Transit Delayed</option>
            <option value="Completed" ${c.status === 'Completed' ? 'selected' : ''}>Completed</option>
          </select>
        </td>
      </tr>`;
    tbody.insertAdjacentHTML('beforeend', tr);
  });


    // Cerrar modal
    const modal = bootstrap.Modal.getInstance(document.getElementById('filterModal'));
    if (modal) modal.hide();

    initStatusListeners();
    initInputHandlers();
    initDatePickers();
  } catch (err) {
    console.error('Error al limpiar filtros:', err);
  }
}



// Helper functions
function formatDate(dateString) {
  if (!dateString) return '';
  const date = new Date(dateString);
  return date.toLocaleDateString('en-GB'); // Formato dd/mm/yyyy
}

function matchStatusColor(status) {
  switch(status) {
    case 'Transit': return 'bg-primary';
    case 'Transit Delayed': return 'bg-warning';
    case 'Completed': return 'bg-success';
    default: return 'bg-secondary';
  }
}
</script>

<!--Paginador
<script>
$(document).ready(function () {
  const table = $('#pc-dt-simple').DataTable({
    paging: true,
    pageLength: 10,
    lengthChange: false,
    info: false,
    searching: false,
    language: {
      paginate: {
        previous: "«",
        next: "»"
      }
    }
  });

  // Clonar y mover el paginador al contenedor personalizado
  const originalPaginator = $('#pc-dt-simple_paginate').detach();
  $('#custom-paginator').append(originalPaginator);
});
</script>

-->

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