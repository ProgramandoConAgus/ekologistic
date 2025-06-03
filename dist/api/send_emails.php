<?php
// scripts/send_emails.php

// 1) Carga de dependencias y conexión
require __DIR__ . '/../con_db.php';        // define $conexion (mysqli)
require __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Array para recolectar resultados
$results = [];

// 2) Consultar los emails pendientes de envío
$sql  = "SELECT * FROM tareas_email WHERE fecha_ejecucion <= NOW() AND enviado = 0";
$res  = $conexion->query($sql);

if (!$res) {
    $msg = "Error en consulta tareas_email: " . $conexion->error;
    error_log($msg);
    echo $msg . PHP_EOL;
    exit(1);
}

while ($t = $res->fetch_assoc()) {
    $mail = new PHPMailer(true);
    $info = [
        'id'           => $t['id'],
        'destinatario' => $t['destinatario'],
        'estado'       => null,
        'error'        => null,
    ];
    try {
        // 3) Configuración SMTP (Hostinger)
        $mail->isSMTP();
        $mail->Host       = 'smtp.hostinger.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'info@ekologistic.appexsoftware.com';
        $mail->Password   = 'EkoLogistic@23';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        // 4) Remitente y destinatario
        $mail->setFrom('info@ekologistic.appexsoftware.com', 'Eko Logistic');
        $mail->addAddress($t['destinatario']);

        // 5) Contenido del mensaje
        $mail->isHTML(true);
        $mail->Subject = $t['asunto'];
        $mail->Body    = $t['cuerpo'];

        // 6) Enviar
        $mail->send();

        // 7) Marcar como enviado
        $upd = $conexion->prepare("
            UPDATE tareas_email 
               SET enviado = 1, fecha_envio = NOW() 
             WHERE id = ?
        ");
        $upd->bind_param("i", $t['id']);
        $upd->execute();
        $upd->close();

        $info['estado'] = 'OK';
    } catch (Exception $e) {
        // Si falla el envío, lo registramos
        $info['estado'] = 'ERROR';
        $info['error']  = $mail->ErrorInfo;
        error_log("Error enviando email tarea {$t['id']}: {$mail->ErrorInfo}");
    }

    // Guardamos resultado
    $results[] = $info;
}

$conexion->close();

// 8) Imprimir resumen de resultados
echo "Resumen de envíos - " . date('Y-m-d H:i:s') . PHP_EOL;
foreach ($results as $r) {
    if ($r['estado'] === 'OK') {
        echo "[OK]     ID {$r['id']} → {$r['destinatario']}" . PHP_EOL;
    } else {
        echo "[ERROR]  ID {$r['id']} → {$r['destinatario']}: {$r['error']}" . PHP_EOL;
    }
}

// Código de salida
// Si al menos uno falló, devolveremos 1; si todos OK, 0.
$exitCode = array_filter($results, fn($r)=> $r['estado'] !== 'OK') ? 1 : 0;
exit($exitCode);

