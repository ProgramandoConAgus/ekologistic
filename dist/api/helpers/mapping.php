<?php
// Helper to fetch mapped invoices for a liquidation row.
// Usage: include_once __DIR__ . '/helpers/mapping.php' or include_once '../api/helpers/mapping.php';
// Then: $invoices = fetch_mapped_invoices($conexion, 'exports', $id);

function detect_mapping_column($conexion, $table, $candidates) {
    foreach ($candidates as $c) {
        $res = $conexion->query("SHOW COLUMNS FROM `" . $conexion->real_escape_string($table) . "` LIKE '" . $conexion->real_escape_string($c) . "'");
        if ($res && $res->num_rows > 0) return $c;
    }
    return null;
}

function fetch_mapped_invoices($conexion, $origin, $id) {
    // origin: 'exports', 'imports', 'despacho'
    $mappingTable = null;
    $candidates = [];
    switch ($origin) {
        case 'exports':
        case 'export':
            $mappingTable = 'export_invoices';
            $candidates = ['idExport', 'ExportID', 'ExportsID', 'ExportsId'];
            break;
        case 'imports':
        case 'import':
            $mappingTable = 'import_invoices';
            $candidates = ['ImportsID', 'ImportID', 'idImport', 'ImportsId'];
            break;
        case 'despacho':
        case 'despachos':
        case 'dispatch':
            $mappingTable = 'despacho_invoices';
            $candidates = ['DespachoID', 'idDespacho', 'IdDespacho', 'DespachosID'];
            break;
        default:
            return [];
    }

    // Detect mapping column
    $mappingCol = detect_mapping_column($conexion, $mappingTable, $candidates);
    if (!$mappingCol) {
        // table may not exist or column not found
        return [];
    }

    $sql = "SELECT Invoice FROM `" . $conexion->real_escape_string($mappingTable) . "` WHERE `" . $conexion->real_escape_string($mappingCol) . "` = ? ORDER BY id ASC";
    $stmt = $conexion->prepare($sql);
    if (!$stmt) return [];
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $res = $stmt->get_result();
    $list = [];
    while ($r = $res->fetch_assoc()) {
        $list[] = $r['Invoice'];
    }
    $stmt->close();
    return $list;
}
