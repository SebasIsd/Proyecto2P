<?php
session_start();

// Verificar que haya una sesión activa y que sea administrador
if (!isset($_SESSION['correo']) || strtolower($_SESSION['rol_nombre']) !== 'administrador') {
    header("Location: ../Login/login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Administrador - UTA</title>
    <link rel="stylesheet" href="../css/estiloslogin.css">
</head>
<body>
    <div class="contenedor">
        <div class="login-box">
            <img src="../images/logouta.jpg" class="logo-uta" alt="Logo UTA">
            <h2>Hola, Administrador 👋</h2>

            <p><strong>Cédula:</strong> <?= $_SESSION['cedula'] ?></p>
            <p><strong>Correo:</strong> <?= $_SESSION['correo'] ?></p>
            <p><strong>Rol:</strong> <?= ucfirst($_SESSION['rol_nombre']) ?></p>

            <br>
            <a href="../Login/logout.php" class="btn-login" style="text-decoration:none;display:inline-block;text-align:center;">Cerrar sesión</a>
        </div>
    </div>
</body>
</html>
