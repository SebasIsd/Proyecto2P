<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Eventos Académicos</title>
    <link rel="stylesheet" href="css/styles.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container">
        <h1 id="home-title">Cargando...</h1>
        <p id="home-content">Cargando...</p>
        <img id="home-image" style="display: none;">
        <?php if (isset($_SESSION['roles']) && in_array('Administrador', $_SESSION['roles'])): ?>
            <button class="btn" onclick="window.location.href='admin_home.php'">Editar Home</button>
        <?php endif; ?>
    </div>
    <script src="js/main.js"></script>
</body>
</html>