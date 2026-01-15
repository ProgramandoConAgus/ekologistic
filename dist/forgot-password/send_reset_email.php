<?php
header('Content-Type: application/json');

require_once '../con_db.php';  // Archivo de conexión que define $conexion (mysqli)
include '../vendor/autoload.php'; // Cargar dependencias de Composer

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$response = [];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Obtener el email enviado por POST
    $email = trim($_POST['recoveryEmail']);

    // Verificar si el email existe en la tabla usuarios
    $stmt = $conexion->prepare("SELECT * FROM usuarios WHERE email = ? LIMIT 1");
    if (!$stmt) {
        echo json_encode(['status' => 'error', 'message' => 'Error en la consulta: ' . $conexion->error]);
        exit;
    }
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();

    if ($user) {
        // Generar un token aleatorio y obtener fecha y hora actual
        $token = bin2hex(random_bytes(50));
        $date  = date("Y-m-d H:i:s");

        // Verificar si ya existe un registro en la tabla password_resets para ese email
        $stmt = $conexion->prepare("SELECT COUNT(*) as count FROM password_resets WHERE email = ?");
        if (!$stmt) {
            echo json_encode(['status' => 'error', 'message' => 'Error en la consulta: ' . $conexion->error]);
            exit;
        }
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $count = $row['count'];
        $stmt->close();

        if ($count > 0) {
            // Actualizar token y fecha de creación
            $stmt = $conexion->prepare("UPDATE password_resets SET token = ?, created_at = ? WHERE email = ?");
            if (!$stmt) {
                echo json_encode(['status' => 'error', 'message' => 'Error en la consulta: ' . $conexion->error]);
                exit;
            }
            $stmt->bind_param("sss", $token, $date, $email);
            $stmt->execute();
            $stmt->close();
        } else {
            // Insertar nuevo registro en password_resets
            $stmt = $conexion->prepare("INSERT INTO password_resets (email, token, created_at) VALUES (?, ?, ?)");
            if (!$stmt) {
                echo json_encode(['status' => 'error', 'message' => 'Error en la consulta: ' . $conexion->error]);
                exit;
            }
            $stmt->bind_param("sss", $email, $token, $date);
            $stmt->execute();
            $stmt->close();
        }

        // Configurar y enviar el correo con PHPMailer
        $mail = new PHPMailer(true);
        try {
            // Configuración del servidor SMTP
            $mail->isSMTP();
            $mail->Host       = 'smtp.hostinger.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'info@ekologistic.appexsoftware.com';
            $mail->Password   = 'EkoLogistic@23';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            // Configurar remitente y destinatario
            $mail->setFrom('info@ekologistic.appexsoftware.com', 'Eko Logistic');
            $mail->addAddress($email);

            // Contenido del email
            $mail->isHTML(true);
            $mail->Subject = 'Solicitud de reinicio de contraseña';
            $mail->Body    = "Haz clic <a href='https://ekologistic.appexsoftware.com/dist/forgot-password/reset-password.php?token=$token'>aquí</a> para reiniciar tu contraseña.";

            $mail->send();

            $response['status'] = 'success';
            $response['message'] = 'El enlace de reinicio fue enviado a tu email.';
        } catch (Exception $e) {
            $response['status'] = 'error';
            $response['message'] = "El mensaje no se pudo enviar: " . $mail->ErrorInfo;
        }
    } else {
        $response['status'] = 'error';
        $response['message'] = 'No se encontró ningún email registrado.';
    }
    
    echo json_encode($response);
    exit;
}
?>
