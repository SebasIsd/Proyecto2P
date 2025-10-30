<?php
session_start();
include("logincon.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $correo = trim($_POST["usuario"]);
    $clave = trim($_POST["clave"]);

    // Evitar inyección SQL
    $correo = $conn->real_escape_string($correo);
    $clave = $conn->real_escape_string($clave);

    // Buscar el usuario junto con su rol
    $sql = "SELECT u.CED_USU, u.COR_USU, u.PAS_USU, u.ID_ROL_USU, r.NOM_ROL
            FROM usuarios u
            INNER JOIN roles r ON u.ID_ROL_USU = r.ID_ROL
            WHERE u.COR_USU = '$correo'";

    $resultado = $conn->query($sql);

    if ($resultado && $resultado->num_rows > 0) {
        $fila = $resultado->fetch_assoc();

        // Verificamos la contraseña (si no está encriptada)
        if ($clave === $fila['PAS_USU']) {
            // Guardar datos en la sesión
            $_SESSION['cedula'] = $fila['CED_USU'];
            $_SESSION['correo'] = $fila['COR_USU'];
            $_SESSION['rol_id'] = $fila['ID_ROL_USU'];
            $_SESSION['rol_nombre'] = $fila['NOM_ROL'];
            // Redirección según el nombre del rol
            if (strtolower($fila['NOM_ROL']) === 'administrador') {
                header("Location: ../Destinoslogin/admin.php");
            } elseif (strtolower($fila['NOM_ROL']) === 'estudiante' || strtolower($fila['NOM_ROL']) === 'docente') {
                header("Location: ../usuarios/usuarios.php");
            } else {
                header("Location: login.php?error=rol_no_valido");
            }
            exit();
        } else {
            $error = "⚠️ Contraseña incorrecta.";
        }
    } else {
        $error = "⚠️ El usuario no existe o el correo es incorrecto.";
    }
}
?>
