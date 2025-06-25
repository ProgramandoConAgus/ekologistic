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


$sql = "
    SELECT
        d.id,
        c.num_op                             AS NUM_OP,
        c.Number_Container                   AS Number_Container,
        c.Booking_BK,
        d.numero_lote                        AS Lot_Number,
        d.fecha_entrada                      AS Entry_Date,
        c.Number_Commercial_Invoice          AS Number_Commercial_Invoice,
        d.numero_parte                       AS Code_Product_EC,
        d.descripcion                        AS Description,
        d.cantidad                           AS Qty,
        d.valor_unitario                     AS Unit_Value,
        d.valor                              AS Value,
        d.unidad                             AS Unit,
        d.longitud_in                        AS Length_in,
        d.ancho_in                           AS Broad_in,
        d.altura_in                          AS Height_in,
        d.peso_lb                            AS Weight_lb,
        d.estado                             AS Status,
        d.recibo_almacen
    FROM container c
    INNER JOIN dispatch d
        ON c.Number_Commercial_Invoice = d.numero_factura
       AND c.Number_Container         = d.notas
    WHERE d.estado = 'En Almacén'
    ORDER BY c.num_op, d.numero_parte
";
$result = $conexion->query($sql);


if (!$result) {
    die("Error en la consulta: " . $conexion->error);
}

/*
try {
    $stmt = $conexion->prepare($sql);
    $stmt->execute();
    $result = $stmt->get_result();

} catch (mysqli_sql_exception $e) {
    echo "Error en la consulta: " . $e->getMessage();
}

*/
?>

<!DOCTYPE html>
<html lang="en">
  <!-- [Head] start -->

  <head>
    <title>Dashboard WareHouse Inventory | Eko Logistic</title>
    <!-- [Meta] -->
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />


      <!-- [Favicon] icon -->
  <link rel="icon" href="../assets/images/ekologistic.png" type="image/x-icon" />
<!-- jQuery (solo una vez) -->
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>

<!-- DataTables con Bootstrap 5 -->
<link
  rel="stylesheet"
  href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css"
/>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<!-- SheetJS para exportar Excel -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>


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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Agrega esto en tu <head> -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
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

<style>
  .table-responsive {
    position: relative;
    padding-bottom: 3.5rem;
  }
  .pagination-wrapper {
    position: absolute;
    bottom: 0.5rem;
    left: 50%;
    transform: translateX(-50%);
    background: #fff;
    padding: 0.25rem 0;
    z-index: 10;
  }
  /* Estilos de paginación Bootstrap */
  .pagination-wrapper .pagination {
    margin: 0;
  }
  .pagination-wrapper .pagination li.page-item {
    margin: 0 0.125rem;
  }
  .pagination-wrapper .pagination li.page-item .page-link {
    padding: 0.375rem 0.75rem;
  }
  .pagination-wrapper .pagination li.active .page-link {
    background-color: #0d6efd;
    border-color: #0d6efd;
    color: #fff;
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
                  <li class="breadcrumb-item" aria-current="page">Dashboard Warehouse Inventory</li>
                </ul>
              </div>
              <div class="col-md-12">
                <div class="page-header-title">
                  <h2 class="mb-0">Dashboard Warehouse Inventory</h2>
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
            <h5 class="mb-0">Warehouse Inventory</h5>
          <!-- Botón único de acciones -->
          <button class="btn btn-sm btn-secondary" data-bs-toggle="modal" data-bs-target="#actionsModal">
            <i class="ti ti-menu-2"></i> Acciones
          </button>
        </div>
        <!-- Modal de Acciones -->
        <div class="modal fade" id="actionsModal" tabindex="-1" aria-labelledby="actionsModalLabel" aria-hidden="true">
          <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title" id="actionsModalLabel">Acciones</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
              </div>
              <div class="modal-body d-flex flex-column gap-2">
                <button type="button"
                        class="btn btn-primary"
                        data-bs-toggle="modal"
                        data-bs-target="#filterModal"
                        data-bs-dismiss="modal">
                  <i class="ti ti-filter"></i> Filtros avanzados
                </button>
                <button type="button"
                        class="btn btn-secondary"
                        id="btnClearFilters"
                        data-bs-dismiss="modal">
                  <i class="ti ti-x"></i> Limpiar
                </button>
                <a href="../forms/importardispatch.php"
                  class="btn btn-success">
                  <i class="ti ti-plus"></i> Nuevo Dispatch Inventory
                </a>
                <button type="button"
                        id="exportBtn"
                        class="btn btn-info">
                  <i class="ti ti-file-export"></i> Exportar a Excel
                </button>
              </div>
            </div>
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
                  <label for="containerFilter" class="form-label">Numero de Container</label>
                  <input type="text" class="form-control" id="containerFilter" placeholder="Ingrese número de Container">
                </div>

                <!-- Filtro por Destiny POD -->
                <div class="mb-3">
                  <label for="OpFilter" class="form-label">Op Number</label>
                  <input type="text" class="form-control" id="OpFilter" placeholder="Ingrese Lot Number">
                </div>

                <!-- Filtro por ETA Date -->
                <div class="mb-3">
                  <label for="rangoFechas" class="form-label">Entry Date</label><br>
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
              <button id="btnApplyFilters" class="btn btn-primary">Aplicar filtros</button>            
            </div>
          </div>
        </div>
      </div>
        <div class="card-body">
    <div class="table-responsive">
        <table class="table table-hover" id="pc-dt-simple">
           <!-- Encabezados de la tabla -->
          <thead>
            
               <tr>
                <th>NUM OP</th>
                <th>Number_Container</th>
                <th>Entry Date</th>
                <th>Warehouse Receipt</th>
                <th>Lot_Number</th>
                <th>Booking_BK</th>
                <th>Number_Commercial_Invoice</th>
                <th>Code Product EC</th>
                <th>Description</th>
                <th>Qty</th>
                <th>Unit Value</th>
                <th>Value</th>
                <th>Unit</th>
                <th>Length (in)</th>
                <th>Broad (in)</th>
                <th>Height (in)</th>
                <th>Weight (lb)</th>
                
                <th>Status‎ ‎ ‎ ‎ ‎ ‎ ‎ ‎ ‎ ‎ ‎ ‎ ‎ ‎ ‎ ‎ ‎ ‎ ‎ ‎ ‎ ‎ ‎</th>
            </tr>
          </thead>

          <!-- Cuerpo de la tabla -->
          <tbody>
            <?php while($row = $result->fetch_assoc()) { ?>
              <tr>
                <td><?= htmlspecialchars($row['NUM_OP']) ?></td>
                <td><?= htmlspecialchars($row['Number_Container']) ?></td>
                <td><?= htmlspecialchars($row['Entry_Date']) ?></td>
                <td><?= htmlspecialchars($row['recibo_almacen']) ?></td>

                <td><?= htmlspecialchars($row['Lot_Number']) ?></td>
                <td><?= htmlspecialchars($row['Booking_BK']) ?></td>
                <td><?= htmlspecialchars($row['Number_Commercial_Invoice']) ?></td>
                <td><?= htmlspecialchars($row['Code_Product_EC']) ?></td>
                <td><?= htmlspecialchars($row['Description']) ?></td>
                <td><?= htmlspecialchars($row['Qty']) ?></td>
                <td><?= htmlspecialchars($row['Unit_Value']) ?></td>
                <td><?= htmlspecialchars($row['Value']) ?></td>
                <td><?= htmlspecialchars($row['Unit']) ?></td>
                <td><?= htmlspecialchars($row['Length_in']) ?></td>
                <td><?= htmlspecialchars($row['Broad_in']) ?></td>
                <td><?= htmlspecialchars($row['Height_in']) ?></td>
                <td><?= htmlspecialchars($row['Weight_lb']) ?></td>
                <td>
                  <select
                    class="form-select form-select-sm status-select bg-light text-dark border-0 rounded-3 shadow-sm fs-6"
                    data-container="<?= htmlspecialchars($row['Number_Container']) ?>"
                    data-invoice="<?= htmlspecialchars($row['Number_Commercial_Invoice']) ?>"
                    data-id="<?= $row['id'] ?>">
                    <option value="Cargado"   <?= $row['Status']=='Cargado'   ? 'selected':'' ?>>Cargado</option>
                    <option value="En Almacén"<?= $row['Status']=='En Almacén'? 'selected':'' ?>>En Almacén</option>
                  </select>
                </td>
              </tr>
            <?php } ?>
          </tbody>
        </table>
    </div>
</div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/es.js"></script> <!-- Soporte en español -->

<!-- ACTUALIZAR STATUS -->
<script>
document.addEventListener('DOMContentLoaded', function () {
  // Atacha listener a TODOS los selects
  document.querySelectorAll('.status-select').forEach(sel => {
    sel.addEventListener('change', function () {
      const container = this.dataset.container;
      const invoice   = this.dataset.invoice;
      const value     = this.value;
      actualizarStatus(container, invoice, value);
    });
  });
});


async function actualizarStatus(container, invoice, value) {
  try {
    Swal.fire({ title:'Cargando', allowOutsideClick:false, didOpen:()=>Swal.showLoading() });

    const res = await fetch('../api/dispatch_status.php', {
      method: 'POST',
      headers: {'Content-Type':'application/json'},
      body: JSON.stringify({ container, invoice, value })
    });
    const json = await res.json();
    Swal.close();

    if (json.success) {
      // Si vino el número de contenedor, mostramos filas afectadas
      if (json.container) {
        Swal.fire({
          icon: 'success',
          title: `Contenedor ${json.container}`,
          text: json.message,
          confirmButtonText: 'OK'
        });
      } else {
        Swal.fire({ icon:'success', title: json.message, toast:true, position:'top-end', timer:1500 });
      }
    } else {
      Swal.fire({ icon:'error', title: json.error||'Error', toast:true, position:'top-end', timer:2000 });
    }
  } catch (err) {
    Swal.close();
    Swal.fire({ icon:'error', title:'Error de conexión', toast:true, position:'top-end', timer:2000 });
    console.error(err);
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
// --- Helper para convertir fechas ---
function toISODate(str) {
  let d,m,y;
  if (str.includes('/')) [d,m,y] = str.split('/');
  else                  [y,m,d] = str.split('-');
  return `${y.padStart(4,'0')}-${m.padStart(2,'0')}-${d.padStart(2,'0')}`;
}

$(document).ready(function(){
  // 1) Inicializa DataTable UNA sola vez
  const table = $('#pc-dt-simple').DataTable({
    paging:       true,
    pageLength:   10,
    lengthChange: false,
    searching:    false,
    info:         false,
    ordering:     false,
    language:     { paginate:{ previous:'«', next:'»' } },
    dom:          't<"pagination-wrapper"p>'
  });
  $('.pagination-wrapper')
    .appendTo($('#pc-dt-simple').closest('.table-responsive'));

  // 2) Inicializa flatpickr en el input de rango
  flatpickr('#rangoFechas', {
    mode: 'range',
    locale: 'es',
    dateFormat: 'Y-m-d'
  });

  // 3) Botones
  $('#btnApplyFilters').on('click', aplicarFiltrosAvanzados);
  $('#btnClearFilters').on('click', limpiarFiltrosAvanzados);

  // 4) Exportar Excel
  $('#exportBtn').on('click', () => {
    const wb = XLSX.utils.table_to_book(
      document.getElementById('pc-dt-simple'),
      { sheet:"Hoja1" }
    );
    XLSX.writeFile(wb, "warehouse_inventory.xlsx");
  });

  // 5) Función para aplicar filtros
  async function aplicarFiltrosAvanzados() {
    const container = $('#containerFilter').val().trim();
    const op        = $('#OpFilter').val().trim();
    const rango     = $('#rangoFechas').val().trim();
    const params    = new URLSearchParams();

    if (container) params.append('container', container);
    if (op)        params.append('op', op);
    if (rango) {
      const [f,t] = rango.split(' a ').map(s=>s.trim());
      params.append('dateFrom', toISODate(f));
      params.append('dateTo',   toISODate(t));
    }

    try {
      const res  = await fetch(`../api/filters/fetchWarehouse.php?${params.toString()}`);
      if (!res.ok) throw new Error(res.statusText);
      const rows = await res.json();

      // refresca la DataTable sin reinit
      table.clear();
      rows.forEach(r => {
        table.row.add([
          r.NUM_OP,
          r.Number_Container,
          r.Entry_Date,
          r.recibo_almacen,
          r.Lot_Number,
          r.Booking_BK,
          r.Number_Commercial_Invoice,
          r.Code_Product_EC,
          r.Description,
          r.Qty,
          r.Unit_Value,
          r.Value,
          r.Unit,
          r.Length_in,
          r.Broad_in,
          r.Height_in,
          r.Weight_lb,
          `<select class="form-select form-select-sm status-select"
                   data-container="${r.Number_Container}"
                   data-invoice="${r.Number_Commercial_Invoice}">
             <option value="Cargado"${r.Status==='Cargado'?' selected':''}>Cargado</option>
             <option value="En Almacén"${r.Status==='En Almacén'?' selected':''}>En Almacén</option>
           </select>`
        ]);
      });
      table.draw();

      // cierra modal y re-ata handlers
      bootstrap.Modal.getInstance($('#filterModal')[0])?.hide();
      initStatusListeners();

    } catch (err) {
      console.error(err);
      Swal.fire('Error', 'No se pudieron cargar los datos', 'error');
    }
  }

  // 6) Función para limpiar filtros
  async function limpiarFiltrosAvanzados() {
    $('#containerFilter, #OpFilter').val('');
    const fp = document.getElementById('rangoFechas')._flatpickr;
    if (fp) fp.clear();
    await aplicarFiltrosAvanzados();
  }

  // 7) Re-ata listener para los selects de Status
  function initStatusListeners() {
    document.querySelectorAll('.status-select').forEach(sel => {
      sel.onchange = async function() {
        try {
          Swal.fire({ title:'Actualizando...', didOpen: ()=> Swal.showLoading(), allowOutsideClick:false });
          const resp = await fetch('../api/dispatch_status.php', {
            method: 'POST',
            headers:{ 'Content-Type':'application/json' },
            body: JSON.stringify({
              container: this.dataset.container,
              invoice:   this.dataset.invoice,
              value:     this.value
            })
          });
          const json = await resp.json();
          Swal.close();
          if (json.success) {
            Swal.fire({ icon:'success', title: json.message, toast:true, position:'top-end', timer:1500 });
          } else {
            throw new Error(json.error || 'Error');
          }
        } catch (e) {
          Swal.close();
          Swal.fire('Error', e.message, 'error');
        }
      };
    });
  }

  // 8) Arranca el listener la primera vez
  initStatusListeners();
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