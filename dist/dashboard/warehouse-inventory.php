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
  c.num_op AS NUM_OP,
  c.Number_Container,
  c.Booking_BK,
  d.codigo_despacho,
  d.fecha_entrada AS Entry_Date,
  d.recibo_almacen AS Receive,
  d.numero_lote AS Lot_Number,
  d.numero_factura AS Number_Commercial_Invoice,
  d.numero_parte AS Code_Product_EC,
  d.descripcion AS Description_Dispatch,
  d.modelo AS Modelo_Dispatch,

  -- Primer Number_PO
  SUBSTRING_INDEX(GROUP_CONCAT(DISTINCT i.Number_PO ORDER BY i.Number_PO), ',', 1) AS First_Number_PO,

  -- Primer Description
  SUBSTRING_INDEX(GROUP_CONCAT(DISTINCT i.Description ORDER BY i.Number_PO), ',', 1) AS First_Description_Item,

  -- Total cantidad de cajas
  SUM(COALESCE(i.Qty_Box,0)) AS Total_Qty_Item_Packing,

  d.palets AS palets,
  d.cantidad AS cantidad,
  (d.palets * d.cantidad) AS Total_Despachado,
  d.valor_unitario AS Unit_Value,
  (d.valor_unitario * d.cantidad) AS Value,
  d.unidad AS Unit,
  d.longitud_in AS Length_in,
  d.ancho_in AS Broad_in,
  d.altura_in AS Height_in,
  d.peso_lb AS Weight_lb,
  d.valor_unitario_restante,
  d.valor_restante,
  d.unidad_restante,
  d.longitud_in_restante,
  d.ancho_in_restante,
  d.altura_in_restante,
  d.peso_lb_restante,
  d.estado AS Status
FROM dispatch d
LEFT JOIN container c ON c.Number_Container = d.notas
LEFT JOIN items i ON i.Number_Commercial_Invoice = d.numero_factura
                  AND i.Code_Product_EC = d.numero_parte
WHERE d.estado = 'En Almacén'
GROUP BY d.id, c.num_op, c.Number_Container, c.Booking_BK, d.codigo_despacho, d.fecha_entrada,
         d.recibo_almacen, d.numero_lote, d.numero_factura, d.numero_parte, d.descripcion, d.modelo,
         d.palets, d.cantidad, d.valor_unitario, d.unidad, d.longitud_in, d.ancho_in, d.altura_in,
         d.peso_lb, d.valor_unitario_restante, d.valor_restante, d.unidad_restante, d.longitud_in_restante,
         d.ancho_in_restante, d.altura_in_restante, d.peso_lb_restante, d.estado
ORDER BY c.num_op, d.descripcion, d.modelo;







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
    <title>Dashboard WareHouse USA 1 | Eko Logistic</title>
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
                  <li class="breadcrumb-item" aria-current="page">Dashboard WareHouse USA 1</li>
                </ul>
              </div>
              <div class="col-md-12">
                <div class="page-header-title">
                  <h2 class="mb-0">Dashboard WareHouse USA 1</h2>
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
            <h5 class="mb-0">WareHouse USA 1</h5>
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
                  <input type="text" class="form-control" id="OpFilter" placeholder="Ingrese Op Number">
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
<?php
$palletsPorGrupo = [];

if (isset($result) && $result->num_rows > 0) {
    $result->data_seek(0);

    while ($rowTemp = $result->fetch_assoc()) {
        // Usamos ?? para evitar "Undefined array key"
        $descripcion = $rowTemp['Description_Item'] ?? '';
        $modelo = $rowTemp['Modelo_Dispatch'] ?? '';
        $totalPalets = $rowTemp['total_palets'] ?? 0;

        $key = $descripcion . '|' . $modelo;

        if (!isset($palletsPorGrupo[$key])) {
            $palletsPorGrupo[$key] = 0;
        }

        if ((int)$totalPalets > 0) {
            $palletsPorGrupo[$key]++;
        }
    }

    // Volvemos al inicio del puntero para reutilizar el resultado
    $result->data_seek(0);
}
?>


<table class="table table-hover" id="pc-dt-simple">
  <thead>
    <tr>
      <th>Dispatch Code</th>
      <th>OP Num</th>
      <th>Container Num</th>
      <th>Entry Date</th>
      <th>Warehouse Rec.</th>
      <th>Lot Num</th>
      <th>Booking BK</th>
      <th>PO Num</th>
      <th>Comm. Invoice Num</th>
      <th>EC Product Code</th>
      <th>Description</th>
      <th>Palets</th>
      <th>Qty/Pallet</th>
      <th>Total</th>
      <th>Qty Item</th>
      <th>Unit Value</th>
      <th>Value</th>
      <th>Unit</th>
      <th>Length (in)</th>
      <th>Width (in)</th>
      <th>Height (in)</th>
      <th>Weight (lb)</th>
      <th>Load Pallets</th>
      <th>Load Qty</th>
      <th>Status‎ ‎ ‎ ‎ ‎ ‎ ‎ ‎ ‎ ‎ ‎ ‎ ‎ ‎ ‎ ‎ ‎ ‎ ‎ ‎ ‎ ‎ ‎ </th>
    </tr>
  </thead>
  <tbody>
<?php while($row = $result->fetch_assoc()) { ?>
  <tr>
   <td>
  <input type="text" class="form-control form-control-sm codigo-despacho-input"
         data-id="<?= htmlspecialchars($row['id']) ?>"
         value="<?= htmlspecialchars($row['codigo_despacho']) ?>"
         placeholder="Código despacho">
</td>

    <td><?= htmlspecialchars($row['NUM_OP']) ?></td>
    <td><?= htmlspecialchars($row['Number_Container']) ?></td>
    <td><?= htmlspecialchars($row['Entry_Date']) ?></td>
    <td><?= htmlspecialchars($row['Receive']) ?></td>
    <td><?= htmlspecialchars($row['Lot_Number']) ?></td>
    <td><?= htmlspecialchars($row['Booking_BK']) ?></td>
    <td><input type="text" class="form-control form-control-sm po-input" data-id="<?= htmlspecialchars($row['idItem'] ?? '') ?>" value="<?= htmlspecialchars($row['First_Number_PO'] ?? '') ?>"> </td>
    <td><?= htmlspecialchars($row['Number_Commercial_Invoice']) ?></td>
    <td><?= htmlspecialchars($row['Code_Product_EC']) ?></td>
    <td><?= htmlspecialchars($row['First_Description_Item']) ?></td>
    <td><?= htmlspecialchars($row['palets']) ?></td>
    <td><?= htmlspecialchars($row['cantidad']) ?></td>
    <td><?= htmlspecialchars($row['Total_Despachado']) ?></td>
    <td><?= htmlspecialchars($row['Total_Qty_Item_Packing']) ?></td>
    <td>$<?= htmlspecialchars($row['Unit_Value']) ?></td>
    <td>$<?= htmlspecialchars($row['Value']) ?></td>
    <td><?= htmlspecialchars($row['Unit']) ?></td>
    <td><?= htmlspecialchars($row['Length_in']) ?></td>
    <td><?= htmlspecialchars($row['Broad_in']) ?></td>
    <td><?= htmlspecialchars($row['Height_in']) ?></td>
    <td><?= htmlspecialchars($row['Weight_lb']) ?></td>
    
    <!-- Input para Palets de carga -->
   <td>
  <input type="number" min="0" class="form-control form-control-sm palets-carga-input"
    data-id="<?= htmlspecialchars($row['id']) ?>" placeholder="0">
</td>

<td>
  <input type="number" min="0" class="form-control form-control-sm cantidad-carga-input"
    data-id="<?= htmlspecialchars($row['id']) ?>" placeholder="0">
</td>


    <td>
      <select class="form-select form-select-sm status-select bg-light text-dark border-0 rounded-3 shadow-sm fs-6"
        data-container="<?= htmlspecialchars($row['Number_Container']) ?>"
        data-invoice="<?= htmlspecialchars($row['Number_Commercial_Invoice']) ?>"
        data-id="<?= htmlspecialchars($row['id']) ?>">
        <option value="Cargado" <?= $row['Status'] == 'Cargado' ? 'selected' : '' ?>>Cargado</option>
        <option value="En Almacén" <?= $row['Status'] == 'En Almacén' ? 'selected' : '' ?>>En Almacén</option>
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
      const id    = this.dataset.id;
      const value = this.value;
      actualizarStatus(id, value);
    });
  });
  // inputs de PO
  document.querySelectorAll('.po-input').forEach(inp => {
    inp.addEventListener('change', handlePoChange);
  });
  document.querySelectorAll('.codigo-despacho-input').forEach(inp => {
  inp.addEventListener('change', handleCodigoDespachoChange);
});

});




async function actualizarStatus(id, value) {
  try {
    // Obtener la fila y los inputs
    const row = document.querySelector(`.status-select[data-id='${id}']`)?.closest('tr');

    if (!row) {
      Swal.fire({
        icon: 'error',
        title: 'Error',
        text: 'No se encontró la fila correspondiente para validar.'
      });
      return;
    }

    // Leer valores de los inputs
    const paletsInput = row.querySelector('.palets-carga-input');
    const cantidadInput = row.querySelector('.cantidad-carga-input');

    const paletsValue = parseFloat(paletsInput?.value);
    const cantidadValue = parseFloat(cantidadInput?.value);

    // Validación previa: verificar que sean números válidos y mayores a 0
    if (value === 'Cargado') {
      if (isNaN(paletsValue) || paletsValue <= 0) {
        Swal.fire({
          icon: 'error',
          title: 'Dato inválido',
          text: 'Debes ingresar un valor válido mayor a 0 para Palets de carga.'
        });
        return;
      }

      if (isNaN(cantidadValue) || cantidadValue <= 0) {
        Swal.fire({
          icon: 'error',
          title: 'Dato inválido',
          text: 'Debes ingresar un valor válido mayor a 0 para Cantidad a cargar.'
        });
        return;
      }
    }

    // Confirmación visual
    Swal.fire({ title: 'Cargando', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

    // Enviar datos por fetch
    const res = await fetch('../api/actualizar_status_dispatch.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        id,
        value,
        palets: paletsValue,
        cantidad: cantidadValue
      })
    });

    const json = await res.json();
    Swal.close();

    if (json.success) {
      if (json.container) {
        await Swal.fire({
          icon: 'success',
          title: `Contenedor ${json.container}`,
          text: json.message,
          confirmButtonText: 'OK'
        });
      } else {
        await Swal.fire({
          icon: 'success',
          title: json.message,
          toast: true,
          position: 'top-end',
          timer: 1500
        });
      }

      // Refrescar la página luego del mensaje
      location.reload();

    } else {
      Swal.fire({ icon: 'error', title: json.error || 'Error', toast: true, position: 'top-end', timer: 2000 });
    }

  } catch (err) {
    Swal.close();
    Swal.fire({ icon: 'error', title: 'Error de conexión', toast: true, position: 'top-end', timer: 2000 });
    console.error(err);
  }
}




async function handlePoChange() {
  const id = this.dataset.id;
  const po = this.value.trim();
  try {
    const res = await fetch('../api/update_dispatch_po.php', {
      method: 'POST',
      headers: { 'Content-Type':'application/json' },
      body: JSON.stringify({ id, po })
    });
    const json = await res.json();
    Swal.fire(
      json.success ? 'Éxito' : 'Error',
      json.success ? (json.message || 'Actualizado') : json.error,
      json.success ? 'success' : 'error'
    );
  } catch {
    Swal.fire('Error de red','','error');
  }
}

async function handleCodigoDespachoChange() {
  const id = this.dataset.id;
  const codigo = this.value.trim();
  const input = this; // referencia al input actual

  try {
    const res = await fetch('../api/update_codigo_despacho.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id, codigo })
    });

    const text = await res.text(); 
    let json;

    try {
      json = JSON.parse(text); 
    } catch (parseError) {
      console.error('Respuesta no es JSON válida:', text);
      return;
    }

    if (json.success) {
      // Cambiar fondo a verde temporalmente
      input.style.backgroundColor = '#d4edda'; // verde claro
      input.style.transition = 'background-color 0.5s';
      setTimeout(() => {
        input.style.backgroundColor = ''; // vuelve al color original
      }, 1500);
    } else {
      // Si hubo error, podemos marcar en rojo temporal
      input.style.backgroundColor = '#f8d7da'; // rojo claro
      setTimeout(() => {
        input.style.backgroundColor = '';
      }, 2000);
      console.error(json.error || 'Error al actualizar código');
    }

  } catch (err) {
    console.error('Error de red o fetch:', err);
    // opcional: marcar en rojo temporal
    input.style.backgroundColor = '#f8d7da';
    setTimeout(() => {
      input.style.backgroundColor = '';
    }, 2000);
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
    // 1) Cabeceras
    const headers = [];
    $('#pc-dt-simple thead th').each((_, th) => {
      headers.push($(th).text().trim());
    });

    // 2) Filas de datos
    const data = [ headers ];
    table.rows().every(function(){
      const $row = $(this.node());
      const rowArr = [];

      $row.find('td').each((i, td) => {
        const $td = $(td);

        // si es la celda de Status, tomar el valor seleccionado
        const $sel = $td.find('select.status-select');
        if ($sel.length) {
          rowArr.push($sel.val());
        } else {
          rowArr.push($td.text().trim());
        }
      });

      data.push(rowArr);
    });

    // 3) Generar libro y descargar
    const ws = XLSX.utils.aoa_to_sheet(data);
    const wb = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(wb, ws, 'Warehouse');
    XLSX.writeFile(wb, 'warehouse_inventory.xlsx');
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
    const [f,t] = rango.split(' a ').map(s => s.trim());
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
        r.Receive,  // antes r.recibo_almacen
        r.Lot_Number,
        r.Booking_BK,
        // input de PO usa r.idItem y r.Number_PO
        `<input
          type="text"
          class="form-control form-control-sm po-input"
          data-id="${r.idItem}"
          value="${r.Number_PO || ''}"
        >`,
        r.Number_Commercial_Invoice,
        r.Code_Product_EC,
        r.Description,
        r.palets,
        r.cantidad,
        r.total_despachado,
        r.Qty_Item_Packing,  // si este es el nombre correcto para Qty Item
        r.Unit_Value,
        r.Value,
        r.Unit,
        r.Length_in,
        r.Broad_in,
        r.Height_in,
        r.Weight_lb,

        // Inputs para palets de carga y cantidad a cargar (nuevas columnas)
        `<input type="number" min="0" class="form-control form-control-sm palets-carga-input" data-id="${r.id}" placeholder="0">`,
        `<input type="number" min="0" class="form-control form-control-sm cantidad-carga-input" data-id="${r.id}" placeholder="0">`,

        // select de status usa r.id
        `<select 
          class="form-select form-select-sm status-select" 
          data-id="${r.id}" 
          data-container="${r.Number_Container}" 
          data-invoice="${r.Number_Commercial_Invoice}"
        >
          <option value="Cargado"${r.Status === 'Cargado' ? ' selected' : ''}>
            Cargado
          </option>
          <option value="En Almacén"${r.Status === 'En Almacén' ? ' selected' : ''}>
            En Almacén
          </option>
        </select>`
      ]);
    });

    table.draw();

    // cierra modal y re-atacha listeners
    bootstrap.Modal.getInstance($('#filterModal')[0])?.hide();
    initStatusListeners();
    initPoListeners();

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