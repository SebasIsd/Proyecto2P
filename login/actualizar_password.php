<?php
require_once "../includes/conexion.php";
header('Content-Type: application/json');

$email = $_POST['emailNewPass'] ?? '';
$codigo_usuario = $_POST['codeNewPass'] ?? '';
$nueva_clave = $_POST['newPassword'] ?? '';

// 1. Validar campos
if (empty($email) || empty($codigo_usuario) || empty($nueva_clave)) {
    echo json_encode(['status' => 'error', 'msg' => 'Faltan datos.']);
    exit;
}

// 2. Validar fortaleza de la nueva contraseña
if (!preg_match('/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[!@$#_\-]).{8,}$/', $nueva_clave)) {
    echo json_encode(["status" => "error", "msg" => "La contraseña debe tener mín 8 caracteres, Mayús, minús, núm, y símbolo (!@$#_-)."]);
    exit;
}

// 3. Volver a verificar el código (como medida de seguridad final)
$stmt = $conn->prepare("SELECT RESET_TOKEN, RESET_EXPIRA FROM USUARIOS WHERE COR_USU = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $fila = $result->fetch_assoc();
    $token_hash_bd = $fila['RESET_TOKEN'];
    $expira_bd_str = $fila['RESET_EXPIRA'];
    
    if (empty($token_hash_bd)) {
        echo json_encode(['status' => 'error', 'msg' => 'La sesión de reseteo no es válida o ya fue usada.']);
        exit;
    }

    $expira_bd = new DateTime($expira_bd_str);
    $ahora = new DateTime();

    // 4. Si el código es válido (coincide Y no ha expirado)
    if (password_verify($codigo_usuario, $token_hash_bd) && $ahora <= $expira_bd) {
        
        // 5. Actualizar la contraseña y limpiar los tokens de reseteo
        $nueva_clave_hash = password_hash($nueva_clave, PASSWORD_DEFAULT);
        
        $updateStmt = $conn->prepare("UPDATE USUARIOS SET PAS_USU = ?, RESET_TOKEN = NULL, RESET_EXPIRA = NULL WHERE COR_USU = ?");
        $updateStmt->bind_param("ss", $nueva_clave_hash, $email);
        
        if ($updateStmt->execute()) {
            echo json_encode(['status' => 'success', 'msg' => 'Contraseña actualizada exitosamente. Ya puedes iniciar sesión.']);
        } else {
            echo json_encode(['status' => 'error', 'msg' => 'Error al actualizar la contraseña.']);
        }
        $updateStmt->close();

    } else {
        echo json_encode(['status' => 'error', 'msg' => 'La sesión de reseteo ha expirado o el código es incorrecto. Vuelve a empezar.']);
    }
} else {
     echo json_encode(['status' => 'error', 'msg' => 'Error de validación de usuario.']);
}

$stmt->close();
$conn->close();
?>