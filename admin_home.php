<?php
require 'php/db.php';
if (!isset($_SESSION['roles']) || !in_array('Administrador', $_SESSION['roles'])) {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Home</title>
    <link rel="stylesheet" href="css/styles.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container">
        <h2>Editar Contenido del Home</h2>
        <div class="form-group">
            <label for="titulo">Título</label>
            <input type="text" id="titulo">
        </div>
        <div class="form-group">
            <label for="contenido">Contenido</label>
            <textarea id="contenido" rows="5"></textarea>
        </div>
        <div class="form-group">
            <label for="imagen">Imagen</label>
            <input type="file" id="imagen" accept="image/*">
        </div>
        <p class="error" id="error"></p>
        <button class="btn" onclick="guardar()">Guardar</button>
    </div>
    <script>
        fetch('php/home_content.php')
            .then(response => response.json())
            .then(data => {
                document.getElementById('titulo').value = data.titulo;
                document.getElementById('contenido').value = data.contenido;
            });

        function guardar() {
            const formData = new FormData();
            formData.append('titulo', document.getElementById('titulo').value);
            formData.append('contenido', document.getElementById('contenido').value);
            const imagen = document.getElementById('imagen').files[0];
            if (imagen) formData.append('imagen', imagen);

            fetch('php/home_content.php', {
                method: 'POST',
                body: formData
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