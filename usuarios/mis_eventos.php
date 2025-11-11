<?php
session_start();
if (!isset($_SESSION['correo'])) {
    header("Location: ../index.php");
    exit();
}

require_once '../includes/conexion.php';

$cedula = $_SESSION['cedula'];

// Cargar eventos inscritos con detalles
$eventos = $conn->query("
    SELECT 
        e.ID_EVE_CUR,
        e.TIT_EVE_CUR,
        e.FEC_INI_EVE_CUR,
        e.FEC_FIN_EVE_CUR,
        e.REQUIERE_ASISTENCIA,
        e.MOD_EVE_CUR,
        e.COS_EVE_CUR,
        t.NOM_TIPO_EVE,
        i.ESTADO_INS,
        i.EST_PAG_INS,
        p.URL_COMPROBANTE AS COMPROBANTE_PAGO,
        i.FEC_INI_INS
    FROM INSCRIPCIONES i
    JOIN EVENTOS_CURSOS e ON i.ID_EVE_CUR = e.ID_EVE_CUR
    JOIN TIPOS_EVENTO t ON e.ID_TIPO_EVE = t.ID_TIPO_EVE
    LEFT JOIN PAGOS p ON i.ID_INS = p.ID_INS
    WHERE i.CED_USU = '$cedula'
    ORDER BY i.FEC_INI_INS DESC
")->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Eventos - UTA</title>
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
        .badge-preinscrito { background: #fff3cd; color: #856404; }
        .badge-confirmado { background: #d4edda; color: #155724; }
        .badge-cancelado { background: #f8d7da; color: #721c24; }
        .badge-asistio { background: #d1ecf1; color: #0c5460; }

        .btn-ver {
            background: var(--primary);
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 10px;
            font-size: 0.85rem;
            transition: all 0.3s;
            display: inline-flex;   
            align-items: center;
            gap: 6px;
            text-decoration: none;
        }

        .btn-ver:hover {
            background: var(--primary-hover);
            transform: translateY(-1px);
        }

        .btn-cancelar {
            background: transparent;
            color: #dc3545;
            border: 1px solid #dc3545;
            padding: 6px 12px;
            border-radius: 10px;
            font-size: 0.8rem;
            transition: all 0.3s;
        }

        .btn-cancelar:hover {
            background: #dc3545;
            color: white;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: var(--gray);
        }

        .empty-state i {
            font-size: 3.5rem;
            color: #ccc;
            margin-bottom: 20px;
        }

        @media (max-width: 768px) {
            .sidebar { width: 80px; }
            .sidebar .logo img { width: 50px; }
            .sidebar a span { display: none; }
            .sidebar a { padding: 16px; justify-content: center; }
            .sidebar a:hover { padding-left: 16px; }
            .content { margin-left: 80px; padding: 20px; }
            .event-item { flex-direction: column; align-items: flex-start; gap: 12px; }
        }
        .event-item {
            background: white;
            padding: 20px;
            border-radius: 16px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            margin-bottom: 20px;
            transition: all 0.2s;
        }

        .event-item:last-child {
            border-bottom: none;
        }

        .event-info h5 {
            margin: 0 0 6px;
            font-size: 1.1rem;
            color: var(--dark);
            font-weight: 600;
        }

        .event-info small {
            color: var(--gray);
            font-size: 0.9rem;
        }

        .event-badge {
            font-size: 0.75rem;
            padding: 5px 10px;
            border-radius: 20px;
            font-weight: 600;
        }

        .event-item {
            background: white;
            padding: 20px;
            border-radius: 16px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            margin-bottom: 20px;
            transition: all 0.2s;
        }

        .requisitos-list {
            margin: 0;
            padding-left: 20px;
            font-size: 0.9rem;
        }

        .btn-ver-comprobante {
            font-size: 0.85rem;
        }

        .content {
            margin-left: 260px;
            padding: 40px;
        }
    </style>
</head>
<body>

    <!-- Sidebar -->
    <div class="sidebar">
        <div class="logo">
            <img src="../images/favico.png" alt="Logo UTA">
        </div>
        <a href="usuarios_inicio.php"><i class="fas fa-home"></i> <span>Inicio</span></a>
        <a href="mis_eventos.php" class="active"><i class="fas fa-calendar-alt"></i> <span>Mis Eventos</span></a>
        <a href="buscar_eventos.php"><i class="fas fa-search"></i> <span>Buscar Eventos</span></a>
        <a href="perfil_usuario.php"><i class="fas fa-user"></i> <span>Perfil</span></a>
        <a href="../Login/logout.php"><i class="fas fa-sign-out-alt"></i> <span>Cerrar Sesión</span></a>
    </div>

 <!-- Contenido -->
    <div class="content">
        <div class="page-header">
            <h2 class="text-dark"><i class="fas fa-calendar-check"></i> Mis Eventos</h2>
        </div>

        <?php if (empty($eventos)): ?>
            <div class="text-center py-5">
                <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                <p class="text-muted">No estás inscrito en ningún evento.</p>
            </div>
        <?php else: ?>
            <?php foreach ($eventos as $e): ?>
                <?php 
                // Cargar requisitos del evento
                $reqs = $conn->query("
                    SELECT r.NOM_REQ, r.TIPO 
                    FROM EVENTOS_REQUISITOS er
                    JOIN REQUISITOS r ON er.ID_REQ = r.ID_REQ
                    WHERE er.ID_EVE_CUR = {$e['ID_EVE_CUR']}
                ")->fetch_all(MYSQLI_ASSOC);

                // Cargar evidencias subidas por el usuario
                $evidencias = $conn->query("
                    SELECT r.NOM_REQ, r.TIPO, e.VALOR_TEXTO, e.NOMBRE_ARCHIVO, e.URL_ARCHIVO, e.TIPO_MIME
                    FROM EVIDENCIAS e
                    JOIN REQUISITOS r ON e.ID_REQ = r.ID_REQ
                    JOIN INSCRIPCIONES i ON e.ID_INS = i.ID_INS
                    WHERE i.ID_EVE_CUR = {$e['ID_EVE_CUR']} AND i.CED_USU = '$cedula'
                ")->fetch_all(MYSQLI_ASSOC);
                ?>

                <div class="event-item">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="event-info flex-grow-1">
                            <h5 class="event-title"><?= htmlspecialchars($e['TIT_EVE_CUR']) ?></h5>

                            <div class="event-meta">
                                <i class="fas fa-calendar"></i> 
                                <?= date('d/m/Y', strtotime($e['FEC_INI_EVE_CUR'])) ?>
                                <?php if ($e['FEC_FIN_EVE_CUR'] && $e['FEC_FIN_EVE_CUR'] !== $e['FEC_INI_EVE_CUR']): ?>
                                    al <?= date('d/m/Y', strtotime($e['FEC_FIN_EVE_CUR'])) ?>
                                <?php endif; ?>
                                • <?= htmlspecialchars($e['NOM_TIPO_EVE']) ?>
                                • <strong>
                                    <?= $e['MOD_EVE_CUR'] == 'Pagado' 
                                        ? 'Pagado ($' . number_format($e['COS_EVE_CUR'], 2) . ')' 
                                        : 'Gratis' ?>
                                </strong>
                            </div>

                            <div class="event-meta">
                                <strong>Estado:</strong> 
                                <span class="badge bg-primary"><?= ucfirst($e['ESTADO_INS']) ?></span>
                                <?php if ($e['ESTADO_INS'] == 'Preinscrito'): ?>
                                    <span class="text-muted">
                                        (Esperando aprobación 
                                        <?= $e['MOD_EVE_CUR'] == 'Pagado' ? 'y verificación de pago' : 'del docente/admin' ?>)
                                    </span>
                                <?php endif; ?>
                            </div>

                            <!-- REQUISITOS -->
                            <div class="mt-2">
                                <strong>Requisitos:</strong>
                                <?php if (empty($reqs)): ?>
                                    <p class="text-muted mb-0">No requiere requisitos adicionales.</p>
                                <?php else: ?>
                                    <ul class="ps-3 mb-0">
                                        <?php foreach ($reqs as $r): ?>
                                            <li><?= htmlspecialchars($r['NOM_REQ']) ?> (<?= $r['TIPO'] ?>)</li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php endif; ?>
                            </div>

                            <!-- BOTÓN VER DOCUMENTOS -->
                            <?php if ($e['COMPROBANTE_PAGO'] || !empty($evidencias)): ?>
                                <div class="mt-3">
                                    <button class="btn btn-sm btn-outline-primary" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#documentosModal"
                                            onclick="cargarDocumentos(<?= $e['ID_EVE_CUR'] ?>, '<?= addslashes($e['TIT_EVE_CUR']) ?>', '<?= $e['COMPROBANTE_PAGO'] ?>', <?= json_encode($evidencias) ?>)">
                                        <i class="fas fa-folder-open"></i> Ver Documentos
                                    </button>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="d-flex flex-column gap-2">
                            <a href="detalle_evento.php?id=<?= $e['ID_EVE_CUR'] ?>" class="btn btn-sm btn-primary">
                                <i class="fas fa-eye"></i> Detalles
                            </a>

                            <?php if ($e['REQUIERE_ASISTENCIA'] && $e['ESTADO_INS'] == 'Inscrito'
                                && strtotime($e['FEC_INI_EVE_CUR']) <= time()
                                && strtotime($e['FEC_FIN_EVE_CUR']) >= time()): ?>
                                <button class="btn btn-sm btn-success" onclick="registrarAsistencia(<?= $e['ID_EVE_CUR'] ?>)">
                                    <i class="fas fa-check"></i> Registrar Asistencia
                                </button>
                            <?php endif; ?>

                            <?php if (in_array($e['ESTADO_INS'], ['Preinscrito', 'Inscrito'])): ?>
                                <button class="btn btn-sm btn-danger" onclick="cancelarInscripcion(<?= $e['ID_EVE_CUR'] ?>)">
                                    <i class="fas fa-times"></i> Cancelar
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- MODAL DE DOCUMENTOS -->
    <div class="modal fade" id="documentosModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="modalTituloDocs">Documentos</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="modalContenidoDocs">
                    <!-- Se llena con JS -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function registrarAsistencia(id) {
            if (!confirm('¿Registrar asistencia?')) return;
            fetch('registrar_asistencia.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'id_evento=' + id
            })
            .then(r => r.json())
            .then(d => { alert(d.message); if (d.success) location.reload(); })
            .catch(() => alert('Error de conexión'));
        }

        function cancelarInscripcion(id) {
            if (!confirm('¿Cancelar inscripción?')) return;
            fetch('cancelar_inscripcion.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'id_evento=' + id
            })
            .then(r => r.json())
            .then(d => { alert(d.message); if (d.success) location.reload(); })
            .catch(() => alert('Error de conexión'));
        }

        function cargarDocumentos(id_evento, titulo, comprobante, evidencias) {
            const modalTitulo = document.getElementById('modalTituloDocs');
            const modalBody = document.getElementById('modalContenidoDocs');
            
            modalTitulo.textContent = `Documentos - ${titulo}`;
            let html = '';

            // COMPROBANTE
            if (comprobante) {
                const path = '../uploads/comprobantes/' + comprobante;
                const ext = comprobante.split('.').pop().toLowerCase();
                html += `<div class="mb-4 p-3 border rounded bg-light">
                    <h6 class="text-primary"><i class="fas fa-file-invoice-dollar"></i> Comprobante de Pago</h6>`;
                if (['jpg', 'jpeg', 'png'].includes(ext)) {
                    html += `<canvas id="canvas-comprobante" class="img-fluid" style="max-height: 500px;"></canvas>
                             <script>
                                 const img = new Image();
                                 img.src = '${path}';
                                 img.onload = () => {
                                     const canvas = document.getElementById('canvas-comprobante');
                                     const ctx = canvas.getContext('2d');
                                     const maxW = 700;
                                     canvas.width = Math.min(img.width, maxW);
                                     canvas.height = canvas.width * (img.height / img.width);
                                     ctx.drawImage(img, 0, 0, canvas.width, canvas.height);
                                 };
                             <\/script>`;
                } else if (ext === 'pdf') {
                    html += `<embed src="${path}" type="application/pdf" width="100%" height="600px" />`;
                }
                html += `</div>`;
            }

            // REQUISITOS
            if (evidencias && evidencias.length > 0) {
                html += `<h6 class="text-success mb-3"><i class="fas fa-clipboard-list"></i> Requisitos Entregados</h6>`;
                evidencias.forEach(ev => {
                    html += `<div class="mb-3 p-3 border rounded">
                        <strong>${ev.NOM_REQ} (${ev.TIPO})</strong><br>`;
                    if (ev.TIPO === 'TEXTO_CORTO' && ev.VALOR_TEXTO) {
                        html += `<p class="mb-0">"${ev.VALOR_TEXTO}"</p>`;
                    } else if (ev.NOMBRE_ARCHIVO) {
                        const path = '../uploads/requisitos/' + ev.NOMBRE_ARCHIVO;
                        const ext = ev.NOMBRE_ARCHIVO.split('.').pop().toLowerCase();
                        if (['jpg', 'jpeg', 'png'].includes(ext)) {
                            const canvasId = 'canvas-req-' + ev.ID_REQ;
                            html += `<canvas id="${canvasId}" class="img-fluid" style="max-height: 400px;"></canvas>
                                     <script>
                                         const img${ev.ID_REQ} = new Image();
                                         img${ev.ID_REQ}.src = '${path}';
                                         img${ev.ID_REQ}.onload = () => {
                                             const c = document.getElementById('${canvasId}');
                                             const ctx = c.getContext('2d');
                                             const maxW = 600;
                                             c.width = Math.min(img${ev.ID_REQ}.width, maxW);
                                             c.height = c.width * (img${ev.ID_REQ}.height / img${ev.ID_REQ}.width);
                                             ctx.drawImage(img${ev.ID_REQ}, 0, 0, c.width, c.height);
                                         };
                                     <\/script>`;
                        } else if (ext === 'pdf') {
                            html += `<embed src="${path}" type="application/pdf" width="100%" height="500px" class="border rounded" />`;
                        }
                    } else {
                        html += `<span class="text-muted">No entregado</span>`;
                    }
                    html += `</div>`;
                });
            } else {
                html += `<p class="text-muted">No se han subido requisitos.</p>`;
            }

            modalBody.innerHTML = html;
        }
    </script>
</body>
</html>