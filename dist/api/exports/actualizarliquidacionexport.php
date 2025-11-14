<?php
include("../../con_db.php");
header('Content-Type: application/json');

try {
    $input = json_decode(file_get_contents('php://input'), true);
    $datos = $input['datos'] ?? [];
    $coeficiente = $input['coeficiente'] ?? null;
    $idExport = $input['idExport'] ?? null;
    if (empty($datos)) {
        echo json_encode(['success' => false, 'message' => 'Datos vacíos']);
        exit;
    }

    // Preparamos la consulta JOIN con la tabla incoterms para filtrar por IdIncoterms
    $sql = "
      UPDATE itemsliquidacionexportincoterms ii
      JOIN incoterms ic 
        ON ic.IdItemsLiquidacionExportIncoterms = ii.IdItemsLiquidacionExportIncoterms
      SET 
        ii.Cantidad       = ?,
        ii.ValorUnitario  = ?,
        ii.ValorTotal     = ?,
        ii.Impuesto       = ?,
        ii.ValorImpuesto  = ?,
        ii.Notas          = ?
      WHERE ic.IdIncoterms = ?
    ";
    $stmt = $conexion->prepare($sql);
    if (!$stmt) {
        throw new Exception("Error preparando UPDATE: " . $conexion->error);
    }

     // ✅ Primero actualizamos el coeficiente de la liquidación
     if ($idExport && $coeficiente !== null) {
        $sqlCoef = "UPDATE exports SET coeficiente = ? WHERE ExportsID = ?";
        $stmtCoef = $conexion->prepare($sqlCoef);
        $stmtCoef->bind_param("di", $coeficiente, $idExport);
        $stmtCoef->execute();
        $stmtCoef->close();
    }

    // Si el payload incluye 'invoices', actualizamos la tabla export_invoices
    if (isset($input['invoices'])) {
        $invoicesPayload = $input['invoices'];
        // normalizar a array
        if (!is_array($invoicesPayload)) {
            // si viene como string con comas
            $invoicesPayload = array_filter(array_map('trim', explode(',', (string)$invoicesPayload)));
        }

        // Iniciamos transacción para mantener consistencia
        $conexion->begin_transaction();
        try {
            // Eliminamos mappings previos
            $del = $conexion->prepare("DELETE FROM export_invoices WHERE idExport = ?");
            $del->bind_param("i", $idExport);
            $del->execute();
            $del->close();

            // Insertamos los nuevos
            $ins = $conexion->prepare("INSERT INTO export_invoices (Invoice, idExport) VALUES (?, ?)");
            foreach ($invoicesPayload as $inv) {
                $invStr = (string)$inv;
                $ins->bind_param("si", $invStr, $idExport);
                $ins->execute();
            }
            $ins->close();

            // Actualizamos campo Number_Commercial_Invoice en exports con la primera factura (por compatibilidad)
            if (!empty($invoicesPayload)) {
                $first = (string)$invoicesPayload[0];
                $colRes = $conexion->query("SHOW COLUMNS FROM exports LIKE 'Number_Commercial_Invoice'");
                if ($colRes && $colRes->num_rows > 0) {
                    $u = $conexion->prepare("UPDATE exports SET Number_Commercial_Invoice = ? WHERE ExportsID = ?");
                    if ($u) {
                        $u->bind_param("si", $first, $idExport);
                        $u->execute();
                        $u->close();
                    }
                }
            }

            $conexion->commit();
        } catch (Exception $ee) {
            $conexion->rollback();
            // no bloquear el resto de la actualización: reportamos pero seguimos
            // agregamos un error al array de errores para informar al cliente
            $errores[] = 'Error actualizando facturas: ' . $ee->getMessage();
        }
    }

    $errores = [];
    foreach ($datos as $i => $d) {
        // Leemos del payload
        $idIncoterms   = intval($d['idIncoterms']    ?? 0);
        $cantidad      = intval($d['cantidad']        ?? 0);
        $valorUnitario = floatval($d['valorUnitario'] ?? 0);
        $valorTotal    = floatval($d['valorTotal']    ?? ($cantidad * $valorUnitario));
        $impuestoPct   = floatval($d['impuestoPct']   ?? 0);
        $valorImpuesto = floatval($d['valorImpuesto'] ?? ($valorTotal * $impuestoPct / 100));
        $notas         = $d['notas'] ?? '';

        // vinculamos y ejecutamos
        $stmt->bind_param(
            "dddddsi",
            $cantidad,
            $valorUnitario,
            $valorTotal,
            $impuestoPct,
            $valorImpuesto,
            $notas,
            $idIncoterms
        );
        if (!$stmt->execute()) {
            $errores[] = "Fila {$i} (IdIncoterms {$idIncoterms}): " . $stmt->error;
        }
    }

    // Respuesta con los datos que efectivamente procesó
    if (empty($errores)) {
        echo json_encode([
            'success'         => true,
            'message'         => 'Actualización exitosa',
            'datosProcesados' => $datos
        ]);
    } else {
        echo json_encode([
            'success'         => false,
            'message'         => 'Algunos registros no se actualizaron.',
            'errors'          => $errores,
            'datosProcesados' => $datos
        ]);
    }

    $stmt->close();

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error inesperado.',
        'error'   => $e->getMessage()
    ]);
}
