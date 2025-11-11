<?php
session_start();
if (!isset($_SESSION['correo'])) {
    header("Location: ../index.php");
    exit();
}

require_once '../includes/conexion.php';

$cedula = $_SESSION['cedula'];

// Filtros
$tipo = $_GET['tipo'] ?? '';
$buscar = trim($_GET['q'] ?? '');

// Consulta base
$sql = "
    SELECT 
        e.ID_EVE_CUR,
        e.TIT_EVE_CUR,
        e.DES_EVE_CUR,
        e.FEC_INI_EVE_CUR,
        e.FEC_FIN_EVE_CUR,
        e.MOD_EVE_CUR,
        e.COS_EVE_CUR,
        t.NOM_TIPO_EVE,
        e.CUPOS_DISPONIBLES,
        e.CAPACIDAD_MAXIMA,
        (e.CUPOS_DISPONIBLES > 0) AS tiene_cupos
    FROM EVENTOS_CURSOS e
    JOIN TIPOS_EVENTO t ON e.ID_TIPO_EVE = t.ID_TIPO_EVE
    WHERE e.ACTIVO = 1
      AND e.FEC_FIN_EVE_CUR >= CURDATE()
";

$params = [];
$types = '';

if ($tipo) {
    $sql .= " AND e.ID_TIPO_EVE = ?";
    $params[] = $tipo;
    $types .= 'i';
}
if ($buscar) {
    $sql .= " AND e.TIT_EVE_CUR LIKE ?";
    $params[] = "%$buscar%";
    $types .= 's';
}

$sql .= " ORDER BY e.FEC_INI_EVE_CUR ASC";

$stmt = $conn->prepare($sql);
if ($params) $stmt->bind_param($types, ...$params);
$stmt->execute();
$eventos = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Tipos para filtro
$tipos = $conn->query("SELECT ID_TIPO_EVE, NOM_TIPO_EVE FROM TIPOS_EVENTO ORDER BY NOM_TIPO_EVE")->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buscar Eventos - UTA</title>
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

        /* CARD GENERAL (Mantengo el estilo original si se usa) */
        .card {
            background: white;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            overflow: hidden;
            transition: transform 0.2s;
            margin-bottom: 20px;
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

        .card-body-custom {
            padding: 25px;
        }

        /* --- Estilos específicos de evento --- */

        .btn-inscribir {
            background: var(--primary);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s;
            text-decoration: none; /* Asegura que el <a> se vea como botón */
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        .btn-inscribir:hover { background: var(--primary-hover); transform: translateY(-1px); color: white; }
        .btn-inscribir.disabled { background: #ccc; cursor: not-allowed; pointer-events: none; color: #666; }

        .btn-detalles {
            background: #fff;
            color: var(--primary);
            border: 1px solid var(--primary);
            padding: 10px 15px;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s;
        }
        .btn-detalles:hover {
            background: var(--primary-light);
            color: var(--primary-hover);
            border-color: var(--primary-hover);
        }

        .event-card { background: white; border-radius: var(--radius); box-shadow: var(--shadow); overflow: hidden; transition: transform 0.2s; }
        .event-card:hover { transform: translateY(-5px); }
        .event-card .card-header-custom { background: var(--primary); color: white; padding: 16px 24px; font-weight: 600; }
        .event-card .card-body { padding: 24px; }
        .event-title { font-size: 1.2rem; font-weight: 600; margin-bottom: 8px; }
        .event-meta { color: var(--gray); font-size: 0.9rem; margin-bottom: 12px; }
        .badge-cupos { font-size: 0.75rem; padding: 5px 10px; border-radius: 20px; }
        .badge-disponible { background: #d4edda; color: #155724; }
        .badge-agotado { background: #f8d7da; color: #721c24; }

        .search-bar { background: white; border-radius: var(--radius); box-shadow: var(--shadow); padding: 20px; margin-bottom: 30px; }
        .search-bar .form-control, .search-bar .form-select { border-radius: 12px; border: 2px solid #e9ecef; padding: 12px 16px; }
        .search-bar .form-control:focus { border-color: var(--primary); box-shadow: 0 0 0 0.2rem rgba(163,0,0,0.15); }

        @media (max-width: 768px) {
            .sidebar { width: 80px; }
            .sidebar .logo img { width: 50px; }
            .sidebar a span { display: none; }
            .sidebar a { padding: 16px; justify-content: center; }
            .sidebar a:hover { padding-left: 16px; }
            .content { margin-left: 80px; padding: 20px; }
            .event-item { flex-direction: column; align-items: flex-start; gap: 12px; }
            .event-card .card-body .d-flex { flex-direction: column; gap: 10px; }
            .btn-inscribir, .btn-detalles { width: 100%; justify-content: center;}
        }
    </style>
</head>
<body>

    <div class="sidebar">
        <div class="logo">
            <img src="../images/favico.png" alt="Logo UTA">
        </div>
        <a href="usuarios_inicio.php"><i class="fas fa-home"></i> <span>Inicio</span></a>
        <a href="mis_eventos.php"><i class="fas fa-calendar-alt"></i> <span>Mis Eventos</span></a>
        <a href="buscar_eventos.php" class="active"><i class="fas fa-search"></i> <span>Buscar Eventos</span></a>
        <a href="perfil_usuario.php"><i class="fas fa-user"></i> <span>Perfil</span></a>
        <a href="../Login/logout.php"><i class="fas fa-sign-out-alt"></i> <span>Cerrar Sesión</span></a>
    </div>
    <div class="content">
        <div class="page-header">
            <i class="fas fa-search"></i>
            <h1>Buscar Eventos</h1>
        </div>

        <div class="search-bar">
            <form method="GET" class="row g-3">
                <div class="col-md-6">
                    <input type="text" class="form-control" name="q" placeholder="Buscar por título..." value="<?= htmlspecialchars($buscar) ?>">
                </div>
                <div class="col-md-4">
                    <select class="form-select" name="tipo">
                        <option value="">Todos los tipos</option>
                        <?php foreach ($tipos as $t): ?>
                            <option value="<?= $t['ID_TIPO_EVE'] ?>" <?= $tipo == $t['ID_TIPO_EVE'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($t['NOM_TIPO_EVE']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search"></i> Buscar
                    </button>
                </div>
            </form>
        </div>

        <div class="row">
            <?php if (empty($eventos)): ?>
                <div class="col-12 text-center py-5">
                    <i class="fas fa-calendar-times" style="font-size: 3rem; color: #ccc;"></i>
                    <h5 class="mt-3 text-muted">No se encontraron eventos</h5>
                </div>
            <?php else: ?>
                <?php foreach ($eventos as $e): ?>
                    <?php 
                    // Cargar requisitos del evento
                    $requisitos = $conn->query("
                        SELECT r.NOM_REQ, r.TIPO
                        FROM EVENTOS_REQUISITOS er
                        JOIN REQUISITOS r ON er.ID_REQ = r.ID_REQ
                        WHERE er.ID_EVE_CUR = {$e['ID_EVE_CUR']}
                        ORDER BY er.ORDEN
                    ")->fetch_all(MYSQLI_ASSOC);
                    ?>
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="event-card">
                            <div class="card-header-custom">
                                <?= htmlspecialchars($e['NOM_TIPO_EVE']) ?>
                            </div>
                            <div class="card-body">
                                <div class="event-title"><?= htmlspecialchars($e['TIT_EVE_CUR']) ?></div>
                                <div class="event-meta">
                                    <i class="fas fa-calendar"></i>
                                    <?= date('d/m/Y', strtotime($e['FEC_INI_EVE_CUR'])) ?>
                                    <?= $e['FEC_FIN_EVE_CUR'] && $e['FEC_FIN_EVE_CUR'] !== $e['FEC_INI_EVE_CUR'] ? ' al ' . date('d/m/Y', strtotime($e['FEC_FIN_EVE_CUR'])) : '' ?>
                                </div>
                                <div class="d-flex justify-content-between align-items-center gap-2">
                                    <span class="badge-cupos badge-<?= $e['tiene_cupos'] ? 'disponible' : 'agotado' ?>">
                                        <?= $e['CUPOS_DISPONIBLES'] ?> / <?= $e['CAPACIDAD_MAXIMA'] ?> cupos
                                    </span>
                                    
                                <button type="button" class="btn-detalles" data-bs-toggle="modal" data-bs-target="#detalleModal"
                                        data-title="<?= htmlspecialchars($e['TIT_EVE_CUR']) ?>"
                                        data-type="<?= htmlspecialchars($e['NOM_TIPO_EVE']) ?>"
                                        data-start="<?= date('d/m/Y', strtotime($e['FEC_INI_EVE_CUR'])) ?>"
                                        data-end="<?= $e['FEC_FIN_EVE_CUR'] ? date('d/m/Y', strtotime($e['FEC_FIN_EVE_CUR'])) : '' ?>"
                                        data-capacity="<?= $e['CAPACIDAD_MAXIMA'] ?>"
                                        data-available="<?= $e['CUPOS_DISPONIBLES'] ?>"
                                        data-description="<?= htmlspecialchars($e['DES_EVE_CUR']) ?>"
                                        data-id="<?= $e['ID_EVE_CUR'] ?>"
                                        data-modalidad="<?= $e['MOD_EVE_CUR'] == 'Pagado' ? 'Pagado ($' . number_format($e['COS_EVE_CUR'], 2) . ')' : 'Gratis' ?>"
                                        data-requisitos='<?= json_encode($requisitos) ?>'>
                                    <i class="fas fa-info-circle"></i> Detalles
                                </button>

                                    <a href="detalle_evento.php?id=<?= $e['ID_EVE_CUR'] ?>"
                                       class="btn-inscribir <?= !$e['tiene_cupos'] ? 'disabled' : '' ?>">
                                        <i class="fas fa-user-plus"></i> Inscribirme
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <div class="modal fade" id="detalleModal" tabindex="-1" aria-labelledby="detalleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content" style="border-radius: var(--radius);">
                <div class="modal-header" style="background-color: var(--primary); color: white; border-radius: var(--radius) var(--radius) 0 0;">
                    <h5 class="modal-title" id="detalleModalLabel">Detalles del Evento</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <h4 id="modal-title" class="mb-3" style="color: var(--dark);"></h4>
                    <p class="mb-1"><strong>Tipo:</strong> <span id="modal-type"></span></p>
                    <p class="mb-1"><strong>Fecha:</strong> <span id="modal-date"></span></p>
                    <p class="mb-1"><strong>Cupos:</strong> <span id="modal-cupos"></span></p>
                    <p class="mb-3"><strong>Modalidad:</strong> <span id="modal-modalidad" style="color: var(--primary); font-weight: 600;"></span></p>
                    
                    <h6 class="mt-4 mb-2" style="color: var(--primary);">Descripción:</h6>
                    <p id="modal-description" style="white-space: pre-wrap;"></p>

                    <h6 class="mt-4 mb-2" style="color: var(--primary);">Requisitos:</h6>
                    <div id="modal-requisitos"></div>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <a id="modal-inscribir-btn" class="btn-inscribir" href="#" style="padding: 8px 15px;">
                        <i class="fas fa-user-plus"></i> Ir a Inscribirme
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    const detalleModal = document.getElementById('detalleModal');
    if (detalleModal) {
        detalleModal.addEventListener('show.bs.modal', event => {
            const button = event.relatedTarget;
            
            const title = button.getAttribute('data-title');
            const type = button.getAttribute('data-type');
            const start = button.getAttribute('data-start');
            const end = button.getAttribute('data-end');
            const capacity = button.getAttribute('data-capacity');
            const available = button.getAttribute('data-available');
            const description = button.getAttribute('data-description');
            const id = button.getAttribute('data-id');
            const modalidad = button.getAttribute('data-modalidad');
            const requisitos = JSON.parse(button.getAttribute('data-requisitos') || '[]');
            const tieneCupos = parseInt(available) > 0;

            let dateString = start;
            if (end) dateString += ' al ' + end;

            document.getElementById('modal-title').textContent = title;
            document.getElementById('modal-type').textContent = type;
            document.getElementById('modal-date').textContent = dateString;
            document.getElementById('modal-cupos').textContent = `${available} disponibles de ${capacity} totales`;
            document.getElementById('modal-description').textContent = description;
            document.getElementById('modal-modalidad').textContent = modalidad;

            // Requisitos
            const reqContainer = document.getElementById('modal-requisitos');
            if (requisitos.length === 0) {
                reqContainer.innerHTML = '<p class="text-muted mb-0">No requiere requisitos adicionales.</p>';
            } else {
                let list = '<ul class="ps-3 mb-0" style="font-size: 0.95rem;">';
                requisitos.forEach(r => {
                    list += `<li><strong>${r.NOM_REQ}</strong> (${r.TIPO})</li>`;
                });
                list += '</ul>';
                reqContainer.innerHTML = list;
            }

            // Botón inscribir
            const inscribirBtn = document.getElementById('modal-inscribir-btn');
            inscribirBtn.href = `detalle_evento.php?id=${id}`;
            
            if (!tieneCupos) {
                inscribirBtn.classList.add('disabled');
                inscribirBtn.innerHTML = '<i class="fas fa-ban"></i> Cupos Agotados';
            } else {
                inscribirBtn.classList.remove('disabled');
                inscribirBtn.innerHTML = '<i class="fas fa-user-plus"></i> Ir a Inscribirme';
            }
        });
    }
</script>
</body>
</html>