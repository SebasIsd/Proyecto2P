<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro</title>
    <link rel="stylesheet" href="css/styles.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container">
        <h2>Registrarse</h2>
        <div class="form-group">
            <label for="nombre">Nombre</label>
            <input type="text" id="nombre" required>
        </div>
        <div class="form-group">
            <label for="apellido">Apellido</label>
            <input type="text" id="apellido" required>
        </div>
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" required>
        </div>
        <div class="form-group">
            <label for="password">Contraseña</label>
            <input type="password" id="password" required>
        </div>
        <div class="form-group">
            <label for="telefono">Teléfono</label>
            <input type="text" id="telefono">
        </div>
        <div class="form-group">
            <label for="direccion">Dirección</label>
            <input type="text" id="direccion">
        </div>
        <div class="form-group">
            <label for="carrera_id">Carrera (Opcional)</label>
            <select id="carrera_id">
                <option value="">Ninguna</option>
                <!-- Llena con PHP o JS desde BD -->
            </select>
        </div>
        <p class="error" id="error"></p>
        <button class="btn" onclick="registrar()">Registrarse</button>
        <a href="login.php">¿Ya tienes cuenta? Inicia sesión</a>
    </div>
    <script>
        function registrar() {
            const formData = new FormData();
            formData.append('nombre', document.getElementById('nombre').value);
            formData.append('apellido', document.getElementById('apellido').value);
            formData.append('email', document.getElementById('email').value);
            formData.append('password', document.getElementById('password').value);
            formData.append('telefono', document.getElementById('telefono').value);
            formData.append('direccion', document.getElementById('direccion').value);
            formData.append('carrera_id', document.getElementById('carrera_id').value);

            fetch('php/registro.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.href = 'login.php';
                } else {
                    document.getElementById('error').textContent = data.error;
                }
            })
            .catch(error => document.getElementById('error').textContent = 'Error: ' + error);
        }
    </script>
</body>
</html>