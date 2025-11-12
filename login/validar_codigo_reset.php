<?php
require_once "../includes/conexion.php";
header('Content-Type: application/json');

$email = $_POST['emailValidate'] ?? '';
$codigo_usuario = $_POST['resetCode'] ?? '';

if (empty($email) || empty($codigo_usuario)) {
    echo json_encode(['status' => 'error', 'msg' => 'Faltan datos.']);
    exit;
}

// 1. Buscar el token hasheado en la BD
$stmt = $conn->prepare("SELECT RESET_TOKEN, RESET_EXPIRA FROM USUARIOS WHERE COR_USU = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $fila = $result->fetch_assoc();
    $token_hash_bd = $fila['RESET_TOKEN'];
    $expira_bd_str = $fila['RESET_EXPIRA'];

    // Verificar que el token exista en la BD
    if (empty($token_hash_bd) || empty($expira_bd_str)) {
         echo json_encode(['status' => 'error', 'msg' => 'No hay una solicitud de reseteo activa.']);
         exit;
    }

    $expira_bd = new DateTime($expira_bd_str);
    $ahora = new DateTime();

    // 2. Verificar que el código coincida (usando password_verify)
    if (!password_verify($codigo_usuario, $token_hash_bd)) {
         echo json_encode(['status' => 'error', 'msg' => 'El código es incorrecto.']);
         exit;
    }

    // 3. Verificar que no esté expirado
    if ($ahora > $expira_bd) {
        echo json_encode(['status' => 'error', 'msg' => 'El código ha expirado. Solicita uno nuevo.']);
        exit;
    }

    // Código correcto y válido
    echo json_encode(['status' => 'success', 'msg' => 'Código verificado.']);

} else {
    echo json_encode(['status' => 'error', 'msg' => 'Error al verificar la cuenta.']);
}

$stmt->close();
$conn->close();
?>