<?php
session_start();

// Verificar que sea administrador
if (!isset($_SESSION['correo']) || strtolower($_SESSION['rol_nombre']) !== 'administrador') {
    header("Location: ../index.php");
    exit();
}

require_once '../includes/conexion.php';

// Cargar lista de eventos
$eventos = $conn->query("
    SELECT e.ID_EVE_CUR, e.TIT_EVE_CUR, e.FEC_INI_EVE_CUR, t.NOM_TIPO_EVE, e.ACTIVO
    FROM EVENTOS_CURSOS e
    JOIN TIPOS_EVENTO t ON e.ID_TIPO_EVE = t.ID_TIPO_EVE
    ORDER BY e.FEC_INI_EVE_CUR DESC
")->fetch_all(MYSQLI_ASSOC);

// Cargar catálogos para modal de edición (si necesitas editar)
$tipos = $conn->query("SELECT ID_TIPO_EVE, NOM_TIPO_EVE FROM TIPOS_EVENTO ORDER BY NOM_TIPO_EVE")->fetch_all(MYSQLI_ASSOC);
$requisitos = $conn->query("SELECT ID_REQ, NOM_REQ FROM REQUISITOS ORDER BY NOM_REQ")->fetch_all(MYSQLI_ASSOC);
$carreras = $conn->query("SELECT ID_CARRERA, NOMBRE_CARRERA FROM TIPOS_CARRERA ORDER BY NOMBRE_CARRERA")->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionar Eventos - UTA</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
            justify-content: space-between;
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

        .btn-primary {
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

        .btn-primary:hover {
            background: var(--primary-hover);
            transform: translateY(-1px);
            box-shadow: 0 4px 10px rgba(163,0,0,0.2);
        }

        .btn-edit {
            background: transparent;
            color: var(--primary);
            border: 1px solid var(--primary);
            padding: 6px 12px;
            border-radius: 10px;
            transition: all 0.3s;
        }

        .btn-edit:hover {
            background: var(--primary);
            color: white;
        }

        .btn-delete {
            background: transparent;
            color: #dc3545;
            border: 1px solid #dc3545;
            padding: 6px 12px;
            border-radius: 10px;
            transition: all 0.3s;
        }

        .btn-delete:hover {
            background: #dc3545;
            color: white;
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

    <!-- Sidebar -->
    <div class="sidebar">
        <div class="logo">
            <img src="../images/usu/logouta.jpg" alt="Logo UTA">
        </div>
        <a href="admin_inicio.php"><i class="fas fa-home"></i> <span>Inicio</span></a>
        <a href="gestionar_eventos.php" class="active"><i class="fas fa-calendar-check"></i> <span>Eventos</span></a>
        <a href="editar_usuario.php"><i class="fas fa-users"></i> <span>Usuarios</span></a>
        <a href="perfil.php"><i class="fas fa-user"></i> <span>Mi Perfil</span></a>
        <a href="../Login/logout.php"><i class="fas fa-sign-out-alt"></i> <span>Cerrar Sesión</span></a>
    </div>

    <!-- Contenido -->
    <div class="content">
        <div class="page-header">
            <i class="fas fa-calendar-check"></i>
            <h1>Gestionar Eventos</h1>
            <a href="eventoNuevo.php" class="btn-primary">
                <i class="fas fa-plus"></i> Nuevo Evento
            </a>
        </div>

        <div class="card">
            <div class="card-header-custom">
                <i class="fas fa-list me-2"></i> Lista de Eventos
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Título</th>
                                <th>Fecha Inicio</th>
                                <th>Tipo</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($eventos)): ?>
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">No hay eventos registrados aún.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($eventos as $e): ?>
                                    <tr>
                                        <td><strong><?= htmlspecialchars($e['TIT_EVE_CUR']) ?></strong></td>
                                        <td><?= date('d/m/Y', strtotime($e['FEC_INI_EVE_CUR'])) ?></td>
                                        <td><?= htmlspecialchars($e['NOM_TIPO_EVE']) ?></td>
                                        <td>
                                            <?php if ($e['ACTIVO']): ?>
                                                <span class="badge badge-success">Activo</span>
                                            <?php else: ?>
                                                <span class="badge badge-danger">Inactivo</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <button class="btn-edit" onclick="editarEvento(<?= $e['ID_EVE_CUR'] ?>)">
                                                <i class="fas fa-edit"></i> Editar
                                            </button>
                                            <button class="btn-delete" onclick="eliminarEvento(<?= $e['ID_EVE_CUR'] ?>)">
                                                <i class="fas fa-trash"></i> Eliminar
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para Editar Evento (similar a eventoNuevo.php) -->
    <div class="modal fade" id="modalEditarEvento" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-edit me-2"></i> Editar Evento</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="formEditarEvento" method="POST" action="guardarEvento.php">
                    <div class="modal-body">
                        <input type="hidden" name="ID_EVE_CUR" id="editIdEvento">
                        <!-- Aquí pon el formulario de eventoNuevo.php adaptado -->
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label">Título *</label>
                                <input type="text" class="form-control" name="TIT_EVE_CUR" id="editTitulo" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Descripción</label>
                                <textarea class="form-control" name="DES_EVE_CUR" id="editDescripcion" rows="3"></textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Tipo de Evento *</label>
                                <select class="form-select" name="ID_TIPO_EVE" id="editTipoEvento" required onchange="cargarRequisitosEditar(this.value)">
                                    <?php foreach ($tipos as $t): ?>
                                        <option value="<?= $t['ID_TIPO_EVE'] ?>"><?= htmlspecialchars($t['NOM_TIPO_EVE']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Modalidad *</label>
                                <select class="form-select" name="MOD_EVE_CUR" id="editModalidad">
                                    <option value="Gratis">Gratis</option>
                                    <option value="Pagado">Pagado</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Costo</label>
                                <input type="number" class="form-control" name="COS_EVE_CUR" id="editCosto" min="0" step="0.01">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Horas Totales</label>
                                <input type="number" class="form-control" name="HORAS_TOTALES" id="editHoras" min="0">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Fecha Inicio Inscripción</label>
                                <input type="date" class="form-control" name="INSCRIPCION_DESDE" id="editInsDesde">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Fecha Fin Inscripción</label>
                                <input type="date" class="form-control" name="INSCRIPCION_HASTA" id="editInsHasta">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Fecha Inicio Evento *</label>
                                <input type="date" class="form-control" name="FEC_INI_EVE_CUR" id="editFecInicio" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Fecha Fin Evento *</label>
                                <input type="date" class="form-control" name="FEC_FIN_EVE_CUR" id="editFecFin" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Lugar</label>
                                <input type="text" class="form-control" name="LUGAR" id="editLugar">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Detalle Ubicación</label>
                                <input type="text" class="form-control" name="UBICACION_DETALLE" id="editDetalle">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Capacidad Máxima *</label>
                                <input type="number" class="form-control" name="CAPACIDAD_MAXIMA" id="editCapacidad" min="0" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Cupos Disponibles *</label>
                                <input type="number" class="form-control" name="CUPOS_DISPONIBLES" id="editCupos" min="0" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Responsable (Cédula)</label>
                                <input type="text" class="form-control" name="RESPONSABLE_CED" id="editResponsable" pattern="\d{10}" placeholder="0101234567">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Requisitos</label>
                                <div id="editRequisitos" class="d-flex flex-wrap gap-2">
                                    <!-- Cargados dinámicamente -->
                                </div>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Carreras</label>
                                <div class="d-flex flex-wrap gap-2">
                                    <?php foreach ($carreras as $c): ?>
                                        <label class="req-chip">
                                            <input type="checkbox" name="CARRERAS[]" value="<?= $c['ID_CARRERA'] ?>" class="carCheck">
                                            <?= htmlspecialchars($c['NOMBRE_CARRERA']) ?>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn-save-modal">
                            <i class="fas fa-save me-2"></i> Guardar Cambios
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        async function cargarRequisitosEditar(idTipo) {
            const cont = document.getElementById('editRequisitos');
            cont.innerHTML = '';
            try {
                const res = await fetch(`obtenerRequisitosPorTipo.php?id_tipo=${idTipo}`);
                const reqs = await res.json();
                reqs.forEach(r => {
                    const label = document.createElement('label');
                    label.className = 'req-chip';
                    label.innerHTML = `
                        <input type="checkbox" name="REQUISITOS[]" value="${r.ID_REQ}" class="reqCheck">
                        ${r.NOM_REQ}
                    `;
                    cont.appendChild(label);
                });
            } catch (err) {
                console.error('Error al cargar requisitos:', err);
            }
        }

        function editarEvento(id) {
            // Aquí carga datos del evento via AJAX y llena el modal
            // Por ejemplo:
            // fetch(`obtenerEvento.php?id=${id}`).then(res => res.json()).then(data => {
            //     document.getElementById('editIdEvento').value = data.ID_EVE_CUR;
            //     document.getElementById('editTitulo').value = data.TIT_EVE_CUR;
            //     // ... llenar todos los campos
            //     // Para requisitos y carreras, checkear los seleccionados
            //     cargarRequisitosEditar(data.ID_TIPO_EVE);
            //     // etc.
            // });
            // document.getElementById('modalEditarEvento').modal('show');
            alert('Función de edición para ID: ' + id + ' (Implementa AJAX para cargar datos)');
            const modal = new bootstrap.Modal(document.getElementById('modalEditarEvento'));
            modal.show();
        }

        function eliminarEvento(id) {
            if (confirm('¿Seguro que quieres eliminar este evento?')) {
                // fetch(`eliminarEvento.php?id=${id}`).then(() => location.reload());
                alert('Evento eliminado ID: ' + id);
            }
        }
    </script>
</body>
</html>