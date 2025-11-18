<!-- Actualización para admin_inicio.php: Dashboard para Administrador con estilos ajustados al tema rojo -->
<?php
session_start();

// Verificar que haya una sesión activa y que sea administrador
if (!isset($_SESSION['correo']) || strtolower($_SESSION['rol_nombre']) !== 'administrador') {
    header("Location: ../index.php");
    exit();
}

require_once __DIR__ . '/../includes/conexion.php'; // Conexión a la BD

// Obtener datos reales de la BD
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM EVENTOS_CURSOS");
$stmt->execute();
$totalEventos = $stmt->get_result()->fetch_assoc()['total'];

$stmt = $conn->prepare("SELECT COUNT(*) as total FROM USUARIOS");
$stmt->execute();
$usuariosRegistrados = $stmt->get_result()->fetch_assoc()['total'];

$stmt = $conn->prepare("SELECT COUNT(*) as total FROM INSCRIPCIONES WHERE ESTADO_INS = 'Preinscrito'");
$stmt->execute();
$inscripcionesPendientes = $stmt->get_result()->fetch_assoc()['total'];

// Eventos por mes (últimos 6 meses, ajusta según necesidades)
$eventosPorMes = [];
for ($i = 5; $i >= 0; $i--) {
    $mes = date('Y-m', strtotime("-$i months"));
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM EVENTOS_CURSOS WHERE DATE_FORMAT(FEC_INI_EVE_CUR, '%Y-%m') = ?");
    $stmt->bind_param("s", $mes);
    $stmt->execute();
    $eventosPorMes[] = $stmt->get_result()->fetch_assoc()['count'];
}
$mesesLabels = [];
for ($i = 5; $i >= 0; $i--) {
    $mesesLabels[] = date('M', strtotime("-$i months"));
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Administrador - UTA</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
    <!-- Sidebar Navbar Lateral Izquierdo -->
    <div class="sidebar">
        <div class="logo">
            <img src="../images/favico.png" alt="Logo UTA">
        </div>
        <a href="admin_inicio.php"><i class="fas fa-home me-2"></i> Inicio</a>
        <a href="gestionar_eventos.php"><i class="fas fa-calendar-check me-2"></i> Gestionar Eventos</a>
        <a href="evidencias_global.php"><i class="fa fa-clipboard-check"></i> Gestionar Evidencias</a>
        <a href="verificar_pagos.php"><i class="fa fa-credit-card"></i> Gestionar Pagos</a>
        <a href="eventos_certificables.php"><i class="fa fa-certificate"></i> Generación de Certificados</a>
        <a href="editar_usuario.php"><i class="fas fa-users me-2"></i> Gestionar Usuarios</a>
        <a href="perfil.php"><i class="fas fa-user me-2"></i> Perfil</a>
        <a href="admin_configuraciones.php" class="active"><i class="fas fa-cog me-2"></i> Configuraciones</a>
        <a href="../Login/logout.php"><i class="fas fa-sign-out-alt me-2"></i> Cerrar Sesión</a>
    </div>

    <!-- Contenido Principal -->
    <div class="content">
       <h1 class="mb-4">Configuraciones de Interfaz</h1>
<p>Bienvenido, <?= ucfirst($_SESSION['rol_nombre']) ?> (<?= $_SESSION['correo'] ?>)</p>

<div class="accordion" id="configAccordion">

    <!-- HOME -->
  <!-- HOME -->
        <?php  
$slider = include __DIR__ . "../../home/slider.php";  
?>
<div class="accordion-item mb-3">
    <h2 class="accordion-header">
        <button class="accordion-button collapsed"
                type="button"
                data-bs-toggle="collapse"
                data-bs-target="#homeConfig1">
            <i class="fas fa-home me-2"></i> Configuración de Home
        </button>
    </h2>
   

   <div id="homeConfig1" class="accordion-collapse collapse" data-bs-parent="#configAccordion">
        <div class="accordion-body">
       <h1 class="mb-4">Habilitar edicion</h1>

        <!-- Comienso slider -->
            <!-- BOTÓN EDITAR -->
            <button id="btnEditarSlider" class="btn-edit">Editar Slider</button>
                <br>
                 <br>
            <form id="sliderForm">

                <?php for ($i = 0; $i < 3; $i++): ?>
                <div class="card mb-3 p-3 border">

                    <h5 class="mb-3">Item <?= $i+1 ?></h5>

                    <div class="mb-3">
                        <label class="form-label">Título</label>
                        <input type="text" 
                               name="titulo_<?= $i+1 ?>" 
                               class="form-control slider-input"
                               value="<?= $slider[$i]['titulo'] ?>"
                               disabled>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Descripción</label>
                        <textarea name="descripcion_<?= $i+1 ?>" 
                                  class="form-control slider-input"
                                  rows="3"
                                  disabled><?= $slider[$i]['descripcion'] ?></textarea>
                    </div>
                </div>
                <?php endfor; ?>

                <button type="submit" id="btnGuardarSlider" class="btn-edit" disabled>
                    Guardar
                </button>
            </form>

        </div>
        <script>
document.getElementById("btnEditarSlider").addEventListener("click", function() {
    const inputs = document.querySelectorAll(".slider-input");
    inputs.forEach(input => input.disabled = false);

    document.getElementById("btnGuardarSlider").disabled = false;
});
</script>
<script>
document.getElementById("sliderForm").addEventListener("submit", function(e) {
    e.preventDefault();

    let formData = new FormData(this);

    fetch("../home/guardar_slider.php", {
        method: "POST",
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === "success") {
            alert("Datos guardados correctamente");

            // bloquear de nuevo
            document.querySelectorAll(".slider-input").forEach(i => i.disabled = true);
            document.getElementById("btnGuardarSlider").disabled = true;
        } else {
            alert("Error: " + data.msg);
        }
    })
    .catch(err => {
        alert("Error en la conexión.");
        console.log(err);
    });
});
</script>
<!-- fin slider -->
 <br>
                 <br>
  <h1 class="mb-4">Mision y Vision</h1>
    <!-- Mision -->
     
<?php
$misionVision = include "../home/mision_vision.php";
?>
<button type="button" id="btnEditarMV" class="btn-edit"   style="margin-left: 20px;">Habilitar edicion</button>
<br>
                 <br>
 <form id="mvForm"> 

    <div class="card mb-3 p-3 border">
        <h5 class="mb-3">Misión</h5>
        <textarea id="inputMision" class="form-control mv-field" rows="4" readonly></textarea>
    </div>

    <div class="card mb-3 p-3 border">
        <h5 class="mb-3">Visión</h5>
        <textarea id="inputVision" class="form-control mv-field" rows="4" readonly></textarea>
    </div>

    <button type="button" id="btnGuardarMV" class="btn-edit" disabled  style="margin-left: 20px;">Guardar</button>
</form>


<script>
document.addEventListener("DOMContentLoaded", () => {
    document.getElementById("inputMision").value = `<?= $misionVision["mision"] ?>`;
    document.getElementById("inputVision").value = `<?= $misionVision["vision"] ?>`;
});
</script>
<script>
function guardarMisionVision() {
    let datos = new FormData();
    datos.append("mision", document.getElementById("inputMision").value);
    datos.append("vision", document.getElementById("inputVision").value);

    fetch("./home/mision_vision_guardar.php", {
        method: "POST",
        body: datos
    })
    .then(r => r.json())
    .then(res => {
        alert(res.msg);
    });
}
</script>
<script>
document.addEventListener("DOMContentLoaded", () => {
    // Cargar datos de PHP
    document.getElementById("inputMision").value = `<?= $misionVision["mision"] ?>`;
    document.getElementById("inputVision").value = `<?= $misionVision["vision"] ?>`;

    // Botón Editar
    document.getElementById("btnEditarMV").addEventListener("click", function() {
        document.querySelectorAll(".mv-field").forEach(campo => campo.removeAttribute("readonly"));
        document.getElementById("btnGuardarMV").disabled = false;
        this.disabled = true; // desactivar botón editar
    });

    // Botón Guardar
    document.getElementById("btnGuardarMV").addEventListener("click", function() {
        let datos = new FormData();
        datos.append("mision", document.getElementById("inputMision").value);
        datos.append("vision", document.getElementById("inputVision").value);

        fetch("../home/mision_vision_guardar.php", {
            method: "POST",
            body: datos
        })
        .then(r => r.json())
        .then(res => {
            alert(res.msg);
            if(res.ok){
                // Volver a bloquear campos y habilitar editar
                document.querySelectorAll(".mv-field").forEach(campo => campo.setAttribute("readonly", true));
                document.getElementById("btnEditarMV").disabled = false;
                document.getElementById("btnGuardarMV").disabled = true;
            }
        });
    });
});
</script>
<script>
    
</script>



    <!-- mision fin -->

    


    </div>


    
</div>


    <!-- CONTACTANOS -->
     
     <?php
$contacto = include "../home/contactanos.php";
?>

    <div class="accordion-item mb-3" style="border-radius: var(--radius); overflow:hidden; box-shadow: var(--shadow);">
    <h2 class="accordion-header">
        <button class="accordion-button collapsed" 
                type="button"
                data-bs-toggle="collapse" 
                data-bs-target="#contactConfig">
            <i class="fas fa-envelope me-2"></i> Configuración de Contáctanos
        </button>
    </h2>

    <div id="contactConfig" class="accordion-collapse collapse" data-bs-parent="#configAccordion">
        <div class="accordion-body">
             <h1 class="mb-4">Configuracion de nosotros</h1>
            <p class="text-muted">Aquí puedes editar información de contacto, mapa e información de la facultad.</p>

            <button id="btnEditarContacto" class="btn-edit">Habilitar edicion</button>
            <br><br>

           <form id="contactoForm" enctype="multipart/form-data">


                <div class="card mb-3 p-3 border">
                    <h5 class="mb-3">Descripción</h5>
                    <textarea name="des_noso" class="form-control contacto-field" rows="3" disabled><?= $contacto['des_noso'] ?></textarea>
                </div>

                <div class="card mb-3 p-3 border">
                    <h5 class="mb-3">Enlace (Mapa / Información)</h5>
                    <input type="text" name="link_noso" class="form-control contacto-field"
                           value="<?= $contacto['link_noso'] ?>" disabled>
                </div>
                <div class="card mb-3 p-3 border">
    <h5 class="mb-3">Imagen (Sección Nosotros)</h5>

    <!-- Vista previa -->
    <img id="previewImgNosotros"
         src="../images/nosotros/<?= $contacto['ruta'] ?>"
         style="width:180px; border-radius:10px; border:2px solid #ccc; margin-bottom:10px;">

    <!-- Input de archivo -->
   <input type="file" 
       name="imgNosotros" 
       id="imgNosotros" 
       class="form-control contacto-field" 
       style="margin-top:10px;" 
       disabled>
</div>

                <button type="submit" id="btnGuardarContacto" class="btn-edit" disabled style="margin-left: 10px;">
                    Guardar
                </button>
            </form>

<?php $desarrolladores = include "../home/desarrolladores.php"; ?>
 <br>
            <br>
<button class="btn-edit mb-3" 
        data-bs-toggle="modal" 
        data-bs-target="#modalAddDev">
    <i class="fas fa-user-plus me-2"></i> Agregar Desarrollador
</button>

<div class="card mt-4">
    <div class="card-header-custom">
        <i class="fas fa-users-cog me-2"></i> Desarrolladores del Sistema
    </div>
    <div class="card-body p-0">

        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Foto</th>
                        <th>Nombre</th>
                        <th>Redes Social</th>
                        <th>Descripción</th>
                        <th>Acción</th>
                    </tr>
                </thead>

             <tbody>
<?php foreach ($desarrolladores as $d): ?>
<tr>
    <!-- Foto -->
    <td>
        <img src="../<?= $d['ruta_completa'] ?>" class="rounded" width="50">
    </td>

    <!-- Nombre completo -->
    <td>
        <strong><?= htmlspecialchars($d['nombre'] . " " . $d['apellido']) ?></strong>
    </td>

    <!-- Contacto -->
    <td>
        <!-- GitHub -->
        <a href="https://github.com/<?= htmlspecialchars($d['github']) ?>" target="_blank" title="GitHub">
           <i class="fab fa-github" aria-hidden="true" style="color:black;"></i>
        </a>

        &nbsp;&nbsp;

        <!-- WhatsApp -->
        <a href="https://wa.me/<?= htmlspecialchars($d['telefono']) ?>" target="_blank" title="WhatsApp">
            <i class="fab fa-whatsapp" style="color:green;"></i>
        </a>

        &nbsp;&nbsp;

        <!-- Correo -->
        <a href="mailto:<?= htmlspecialchars($d['correo']) ?>" title="Enviar correo">
            <i class="fa fa-envelope fa-lg"></i>
        </a>
    </td>

    <!-- Descripción -->
    <td><?= htmlspecialchars($d['descripcion']) ?></td>

    <!-- Acción: Editar y Eliminar -->
    <td>
        <button class="btn-edit"
            data-bs-toggle="modal"
            data-bs-target="#modalEditDev"
            onclick='cargarDesarrollador(<?= json_encode($d) ?>)'>
            <i class="fas fa-edit"></i> Editar
        </button>

        <button class="btn btn-danger btn-sm"
            onclick="eliminarDesarrollador('<?= $d['ruta'] ?>')">
            <i class="fa fa-trash"></i> Eliminar
        </button>
    </td>
</tr>
<?php endforeach; ?>
</tbody>
<script>
function eliminarDesarrollador(ruta) {

    if (!confirm("¿Seguro que deseas eliminar a este desarrollador?")) {
        return;
    }

    let datos = new FormData();
    datos.append("ruta", ruta);

    fetch("../home/desarrolladores_eliminar.php", {
        method: "POST",
        body: datos
    })
    .then(r => r.json())
    .then(res => {
        alert(res.msg);
        if (res.status === "success") {
            location.reload();
        }
    });
}
</script>

                                
            </table>
        </div>

    </div>
</div>
<br>
<br>
<div class="row mb-4">

    <!-- Vista previa del mapa -->
    <div class="col-md-6">
        <div class="card p-3 border">
            <h5 class="mb-3">Vista previa del mapa</h5>

            <div id="previewMapaContainer"
                 style="width:100%; height:300px; border-radius:10px; overflow:hidden; border:2px solid #ccc;">
                <?= $contacto['maps'] ?> 
            </div>
        </div>
    </div>

    <!-- Editor -->
    <div class="col-md-6">
        <div class="card p-3 border">

            <h5 class="mb-3">Editar iframe del mapa</h5>

            <label class="form-label">Código iframe</label>

            <textarea 
                class="form-control" 
                name="maps" 
                rows="8"
                id="campoIframe"
                disabled
            ><?= htmlspecialchars($contacto['maps']) ?></textarea>

            <p class="text-muted mt-2" style="font-size:13px; font-style:italic;">
                El iframe de Google Maps se guardará tal como lo pegues aquí.
            </p>

            <!-- BOTONES -->
            <div class="mt-3">
                <button type="button" id="btnEditar" class="btn btn-primary">
                    Editar
                </button>

                <button type="submit" id="btnGuardar" class="btn btn-success" style="display:none;">
                    Guardar cambios
                </button>
            </div>

        </div>
    </div>

</div>
<script>
document.addEventListener("DOMContentLoaded", () => {

    const campo = document.getElementById("campoIframe");
    const btnEditar = document.getElementById("btnEditar");
    const btnGuardar = document.getElementById("btnGuardar");
    const preview = document.getElementById("previewMapaContainer");

    // Habilitar edición
    btnEditar.addEventListener("click", () => {
        campo.disabled = false;
        campo.focus();
        btnEditar.style.display = "none";
        btnGuardar.style.display = "inline-block";
    });

    // Actualizar vista previa en tiempo real
    campo.addEventListener("input", () => {
        preview.innerHTML = campo.value;
    });

    // Guardar solo el iframe (maps) al pulsar Guardar
    btnGuardar.addEventListener("click", (e) => {
        e.preventDefault();

        const mapsValue = campo.value.trim();

        // Validación mínima: no vacío y contiene iframe o startsWith <iframe
        if (mapsValue === "") {
            alert("El campo del iframe no puede estar vacío.");
            return;
        }

        if (!mapsValue.includes("<iframe")) {
            // opcional: permitir también cadenas que empiecen por https (src directo)
            if (!mapsValue.startsWith("https://")) {
                if (!confirm("El contenido no parece ser un iframe. ¿Deseas guardarlo igual?")) {
                    return;
                }
            }
        }

        // Crear FormData y enviar solo maps
        const fd = new FormData();
        fd.append("maps", mapsValue);

        // Ajusta la ruta si tu archivo está en otra ubicación relativa
        fetch("../home/contactanos_maps_guardar.php", {
            method: "POST",
            body: fd
        })
        .then(r => r.json())
        .then(res => {
            alert(res.msg || "Respuesta recibida");
            if (res.status === "success") {
                // Desactivar editor y cambiar botones
                campo.disabled = true;
                btnGuardar.style.display = "none";
                btnEditar.style.display = "inline-block";

                // Asegurar vista previa actualizada con lo guardado (ya lo hicimos antes)
                preview.innerHTML = mapsValue;
            }
        })
        .catch(err => {
            console.error(err);
            alert("Error al guardar el mapa. Revisa la consola.");
        });
    });

});
</script>




<div class="modal fade" id="modalEditDev" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-user-edit me-2"></i> Editar Desarrollador</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <form id="formEditarDev" enctype="multipart/form-data">
                <div class="modal-body">

                    <input type="hidden" name="ruta_actual" id="ruta_actual">

                    <div class="row g-3">
                        
                        <div class="col-md-6">
                            <label class="form-label">Nombre *</label>
                            <input type="text" id="dev_nombre" name="nombre" class="form-control" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Apellido *</label>
                            <input type="text" id="dev_apellido" name="apellido" class="form-control" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Correo *</label>
                            <input type="email" id="dev_correo" name="correo" class="form-control" required>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Descripción *</label>
                            <textarea id="dev_descripcion" name="descripcion" rows="3" class="form-control" required></textarea>
                        </div>
                            <div class="col-md-6">
    <label class="form-label">GitHub *</label>
    <input type="text" id="dev_github" name="github" class="form-control" required>
</div>

<div class="col-md-6">
    <label class="form-label">Teléfono (WhatsApp) *</label>
    <input type="text" id="dev_telefono" name="telefono" class="form-control" required>
</div>

                        <div class="col-md-6">
                            <label class="form-label">Foto (opcional)</label>
                            <input type="file" name="foto" class="form-control">
                        </div>

                        <div class="col-md-6">
                          <br>
                        </div>

                    </div>

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-save-modal">
                        <i class="fas fa-save me-2"></i> Guardar Cambios
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>

<!-- MODAL AGREGAR DESARROLLADOR -->
<div class="modal fade" id="modalAddDev" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
           
            <div class="modal-header">
               
                <h5 class="modal-title"><i class="fas fa-user-plus me-2"></i> Agregar Desarrollador</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <form id="formAgregarDev" enctype="multipart/form-data">
                <div class="modal-body">

                    <div class="row g-3">
                        
                        <div class="col-md-6">
                            <label class="form-label">Nombre *</label>
                            <input type="text" name="nombre" class="form-control" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Apellido *</label>
                            <input type="text" name="apellido" class="form-control" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Correo *</label>
                            <input type="email" name="correo" class="form-control" required>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Descripción *</label>
                            <textarea name="descripcion" rows="3" class="form-control" required></textarea>
                        </div>

                            <div class="col-md-6">
    <label class="form-label">GitHub *</label>
    <input type="text" name="github" class="form-control" required>
</div>

<div class="col-md-6">
    <label class="form-label">Teléfono (WhatsApp) *</label>
    <input type="text" name="telefono" class="form-control" required>
</div>

                        <div class="col-md-6">
                            <label class="form-label">Foto *</label>
                            <input type="file" name="foto" class="form-control" required>
                        </div>

                    </div>

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-save-modal">
                        <i class="fas fa-save me-2"></i> Guardar
                    </button>
                </div>

            </form>

        </div>
    </div>
</div>
<script>
document.getElementById("formAgregarDev").addEventListener("submit", function(e) {
    e.preventDefault();

    let datos = new FormData(this);

    fetch("../home/desarrolladores_agregar.php", {
        method: "POST",
        body: datos
    })
    .then(r => r.json())
    .then(res => {
        alert(res.msg);

        if (res.status === "success") {
            location.reload(); // Recargar la tabla automáticamente
        }
    });
});
</script>


<script>
function cargarDesarrollador(data) {
    document.getElementById("dev_nombre").value = data.nombre;
    document.getElementById("dev_apellido").value = data.apellido;
    document.getElementById("dev_correo").value = data.correo;
    document.getElementById("dev_descripcion").value = data.descripcion;

    document.getElementById("dev_github").value = data.github;
    document.getElementById("dev_telefono").value = data.telefono;

    document.getElementById("ruta_actual").value = data.ruta; 
    document.getElementById("dev_imagen").src = data.ruta_completa;
}


document.getElementById("formEditarDev").addEventListener("submit", function(e) {
    e.preventDefault();

    let correo = document.getElementById("dev_correo").value;

    if (!correo.endsWith("@uta.edu.ec")) {
        alert("El correo debe terminar en @uta.edu.ec");
        return;
    }

    let datos = new FormData(this);

    fetch("../home/desarrolladores_guardar.php", {
        method: "POST",
        body: datos
    })
    .then(r => r.json())
    .then(res => {
        alert(res.msg);
        if (res.status === "success") {
            location.reload();
        }
    });
});

</script>

        </div>
    </div>
</div>
<script>
document.addEventListener("DOMContentLoaded", () => {

    const btnEditar = document.getElementById("btnEditarContacto");
    const btnGuardar = document.getElementById("btnGuardarContacto");
    const campos = document.querySelectorAll(".contacto-field");

    // Habilitar edición
    btnEditar.addEventListener("click", () => {
        campos.forEach(c => c.disabled = false);
        btnGuardar.disabled = false;
        btnEditar.disabled = true;
    });

    // Validación + Guardado
    document.getElementById("contactoForm").addEventListener("submit", function(e) {
        e.preventDefault();

        let des = this.des_noso.value.trim();
        let link = this.link_noso.value.trim();

        let errores = [];

        if (des.length === 0) errores.push("La descripción no puede estar vacía.");
        if (!/^https:\/\//.test(link)) errores.push("El enlace debe iniciar con https://");

        if (errores.length > 0) {
            alert(errores.join("\n"));
            return;
        }

        let datos = new FormData(this);

        fetch("../home/contactanos_guardar.php", {
            method: "POST",
            body: datos
        })
        .then(r => r.json())
        .then(res => {
            alert(res.msg);
            if (res.status === "success") {
                campos.forEach(c => c.disabled = true);
                btnGuardar.disabled = true;
                btnEditar.disabled = false;
            }
        });
    });   //  ←🔥 AQUÍ FALTABA EL CIERRE

    // Vista previa de la nueva imagen
    document.getElementById("imgNosotros").addEventListener("change", (e) => {
        const file = e.target.files[0];
        if (file) {
            document.getElementById("previewImgNosotros").src = URL.createObjectURL(file);
        }
    });

});
</script>







    <!-- fin CONTACTANOS -->
    <!-- FOOTER -->
 <?php
$footer = include __DIR__ . "/../home/footer.php"; // Ajusta la ruta según tu archivo
?>

<div class="accordion-item mb-3">
    <h2 class="accordion-header">
        <button class="accordion-button collapsed"
                type="button"
                data-bs-toggle="collapse"
                data-bs-target="#footerConfig">
            <i class="fas fa-bars me-2"></i> Configuración de Footer
        </button>
    </h2>

    <div id="footerConfig" class="accordion-collapse collapse" data-bs-parent="#configAccordion">
        <div class="accordion-body">
            <p class="text-muted">Edita la información del pie de página, redes sociales y texto legal.</p>

            <button id="btnEditarFooter" class="btn-edit">Editar Footer</button>
            <br><br>

            <form id="footerForm">

                <div class="card mb-3 p-3 border">
                    <h5 class="mb-3">Teléfono</h5>
                    <input type="text" name="telefono" class="form-control footer-field" value="<?= $footer['telefono'] ?>" disabled>
                </div>

                <div class="card mb-3 p-3 border">
                    <h5 class="mb-3">Correo</h5>
                    <input type="email" name="correo" class="form-control footer-field" value="<?= $footer['correo'] ?>" disabled>
                </div>

                <div class="card mb-3 p-3 border">
                    <h5 class="mb-3">Descripción del Logo</h5>
                    <input type="text" name="Des_logo" class="form-control footer-field" value="<?= $footer['Des_logo'] ?>" disabled>
                </div>

                <div class="card mb-3 p-3 border">
                    <h5 class="mb-3">Facebook</h5>
                    <input type="text" name="face" class="form-control footer-field" value="<?= $footer['face'] ?>" disabled>
                </div>

                <div class="card mb-3 p-3 border">
                    <h5 class="mb-3">Instagram/Gráfica</h5>
                    <input type="text" name="ins_gra" class="form-control footer-field" value="<?= $footer['ins_gra'] ?>" disabled>
                </div>

                <div class="card mb-3 p-3 border">
                    <h5 class="mb-3">Días de Atención</h5>
                    <input type="text" name="dias" class="form-control footer-field" value="<?= $footer['dias'] ?>" disabled>
                </div>

                <div class="card mb-3 p-3 border">
                    <h5 class="mb-3">Horas de Atención</h5>
                    <input type="text" name="horas" class="form-control footer-field" value="<?= $footer['horas'] ?>" disabled>
                </div>

                <div class="card mb-3 p-3 border">
                    <h5 class="mb-3">Derechos</h5>
                    <textarea name="derechos" class="form-control footer-field" rows="3" disabled><?= $footer['derechos'] ?></textarea>
                </div>

                <button type="submit" id="btnGuardarFooter" class="btn-edit" disabled style="margin-left: 10px;">Guardar</button>
            </form>

        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", () => {

    const btnEditar = document.getElementById("btnEditarFooter");
    const btnGuardar = document.getElementById("btnGuardarFooter");
    const campos = document.querySelectorAll(".footer-field");

    // Habilitar edición
    btnEditar.addEventListener("click", () => {
        campos.forEach(campo => campo.removeAttribute("disabled"));
        btnGuardar.disabled = false;
        btnEditar.disabled = true;
    });

    // Validación y envío
    document.getElementById("footerForm").addEventListener("submit", function(e) {
        e.preventDefault();

        let telefono = this.telefono.value.trim();
        let correo   = this.correo.value.trim();
        let face     = this.face.value.trim();
        let insta    = this.ins_gra.value.trim();

        // Validaciones
        let errores = [];

        // Teléfono: +5939... o 09...
        if (!/^(\+5939\d{8}|09\d{8})$/.test(telefono)) {
            errores.push("Teléfono inválido. Debe iniciar con +5939 o 09 y tener 10 dígitos.");
        }

        // Correo: @uta.edu.ec
        if (!/^[a-zA-Z0-9._%+-]+@uta\.edu\.ec$/.test(correo)) {
            errores.push("Correo inválido. Debe ser @uta.edu.ec");
        }

        // Facebook e Instagram no vacíos y comiencen con https
        if (!face || !/^https:\/\//.test(face)) {
            errores.push("Facebook debe comenzar con https y no puede estar vacío.");
        }

        if (!insta || !/^https:\/\//.test(insta)) {
            errores.push("Instagram/Gráfica debe comenzar con https y no puede estar vacío.");
        }

        if (errores.length > 0) {
            alert(errores.join("\n"));
            return;
        }

        // Enviar datos
        let formData = new FormData(this);

        fetch("../home/guardar_footer.php", {
            method: "POST",
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            alert(data.msg);
            if (data.status === "success") {
                // Bloquear campos de nuevo
                campos.forEach(campo => campo.setAttribute("disabled", true));
                btnGuardar.disabled = true;
                btnEditar.disabled = false;
            }
        })
        .catch(err => {
            alert("Error en la conexión.");
            console.log(err);
        });

    });

});
</script>


     <!-- fin FOOTER -->

</div>


     
      

        <!-- Gráfico de Eventos por Mes -->
   

    <script>
        // Gráfico con Chart.js
        const ctx = document.getElementById('eventosChart').getContext('2d');
        const eventosChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?= json_encode($mesesLabels) ?>,
                datasets: [{
                    label: 'Eventos Creados',
                    data: <?= json_encode($eventosPorMes) ?>,
                    backgroundColor: 'rgba(163, 0, 0, 0.6)', /* Rojo con opacidad */
                    borderColor: 'rgba(163, 0, 0, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });
    </script>
</body>
</html>