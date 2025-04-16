<?php
session_start();
include('../usuarioClass.php');
include("../con_db.php");
$IdUsuario=$_SESSION["IdUsuario"];

$usuario= new Usuario($conexion);

$user=$usuario->obtenerUsuarioPorId($IdUsuario);


// Construcción de la consulta SQL
$sql = "SELECT 
    pl.IdPackingList AS 'ITEM #',
    MAX(c.num_dae) AS 'Num OP',
    MAX(c.Destinity_POD) AS 'Destinity POD',
    MAX(c.Booking_BK) AS 'Booking_BK',
    MAX(c.Number_Container) AS 'Number_Container',
    SUM(i.Qty_Box) AS 'Qty_Box',
    SUM(i.Total_Price_EC) AS 'TOTAL PRICE EC',
    pl.Date_Created AS 'Date created',
    DATE_FORMAT(pl.Date_Created, '%H:%i') AS 'Hour',
    CONCAT(u.nombre, ' ', u.apellido) AS 'User Name',
    pl.path_file AS 'File Home',
    pl.status AS 'STATUS'
FROM 
    packing_list pl
JOIN 
    usuarios u ON pl.IdUsuario = u.IdUsuario
JOIN 
    container c ON pl.IdPackingList = c.IdPackingList
JOIN 
    items i ON c.IdContainer = i.IdContainer
GROUP BY 
    pl.IdPackingList;";

try {
    $stmt = $conexion->prepare($sql);
    $stmt->execute();
    $result = $stmt->get_result();

} catch (mysqli_sql_exception $e) {
    echo "Error en la consulta: " . $e->getMessage();
}


?>

<!DOCTYPE html>
<html lang="en">
  <!-- [Head] start -->

  <head>
    <title>Dashboard Packing List | Eko Logistic</title>
    <!-- [Meta] -->
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />


      <!-- [Favicon] icon -->
  <link rel="icon" href="./assets/images/ekologistic.png" type="image/x-icon" />

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
    <script src="https://cdn.jsdelivr.net/npm/handsontable/dist/handsontable.full.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/handsontable/dist/handsontable.full.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
    /* Ajustes para el modal y Handsontable */
    .modal-xl {
        max-width: 95% !important;
    }
    
    #excelEditor {
        width: 100%;
        overflow: auto;
    }
    
    .handsontable {
        font-size: 12px;
    }
    
    .htCore td {
        white-space: nowrap;
    }
</style>
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
        <li class="pc-item"><a class="pc-link" href="../dashboard/panel-packinglist.php">Dashboard Packing List</a></li>
        <li class="pc-item"><a class="pc-link" href="../dashboard/index.php">Dashboard Logistic</a></li>
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
                  <li class="breadcrumb-item" aria-current="page">Dashboard Packing List</li>
                </ul>
              </div>
              <div class="col-md-12">
                <div class="page-header-title">
                  <h2 class="mb-0">Dashboard Packing List</h2>
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
            <button class="btn btn-sm btn-success">
                <a class="text-white" href="../forms/importarpk.php">Nuevo Packing List</a>
            </button>
        </div>
        <div class="card-body">
    <div class="table-responsive">
        <table class="table table-hover" id="pc-dt-simple">
           <!-- Encabezados de la tabla -->
<thead>
    <tr>
        <th>ITEM #</th>
        <th>Num DAE</th>
        <th>Destinity POD</th>
        <th>Booking_BK</th>
        <th>Number_Container</th>
        <th>Qty_Box</th>
        <th>TOTAL PRICE EC</th>
        <th>Date created</th>
        <th>Hour</th>
        <th>User Name</th>
        <th>File Home</th>
        <th>STATUS</th>
    </tr>
</thead>

<!-- Cuerpo de la tabla -->
<tbody>
    <?php while($packings = $result->fetch_assoc()) { ?>
    <tr>
        <td><?= $packings['ITEM #'] ?></td>
        <td><?= $packings['Num OP'] ?></td>
        <td><?= $packings['Destinity POD'] ?></td>
        <td><?= $packings['Booking_BK'] ?></td>
        <td><?= $packings['Number_Container'] ?></td>
        <td><?= $packings['Qty_Box'] ?></td>
        <td><?= number_format($packings['TOTAL PRICE EC'], 2) ?></td>
        <td><?= date('Y-m-d', strtotime($packings['Date created'])) ?></td>
        <td><?= $packings['Hour'] ?></td>
        <td><?= $packings['User Name']  ?></td>
        <td>
            <div class="d-flex gap-0">
                <button class="btn d-flex align-items-center btn-edit-excel" 
                        data-excel-path="<?= $packings['File Home'] ?>" 
                        data-packing-id="<?= $packings['ITEM #'] ?>">
                    <i class="ti ti-edit f-30"></i>
                </button>

                <a href="<?= $packings['File Home'] ?>" download class="btn d-flex align-items-center btn-download-excel">
                    <i class="ti ti-download f-30"></i>
                </a>
            </div>
        </td>


        <td><?= $packings['STATUS'] ?></td>
    </tr>
    <?php } ?>
</tbody>
        </table>
    </div>
</div>

    </div>
</div>
<!-- Modal para edición -->
<div class="modal fade" id="editExcelModal" tabindex="-1" aria-labelledby="editExcelModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editExcelModalLabel">Editar Excel</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="excelEditor" style="height: 600px;"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-primary" id="guardarCambios">Guardar cambios</button>
            </div>
        </div>
    </div>
</div>
<!-- Fin Modal edicion-->

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

<script>
    document.addEventListener('DOMContentLoaded', function() {
        let hot;
        let currentExcelPath;
        let currentPackingId;

        // Abrir modal con editor
        document.querySelectorAll('.btn-edit-excel').forEach(btn => {
    btn.addEventListener('click', function() {
        currentExcelPath = this.dataset.excelPath;
        currentPackingId = this.dataset.packingId;
        
        fetch(`../api/leer_excel.php?path=${encodeURIComponent(currentExcelPath)}&packingId=${currentPackingId}`)
            .then(response => response.json())
            .then(data => {
                const container = document.getElementById('excelEditor');
                
                if(!hot) {
                    hot = new Handsontable(container, {
                        data: data,
                        rowHeaders: true,
                        colHeaders: true,
                        contextMenu: true,
                        licenseKey: 'tu-licencia',
                        stretchH: 'all', // Ajustar columnas al ancho
                        width: '100%',
                        height: '70vh',
                        manualColumnResize: true,
                        manualRowResize: true
                    });
                } else {
                    hot.updateSettings({data: data});
                }
                
                // Redimensionar después de mostrar el modal
                $('#editExcelModal').modal('show').on('shown.bs.modal', function() {
                    hot.render();
                    hot.view.adjustElementsSize();
                });
            });
    });
});

        // Guardar cambios
        document.getElementById('guardarCambios').addEventListener('click', function() {
            const datos = hot.getData();
            
            fetch('../api/guardar_excel.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({
                    path: currentExcelPath,
                    packingId: currentPackingId,
                    data: datos
                })
            })
            .then(response => {
                if(!response.ok) throw new Error('Error al guardar');
                return response.json();
            })
            .then(result => {
                if(result.success) {
                    location.reload();
                } else {
                    throw new Error(result.error || 'Error desconocido');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error al guardar cambios: ' + error.message);
            });
        });
    });
    </script>
    <script>
// Forzar redimensionamiento al cambiar tamaño de ventana
window.addEventListener('resize', function() {
    if (hot) {
        hot.render();
        hot.view.adjustElementsSize();
    }
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