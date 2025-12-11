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
            --primary-light: #ffebeb;
            --gray-light: #f8f9fa;
            --gray: #6c757d;
            --dark: #333;
            --shadow: 0 8px 25px rgba(0, 0, 0, 0.12);
            --radius: 16px;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f5f5f5 0%, #e0e0e0 100%);
            color: var(--dark);
            min-height: 100vh;
        }

        .sidebar {
            position: fixed;
            top: 0; left: 0;
            width: 260px;
            height: 100vh;
            background: var(--primary);
            color: white;
            padding: 25px 0;
            box-shadow: 5px 0 20px rgba(0,0,0,0.15);
            z-index: 1000;
        }

        .sidebar .logo {
            text-align: center;
            margin-bottom: 40px;
            padding: 0 25px;
        }

        .sidebar .logo img {
            width: 130px;
            border-radius: 50%;
            border: 5px solid rgba(255,255,255,0.25);
            transition: all 0.3s;
        }

        .sidebar .logo img:hover {
            transform: scale(1.08);
            border-color: white;
        }

        .sidebar a {
            color: rgba(255,255,255,0.9);
            padding: 16px 28px;
            display: flex;
            align-items: center;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s;
            border-left: 4px solid transparent;
        }

        .sidebar a i {
            width: 24px;
            margin-right: 14px;
            font-size: 1.15rem;
        }

        .sidebar a:hover, .sidebar a.active {
            background: var(--primary-hover);
            color: white;
            border-left-color: white;
            padding-left: 32px;
        }

        .content {
            margin-left: 260px;
            padding: 40px;
        }

        .page-header {
            background: white;
            padding: 25px 30px;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            margin-bottom: 30px;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .page-header i {
            font-size: 1.8rem;
            color: var(--primary);
        }

        .page-header h1 {
            margin: 0;
            font-size: 1.6rem;
            font-weight: 600;
            color: var(--dark);
        }

        .card {
            background: white;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            overflow: hidden;
            transition: transform 0.2s;
        }

        .card:hover {
            transform: translateY(-3px);
        }

        .card-header-custom {
            background: var(--primary);
            color: white;
            padding: 18px 28px;
            font-weight: 600;
            font-size: 1.15rem;
        }

        .table {
            margin: 0;
        }

        .table thead {
            background: var(--primary);
            color: white;
        }

        .table thead th {
            border: none;
            font-weight: 600;
            padding: 16px;
            font-size: 0.95rem;
        }

        .table tbody td {
            padding: 16px;
            vertical-align: middle;
            border-color: #eee;
        }

        .table tbody tr:hover {
            background: var(--primary-light);
        }

        .badge {
            font-size: 0.8rem;
            padding: 6px 12px;
            border-radius: 20px;
        }

        .badge-success {
            background: #d4edda;
            color: #155724;
        }

        .badge-danger {
            background: #f8d7da;
            color: #721c24;
        }

        .btn-edit {
            background: var(--primary);
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 10px;
            font-size: 0.9rem;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .btn-edit:hover {
            background: var(--primary-hover);
            transform: translateY(-1px);
            box-shadow: 0 4px 10px rgba(163,0,0,0.2);
        }

        .avatar-circle {
            width: 46px;
            height: 46px;
            border-radius: 50%;
            background: var(--primary-light);
            color: var(--primary);
            font-weight: bold;
            font-size: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 3px solid white;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        /* Modal */
        .modal-content {
            border-radius: var(--radius);
            border: none;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        }

        .modal-header {
            background: var(--primary);
            color: white;
            border-radius: var(--radius) var(--radius) 0 0;
            padding: 18px 25px;
        }

        .modal-header .btn-close {
            filter: brightness(0) invert(1);
        }

        .modal-body {
            padding: 30px;
        }

        .form-control, .form-select {
            border: 2px solid #e9ecef;
            border-radius: 12px;
            padding: 12px 16px;
            font-size: 0.95rem;
            transition: all 0.3s;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 0.2rem rgba(163,0,0,0.15);
        }

        .form-label {
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 8px;
        }

        .btn-save-modal {
            background: var(--primary);
            color: white;
            border: none;
            padding: 12px 28px;
            border-radius: 12px;
            font-weight: 600;
            transition: all 0.3s;
        }

        .btn-save-modal:hover {
            background: var(--primary-hover);
            transform: translateY(-2px);
        }

        @media (max-width: 768px) {
            .sidebar { width: 80px; }
            .sidebar .logo img { width: 50px; }
            .sidebar a span { display: none; }
            .sidebar a { padding: 16px; justify-content: center; }
            .sidebar a:hover { padding-left: 16px; }
            .content { margin-left: 80px; padding: 20px; }
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

<div class="row mb-3">
    <div class="col-md-6">
        <input type="search" id="buscarEventos" class="form-control"
               placeholder="Buscar por título o responsable...">
    </div>
</div>

<table id="tablaEventos" class="table table-striped table-bordered table-sm align-middle">

                <thead class="table-secondary">
                    <tr>
                        
        <th class="text-center">Fav</th>
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

    <!-- ⭐ FAVORITO ANTES DEL ID -->
    <?php
    $favQuery = $conn->query("SELECT 1 FROM EVENTOS_FAVORITOS WHERE ID_EVE_CUR = {$r['ID_EVE_CUR']}");
    $esFavorito = $favQuery->num_rows > 0;
    ?>
    <td class="text-center">
        <i class="fa-solid fa-heart fs-4 favorito"
           data-id="<?= $r['ID_EVE_CUR'] ?>"
           style="cursor:pointer; color:<?= $esFavorito ? 'red' : '#bbb' ?>;">
        </i>
    </td>

    <!-- ID -->
    <td><?= $r['ID_EVE_CUR'] ?></td>

    <!-- Título -->
    <td><?= htmlspecialchars($r['TIT_EVE_CUR']) ?></td>

    <!-- Responsable -->
    <td><?= htmlspecialchars($r['responsable']) ?></td>

    <!-- Acciones -->
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




<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>


// Validar que haya responsable
$("#formEvento").on("submit",function(e){
    if(!$("#RESPONSABLE_CED").val()){
        alert("Seleccione un responsable");
        e.preventDefault();
    }
});

// 🔍 BUSCADOR EN LISTA DE EVENTOS
$("#buscarEventos").on("input", function () {
    const filtro = $(this).val().toLowerCase();
    $("#tablaEventos tbody tr").each(function () {
        const texto = $(this).text().toLowerCase();
        $(this).toggle(texto.includes(filtro));
    });
});

</script>
<?php include "modalBuscarResponsable.php"; ?>

</body>
</html>
