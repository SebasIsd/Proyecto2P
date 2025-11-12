<?php
// verificar.php
require_once "../../includes/conexion.php";

// 1. Obtener el token de la URL
if (isset($_GET['token'])) {
    $token = $_GET['token'];

    // 2. Buscar al usuario por el token y verificar la expiración
    // Usamos $token para la busqueda
    $stmt = $conn->prepare("SELECT COR_USU, ACTIVO, VERIFICACION_EXPIRA FROM USUARIOS WHERE VERIFICACION_TOKEN = ? AND ACTIVO = 0");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows > 0) {
        $usuario = $resultado->fetch_assoc();
        $stmt->close();
        
        // ========================================================
        // === AÑADIR DEPURACIÓN TEMPORAL (MUY IMPORTANTE) ===
        // ========================================================
        echo "DEBUG: Token encontrado. <br>";
        echo "DEBUG: Correo: " . $usuario['COR_USU'] . "<br>";
        echo "DEBUG: VERIFICACION_EXPIRA de BD: [" . $usuario['VERIFICACION_EXPIRA'] . "]<br>";
        // ========================================================

        // 3. Verificar si el token ha expirado
        $expira = strtotime($usuario['VERIFICACION_EXPIRA']); // ESTA ES LA LÍNEA 21
        $ahora = time();
        
        // Compara la fecha de expiración con la hora actual
        if ($expira > $ahora) {
            
            // 4. Si es válido, activar la cuenta y borrar el token
            $stmt = $conn->prepare("UPDATE USUARIOS SET ACTIVO = 1, VERIFICACION_TOKEN = NULL, VERIFICACION_EXPIRA = NULL WHERE COR_USU = ?");
            $stmt->bind_param("s", $usuario['COR_USU']);
            $stmt->execute();
            
            if ($stmt->affected_rows > 0) {
                // ÉXITO
                header("Location: ../../index.php?success=verificacion_ok");
                exit();
            } else {
                // ERROR (Aunque debería funcionar si el token es válido)
                echo "Error al activar la cuenta.";
            }
            $stmt->close();
        } else {
            // ERROR: El token expiró
            echo "Error de Verificación: Enlace expirado. <a href='solicitar_nuevo_token.php'>Solicitar nuevo código</a>.";
        }
    } else {
        // ERROR: Token no encontrado (no válido o ya usado)
        echo "Error de Verificación: Enlace de verificación no válido o ya utilizado.";
    }
} else {
    echo "Acceso no autorizado.";
}
$conn->close();
?>