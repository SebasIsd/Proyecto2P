<?php
session_start();
if (!isset($_SESSION['correo'])) {
    header("Location: ../index.php");
    exit();
}

require_once '../includes/conexion.php';

$cedula = $_SESSION['cedula'];

// Filtros
$tipo = $_GET['tipo'] ?? '';              // Tipo de curso/evento
$buscar = trim($_GET['q'] ?? '');         // Nombre del evento
$modalidad = $_GET['modalidad'] ?? '';    // Presencial / Virtual
$mod_evento = $_GET['mod_evento'] ?? '';  // Gratis / Pagado

// Consulta base mejorada
$sql = "
    SELECT DISTINCT
        e.ID_EVE_CUR,
        e.TIT_EVE_CUR,
        e.DES_EVE_CUR,
        e.FEC_INI_EVE_CUR,
        e.FEC_FIN_EVE_CUR,
        e.MOD_EVE_CUR,
        e.COS_EVE_CUR,
        e.LUGAR,
        e.IMG_EVE_CUR,
        t.NOM_TIPO_EVE,
        e.CUPOS_DISPONIBLES,
        e.CAPACIDAD_MAXIMA,
        (e.CUPOS_DISPONIBLES > 0) AS tiene_cupos
    FROM EVENTOS_CURSOS e
    JOIN TIPOS_EVENTO t ON e.ID_TIPO_EVE = t.ID_TIPO_EVE
    LEFT JOIN EVENTOS_CARRERAS ec ON e.ID_EVE_CUR = ec.ID_EVE_CUR
    JOIN USUARIOS u ON u.CED_USU = ?
    WHERE e.ACTIVO = 1
      AND e.FEC_FIN_EVE_CUR >= CURDATE()
      AND (ec.ID_CARRERA = u.ID_CARRERA_USU OR ec.ID_CARRERA IS NULL)
";

$params = [$cedula];
$types = 's';

// 🔍 Filtro por nombre
if ($buscar) {
    $sql .= " AND e.TIT_EVE_CUR LIKE ?";
    $params[] = "%$buscar%";
    $types .= 's';
}

// 💰 Filtro por tipo de evento (Gratis/Pagado)
if ($mod_evento && in_array($mod_evento, ['Gratis', 'Pagado'])) {
    $sql .= " AND e.MOD_EVE_CUR = ?";
    $params[] = $mod_evento;
    $types .= 's';
}

// 📘 Filtro por tipo de curso/evento
if ($tipo) {
    $sql .= " AND e.ID_TIPO_EVE = ?";
    $params[] = $tipo;
    $types .= 'i';
}

// 🧑‍🏫 Filtro por modalidad (si aplica)
if ($modalidad && in_array($modalidad, ['Presencial', 'Virtual'])) {
    $sql .= " AND e.LUGAR LIKE ?";
    $params[] = "%$modalidad%";
    $types .= 's';
}


$sql .= " ORDER BY e.FEC_INI_EVE_CUR ASC";

$stmt = $conn->prepare($sql);
if ($params) $stmt->bind_param($types, ...$params);
$stmt->execute();
$eventos = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Tipos de evento (para el select)
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

    /* --- SIDEBAR --- */
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

    /* --- CONTENIDO PRINCIPAL --- */
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

    /* --- FORMULARIO DE BÚSQUEDA --- */
    .search-bar {
        background: white;
        border-radius: var(--radius);
        box-shadow: var(--shadow);
        padding: 25px 30px;
        margin-bottom: 35px;
        transition: all 0.3s ease;
    }

    .search-bar:hover {
        box-shadow: 0 10px 30px rgba(0,0,0,0.15);
    }

    .search-bar .form-control,
    .search-bar .form-select {
        border-radius: 12px;
        border: 2px solid #e9ecef;
        padding: 12px 16px;
        font-size: 0.95rem;
        transition: all 0.3s ease;
    }

    .search-bar .form-control:focus,
    .search-bar .form-select:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 0.2rem rgba(163,0,0,0.15);
    }

    .search-bar .btn-primary {
        background: var(--primary);
        border: none;
        padding: 10px 25px;
        font-weight: 600;
        border-radius: 10px;
        transition: all 0.3s;
    }

    .search-bar .btn-primary:hover {
        background: var(--primary-hover);
        transform: translateY(-1px);
    }

    .btn-clear {
        background: var(--gray-light);
        color: var(--dark);
        border: 1px solid #ccc;
        padding: 10px 25px;
        font-weight: 500;
        border-radius: 10px;
        transition: all 0.3s;
    }

    .btn-clear:hover {
        background: var(--primary-light);
        color: var(--primary);
        border-color: var(--primary);
    }

    /* --- TARJETAS DE EVENTOS --- */
    .event-card {
        background: white;
        border-radius: var(--radius);
        box-shadow: var(--shadow);
        overflow: hidden;
        transition: transform 0.2s;
    }

    .event-card:hover {
        transform: translateY(-5px);
    }

    .event-card .card-header-custom {
        background: var(--primary);
        color: white;
        padding: 16px 24px;
        font-weight: 600;
    }

    .event-card .card-body {
        padding: 24px;
    }

    .event-title {
        font-size: 1.1rem;
        font-weight: 600;
        margin-bottom: 8px;
    }

    .event-meta {
        color: var(--gray);
        font-size: 0.9rem;
        margin-bottom: 12px;
    }

    .badge-cupos {
        font-size: 0.75rem;
        padding: 6px 12px;
        border-radius: 20px;
    }

    .badge-disponible { background: #d4edda; color: #155724; }
    .badge-agotado { background: #f8d7da; color: #721c24; }

    .btn-inscribir {
        background: var(--primary);
        color: white;
        border: none;
        padding: 10px 18px;
        border-radius: 10px;
        font-weight: 600;
        transition: all 0.3s;
        display: inline-flex;
        align-items: center;
        gap: 5px;
        text-decoration: none;
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

    /* --- RESPONSIVE --- */
    @media (max-width: 992px) {
        .search-bar form .col-md-3,
        .search-bar form .col-md-2 {
            flex: 1 1 100%;
        }
    }

    @media (max-width: 768px) {
        .sidebar { width: 80px; }
        .sidebar .logo img { width: 50px; }
        .sidebar a span { display: none; }
        .sidebar a { padding: 16px; justify-content: center; }
        .sidebar a:hover { padding-left: 16px; }
        .content { margin-left: 80px; padding: 20px; }
        .event-item { flex-direction: column; align-items: flex-start; gap: 12px; }
        .event-card .card-body .d-flex { flex-direction: column; gap: 10px; }
        .btn-inscribir, .btn-detalles { width: 100%; justify-content: center; }
    }
    /* --- Ajuste visual para botones del buscador --- */
    #filtros-form .d-flex button {
    flex: 1;
    font-size: 0.95rem;
    }

    #filtros-form .btn-primary i,
    #filtros-form .btn-clear i {
    margin-right: 6px;
    }
    .event-card {
        transition: all 0.3s ease;
        border-radius: 16px !important;
    }
    .card-img-top {
        transition: all 0.3s ease;
    }
    .event-card:hover .card-img-top {
        transform: scale(1.05);
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
                <br> <br> <br><br><br><br><br><br><br><br><br>  <br><br><br>
        <hr>
          <a href="https://sdsnt2003.atlassian.net/servicedesk/customer/portal/102" target="_blank"><i class="fa fa-exclamation-triangle" aria-hidden="true"></i>Encontraste un fallo?</a>
    </div>
    <div class="content">
        <div class="page-header">
            <i class="fas fa-search"></i>
            <h1>Buscar Eventos</h1>
        </div>

        <div class="search-bar">
            <form class="row g-2 mb-4" method="get">
                <div class="col-md-3">
                    <input type="text" class="form-control" name="q" placeholder="🔍 Buscar por nombre..." value="<?= htmlspecialchars($buscar) ?>">
                </div>
                <div class="col-md-2">
                    <select class="form-select" name="mod_evento">
                        <option value="">Tipo de evento</option>
                        <option value="Gratis" <?= $mod_evento=='Gratis'?'selected':'' ?>>Gratis</option>
                        <option value="Pagado" <?= $mod_evento=='Pagado'?'selected':'' ?>>Pagado</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select class="form-select" name="modalidad">
                        <option value="">Modalidad</option>
                        <option value="Presencial" <?= $modalidad=='Presencial'?'selected':'' ?>>Presencial</option>
                        <option value="Virtual" <?= $modalidad=='Virtual'?'selected':'' ?>>Virtual</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select class="form-select" name="tipo">
                        <option value="">Tipo de curso</option>
                        <?php foreach ($tipos as $t): ?>
                            <option value="<?= $t['ID_TIPO_EVE'] ?>" <?= $tipo==$t['ID_TIPO_EVE']?'selected':'' ?>>
                                <?= htmlspecialchars($t['NOM_TIPO_EVE']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button class="btn btn-primary" type="submit">
                        <i class="fa fa-search"></i> Buscar
                    </button>
                    <button type="button" class="btn-clear" onclick="window.location='buscar_eventos.php'">
                        <i class="fa fa-eraser"></i> Limpiar
                    </button>
                </div>
            </form>

        </div>

        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 row-cols-xl-4 g-4">
            <?php foreach ($eventos as $e): 
                // Imagen por defecto si no hay
                $imagen = !empty($e['IMG_EVE_CUR']) ? '../' . htmlspecialchars($e['IMG_EVE_CUR']) : '../images/default-event.jpg'; // Crea una imagen por defecto si quieres
                $fecha = date('d/m/Y', strtotime($e['FEC_INI_EVE_CUR']));
                if ($e['FEC_FIN_EVE_CUR']) $fecha .= ' - ' . date('d/m/Y', strtotime($e['FEC_FIN_EVE_CUR']));
                
                $cupos_texto = $e['tiene_cupos'] ? "<span class='text-success fw-bold'>{$e['CUPOS_DISPONIBLES']} disponibles</span>" : "<span class='text-danger fw-bold'>Cupos agotados</span>";
                $modalidad = $e['MOD_EVE_CUR'] == 'Pagado' ? "Pagado - \${$e['COS_EVE_CUR']}" : 'Gratis';

                // Obtener requisitos para este evento
                $requisitos_query = $conn->prepare("
                    SELECT r.ID_REQ, r.NOM_REQ, r.TIPO
                    FROM EVENTOS_REQUISITOS er
                    JOIN REQUISITOS r ON er.ID_REQ = r.ID_REQ
                    WHERE er.ID_EVE_CUR = ?
                ");
                $requisitos_query->bind_param("i", $e['ID_EVE_CUR']);
                $requisitos_query->execute();
                $requisitos = $requisitos_query->get_result()->fetch_all(MYSQLI_ASSOC);
                $requisitos_json = json_encode($requisitos);
            ?>
                <div class="col">
                        <div class="card h-100 shadow-sm border-0 overflow-hidden event-card">
                            <!-- Imagen con overlay -->
                            <div class="position-relative">
                                <img src="<?= $imagen ?>" class="card-img-top" alt="<?= htmlspecialchars($e['TIT_EVE_CUR']) ?>" style="height: 220px; object-fit: cover;">
                                <div class="card-img-overlay d-flex flex-column justify-content-between p-3" style="background: linear-gradient(to bottom, rgba(163,0,0,0.7) 0%, rgba(163,0,0,0.1) 100%);">
                                    <div>
                                        <h5 class="card-title text-white fw-bold mb-1"><?= htmlspecialchars($e['TIT_EVE_CUR']) ?></h5>
                                        <span class="badge bg-light text-dark"><?= htmlspecialchars($e['NOM_TIPO_EVE']) ?></span>
                                    </div>
                                </div>
                            </div>

                        <!-- Cuerpo de la card -->
                        <!-- Cuerpo de la card -->
                            <div class="card-body d-flex flex-column">
                                <p class="card-text text-muted flex-grow-1" style="font-size: 0.92rem;">
                                    <?= strlen($e['DES_EVE_CUR']) > 120 ? htmlspecialchars(substr($e['DES_EVE_CUR'], 0, 120)) . '...' : htmlspecialchars($e['DES_EVE_CUR']) ?>
                                </p>

                                <ul class="list-unstyled small text-muted mt-2">
                                    <li><i class="fas fa-calendar-alt text-primary me-2"></i><?= $fecha ?></li>
                                    <li><i class="fas fa-map-marker-alt text-primary me-2"></i><?= htmlspecialchars($e['LUGAR']) ?></li>
                                    <li><i class="fas fa-users text-primary me-2"></i><?= $cupos_texto ?> de <?= $e['CAPACIDAD_MAXIMA'] ?></li>
                                    <li><i class="fas fa-tag text-primary me-2"></i><?= $modalidad ?></li>
                                </ul>

                            <div class="mt-auto pt-3">
                                <div class="btn-group w-100" role="group">
                                    <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#detalleModal"
                                        data-title="<?= htmlspecialchars($e['TIT_EVE_CUR']) ?>"
                                        data-type="<?= htmlspecialchars($e['NOM_TIPO_EVE']) ?>"
                                        data-start="<?= date('d/m/Y', strtotime($e['FEC_INI_EVE_CUR'])) ?>"
                                        data-end="<?= $e['FEC_FIN_EVE_CUR'] ? date('d/m/Y', strtotime($e['FEC_FIN_EVE_CUR'])) : '' ?>"
                                        data-capacity="<?= $e['CAPACIDAD_MAXIMA'] ?>"
                                        data-available="<?= $e['CUPOS_DISPONIBLES'] ?>"
                                        data-description="<?= htmlspecialchars($e['DES_EVE_CUR']) ?>"
                                        data-id="<?= $e['ID_EVE_CUR'] ?>"
                                        data-modalidad="<?= $e['MOD_EVE_CUR'] == 'Pagado' ? 'Pagado ($' . number_format($e['COS_EVE_CUR'], 2) . ')' : 'Gratis' ?>"
                                        data-requisitos='<?= $requisitos_json ?>'>
                                        <i class="fas fa-info-circle"></i> Detalles
                                    </button>
                                    <?php if ($e['tiene_cupos']): ?>
                                        <a href="detalle_evento.php?id=<?= $e['ID_EVE_CUR'] ?>" class="btn btn-danger btn-sm">
                                            <i class="fas fa-user-plus"></i> Inscribirme
                                        </a>
                                    <?php else: ?>
                                        <button class="btn btn-secondary btn-sm" disabled>Cupos agotados</button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <?php if (empty($eventos)): ?>
            <div class="text-center py-5">
                <i class="fas fa-search fa-3x text-muted mb-3"></i>
                <h4 class="text-muted">No se encontraron eventos</h4>
                <p class="text-muted">Prueba con otros filtros o busca más tarde.</p>
            </div>
        <?php endif; ?>
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
                    list += `<li><strong>${r.NOM_REQ}</strong></li>`;
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