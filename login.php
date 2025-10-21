<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <link rel="stylesheet" href="css/styles.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container">
        <h2>Iniciar Sesión</h2>
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" required>
        </div>
        <div class="form-group">
            <label for="password">Contraseña</label>
            <input type="password" id="password" required>
        </div>
        <p class="error" id="error"></p>
        <button class="btn" onclick="login()">Login</button>
        <a href="registro.php">¿No tienes cuenta? Regístrate</a>
    </div>
    <script>
        function login() {
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            fetch('php/login.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `email=${encodeURIComponent(email)}&password=${encodeURIComponent(password)}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.href = 'index.php';
                } else {
                    document.getElementById('error').textContent = data.error;
                }
            })
            .catch(error => document.getElementById('error').textContent = 'Error: ' + error);
        }
    </script>
</body>
</html>