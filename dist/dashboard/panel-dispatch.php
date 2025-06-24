<?php
session_start();

include('../usuarioClass.php');
include("../con_db.php");
$IdUsuario=$_SESSION["IdUsuario"];
if(!$_SESSION["IdUsuario"]){
  header("Location: ../");
}
error_reporting(E_ALL);
ini_set('display_errors', 1);
$usuario= new Usuario($conexion);

$user=$usuario->obtenerUsuarioPorId($IdUsuario);


$sql = "
  SELECT
    d.id,
    i.idItem AS idItem,                      -- <-- lo agregamos
    c.num_op                             AS NUM_OP,
    c.Number_Container                   AS Number_Container,
    c.Booking_BK,
    d.numero_lote                        AS Lot_Number,
    d.fecha_entrada                      AS Entry_Date,
    d.fecha_salida                       AS Out_Date,
    c.Number_Commercial_Invoice          AS Number_Commercial_Invoice,
    d.numero_parte                       AS Code_Product_EC,
    i.Number_PO                          AS Number_PO,
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
INNER JOIN items i
    ON i.idContainer = c.idContainer
WHERE d.estado = 'Cargado'
ORDER BY c.num_op, d.numero_parte;


    
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
  <title>Dashboard Dispatch Inventory | Eko Logistic</title>
  <!-- [Meta] -->
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />

  <!-- [Favicon] icon -->
  <link rel="icon" href="../assets/images/ekologistic.png" type="image/x-icon" />

  <!-- DataTables con Bootstrap5 -->
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css"/>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>

  <!-- Flatpickr para fechas -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css" />
  <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
  <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/es.js"></script>

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

  <!-- Handsontable -->
  <script src="https://cdn.jsdelivr.net/npm/handsontable/dist/handsontable.full.min.js"></script>
  <link href="https://cdn.jsdelivr.net/npm/handsontable/dist/handsontable.full.min.css" rel="stylesheet">

  <!-- SweetAlert2 -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

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

    .table-responsive {
      position: relative;
      padding-bottom: 3.5rem;
    }

    .table-responsive .pagination-wrapper {
      position: absolute;
      bottom: 0.5rem;
      left: 50%;
      transform: translateX(-50%);
      background: #fff;
      padding: 0.25rem 0;
      z-index: 10;
    }

    /* Botones Bootstrap */
    .table-responsive .pagination-wrapper .pagination {
      margin: 0;
    }

    .table-responsive .pagination-wrapper .pagination li.page-item {
      margin: 0 0.125rem;
    }

    .table-responsive .pagination-wrapper .pagination li.page-item .page-link {
      padding: 0.375rem 0.75rem;
    }

    .table-responsive .pagination-wrapper .pagination li.active .page-link {
      background-color: #0d6efd;
      border-color: #0d6efd;
      color: #fff;
    }

    /* Estilos adicionales para la tabla */
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
                  <li class="breadcrumb-item" aria-current="page">Dashboard Dispatch Inventory</li>
                </ul>
              </div>
              <div class="col-md-12">
                <div class="page-header-title">
                  <h2 class="mb-0">Dashboard Dispatch Inventory</h2>
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
        <div class="card-header d-flex align-items-center justify-content-end py-3">
            <h5 class="mb-0"></h5>
           <!-- <div class="d-flex gap-2 align-items-center">
              <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#filterModal">
                <i class="ti ti-filter"></i> Filtros avanzados
              </button>
              <button class="btn btn-sm btn-secondary" onclick="limpiarFiltrosAvanzados()">
                <i class="ti ti-x"></i> Limpiar
              </button>
            </div>-->
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
                  <!-- Filtro por Cliente 
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
                  -->
                  <!-- Filtro por Number PO 
                  <div class="mb-3">
                    <label for="poFilter" class="form-label">Número de PO</label>
                    <input type="text" class="form-control" id="poFilter" placeholder="Ingrese número de PO">
                  </div>
                  -->
                  <!-- Filtro por Numero OP (Container) -->
                  <div class="mb-3">
                    <label for="containerFilter" class="form-label">Número de Contenedor (OP)</label>
                    <input type="text" class="form-control" id="containerFilter" placeholder="Ingrese número de contenedor">
                  </div>
                  <div class="mb-3">
                    <label for="containerFilter" class="form-label">Date Created</label><br>
                    <input type="text" id="rangoFechas" class="form-control form-control-sm" placeholder="Seleccione rango" style="max-width: 220px;" readonly>
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
          <table id="pc-dt-simple" class="table table-hover">
            <thead>
              <tr>
                <th>NUM OP</th>
                <th>Number_Container</th>
                <th>Booking_BK</th>
                <th>PO Number</th>
                <th>Entry Date</th>
                <th>Dispatch Date</th>
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
                <th>Status</th>
              </tr>
            </thead>
            <tbody>
              <?php while ($row = $result->fetch_assoc()): ?>
              <tr>
                <td><?= htmlspecialchars($row['NUM_OP']) ?></td>
                <td><?= htmlspecialchars($row['Number_Container']) ?></td>
                <td><?= htmlspecialchars($row['Booking_BK']) ?></td>
                <td>
                 <input
  type="text"
  class="form-control form-control-sm po-input"
  data-id="<?= $row['idItem'] ?>"
  value="<?= htmlspecialchars($row['Number_PO']) ?>"
>
                </td>
                <td><?= htmlspecialchars($row['Entry_Date']) ?></td>
                <td>
                  <input
                    type="text"
                    class="form-control form-control-sm dispatch-date-picker"
                    data-id="<?= $row['id'] ?>"
                    value="<?= htmlspecialchars($row['Out_Date']) ?>"
                    placeholder="YYYY-MM-DD"
                  >
                </td>
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
                <td >
                  <select class="form-select form-select-sm status-select bg-light text-dark border-0 rounded-3 shadow-sm fs-6" data-id="<?= $row['id'] ?>">
                    <option value="Cargado" <?= $row['Status'] == 'Cargado' ? 'selected' : '' ?>>Cargado</option>
                  </select>
                </td>
              </tr>
              <?php endwhile; ?>
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
    // Guardar cambios en Status
    document.querySelectorAll('.status-select').forEach(select => {
        select.addEventListener('change', function () {
            const id = this.getAttribute('data-id'); // Id del packinglist
            const value = this.value; // Nuevo valor seleccionado

            actualizarStatus(id, value);
        });
    });

    function actualizarStatus(id, value) {
        debugger
        fetch('../api/dispatch_status.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id, value }) // Enviamos solo id y value
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
              Swal.fire({
              title: 'Éxito',
              text: data.message,
              icon: 'success',
              confirmButtonText: 'Continuar'
            });
            } else {
                alert('Error: ' + data.error);
            }
        })
        .catch(error => console.error('Error:', error));
    }
});


$(document).ready(function() {
  const toast = Swal.mixin({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 2000
  });

  var dt;
  const dtConfig = {
    paging: true,
    pageLength: 10,
    lengthChange: false,
    searching: false,
    info: false,
    ordering: false,
    language: { paginate:{ previous:'«', next:'»' } },
    dom: 't<"pagination-wrapper"p>'
  };

  function initDataTable() {
    dt = $('#pc-dt-simple').DataTable(dtConfig);
    $('.pagination-wrapper')
      .appendTo( $('#pc-dt-simple').closest('.table-responsive') );
  }
  initDataTable();

  // Dispatch Date editor
  flatpickr('.dispatch-date-picker', {
    locale:'es', dateFormat:'Y-m-d',
    onChange: (_, dateStr, inst) => {
      fetch('../api/update_dispatch_date.php', {
        method: 'POST',
        headers:{'Content-Type':'application/json'},
        body: JSON.stringify({ id: inst.input.dataset.id, date: dateStr })
      })
      .then(r => r.json())
      .then(json => {
        if (json.success) {
          toast.fire({ icon: 'success', title: 'Fecha actualizada' });
        } else {
          toast.fire({ icon: 'error', title: json.error });
        }
      })
      .catch(() => toast.fire({ icon: 'error', title: 'Error de red' }));
    }
  });

  // PO Number editor
  $('.po-input').change(function(){
    fetch('../api/update_dispatch_po.php', {
      method: 'POST',
      headers:{'Content-Type':'application/json'},
      body: JSON.stringify({ id: this.dataset.id, po: this.value })
    })
    .then(r => r.json())
    .then(json => {
      if (json.success) {
        toast.fire({ icon: 'success', title: 'PO actualizada' });
      } else {
        toast.fire({ icon: 'error', title: json.error });
      }
    })
    .catch(() => toast.fire({ icon: 'error', title: 'Error de red' }));
  });

  // Status selector
  $('.status-select').change(function(){
    fetch('../api/dispatch_status.php', {
      method: 'POST',
      headers:{'Content-Type':'application/json'},
      body: JSON.stringify({ id: this.dataset.id, value: this.value })
    })
    .then(r => r.json())
    .then(json => {
      if (json.success) {
        toast.fire({ icon: 'success', title: 'Estado guardado' });
      } else {
        toast.fire({ icon: 'error', title: json.error });
      }
    })
    .catch(() => toast.fire({ icon: 'error', title: 'Error de red' }));
  });
});
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
// Forzar redimensionamiento al cambiar tamaño de ventana

window.addEventListener('resize', function() {
    if (hot) {
        hot.render();
        hot.view.adjustElementsSize();
    }
});
</script>





<script>

// Función para inicializar listeners de status
function initStatusListeners() {
    document.querySelectorAll('.status-select').forEach(select => {
        select.removeEventListener('change', handleStatusChange); // Eliminar existentes
        select.addEventListener('change', handleStatusChange);
    });
}

function handleStatusChange() {
    const id = this.getAttribute('data-id');
    const value = this.value;
    actualizarStatus(id, value);
}

async function aplicarFiltrosAvanzados() {

const container = document.getElementById('containerFilter').value;
const rango = document.getElementById('rangoFechas').value.trim();

const params = new URLSearchParams();
if (container) params.append('container', container);

if (rango) {
    const partes = rango.split(' a ');
    if (partes.length === 2) {
        const [desde, hasta] = partes;
        params.append('dateFrom', desde);
        params.append('dateTo', hasta);
    }
}

try {
    const res = await fetch(`../api/filters/fetchPanelPL.php?${params.toString()}`);
    if (!res.ok) throw new Error(res.statusText);
    const rows = await res.json();

    const tbody = document.querySelector('#pc-dt-simple tbody');
    if (dt) {
        dt.destroy();
        $('.pagination-wrapper').remove();
    }
    tbody.innerHTML = '';

    rows.forEach(row => {
        const tr = `
        <tr>
            <td>${row['Num OP'] || ''}</td>
            <td>${row['Destinity POD'] || ''}</td>
            <td>${row['Booking_BK'] || ''}</td>
            <td>${row['Number_Container'] || ''}</td>
            <td>${row['Qty_Box'] || 0}</td>
            <td>$${Number(row['TOTAL PRICE EC'] || 0).toFixed(2)}</td>
            <td>${formatDate(row['Date created'])}</td>
            <td>${row['Hour'] || ''}</td>
            <td>${row['User Name'] || ''}</td>
            <td>
                <div class="d-flex gap-0">
                    <button class="btn d-flex align-items-center btn-edit-excel" 
                            data-excel-path="${row['File Home'] || '#'}" 
                            data-packing-id="${row['ITEM #'] || ''}">
                        <i class="ti ti-edit f-30"></i>
                    </button>
                    <a href="${row['File Home'] || '#'}" download 
                        class="btn d-flex align-items-center btn-download-excel">
                        <i class="ti ti-download f-30"></i>
                    </a>
                </div>
            </td>
            <td>
                <select class="form-select form-select-sm status-select bg-light text-dark border-0 rounded-3 shadow-sm fs-6" 
                        data-id="${row['ITEM #'] || ''}">
                    <option value="Inicial" ${row.STATUS === 'Inicial' ? 'selected' : ''}>Inicial</option>
                    <option value="Completado" ${row.STATUS === 'Completado' ? 'selected' : ''}>Completado</option>
                </select>
            </td>
        </tr>`;
        tbody.insertAdjacentHTML('beforeend', tr);
    });
    initDataTable();

    initStatusListeners();
    initEditButtons();
    const modalEl = document.getElementById('filterModal');
    const modal = bootstrap.Modal.getInstance(modalEl);
    modal?.hide();

} catch (err) {
    console.error('Error al cargar datos:', err);
    Swal.fire('Error', 'No se pudieron cargar los datos', 'error');
}
}

function formatDate(dateString) {
if (!dateString) return '';
const date = new Date(dateString);
return isNaN(date) ? '' : 
    `${String(date.getDate()).padStart(2, '0')}-${String(date.getMonth() + 1).padStart(2, '0')}-${date.getFullYear()}`;
}

async function limpiarFiltrosAvanzados() {
  document.getElementById('containerFilter').value = '';
  document.getElementById('rangoFechas').value = '';

  try {
      const res = await fetch(`../api/filters/fetchPanelPL.php`);
      if (!res.ok) throw new Error(res.statusText);
      const rows = await res.json();

      const tbody = document.querySelector('#pc-dt-simple tbody');
      if (dt) {
        dt.destroy();
        $('.pagination-wrapper').remove();
      }
      tbody.innerHTML = '';

      rows.forEach(row => {
        const tr = `
        <tr>
            <td>${row['Num OP'] || ''}</td>
            <td>${row['Destinity POD'] || ''}</td>
            <td>${row['Booking_BK'] || ''}</td>
            <td>${row['Number_Container'] || ''}</td>
            <td>${row['Qty_Box'] || 0}</td>
            <td>$${Number(row['TOTAL PRICE EC'] || 0).toFixed(2)}</td>
            <td>${formatDate(row['Date created'])}</td>
            <td>${row['Hour'] || ''}</td>
            <td>${row['User Name'] || ''}</td>
            <td>
                <div class="d-flex gap-0">
                    <button class="btn d-flex align-items-center btn-edit-excel" 
                            data-excel-path="${row['File Home'] || '#'}" 
                            data-packing-id="${row['ITEM #'] || ''}">
                        <i class="ti ti-edit f-30"></i>
                    </button>
                    <a href="${row['File Home'] || '#'}" download 
                        class="btn d-flex align-items-center btn-download-excel">
                        <i class="ti ti-download f-30"></i>
                    </a>
                </div>
            </td>
            <td>
                <select class="form-select form-select-sm status-select bg-light text-dark border-0 rounded-3 shadow-sm fs-6" 
                        data-id="${row['ITEM #'] || ''}">
                    <option value="Inicial" ${row.STATUS === 'Inicial' ? 'selected' : ''}>Inicial</option>
                    <option value="Completado" ${row.STATUS === 'Completado' ? 'selected' : ''}>Completado</option>
                </select>
            </td>
        </tr>`;
        tbody.insertAdjacentHTML('beforeend', tr);
      });
      initDataTable();

      initStatusListeners();
      initEditButtons();
      const modalEl = document.getElementById('filterModal');
      const modal = bootstrap.Modal.getInstance(modalEl);
      modal?.hide();

  } catch (err) {
      console.error('Error al cargar datos:', err);
      Swal.fire('Error', 'No se pudieron resetear los filtros', 'error');
  }
}

// Inicialización al cargar la página
document.addEventListener('DOMContentLoaded', () => {
initStatusListeners();
initEditButtons();
});

// Función para botones de edición (ejemplo básico)
function initEditButtons() {
document.querySelectorAll('.btn-edit-excel').forEach(btn => {
    btn.addEventListener('click', function() {
        const packingId = this.dataset.packingId;
        const excelPath = this.dataset.excelPath;
        // Lógica de edición aquí
        console.log('Editar:', packingId, excelPath);
    });
});
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
