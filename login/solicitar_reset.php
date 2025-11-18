<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// 1. Ajusta la ruta a tu 'vendor/autoload.php' (un nivel arriba)
require_once "../vendor/autoload.php"; 
require_once "../includes/conexion.php";

header('Content-Type: application/json');
$email = $_POST['email'] ?? '';

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['status' => 'error', 'msg' => 'Por favor, ingresa un correo válido.']);
    exit;
}

// 1. Verificar si el correo existe Y ESTÁ ACTIVO
$stmt = $conn->prepare("SELECT CED_USU, NOM_PRI_USU FROM USUARIOS WHERE COR_USU = ? AND ACTIVO = 1");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $fila = $result->fetch_assoc();
    $nombre = $fila['NOM_PRI_USU'];
    
    // 2. Generar código y guardarlo (hasheado)
    try {
        $codigo = random_int(100000, 999999); // Código de 6 dígitos
        $codigo_hash = password_hash($codigo, PASSWORD_DEFAULT);
        $expira = (new DateTime())->modify('+10 minutes')->format('Y-m-d H:i:s'); // Válido por 10 min

        $updateStmt = $conn->prepare("UPDATE USUARIOS SET RESET_TOKEN = ?, RESET_EXPIRA = ? WHERE COR_USU = ?");
        $updateStmt->bind_param("sss", $codigo_hash, $expira, $email);
        $updateStmt->execute();
        $updateStmt->close();

        // 3. Enviar el correo
        $mail = new PHPMailer(true);
        // (Configura tu SMTP - CAMBIA ESTO)
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com'; // Ej: smtp.gmail.com
        $mail->SMTPAuth   = true;
        $mail->Username   = 'sebsnt2003@gmail.com';    // TU CORREO
        $mail->Password   = 'buybxekrppoucgvh'; // TU CONTRASEÑA DE APP
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        $mail->CharSet    = 'UTF-8';

        $mail->setFrom('sebsnt2003@gmail.com', 'Eventos UTA');
        $mail->addAddress($email, $nombre); 

        $mail->isHTML(true);
        $mail->Subject = 'Tu código de recuperación de contraseña - Eventos UTA';
        $mail->Body    = "
            <h1>Hola $nombre,</h1>
            <p>Hemos recibido una solicitud para restablecer tu contraseña.</p>
            <p>Tu código de verificación es:</p>
            <h2 style='font-size: 36px; text-align: center; letter-spacing: 5px; background: #f4f4f4; padding: 10px; border-radius: 5px;'>
                $codigo
            </h2>
            <p>Este código es válido por 10 minutos.</p>
            <p>Si no solicitaste esto, puedes ignorar este correo.</p>
        ";
        
        $mail->send();

        echo json_encode(['status' => 'success', 'msg' => 'Te hemos enviado un código. Revisa tu correo (y la carpeta de spam).']);

    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'msg' => "Error al enviar el correo: {$mail->ErrorInfo}"]);
    }

} else {
    // No revelamos si el correo existe o no por seguridad, pero sí si está inactivo.
    // Verificamos si existe pero está inactivo:
    $stmt_inactive = $conn->prepare("SELECT CED_USU FROM USUARIOS WHERE COR_USU = ? AND ACTIVO = 0");
    $stmt_inactive->bind_param("s", $email);
    $stmt_inactive->execute();
    if ($stmt_inactive->get_result()->num_rows === 1) {
         echo json_encode(['status' => 'error', 'msg' => 'Esta cuenta existe pero no ha sido verificada. Por favor, revisa tu correo de bienvenida.']);
    } else {
         echo json_encode(['status' => 'error', 'msg' => 'El correo electrónico no se encuentra registrado.']);
    }
    $stmt_inactive->close();
}

$stmt->close();
$conn->close();
?>