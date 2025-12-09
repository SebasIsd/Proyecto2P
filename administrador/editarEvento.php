<?php
session_start();
require_once '../includes/conexion.php';

// Seguridad
if (!isset($_SESSION['correo']) || strtolower($_SESSION['rol_nombre']) !== 'administrador') {
    header("Location: ../index.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: crearEvento.php");
    exit();
}

$id = intval($_GET['id']);

// Obtener Evento
$sql = $conn->prepare("SELECT ID_EVE_CUR, TIT_EVE_CUR, RESPONSABLE_CED FROM EVENTOS_CURSOS WHERE ID_EVE_CUR = ?");
$sql->bind_param("i", $id);
$sql->execute();
$result = $sql->get_result();
$evento = $result->fetch_assoc();

if (!$evento) {
    header("Location: crearEvento.php?msg=Evento no encontrado");
    exit();
}

// Guardar cambios
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = strtoupper(trim($_POST['titulo']));
    $resp = $_POST['responsable'];

    if ($titulo === '' || $resp === '') {
        $msg = "Todos los campos son obligatorios";
    } else {
        $u = $conn->prepare("UPDATE EVENTOS_CURSOS SET TIT_EVE_CUR=?, RESPONSABLE_CED=? WHERE ID_EVE_CUR=?");
        $u->bind_param("ssi", $titulo, $resp, $id);
        if ($u->execute()) {
            header("Location: crearEvento.php?msg=Evento actualizado correctamente");
            exit();
        } else {
            $msg = "Error al actualizar";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Editar Evento - UTA</title>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<style>
:root {
    --primary: #a30000;
    --primary-hover: #d51313;
    --dark: #333;
}
body {
    font-family: 'Segoe UI', sans-serif;
    background: #eee;
}
.sidebar {
    position: fixed;
    top: 0;
    left: 0;
    width: 260px;
    height: 100vh;
    background: var(--primary);
    color: white;
    padding: 25px 0;
    box-shadow: 5px 0 20px rgba(0,0,0,0.15);
    z-index: 1000;
}
.sidebar a {
    color: white;
    padding: 14px 28px;
    display: block;
    text-decoration: none;
}
.sidebar a:hover, .sidebar a.active {
    background: var(--primary-hover);
}
.content {
    margin-left: 260px;
    padding: 40px;
}
</style>
</head>
<body>

<!-- SIDEBAR -->
<div class="sidebar">
    <div class="logo text-center mb-3">
        <img src="../images/favico.png" width="120">
    </div>
    <a href="adminInicio.php"><i class="fas fa-home me-2"></i>Inicio</a>
    <a href="crearEvento.php" class="active"><i class="fas fa-calendar-check me-2"></i>Gestionar Eventos</a>
    <a href="gestionUsuarios.php"><i class="fas fa-users me-2"></i>Gestionar Usuarios</a>
    <a href="perfil.php"><i class="fas fa-user me-2"></i>Perfil</a>
    <a href="configuraciones.php"><i class="fas fa-cog me-2"></i>Configuraciones</a>
    <a href="../Login/logout.php"><i class="fas fa-sign-out-alt me-2"></i>Cerrar Sesión</a>
</div>

<!-- CONTENIDO -->
<div class="content">

    <h4 class="mb-4">Editar Evento</h4>

    <?php if (!empty($msg)): ?>
    <div class="alert alert-warning"><?= $msg ?></div>
    <?php endif; ?>

    <form method="post" class="card p-4 shadow-sm">
        <div class="mb-3">
            <label class="form-label">Título del Evento</label>
            <input type="text" name="titulo" class="form-control"
                   value="<?= htmlspecialchars($evento['TIT_EVE_CUR']) ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Responsable</label>
            <div class="input-group">
                <input type="text" id="responsable_display" class="form-control" readonly
                       value="<?= $evento['RESPONSABLE_CED'] ?>">
                <input type="hidden" name="responsable" id="RESPONSABLE_CED"
                       value="<?= $evento['RESPONSABLE_CED'] ?>">
                <button type="button" class="btn btn-outline-secondary"
                        data-bs-toggle="modal" data-bs-target="#modalBuscar">
                    <i class="bi bi-search"></i>
                </button>
            </div>
        </div>

        <div class="text-end">
            <a href="crearEvento.php" class="btn btn-secondary">Cancelar</a>
            <button type="submit" class="btn btn-primary">Guardar Cambios</button>
        </div>
    </form>

</div> <!-- FIN CONTENT -->

<?php include "modalBuscarResponsable.php"; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
