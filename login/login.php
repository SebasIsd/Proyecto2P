<?php if (isset($_GET['error'])): ?>
    <div class="error">
        <?php 
        if ($_GET['error'] == 'usuario_no_encontrado') echo "⚠️ Usuario no encontrado.";
        elseif ($_GET['error'] == 'contraseña_incorrecta') echo "⚠️ Contraseña incorrecta.";
        elseif ($_GET['error'] == 'rol_no_valido') echo "⚠️ Rol no válido.";
        ?>
    </div>
<?php endif; ?>


<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Universidad Técnica de Ambato</title>
    <link rel="stylesheet" href="../css/estiloslogin.css">
</head>
<body>
    <div class="contenedor">
        <div class="login-box">
            <img src="../images/logouta.jpg" class="logo-uta" alt="Logo UTA">
            <h2>login</h2>

            <?php if (!empty($error)): ?>
                <div class="error"><?= $error ?></div>
            <?php endif; ?>

            <form method="POST" action="validar.php">
                <div class="input-group">
                    <label for="usuario">Usuario</label>
                    <input type="email" name="usuario" id="usuario" placeholder="Ingrese su correo" required >
                </div>
                <div class="input-group">
                    <label for="clave">Contraseña</label>
                    <input type="password" name="clave" id="clave" placeholder="Ingrese su contraseña" required>
                </div>
                <button type="submit" class="btn-login">Ingresar</button>
            </form>
            <br>
                        <div class="registro">
                ¿No tienes cuenta?
                <a href="registro.php">Regístrate aquí</a>
            </div>
            <p class="nota">© Universidad Técnica de Ambato - 2025</p>
        </div>
    </div>


</body>
</html>