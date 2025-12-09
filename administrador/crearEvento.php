<?php
session_start();
require_once '../includes/conexion.php'; // 📌 IMPORTANTE PARA QUE $conn FUNCIONE

// seguridad: solo administrador
if (!isset($_SESSION['correo']) || strtolower($_SESSION['rol_nombre'] ?? '') !== 'administrador') {
    header("Location: ../index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Crear Evento - UTA</title>

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
    <a href="#" class="active"><i class="fas fa-calendar-check me-2"></i>Gestionar Eventos</a>
    <a href="gestionUsuarios.php"><i class="fas fa-users me-2"></i>Gestionar Usuarios</a>
    <a href="perfil.php"><i class="fas fa-user me-2"></i>Perfil</a>
    <a href="configuraciones.php"><i class="fas fa-cog me-2"></i>Configuraciones</a>
    <a href="../Login/logout.php"><i class="fas fa-sign-out-alt me-2"></i>Cerrar Sesión</a>
</div>


<div class="content">

    <!-- 📌 MENSAJE DE GUARDADO -->
    <?php if (!empty($_GET['msg'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($_GET['msg']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>


    <!-- 📌 FORMULARIO CREAR EVENTO -->
    <div class="card p-4 shadow-sm mb-4">
        <h4 class="mb-3">Crear Evento — Seleccionar Responsable</h4>

        <form id="formEvento" method="post" action="guardarEvento.php">
            <div class="row g-3">
                <div class="col-md-8">
                    <label class="form-label">Título del evento *</label>
                    <input type="text" name="TIT_EVE_CUR" class="form-control" required maxlength="150">
                </div>

                <div class="col-md-4">
                    <label class="form-label">Responsable *</label>
                    <div class="input-group">
                        <input type="text" id="responsable_display" class="form-control" readonly placeholder="Seleccione...">
                        <input type="hidden" id="RESPONSABLE_CED" name="RESPONSABLE_CED">
                        <button type="button" data-bs-toggle="modal" data-bs-target="#modalBuscar"
                                class="btn btn-outline-secondary">
                            <i class="bi bi-search"></i>
                        </button>
                    </div>
                    <small id="responsableNombre" class="text-primary fw-bold"></small>
                </div>

                <div class="col-12 text-end mt-3">
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </div>
        </form>
    </div>


    <!-- 📌 LISTADO DE EVENTOS EN LA MISMA VISTA -->
    <?php
    $q = $conn->query("
        SELECT e.ID_EVE_CUR, e.TIT_EVE_CUR,
       CONCAT(u.NOM_PRI_USU,' ',u.APE_PRI_USU) AS responsable
FROM EVENTOS_CURSOS e
LEFT JOIN USUARIOS u ON u.CED_USU = e.RESPONSABLE_CED
ORDER BY e.ID_EVE_CUR DESC

    ");
    ?>

    <div class="card shadow-sm">
        <div class="card-body">
            <h5 class="mb-3"><i class="bi bi-list-ul me-2"></i>Eventos Registrados</h5>

            <table class="table table-striped table-bordered table-sm align-middle">
                <thead class="table-secondary">
                    <tr>
                        <th>ID</th>
                        <th>Título</th>
                        <th>Responsable</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                <?php if($q->num_rows > 0): ?>
                    <?php while($r = $q->fetch_assoc()): ?>
                    <tr>
                        <td><?= $r['ID_EVE_CUR'] ?></td>
                        <td><?= htmlspecialchars($r['TIT_EVE_CUR']) ?></td>
                        <td><?= htmlspecialchars($r['responsable']) ?></td>
                        <td class="text-center">
                            <a href="editarEvento.php?id=<?= $r['ID_EVE_CUR'] ?>" class="btn btn-warning btn-sm">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <a href="eliminarEvento.php?id=<?= $r['ID_EVE_CUR'] ?>" class="btn btn-danger btn-sm"
                               onclick="return confirm('¿Eliminar este evento?')">
                                <i class="bi bi-trash"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="4" class="text-center text-muted">Aún no hay eventos registrados</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div> <!-- FIN CONTENT -->


<!-- 📌 MODAL BUSCAR RESPONSABLE -->
<div class="modal fade" id="modalBuscar">
<div class="modal-dialog modal-lg modal-dialog-scrollable">
<div class="modal-content">

    <div class="modal-header">
        <h5 class="modal-title">Buscar Responsable</h5>
        <button class="btn-close" data-bs-dismiss="modal"></button>
    </div>

    <div class="modal-body">
        <input type="search" id="termBuscar" class="form-control mb-3" placeholder="Buscar por cédula, nombre o apellido">
        
        <table class="table table-hover table-sm">
            <thead class="table-secondary">
                <tr>
                    <th>Cédula</th>
                    <th>Nombre</th>
                    <th>Correo</th>
                    <th></th>
                </tr>
            </thead>
            <tbody id="tblResultados">
                <tr><td colspan="4" class="text-center text-muted">Escriba para buscar...</td></tr>
            </tbody>
        </table>
    </div>

</div>
</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
// 🔍 Buscador AJAX
let timer=null;
$("#termBuscar").on("input",function(){
    const q = this.value.trim();
    clearTimeout(timer);
    if(q.length < 2){
        $("#tblResultados").html('<tr><td colspan="4" class="text-muted text-center">Escriba 2 letras...</td></tr>');
        return;
    }
    timer = setTimeout(()=>buscar(q),300);
});

function buscar(q){
    $("#tblResultados").html('<tr><td colspan="4" class="text-center">Buscando...</td></tr>');
    $.getJSON("buscarResponsable.php",{term:q},function(data){
        if(!data.length){
            $("#tblResultados").html('<tr><td colspan="4" class="text-danger text-center">Sin resultados</td></tr>');
            return;
        }
        $("#tblResultados").html(
            data.map(u => `
                <tr>
                    <td>${u.cedula}</td>
                    <td>${u.nombreCompleto}</td>
                    <td>${u.correo}</td>
                    <td>
                        <button class="btn btn-success btn-sm"
                                onclick="sel('${u.cedula}','${u.nombreCompleto}')">
                            Elegir
                        </button>
                    </td>
                </tr>
            `).join("")
        );
    });
}

function sel(cedula,nombre){
    $("#RESPONSABLE_CED").val(cedula);
    $("#responsable_display").val(cedula+" - "+nombre);
    $("#responsableNombre").text(nombre);
    bootstrap.Modal.getInstance(document.getElementById("modalBuscar")).hide();
}

// Validar que haya responsable
$("#formEvento").on("submit",function(e){
    if(!$("#RESPONSABLE_CED").val()){
        alert("Seleccione un responsable");
        e.preventDefault();
    }
});
</script>

</body>
</html>
