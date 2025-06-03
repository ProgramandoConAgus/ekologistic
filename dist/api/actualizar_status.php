<?php
require_once '../con_db.php';
require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

header('Content-Type: application/json');

try {
    $input = json_decode(file_get_contents('php://input'), true);
    if (empty($input['id']) || empty($input['value'])) {
        throw new Exception('Datos inválidos');
    }

    $id    = filter_var($input['id'], FILTER_SANITIZE_NUMBER_INT);
    $value = filter_var($input['value'], FILTER_SANITIZE_STRING);

    // Validar estados permitidos
    $allowed = ['Transit', 'Transit Delayed', 'Completed'];
    if (!in_array($value, $allowed)) {
        throw new Exception('Estado no permitido');
    }

    // 1) SELECT para datos del contenedor
    $stmtInfo = $conexion->prepare("
        SELECT 
            num_op,
            status,
            Destinity_POD    AS destino,
            Shipping_Line    AS shipping_line,
            Booking_BK       AS booking,
            Number_Container AS numero_contenedor
        FROM container
        WHERE IdContainer = ?
    ");
    $stmtInfo->bind_param("i", $id);
    $stmtInfo->execute();
    $resInfo = $stmtInfo->get_result();
    if ($resInfo->num_rows === 0) {
        throw new Exception('Container no encontrado', 404);
    }
    $info = $resInfo->fetch_assoc();
    $stmtInfo->close();

    // 2) UPDATE de status
    $stmtUpd = $conexion->prepare("UPDATE container SET status = ? WHERE IdContainer = ?");
    $stmtUpd->bind_param("si", $value, $id);
    if (!$stmtUpd->execute()) {
        throw new Exception('Error al actualizar status: ' . $conexion->error);
    }
    $stmtUpd->close();

    // 3) Si es Completed, envío de correo inmediato
    if ($value === 'Completed') {
        // destinatarios
        $destinatarios = [
            'export@ekopackingsas.com',
            'Kreinoso@ekopackingsas.com',
            'jjloaiza@ekopackingsas.com'
        ];
        // asunto y cuerpo
        $asunto = 'Alerta: Cambio de estado a Completed en el dashboard Logistic';
        $cuerpo = "
            <p>Estimado equipo,</p>
            <p>Se ha realizado un cambio de estado en el dashboard Logistic. A continuación se detallan los cambios relevantes.</p>
            <p><strong>Detalles:</strong><br>
             - Número de operación: {$info['num_op']}<br>
             - Destino: {$info['destino']}<br>
             - Shipping Line: {$info['shipping_line']}<br>
             - Booking: {$info['booking']}<br>
             - Número de contenedor: {$info['numero_contenedor']}<br>
             - Estado: {$value}</p>
            <p>Por favor, revise la situación y proceda según sea necesario.</p>
            <p>Eko Logistic Software</p>
        ";

        // Configuración PHPMailer
        foreach ($destinatarios as $dest) {
            $mail = new PHPMailer(true);
            try {
                // SMTP
                $mail->isSMTP();
                $mail->Host       = 'smtp.hostinger.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'info@ekologistic.appexsoftware.com';
                $mail->Password   = 'EkoLogistic@23';
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = 587;

                // Remitente y destinatario
                $mail->setFrom('info@ekologistic.appexsoftware.com', 'Eko Logistic');
                $mail->addAddress($dest);

                // Contenido
                $mail->isHTML(true);
                $mail->Subject = $asunto;
                $mail->Body    = $cuerpo;

                // Enviar
                $mail->send();
            } catch (Exception $e) {
                // Podrías loggear el error si quieres
                error_log("Error enviando email a {$dest}: " . $mail->ErrorInfo);
            }
        }

        $message = 'Estado actualizado y correos enviados.';
    } else {
        $message = 'Estado actualizado.';
    }

    echo json_encode([
        'success' => true,
        'message' => $message
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error'   => $e->getMessage()
    ]);
}
