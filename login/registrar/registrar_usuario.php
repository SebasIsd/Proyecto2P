<?php
// 1. Incluir PHPMailer al inicio
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// 2. Ajusta la ruta a tu 'vendor/autoload.php' (dos niveles arriba)
require_once "../../vendor/autoload.php"; 
require_once "../../includes/conexion.php"; // conexión MySQLi

header('Content-Type: application/json; charset=utf-8');
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Verificar que la petición sea POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(["status" => "error", "msg" => "Método no permitido"]);
    exit;
}

// Buscar el ID del rol "Asistente"
$rol = null;
$nombreRol = 'Asistente';
$stmt = $conn->prepare("SELECT ID_ROL FROM roles WHERE NOM_ROL = ?");
$stmt->bind_param("s", $nombreRol);
$stmt->execute();
$result = $stmt->get_result();
if ($fila = $result->fetch_assoc()) {
    $rol = $fila['ID_ROL'];
} else {
    echo json_encode(["status" => "error", "msg" => "No se encontró el rol 'Asistente'."]);
    exit;
}
$stmt->close();

// Capturar los valores enviados
$cedula    = trim($_POST["cedula"] ?? "");
$nomPri    = trim($_POST["primer_nombre"] ?? "");
$nomSeg    = trim($_POST["segundo_nombre"] ?? "");
$apePri    = trim($_POST["primer_apellido"] ?? "");
$apeSeg    = trim($_POST["segundo_apellido"] ?? "");
$correo    = trim($_POST["correo"] ?? "");
$password  = trim($_POST["password"] ?? "");
$telefono  = trim($_POST["telefono"] ?? "");
$direccion = trim($_POST["direccion"] ?? "");
$fechaNac  = trim($_POST["fecha_nac"] ?? "");
$carrera   = intval($_POST["carrera"] ?? 0);

// --- Validaciones (tu código original) ---
if (
    empty($cedula) || empty($nomPri) || empty($apePri) || empty($correo) ||
    empty($password) || empty($telefono) || empty($direccion) || empty($rol)
) {
    echo json_encode(["status" => "error", "msg" => "Todos los campos obligatorios deben ser llenados."]);
    exit;
}
if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(["status" => "error", "msg" => "El correo no tiene un formato válido."]);
    exit;
}
if (!preg_match('/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[!@$#_\-]).{8,}$/', $password)) {
    echo json_encode(["status" => "error", "msg" => "La contraseña debe tener mínimo 8 caracteres, incluir mayúsculas, minúsculas, números y un símbolo (!@$#_-)."]);
    exit;
}
if (!preg_match("/^09\d{8}$/", $telefono)) {
    echo json_encode(["status" => "error", "msg" => "El teléfono debe comenzar con 09 y tener 10 dígitos."]);
    exit;
}
if (!preg_match("/^\d{10}$/", $cedula)) {
    echo json_encode(["status" => "error", "msg" => "La cédula debe tener exactamente 10 dígitos."]);
    exit;
}
$fecha_naci = new DateTime($fechaNac);
$hoy = new DateTime();
$edad = $hoy->diff($fecha_naci)->y;
if ($edad < 17) {
    echo json_encode(["status" => "error", "msg" => "Debes tener al menos 17 años para registrarte."]);
    exit;
}

// Verificar duplicados (tu código original)
$stmt = $conn->prepare("SELECT CED_USU FROM USUARIOS WHERE CED_USU = ?");
$stmt->bind_param("s", $cedula);
$stmt->execute();
if ($stmt->get_result()->num_rows > 0) {
    echo json_encode(["status" => "error", "msg" => "La cédula ya está registrada."]);
    $stmt->close();
    exit;
}
$stmt->close();

$stmt = $conn->prepare("SELECT COR_USU FROM USUARIOS WHERE COR_USU = ?");
$stmt->bind_param("s", $correo);
$stmt->execute();
if ($stmt->get_result()->num_rows > 0) {
    echo json_encode(["status" => "error", "msg" => "El correo ya está registrado."]);
    $stmt->close();
    exit;
}
$stmt->close();
// --- Fin Validaciones ---


// Si todas las validaciones pasan, procedemos a insertar y enviar correo
try {
    // 1. Generar Token de Verificación
    $token = bin2hex(random_bytes(32));
    $expira = (new DateTime())->modify('+1 day')->format('Y-m-d H:i:s'); // Válido por 1 día

    // 2. Encriptar la contraseña
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    
    // 3. Modificar el INSERT para incluir el token y ACTIVO = 0
    $sql = "INSERT INTO USUARIOS 
            (CED_USU, NOM_PRI_USU, NOM_SEG_USU, APE_PRI_USU, APE_SEG_USU, COR_USU, PAS_USU, TEL_USU, DIR_USU, FEC_NAC_USU, ID_ROL_USU, ID_CARRERA_USU, ACTIVO, VERIFICACION_TOKEN, VERIFICACION_EXPIRA)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0, ?, ?)"; // ACTIVO=0

    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
        "ssssssssssiisss", // 15 parámetros
        $cedula, $nomPri, $nomSeg, $apePri, $apeSeg,
        $correo, $passwordHash, $telefono, $direccion, $fechaNac,
        $rol, $carrera,
        $token, $expira // Nuevos valores
    );

    if ($stmt->execute()) {
        // 4. Enviar el Correo de Verificación
        $mail = new PHPMailer(true);
        
        // Configuración del servidor SMTP (CAMBIA ESTO)
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com'; // Ej: smtp.gmail.com
        $mail->SMTPAuth   = true;
        $mail->Username   = 'sebsnt2003@gmail.com';    // TU CORREO
        $mail->Password   = 'buybxekrppoucgvh'; // TU CONTRASEÑA DE APP
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        $mail->CharSet    = 'UTF-8';

        // Remitente y Destinatario
        $mail->setFrom('sebsnt2003@gmail.com', 'Eventos UTA');
        $mail->addAddress($correo, $nomPri . ' ' . $apePri); 

        // Contenido del correo
        $mail->isHTML(true);
        $mail->Subject = 'Verifica tu cuenta - Eventos UTA';
        
        // CAMBIA "https://tusitio.com" por tu dominio real
        $enlaceVerificacion = "http://localhost/Proyecto2P/login/registrar/verificar.php?token=" . $token;

        $mail->Body    = "
            <h1>¡Bienvenido a Eventos UTA, $nomPri!</h1>
            <p>Gracias por registrarte. Por favor, haz clic en el siguiente enlace para activar tu cuenta:</p>
            <p><a href='$enlaceVerificacion' style='padding: 10px 15px; background-color: #6d1313; color: white; text-decoration: none; border-radius: 5px;'>Activar mi cuenta</a></p>
            <p>Si el botón no funciona, copia y pega el siguiente enlace en tu navegador:</p>
            <p>$enlaceVerificacion</p>
            <p>Este enlace expirará en 24 horas.</p>
        ";
        $mail->AltBody = "Para activar tu cuenta, copia y pega este enlace en tu navegador: $enlaceVerificacion";

        $mail->send();
        
        echo json_encode(["status" => "success", "msg" => "¡Registro exitoso! Revisa tu correo electrónico para activar tu cuenta."]);

    } else {
        echo json_encode(["status" => "error", "msg" => "Error al registrar el usuario: " . $stmt->error]);
    }
    
    $stmt->close();

} catch (Exception $e) {
    // Captura errores del insert o del envío de correo
    // Si falla el correo, deberíamos borrar el usuario (o reintentar), pero por ahora solo informamos.
    echo json_encode(["status" => "error", "msg" => "Usuario registrado, pero no se pudo enviar el correo de verificación. Contacta a soporte. Error: {$mail->ErrorInfo}"]);
}

$conn->close();
?>