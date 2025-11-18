<?php
session_start();
include("../includes/conexion.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $correo = trim($_POST["usuario"]);
    $clave = trim($_POST["clave"]);

    // Evitar inyección SQL
    $correo = $conn->real_escape_string($correo);

    $sql = "SELECT u.CED_USU, u.COR_USU, u.PAS_USU, u.ID_ROL_USU, u.ACTIVO, r.NOM_ROL
            FROM usuarios u
            INNER JOIN roles r ON u.ID_ROL_USU = r.ID_ROL
            WHERE u.COR_USU = '$correo'";

    $resultado = $conn->query($sql);

    if ($resultado && $resultado->num_rows > 0) {
        $fila = $resultado->fetch_assoc();

        // ✅ Verificar si el usuario está activo
        if ($fila['ACTIVO'] != 1) {
            header("Location: ../index.php?error=usuario_no_verificado&modal=login");
            exit();
        }

        // ✅ Verificar contraseña con hash
        if (password_verify($clave, $fila['PAS_USU'])) {
            $_SESSION['cedula'] = $fila['CED_USU'];
            $_SESSION['correo'] = $fila['COR_USU'];
            $_SESSION['rol_id'] = $fila['ID_ROL_USU'];
            $_SESSION['rol_nombre'] = $fila['NOM_ROL'];

            $redirect_to = isset($_POST['redirect_to']) && !empty($_POST['redirect_to']) 
               ? $_POST['redirect_to'] 
               : null;

if ($redirect_to) {
    header("Location: ../usuarios/" . $redirect_to);
    exit();
}

            $rol = strtolower($fila['NOM_ROL']);
            if ($rol === 'administrador') {
                header("Location: ../admin/admin_inicio.php");
            } elseif ($rol === 'asistente') {
                header("Location: ../usuarios/usuarios_inicio.php");
            } else {
                header("Location: ../index.php?error=rol_no_valido&modal=login");
            }
            exit();
        } else {
            header("Location: ../index.php?error=contraseña_incorrecta&modal=login");
            exit();
        }
    } else {
        header("Location: ../index.php?error=usuario_no_encontrado&modal=login");
        exit();
    }
}
?>
