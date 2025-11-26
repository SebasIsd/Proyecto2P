<?php
session_start();

// Verificar administrador
if (!isset($_SESSION['correo']) || strtolower($_SESSION['rol_nombre']) !== 'administrador') {
    header("Location: ../index.php");
    exit();
}

require_once '../includes/conexion.php';

// Cargar lista de usuarios
$usuarios = $conn->query("
    SELECT u.*, r.NOM_ROL 
    FROM USUARIOS u 
    JOIN ROLES r ON u.ID_ROL_USU = r.ID_ROL 
    ORDER BY u.APE_PRI_USU, u.NOM_PRI_USU
")->fetch_all(MYSQLI_ASSOC);

// Cargar catálogos para el modal
$roles = $conn->query("SELECT ID_ROL, NOM_ROL FROM ROLES ORDER BY NOM_ROL")->fetch_all(MYSQLI_ASSOC);
$carreras = $conn->query("SELECT ID_CARRERA, NOMBRE_CARRERA FROM TIPOS_CARRERA ORDER BY NOMBRE_CARRERA")->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionar Usuarios - UTA</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* (mantén tus estilos existentes — no los cambié excepto lo que necesites) */
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
            top: 0;
            left: 0;
            width: 260px;
            height: 100vh;
            background: var(--primary);
            color: white;
            padding: 25px 0;
            box-shadow: 5px 0 20px rgba(0, 0, 0, 0.15);
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
            border: 5px solid rgba(255, 255, 255, 0.25);
            transition: all 0.3s;
        }

        .sidebar .logo img:hover {
            transform: scale(1.08);
            border-color: white;
        }

        .sidebar a {
            color: rgba(255, 255, 255, 0.9);
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

        .sidebar a:hover,
        .sidebar a.active {
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
            padding: 15px 20px;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 15px;
            justify-content: space-between;
        }

        .page-header-left {
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
            font-size: 1.4rem;
            font-weight: 600;
            color: var(--dark);
        }

        .search-input {
            max-width: 420px;
            width: 100%;
        }

        .card {
            background: white;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            overflow: hidden;
            transition: transform 0.2s;
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
            padding: 12px;
            font-size: 0.95rem;
        }

        .table tbody td {
            padding: 12px;
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
            box-shadow: 0 4px 10px rgba(163, 0, 0, 0.2);
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
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        /* Modal */
        .modal-content {
            border-radius: var(--radius);
            border: none;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
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

        .form-control,
        .form-select {
            border: 2px solid #e9ecef;
            border-radius: 12px;
            padding: 12px 16px;
            font-size: 0.95rem;
            transition: all 0.3s;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 0.2rem rgba(163, 0, 0, 0.15);
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
            .sidebar {
                width: 80px;
            }

            .sidebar .logo img {
                width: 50px;
            }

            .sidebar a span {
                display: none;
            }

            .sidebar a {
                padding: 16px;
                justify-content: center;
            }

            .sidebar a:hover {
                padding-left: 16px;
            }

            .content {
                margin-left: 80px;
                padding: 20px;
            }

            .page-header {
                flex-direction: column;
                align-items: stretch;
            }

            .search-input {
                max-width: 150%;
            }
        }
    </style>
</head>

<body>

    <!-- Sidebar -->
    <div class="sidebar">
        <div class="logo">
            <img src="../images/favico.png" alt="Logo UTA">
        </div>
        <a href="admin_inicio.php"><i class="fas fa-home me-2"></i> Inicio</a>
        <a href="gestionar_eventos.php"><i class="fas fa-calendar-check me-2"></i> Gestionar Eventos</a>
        <a href="evidencias_global.php"><i class="fa fa-clipboard-check"></i> Gestionar Evidencias</a>
        <a href="verificar_pagos.php"><i class="fa fa-credit-card"></i> Gestionar Pagos</a>
        <a href="eventos_certificables.php"><i class="fa fa-certificate"></i> Generación de Certificados</a>
        <a href="editar_usuario.php" class="active"><i class="fas fa-users me-2"></i> Gestionar Usuarios</a>
        <a href="perfil.php"><i class="fas fa-user me-2"></i> Perfil</a>
        <a href="admin_configuraciones.php"><i class="fas fa-cog me-2"></i> Configuraciones</a>
        <a href="../Login/logout.php"><i class="fas fa-sign-out-alt me-2"></i> Cerrar Sesión</a>
    </div>

    <!-- Contenido -->
    <div class="content">
        <div class="page-header">
            <div class="page-header-left">
                <i class="fas fa-users"></i>
                <h1>Gestionar Usuarios</h1>
            </div>
        </div>

            <!-- INPUT DE BÚSQUEDA (busca mientras escribes) -->
            <div>
                <input id="buscarUsuario" class="form-control search-input" type="search" placeholder="Buscar" aria-label="Buscar usuarios">
            </div></br>

        <div class="card">
            <div class="card-header-custom">
                <i class="fas fa-list me-2"></i> Lista de Usuarios Registrados
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table id="tablaUsuarios" class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th></th>
                                <th>Usuario</th>
                                <th>Correo</th>
                                <th>Rol</th>
                                <th>Estado</th>
                                <th>Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($usuarios as $u): ?>
                                <tr>
                                    <td>
                                        <div class="avatar-circle">
                                            <?= strtoupper(substr($u['NOM_PRI_USU'], 0, 1) . substr($u['APE_PRI_USU'], 0, 1)) ?>
                                        </div>
                                    </td>
                                    <td>
                                        <strong><?= htmlspecialchars($u['NOM_PRI_USU'] . ' ' . $u['APE_PRI_USU']) ?></strong><br>
                                        <small class="text-muted"><?= htmlspecialchars($u['CED_USU']) ?></small>
                                    </td>
                                    <td><?= htmlspecialchars($u['COR_USU']) ?></td>
                                    <td><span class="badge bg-primary"><?= htmlspecialchars($u['NOM_ROL']) ?></span></td>
                                    <td>
                                        <?php if ($u['ACTIVO']): ?>
                                            <span class="badge badge-success">Activo</span>
                                        <?php else: ?>
                                            <span class="badge badge-danger">Inactivo</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <button class="btn-edit" data-bs-toggle="modal" data-bs-target="#editModal"
                                            onclick='cargarUsuario(<?= htmlspecialchars(json_encode($u), ENT_QUOTES, 'UTF-8') ?>)'>
                                            <i class="fas fa-edit"></i> Editar
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Edición -->
    <div class="modal fade" id="editModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-user-edit me-2"></i> Editar Usuario</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="formEditarUsuario" method="POST" action="actualizar_usuario.php">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="cedula" class="form-label">Cédula</label>
                                <input type="text" class="form-control" id="cedula" name="cedula" readonly>
                            </div>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Primer Nombre *</label>
                                    <input type="text" class="form-control" name="nom_pri" id="nom_pri" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Segundo Nombre</label>
                                    <input type="text" class="form-control" name="nom_seg" id="nom_seg">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Primer Apellido *</label>
                                    <input type="text" class="form-control" name="ape_pri" id="ape_pri" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Segundo Apellido</label>
                                    <input type="text" class="form-control" name="ape_seg" id="ape_seg">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Correo *</label>
                                    <input type="email" class="form-control" name="correo" id="correo" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Teléfono</label>
                                    <input type="text" class="form-control" name="telefono" id="telefono" pattern="\d{10}">
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Dirección</label>
                                    <textarea class="form-control" name="direccion" id="direccion" rows="2"></textarea>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="fec_nac_usu" class="form-label">Fecha de Nacimiento</label>
                                    <input type="date" class="form-control" id="fec_nac_usu" name="fec_nac_usu">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Rol *</label>
                                    <select class="form-select" name="rol_id" id="rol_id" required>
                                        <?php foreach ($roles as $r): ?>
                                            <option value="<?= $r['ID_ROL'] ?>"><?= htmlspecialchars($r['NOM_ROL']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Carrera</label>
                                    <select class="form-select" name="carrera_id" id="carrera_id">
                                        <option value="">-- Sin carrera --</option>
                                        <?php foreach ($carreras as $c): ?>
                                            <option value="<?= $c['ID_CARRERA'] ?>"><?= htmlspecialchars($c['NOMBRE_CARRERA']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Nueva Contraseña</label>
                                    <input type="password" class="form-control" name="clave" id="clave" minlength="6">
                                    <small class="text-muted">Dejar en blanco para no cambiar</small>
                                </div>
                                <div class="col-md-6 d-flex align-items-end">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="activo" id="activo">
                                        <label class="form-check-label" for="activo">Usuario Activo</label>
                                    </div>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // ---------- CARGAR USUARIO EN EL MODAL ----------
        function cargarUsuario(usuario) {
            document.getElementById('cedula').value = usuario.CED_USU;
            document.getElementById('nom_pri').value = usuario.NOM_PRI_USU;
            document.getElementById('nom_seg').value = usuario.NOM_SEG_USU || '';
            document.getElementById('ape_pri').value = usuario.APE_PRI_USU;
            document.getElementById('ape_seg').value = usuario.APE_SEG_USU || '';
            document.getElementById('correo').value = usuario.COR_USU;
            document.getElementById('telefono').value = usuario.TEL_USU || '';
            document.getElementById('direccion').value = usuario.DIR_USU || '';
            document.getElementById('fec_nac_usu').value = usuario.FEC_NAC_USU || '';
            document.getElementById('rol_id').value = usuario.ID_ROL_USU;
            document.getElementById('carrera_id').value = usuario.ID_CARRERA_USU || '';
            document.getElementById('activo').checked = usuario.ACTIVO == 1;

            // limpiar campo contraseña
            var claveInput = document.getElementById('clave');
            if (claveInput) claveInput.value = '';

            // Mostrar modal (usar el id real del modal)
            var myModal = new bootstrap.Modal(document.getElementById('editModal'));
            myModal.show();
        }

        // ---------- BÚSQUEDA EN TIEMPO REAL (sin botón) ----------
        (function() {
            const inputBuscar = document.getElementById('buscarUsuario');
            const tabla = document.getElementById('tablaUsuarios');
            const tbody = tabla.querySelector('tbody');

            // debounce simple
            function debounce(fn, delay) {
                let t;
                return function(...args) {
                    clearTimeout(t);
                    t = setTimeout(() => fn.apply(this, args), delay);
                };
            }

            function filtrar(value) {
                const q = value.trim().toLowerCase();
                const filas = tbody.querySelectorAll('tr');

                if (!q) {
                    // mostrar todas
                    filas.forEach(tr => tr.style.display = '');
                    return;
                }

                filas.forEach(tr => {
                    // concatenar columnas relevantes: nombre + cedula + correo + rol
                    const nombre = (tr.cells[1]?.textContent || '').toLowerCase();
                    const correo = (tr.cells[2]?.textContent || '').toLowerCase();
                    const rol = (tr.cells[3]?.textContent || '').toLowerCase();
                    // incluye cédula que aparece en la segunda celda como texto pequeño
                    const combinado = `${nombre} ${correo} ${rol}`.replace(/\s+/g, ' ');
                    if (combinado.indexOf(q) !== -1) {
                        tr.style.display = '';
                    } else {
                        tr.style.display = 'none';
                    }
                });
            }

            const debouncedFiltrar = debounce(function(e) {
                filtrar(e.target.value);
            }, 200);

            if (inputBuscar) {
                inputBuscar.addEventListener('input', debouncedFiltrar);
            }
        })();

        // ---------- ENVÍO AJAX DEL FORMULARIO (mantengo tu lógica) ----------
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('formEditarUsuario');

            if (form) {
                form.addEventListener('submit', function(e) {
                    // detener la redirección predeterminada
                    e.preventDefault();

                    const formData = new FormData(this);

                    // asegurar que 'activo' se envía como 1 o 0
                    const activoCheckbox = document.getElementById('activo');
                    if (activoCheckbox && activoCheckbox.checked) {
                        formData.set('activo', 1);
                    } else {
                        formData.set('activo', 0);
                    }

                    fetch('actualizar_usuario.php', {
                            method: 'POST',
                            body: formData
                        })
                        .then(response => {
                            if (!response.ok) {
                                throw new Error('Respuesta de red fallida con estado: ' + response.status);
                            }
                            return response.json();
                        })
                        .then(data => {
                            if (data.success) {
                                alert("Éxito: " + data.message);
                                // recargar la página para ver cambios
                                window.location.reload();
                            } else {
                                alert("Error: " + data.message);
                            }
                        })
                        .catch(error => {
                            console.error('Error en la petición AJAX:', error);
                            alert("Hubo un error de conexión con el servidor o en el procesamiento. Revisa la consola.");
                        });
                });
            } else {
                console.error('Error: No se encontró el elemento con ID "formEditarUsuario".');
            }
        });
    </script>
</body>

</html>
