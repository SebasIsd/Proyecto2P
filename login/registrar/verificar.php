<?php
require_once "../../includes/conexion.php"; // Ajusta la ruta

$token = $_GET['token'] ?? '';

if (empty($token)) {
    die("Token no proporcionado.");
}

$conn->begin_transaction();

try {
    // 1. Buscar el token, verificar que no haya expirado Y que el usuario esté inactivo (ACTIVO = 0)
    $stmt = $conn->prepare("SELECT CED_USU FROM USUARIOS WHERE VERIFICACION_TOKEN = ? AND VERIFICACION_EXPIRA > NOW() AND ACTIVO = 0");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $fila = $result->fetch_assoc();
        $cedula = $fila['CED_USU'];
        $stmt->close();

        // 2. Activar el usuario (ACTIVO = 1) y limpiar el token
        $updateStmt = $conn->prepare("UPDATE USUARIOS SET ACTIVO = 1, VERIFICACION_TOKEN = NULL, VERIFICACION_EXPIRA = NULL WHERE CED_USU = ?");
        $updateStmt->bind_param("s", $cedula);
        $updateStmt->execute();
        
        if ($updateStmt->affected_rows === 1) {
            $conn->commit();
            // Mensaje de éxito
            echo "<!DOCTYPE html><html lang='es'><head><meta charset='UTF-8'><title>Cuenta Verificada</title>";
            echo "<style>body { font-family: Arial, sans-serif; display: grid; place-items: center; min-height: 90vh; background-color: #f4f4f4; } .container { padding: 2rem; background: white; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); text-align: center; } a { color: #6d1313; text-decoration: none; font-weight: bold; }</style>";
            echo "</head><body><div class='container'>";
            echo "<h1>¡Cuenta Verificada!</h1>";
            echo "<p>Tu cuenta ha sido activada exitosamente. Ahora puedes iniciar sesión.</p>";
            echo "<a href='../../index.php'>Volver al inicio</a>";
            echo "</div></body></html>";
        } else {
            throw new Exception("No se pudo actualizar el usuario.");
        }
        
        $updateStmt->close();
        
    } else {
        // 3. Token inválido, expirado o usuario ya activo
        $stmt->close();
        throw new Exception("Enlace de verificación no válido, expirado o ya utilizado.");
    }

} catch (Exception $e) {
    $conn->rollback();
    // Mensaje de error
    echo "<!DOCTYPE html><html lang='es'><head><meta charset='UTF-8'><title>Error</title>";
    echo "<style>body { font-family: Arial, sans-serif; display: grid; place-items: center; min-height: 90vh; background-color: #f4f4f4; } .container { padding: 2rem; background: white; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); text-align: center; color: #D8000C; background-color: #FFD2D2; } a { color: #6d1313; text-decoration: none; font-weight: bold; }</style>";
    echo "</head><body><div class='container'>";
    echo "<h1>Error de Verificación</h1>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "<a href='../../index.php'>Volver al inicio</a>";
    echo "</div></body></html>";
}

$conn->close();
?>