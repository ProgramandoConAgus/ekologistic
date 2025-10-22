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
  c.num_op AS NUM_OP,
  c.Number_Container,
  c.Booking_BK,
  d.fecha_entrada AS Entry_Date,
  d.fecha_salida AS departure_date,
  d.recibo_almacen AS Receive,
  d.numero_lote AS Lot_Number,
  d.numero_factura AS Number_Commercial_Invoice,
  d.numero_parte AS Code_Product_EC,
  d.descripcion AS Description_Dispatch,
  d.modelo AS Modelo_Dispatch,

  (SELECT i.Description FROM items i 
   WHERE i.Number_Commercial_Invoice = d.numero_factura 
     AND i.Code_Product_EC = d.numero_parte
   ORDER BY i.Number_PO LIMIT 1) AS Description_Item,

  (SELECT i.Number_PO FROM items i 
   WHERE i.Number_Commercial_Invoice = d.numero_factura 
     AND i.Code_Product_EC = d.numero_parte
   ORDER BY i.Number_PO LIMIT 1) AS Number_PO,

  (SELECT SUM(i.Qty_Box) FROM items i 
   WHERE i.Number_Commercial_Invoice = d.numero_factura 
     AND i.Code_Product_EC = d.numero_parte) AS Qty_Item_Packing,

  d.palets AS palets,
  d.codigo_despacho,
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

FROM palets_cargados d
LEFT JOIN container c
  ON c.Number_Container = d.notas

WHERE d.estado = 'Cargado'

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
                  <li class="breadcrumb-item" aria-current="page">Dashboard Warehouse Receipt</li>
                </ul>
              </div>
              <div class="col-md-12">
                <div class="page-header-title">
                  <h2 class="mb-0">Dashboard Warehouse Receipt</h2>
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
    <h5 class="mb-0">Warehouse Receipt</h5>
    <div class="d-flex gap-2 align-items-center">
      <button id="btnDownloadExcel" class="btn btn-sm btn-success">
        <i class="ti ti-file-export"></i> Descargar Excel
      </button>
      <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#filterModal">
        <i class="ti ti-filter"></i> Filtros avanzados
      </button>
      <button id="btnClearFilters" class="btn btn-sm btn-secondary">
       <i class="ti ti-x"></i> Limpiar
      </button>
    </div>
  </div>

  <div class="card-body">
    <div class="table-responsive">
      <table class="table table-hover" id="pc-dt-simple">
        <thead>
          <tr>
            <th>NUM OP</th>
            <th>Code Dispatch</th>
            <th>Departure Date</th>
            <th>Warehouse Receipt</th>
            <th>Lot_Number</th>
            <th>Number_PO</th>
            <th>Number_Commercial_Invoice</th>
            <th>Description</th>
            <th>Palets</th>
            <th>Cantidad</th>
            <th>Qty Item Packing</th>
            <th>Length (in)</th>
            <th>Broad (in)</th>
            <th>Height (in)</th>
            <th>Weight (lb)</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
          <?php while($row = $result->fetch_assoc()) { ?>
           <tr>
  <td><?= htmlspecialchars($row['NUM_OP']) ?></td>
  <td><?= htmlspecialchars($row['codigo_despacho']) ?></td>

  <!-- Departure Date editable -->
  <td>
    <input type="date"
      class="form-control form-control-sm editable-field"
      data-id="<?= $row['id'] ?>"
      data-field="departure_date"
      value="<?= htmlspecialchars($row['departure_date']) ?>">
  </td>

  <td><?= htmlspecialchars($row['Receive']) ?></td>
  <td><?= htmlspecialchars($row['Lot_Number']) ?></td>
  
   <td><?= htmlspecialchars($row['Number_PO']) ?></td>

  <td><?= htmlspecialchars($row['Number_Commercial_Invoice']) ?></td>
  <td><?= htmlspecialchars($row['Description_Item']) ?></td>
  <td><?= htmlspecialchars($row['palets']) ?></td>
  <td><?= htmlspecialchars($row['cantidad']) ?></td>
  <td><?= htmlspecialchars($row['Qty_Item_Packing']) ?></td>

  <!-- Medidas editables -->
  <td>
    <input type="number"
      step="any"
      class="form-control form-control-sm editable-field"
      data-id="<?= $row['id'] ?>"
      data-field="Length_in"
      value="<?= htmlspecialchars($row['Length_in']) ?>">
  </td>
  <td>
    <input type="number"
      step="any"
      class="form-control form-control-sm editable-field"
      data-id="<?= $row['id'] ?>"
      data-field="Broad_in"
      value="<?= htmlspecialchars($row['Broad_in']) ?>">
  </td>
  <td>
    <input type="number"
      step="any"
      class="form-control form-control-sm editable-field"
      data-id="<?= $row['id'] ?>"
      data-field="Height_in"
      value="<?= htmlspecialchars($row['Height_in']) ?>">
  </td>
  <td>
    <input type="number"
      step="any"
      class="form-control form-control-sm editable-field"
      data-id="<?= $row['id'] ?>"
      data-field="Weight_lb"
      value="<?= htmlspecialchars($row['Weight_lb']) ?>">
  </td>

  <td>
    <select class="form-select form-select-sm status-select"
      data-container="<?= htmlspecialchars($row['Number_Container']) ?>"
      data-invoice="<?= htmlspecialchars($row['Number_Commercial_Invoice']) ?>"
      data-id="<?= $row['id'] ?>">
      <option value="Cargado" <?= $row['Status'] == 'Cargado' ? 'selected' : '' ?>>Cargado</option>
    </select>
  </td>
</tr>

          <?php } ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Modal -->
<div class="modal fade" id="modalCodigoDespacho" tabindex="-1" aria-labelledby="modalCodigoDespachoLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalCodigoDespachoLabel">Seleccionar Código de Despacho</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        <select id="selectCodigoDespacho" class="form-select">
          <option value="">Cargando...</option>
        </select>
      </div>
      <div class="modal-footer">
        <button id="btnConfirmarDespacho" type="button" class="btn btn-primary" disabled>Descargar Excel</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
      </div>
    </div>
  </div>
</div>


<!-- Librería SheetJS para exportar Excel -->
<script src="https://cdn.sheetjs.com/xlsx-latest/package/dist/xlsx.full.min.js"></script>
<script>
// Función para cargar los codigos despacho únicos vía AJAX
async function cargarCodigosDespacho() {
  const select = document.getElementById('selectCodigoDespacho');
  select.innerHTML = '<option value="">Cargando...</option>';
  try {
    const res = await fetch('../api/despacho/get_codigos_despacho.php');
    const data = await res.json();
    if(data.success) {
      select.innerHTML = '<option value="">-- Seleccione Código de Despacho --</option>';
      data.codigos.forEach(codigo => {
        const opt = document.createElement('option');
        opt.value = codigo;
        opt.textContent = codigo;
        select.appendChild(opt);
      });
    } else {
      select.innerHTML = '<option value="">Error cargando códigos</option>';
    }
  } catch {
    select.innerHTML = '<option value="">Error de conexión</option>';
  }
}





// Cuando hacen click en el botón abrir modal y cargar opciones
document.getElementById('btnDownloadExcel').addEventListener('click', function () {
  const modal = new bootstrap.Modal(document.getElementById('modalCodigoDespacho'));
  cargarCodigosDespacho();
  modal.show();
});

// Habilitar botón Confirmar solo si se selecciona un código
document.getElementById('selectCodigoDespacho').addEventListener('change', function() {
  document.getElementById('btnConfirmarDespacho').disabled = !this.value;
});

document.getElementById('btnConfirmarDespacho').addEventListener('click', async function () {
  const codigoSeleccionado = document.getElementById('selectCodigoDespacho').value;

  if (!codigoSeleccionado) {
    alert("Por favor selecciona un código de despacho.");
    return;
  }

  try {
      console.log("Código seleccionado:", codigoSeleccionado);

    const response = await fetch('../api/despacho/get_datos_por_codigo.php', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({
      codigo_despacho: codigoSeleccionado
    })
  })

    if (!response.ok) throw new Error("Error en la respuesta del servidor");

    const responseData = await response.json();

  if (
    !responseData.success ||
    !Array.isArray(responseData.data) ||
    responseData.data.length === 0
  ) {
    alert("No se encontraron datos para ese código.");
    return;
  }

  const data = responseData.data;

  const encabezados = [
    "Warehouse Receipt", "Number_Commercial_Invoice", "Lot_Number", "Code Product EC",
    "Number_PO", "Description", "Packing U.", "QTY Pallet", "QTY Box", "Total Boxes",
    "Length (in)", "Broad (in)", "Height (in)", "Weight (lb)", "Unit Value", "Value"
  ];

  const datosFormateados = data.map(item => [
    item.Receive,                          // Warehouse Receipt
    item.Number_Commercial_Invoice,
    item.Lot_Number,
    item.Code_Product_EC,
    item.Number_PO,
    item.Description_Item,
    item.Qty_Item_Packing,                // Packing U.
    item.palets,
    item.cantidad,
    item.Total_Despachado,
    item.Length_in,
    item.Broad_in,
    item.Height_in,
    item.Weight_lb,
    item.Unit_Value,
    item.Value
  ]);

    const ws = XLSX.utils.aoa_to_sheet([encabezados, ...datosFormateados]);
    const wb = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(wb, ws, "Despacho");
    XLSX.writeFile(wb, `despacho_${codigoSeleccionado}.xlsx`);

    // Cerrar modal
    const modalElement = document.getElementById('modalCodigoDespacho');
    const modalInstance = bootstrap.Modal.getInstance(modalElement);
    if (modalInstance) {
      modalInstance.hide();
    }
  } catch (error) {
    console.error("Error al obtener los datos:", error);
    alert("Ocurrió un error al generar el archivo.");
  }
});




</script>

</div>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/es.js"></script> <!-- Soporte en español -->
<!-- ACTUALIZAR STATUS -->

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

<style>
.status-select {
  min-width: 100px;    /* ó el que necesites */
}
</style>

   





<script>
  let table;

  // Helper: convierte "d/m/Y" o "Y-m-d" a "Y-m-d"
  function toISODate(str) {
    let d, m, y;
    if (str.includes('/')) [d, m, y] = str.split('/');
    else                  [y, m, d] = str.split('-');
    return `${y.padStart(4,'0')}-${m.padStart(2,'0')}-${d.padStart(2,'0')}`;
  }



  // Solo UNA inicialización de DataTable
$(document).ready(function(){
  $('#pc-dt-simple').DataTable({
    autoWidth:    true,
    scrollX:      false,     
    scrollCollapse: false,   
    paging:       true,
    pageLength:   10,
    lengthChange: false,
    searching:    false,
    info:         false,
    ordering:     false,
    language:     { paginate:{ previous:'«', next:'»' } },
    dom:          't<"pagination-wrapper"p>',
    columnDefs: [
      
      { targets: 9, width: '250px' },   
    ]
  });
  // mueve el paginador dentro del wrapper
  $('.pagination-wrapper')
    .appendTo($('#pc-dt-simple').closest('.table-responsive'));

  // 2) flatpickr
  flatpickr('#rangoFechas',{ mode:'range', locale:'es', dateFormat:'Y-m-d' });

  // 3) Botones de filtro
  $('#btnApplyFilters').on('click', aplicarFiltrosAvanzados);
  $('#btnClearFilters' ).on('click', limpiarFiltrosAvanzados);

  $('#pc-dt-simple tbody')
    .off('change', '.status-select')    // elimina viejos (por si acaso)
    .on('change', '.status-select', handleStatusChange);

  $('#pc-dt-simple tbody')
  .off('change', '.po-input')
  .on('change', '.po-input', handlePoChange);


  });

    
    async function handleStatusChange() {
      try {
        const res  = await fetch('../api/actualizar_status_dispatch.php', {
          method: 'POST',
          headers: { 'Content-Type':'application/json' },
          body: JSON.stringify({ id:this.dataset.id, value:this.value })
        });
        const json = await res.json();
        Swal.fire(
          json.success ? 'Éxito' : 'Error',
          json.success ? json.message : json.error,
          json.success ? 'success' : 'error'
        );
      } catch (err) {
        Swal.fire('Error de red','','error');
      }
    }
    
     document.querySelectorAll('input[data-field]').forEach(input => {
  input.addEventListener('change', function () {
    const id = this.dataset.id;
    const field = this.dataset.field;
    const value = this.value.trim();
    handleFieldChange(field, value, id);
  });
});



   async function handleFieldChange(field, value, id) {
  try {
    const res = await fetch('../api/update_dispatch_field.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id, field, value })
    });
    const json = await res.json();
    Swal.fire(
      json.success ? 'Éxito' : 'Error',
      json.success ? json.message : json.error,
      json.success ? 'success' : 'error'
    );
  } catch {
    Swal.fire('Error de red', '', 'error');
  }
}


  // Función de aplicar filtros (igual que tenías)
  async function aplicarFiltrosAvanzados() {
    // 0) Asegurarnos de tener la instancia de DataTable
    const table = $('#pc-dt-simple').DataTable();

    // 1) Leer filtros
    const op    = $('#OpFilter').val().trim();
    const lot   = $('#LotFilter').val().trim();
    const rango = $('#rangoFechas').val().trim();
    const params = new URLSearchParams();
    if (op)    params.append('op', op);
    if (lot)   params.append('lot', lot);
    if (rango) {
      const [f,t] = rango.split(' a ').map(s=>s.trim());
      params.append('dateFrom', toISODate(f));
      params.append('dateTo',   toISODate(t));
    }

    try {
      // 2) Llamada AJAX
      const query = params.toString();
      const res   = await fetch(`../api/filters/fetchDispatch.php?${query}`);
      if (!res.ok) throw new Error(res.statusText);
      const items = await res.json();

      // 3) Limpiar y repoblar la tabla
      table.clear();
      items.forEach(item => {
        table.row.add([
          item.NUM_OP,
          item.Number_Container,
          item.Booking_BK,
          `<input
            type="text"
            class="form-control form-control-sm po-input"
            data-id="${item.idItem}"
            value="${item.Number_PO||''}"
          >`,
          item.Lot_Number,
          item.Receive,
          item.Entry_Date ? item.Entry_Date : '<span class="text-muted">no cargado</span>',
          item.Out_Date   ? item.Out_Date   : '<span class="text-muted">no cargado</span>',
          item.Code_Product_EC,
          item.Description,
          item.Qty,
          item.Unit_Value,
          item.Value,
          item.Unit,
          item.Length_in,
          item.Broad_in,
          item.Height_in,
          item.Weight_lb,
          `<select
            class="form-select form-select-sm status-select"
            data-id="${item.id}"
            data-container="${item.Number_Container}"
            data-invoice="${item.Number_Commercial_Invoice}"
          >
            <option value="Cargado"${item.Status==='Cargado'?' selected':''}>Cargado</option>
            <option value="En Almacén"${item.Status==='En Almacén'?' selected':''}>En Almacén</option>
          </select>`
        ]);
      });
      table.draw();

      // 4) Cerrar modal
      bootstrap.Modal.getInstance($('#filterModal')[0])?.hide();

      // 5) Volver a enganchar los handlers
     $('#pc-dt-simple tbody')
  .off('change', '.status-select').on('change', '.status-select', handleStatusChange)
  .off('change', '.po-input')    .on('change', '.po-input',    handlePoChange)
  .off('change', 'input[data-field]').on('change', 'input[data-field]', function () {
    const id = this.dataset.id;
    const field = this.dataset.field;
    const value = this.value.trim();
    handleFieldChange(field, value, id);
  });


    } catch (err) {
      console.error('Error al aplicar filtros:', err);
      Swal.fire('Error', 'No se pudieron cargar los datos', 'error');
    }
  }


  // Limpia filtros y llama a la función anterior
  async function limpiarFiltrosAvanzados() {
    $('#OpFilter, #LotFilter').val('');
    const fp = document.getElementById('rangoFechas')._flatpickr;
    if (fp) fp.clear();
    await aplicarFiltrosAvanzados();
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


<?php

?>